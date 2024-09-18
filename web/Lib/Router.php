<?php
namespace Framework;

use Exception;
use Framework\Components\HostConfig;
use Framework\Components\Menu\AbstractMenuBuilder;
use Framework\Components\Menu\BaseMenu;
use Framework\Components\SiteSettings;
use Framework\Components\User;
use Framework\Controllers\Pages\AbstractPage;
use Framework\Helpers\Str;
use Framework\Locale\Translate;
use Framework\Models\Database\Db;
use Framework\Models\Session\Session;

class Router
{
    private string $page = '';

    private string $originalPage = '';

    private ?array $params = [];

	private AbstractMenuBuilder $menu;

	private User $user;

    private HostConfig $hostConfig;

	public function __construct(string $host = '', ?Psr4ClassAutoloader $autoLoader = null)
    {
        $this->hostConfig = HostConfig::create()->load(($host ?: $_SERVER['HTTP_HOST']));

        if($this->hostConfig->isShareSession()) {
            if(substr_count($this->hostConfig->getHost(), '.') > 1) {
                $mainDomain = substr($this->hostConfig->getHost(), strpos($this->hostConfig->getHost(), '.'), 100);
            }else{
                $mainDomain = '.' . $this->hostConfig->getHost();
            }

            session_set_cookie_params(0, '/', $mainDomain);
            ini_set('session.cookie_domain', $mainDomain);
        }

        if($this->hostConfig->isRequireAuthentication()) {
            $this->setBasicAuth();
        }

        define('APP_ROOT', DIR_WEB . 'Applications/' . $this->hostConfig->getApplication() . '/');

        if(!Empty($autoLoader)){
            $autoLoader->addNamespace('Applications', DIR_WEB . 'Applications/');
        }
	}

    public function __destruct()
    {

    }

    private function setBasicAuth():void
    {
        if($this->hostConfig->getAuthConfig()) {

            if (empty(Session::get(SESSION_HTACCESS)) && isset($_SERVER['PHP_AUTH_USER'])) {
                if ($_SERVER['PHP_AUTH_PW'] == $this->hostConfig->getAuthConfig('password') && $_SERVER['PHP_AUTH_USER'] == $this->hostConfig->getAuthConfig('user')) {
                    Session::set(SESSION_HTACCESS, true);
                }
            }

            if (empty(Session::get(SESSION_HTACCESS))) {
                header('WWW-Authenticate: Basic realm="' . $this->hostConfig->getAuthConfig('realm') . '"');
                header('HTTP/1.0 401 Unauthorized');

                die($this->hostConfig->getAuthConfig('errorMessage'));
            }
        }
    }

	public function init(bool $skipLogin = false):void
    {
		Session::start();

        SiteSettings::create()->load($this->hostConfig->getClientId());

        $this->user = User::create()->load();

        $this->setLanguage();

        Translate::create()->load($this->hostConfig);

        if($this->hostConfig->isMaintenance()){
            $this->page = 'Maintenance';
        }else {
            $menuClass = '\\Applications\\' . $this->hostConfig->getApplication() . '\\Controllers\\Menu';

            if(class_exists($menuClass)){
                $this->menu = new $menuClass();
            }else{
                $this->menu = new BaseMenu();
            }
            $this->menu->setup();

            if ($this->user->isLoggedIn() || $skipLogin) {

            }elseif(!$this->user->isLoggedIn() && $this->hostConfig->getApplication() == DEFAULT_APPLICATION){
                $this->page = 'login';
            }
        }

        Db::create()->setVariables([
            'clientId' => ($this->hostConfig->getClientId() ?: 0),
            'userId'   => ($this->user->getId() ?: 0),
            'ip'       => $_SERVER['REMOTE_ADDR']
        ]);

        $this->parseUrl(($_GET['path'] ?? ''));
	}

    private function parseUrl(string $path):void
    {
        if(!Empty($path)){
            $uri = explode('/', rtrim($path, '/'));

            $accessRights = (!empty($this->user->getProperty('accessRights'))) ? array_keys($this->user->getProperty('accessRights')) : [];

            foreach($uri as $i => $key) {
                $menuItem = $this->menu->getItem($key);

                if($menuItem){
                    $menuItem->setIsSelected();

                    if(
                        !$menuItem->isRequireLogin() || ($menuItem->isRequireLogin() && in_array($key, $accessRights) && $this->user->isLoggedIn())
                    ){

                        $this->page = $key;

                        if($menuItem->getPageModel()){
                            $this->originalPage = $key;
                            $this->page = $menuItem->getPageModel();
                        }

                    }else{
                        $this->originalPage = $path;
                        $this->page = 'login';
                    }

                    unset($uri[$i]);

                    if($menuItem->itemCount() == 0){
                        break;
                    }

                }else{
                    $this->page = 'page-not-found';
                    break;
                }
            }

            if (!empty($uri)) {
                $this->params = array_values($uri);
            }
        }

        if (empty($this->page)) {
            $this->page = 'index';
        }

        if (empty($this->originalPage)) {
            $this->originalPage = $this->page;
        }
    }

    /**
     * @throws Exception
     */
    public function display():void
    {
        if (strtolower($this->page) === 'ajax') {
            $isAjaxCall = true;

            if($this->hostConfig->getApplication() === 'Admin' && !$this->user->isLoggedIn()){
                exit();
            }

            $this->page = trim($this->params[0]);

            array_shift($this->params);

            $classRoot = 'Ajax';

            $menu = null;
        }else{
            $isAjaxCall = false;

            $classRoot = 'Pages';

            $menu = $this->menu->setPage($this->page, $this->originalPage);
        }

        $pageClass = '\\Applications\\' . $this->hostConfig->getApplication() . '\\Controllers\\' . $classRoot . '\\' . Str::dashesToCamelCase($this->page);
        /*
        if(!class_exists($pageClass)){
            $pageClass = '\\Applications\\' . $this->hostConfig->getApplication() . '\\Controllers\\' . $classRoot . '\\GenericPage';
        }
        */

        if(class_exists($pageClass)){
            $page = new $pageClass();
            if($page instanceof AbstractPage) {
                $page->create($this->params, $isAjaxCall, $menu);

                View::renderPage($page);
            }else{
                throw new Exception ('Given class is not an instance of Page');
            }
        }else{
            throw new Exception ('Page class not found');
        }
	}

	private function setLanguage():void
    {
		if(isset($_REQUEST['lang']) AND isset($GLOBALS['LANGUAGES'][strtolower($_REQUEST['lang'])])){
			$this->hostConfig->setLanguage(strtolower($_REQUEST['lang']));
		}else{
			$language = Session::get(SESSION_LOCALE);

			if (!empty($language)) {
                $this->hostConfig->setLanguage(strtolower($language));
			} else if (empty($this->language)) {
                $this->hostConfig->setLanguage();
			}
		}

        Session::set(SESSION_LOCALE, $this->hostConfig->getLanguage());
	}

    public static function isApiRequest():bool
    {
        if(API_ENABLED) {
            if (defined('API_HOST_NAME') && $_SERVER['HTTP_HOST'] === API_HOST_NAME) {
                return true;
            }

            if (!empty($_REQUEST['path'])) {
                list($service,) = explode('/', trim($_REQUEST['path'], '/'));
                if (strtolower($service) === 'api') {
                    return true;
                }
            }
        }

        return false;
    }

    public static function pageRedirect(string $url, int $httpCode = 0):never
    {
        if($httpCode){
            http_response_code($httpCode);
        }

		header("Location: " . $url);

		exit();
	}
}