<?php

namespace Framework;

use Exception;
use Framework\Components\Enums\PageType;
use Framework\Components\Lists\ListLanguages;
use Framework\Components\Menu\MenuItem;
use Framework\Components\Messages;
use Framework\Components\TwigExtension;
use Framework\Components\HostConfig;
use Framework\Components\SiteSettings;
use Framework\Components\User;
use Framework\Controllers\Pages\AbstractPage;

use Twig;
//use Twig\Extension\AppExtension;
use Twig\Error\LoaderError;
use Twig\Extension\DebugExtension;
use Twig\Extension\StringLoaderExtension;
use Twig\Loader\FilesystemLoader;

class View
{
    private static View $instance;

	private static FilesystemLoader $loader;

    public function __construct()
    {

    }

    public static function create():View
    {
        if (!isset(self::$instance)) {
            self::$instance = new view();
        }

        return self::$instance;
    }

	private static function getCommonVariables(?AbstractPage $page = null):array
    {
        $user = User::create();
        $host = HostConfig::create();
        $theme = $host->getTheme();

        $publicDomain = false;

        if(!Empty($host->getPublicSite())){
            $publicDomain = rtrim($host->getPublicSite(), '/');
        }

        $variables = [
			'domain'        => $host->getDomain(),
			'publicDomain'  => ($publicDomain ? $publicDomain . '/' : $host->getDomain()),
			'host'        	=> $host->getHost(),
            'siteName'      => ($host->getName() ?? ''),
            'language'      => $host->getLanguage(),
            'languages'     => (new ListLanguages())->getList(),
            'currencies'    => $host->getCurrencies(),
			'user'          => $user,
			'loggedIn'      => $user->isLoggedIn(),
			'images'  		=> '/images/',
			'theme'  		=> '/themes/' . $theme . '/',
			'uploads'  		=> FOLDER_UPLOAD . $host->getClientId() . '/',
			'isProduction'  => !(SERVER_ID == 'development'),
			'settings'  	=> SiteSettings::create()->getSettings(),
			'messages'  	=> Messages::create()->get(true),
			'clientId'	    => $host->getClientId(),
		];

        if(!Empty($page)){
            $variables['menuSidebar']   = $page->getMenu()->buildMenu($user->getGroup(), $user->getProperty('accessRights'), MenuItem::MENU_POSITION_SIDEBAR);
            $variables['menuHeader']    = $page->getMenu()->buildMenu($user->getGroup(), $user->getProperty('accessRights'), MenuItem::MENU_POSITION_HEADER);
            $variables['menuFooter']    = $page->getMenu()->buildMenu($user->getGroup(), $user->getProperty('accessRights'), MenuItem::MENU_POSITION_FOOTER);
            $variables['pageTemplate'] 	= $page->getTemplate();
            $variables['params']        = $page->getUrlParams();
            $variables['page']          = $page;
        }

        return $variables;
	}

	private static function getTwig(string $autoescape = 'html', bool $debug = true):Twig\Environment
    {
        $host = HostConfig::create();

        $theme = $host->getTheme();
        $theme = ucfirst(strtolower($theme));

        if($theme) {
            $theme = '/' . $theme;
        }

        self::$loader = new FilesystemLoader(
            [
                APP_ROOT . 'Views' . $theme,
                APP_ROOT . 'Views' . $theme . '/Macros',
                DOC_ROOT . 'web/Applications/Common/Views' . $theme,
                DOC_ROOT . 'web/Applications/Common/Views'  .$theme . '/Controllers',
                DOC_ROOT . 'web/Applications/Common/Views'  .$theme . '/Macros',
                DOC_ROOT . 'web/Applications/Common/Views'  .$theme . '/Layouts',
                DOC_ROOT . 'web/Applications/Common/Doctemplates',
            ],
            APP_ROOT . 'Views'
        );

        $twig = new Twig\Environment(self::$loader, [
            'cache' => (TWIG_CACHE_ENABLED ? DIR_CACHE . 'twig/' . $host->getApplication() : false),
            'debug' => $debug,
            'autoescape' => $autoescape
        ]);

        $twig->addExtension(new StringLoaderExtension());
        $twig->addExtension(new TwigExtension());

        if($debug) {
            $twig->addExtension(new DebugExtension());
        }

		return $twig;
	}

    /**
     * @throws LoaderError
     */
    public static function addTwigPath($dir, $namespace = FilesystemLoader::MAIN_NAMESPACE):self
    {
        $view = View::create();

        $theme = HostConfig::create()->getTheme();

        self::$loader->addPath(APP_ROOT . 'Views'  . ($theme ? $theme . '/' : '') . $dir, $namespace);

        return $view;
    }

    /**
     * @todo move to Form or Page class
     */
    /*
	public function includeValidationJS() {
		$this->addJs('parsley/parsley.min.js', 'parsley', false, false, false); // form validation
		if($this->owner->language != 'en'){
			$this->addJs('parsley/i18n/'.$this->owner->language . '.js', 'parsley-' . $this->owner->language, false, false, false);
			$this->addJs('parsley/i18n/'.$this->owner->language . '.extra.js', 'parsley-' . $this->owner->language.'-extra', false, false, false);
		}
	}
    */

	private static function placeHeaders(array $headers):void
    {
        if(!Empty($headers)){
            foreach($headers AS $header){
                header($header);
            }
        }
	}

	public static function renderPage(AbstractPage $page):void
    {
		self::placeHeaders($page->getHTTPHeaders());

        if($page->getType() === PageType::Template){

            $twig = self::getTwig();

            $commonVariables = self::getCommonVariables($page);

            $headerData = [
                'title'           => ($page->getPageTitle() ?? ''),
                'css'             => $page->getCss(),
                'js'              => $page->getJs(),
                'headers'         => $page->getHeaders(),
                'pageDescription' => ($page->getDescription() ?? ''),
                'pageKeyWords'    => ($page->getKeywords() ?? ''),
                'meta'       	  => ($page->getMeta() ?? ''),
                'version'         => [
                    'css'		  => VERSION_CSS,
                    'js'		  => VERSION_JS,
                ],
            ];

            try {
                $content = $twig->load($page->getLayout() . '.layout' . TWIG_FILE_EXTENSION);

                echo $twig->render(
                    'html-header' . TWIG_FILE_EXTENSION,
                    $commonVariables + $headerData
                );

                $commonVariables['forms'] = $page->getForms();

                $commonVariables['tables'] = $page->getTables();

                echo $content->render($commonVariables + $page->getData());

                echo $twig->render(
                    'html-footer' . TWIG_FILE_EXTENSION,
                    [
                        'js'         => $page->getJs(),
                        'jsVersion'  => VERSION_JS,
                        'settings'   => SiteSettings::create()->getSettings()
                    ]
                );

            } catch(Exception $e) {
                die($e->getMessage());
            }

		}elseif($page->getType() === PageType::Json){
            header('Content-type: application/json; charset=utf-8');

            echo json_encode($page->getData(), JSON_UNESCAPED_UNICODE);

        }elseif($page->getType() === PageType::Raw){

            echo $page->getRawData();
        }

	}

	/**
	 * Custom rendering
	 * @param string $template (template filename)
	 * @param array $data (data array used in template)
	 * @param bool $addCommon (add common variables or not)
	 * @return string (generated html content)
	 */
	public static function renderContent(string $template, array $data, bool $addCommon = false):string
    {
		$twig = self::getTwig();

		if($addCommon){
            $data = array_merge($data, self::getCommonVariables());
		}

		try {
			$result = $twig->render($template . TWIG_FILE_EXTENSION, $data);
		} catch(Exception $e) {
			die($e->getMessage());
		}

		return $result;
	}

    public static function renderFromString(string $template, array $data):string
    {
        $twig = self::getTwig(false);

        try {
            $template = twig_template_from_string($twig, $template);

            $result = $template->render($data);

        } catch(Exception $e) {
            echo $e->getMessage();

            return false;
        }

        return $result;
    }

}