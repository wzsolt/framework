<?php

namespace Framework\Controllers\Pages;

use Framework\Components\Enums\PageType;
use Framework\Components\HostConfig;
use Framework\Components\Menu\AbstractMenuBuilder;
use Framework\Components\Traits\AddCssTrait;
use Framework\Components\Traits\AddJsTrait;
use Framework\Components\User;
use Framework\Controllers\Forms\AbstractForm;
use Framework\Controllers\Tables\AbstractTable;
use Framework\Models\Database\Db;

abstract class AbstractPage
{
    use AddCssTrait, AddJsTrait;

    const PAGE_TYPE_TEMPLATE = 'template';

    const PAGE_TYPE_JSON = 'json';

    const PAGE_TYPE_RAW = 'raw';

    const PAGE_TYPE_OBJECT = 'object';

    private PageType $type = PageType::Template;

    private string $template;

    private string $layout = 'default';

    private string $pageTitle = '';

    private string $title = '';

    private string $icon = '';

    private string $backgroundImage = '';

    private string $description = '';

    private string $keywords = '';

    private string $subTitle = '';

    private array $meta = [];

    private ?array $urlParams = [];

    private array $data = [];

    private string $rawData = '';

    private array $headers = [];

    private array $HTTPHeaders = [];

    private string $customTemplate = '';

    private string $customTemplatePath;

    private string $customTemplateNameSpace;

    private ?AbstractMenuBuilder $menu;

    private array $tables = [];

    private array $forms = [];

    protected bool $isAjaxCall = false;

    protected abstract function config():void;

    final public function create(?array $params = [], bool $isAjaxCall = false, ?AbstractMenuBuilder $menu = null):void
    {
        $this->urlParams = $params;

        $this->isAjaxCall = $isAjaxCall;

        if(!Empty($menu)) {
            $this->menu = $menu;

            $this->setTemplate($menu->getPage());

            if ($currentMenu = $menu->getCurrentMenu()) {
                $currentMenu->setIsSelected();

                if(HostConfig::create()->getApplication() == 'Admin' && Empty($this->title)) {
                    $this->setTitle($currentMenu->getLabel());
                }

                $this->setIcon($currentMenu->getIcon());

                $this->setLayout($currentMenu->getLayout());
            }
        }

        $this->config();
    }

    public function hasForm():bool
    {
        return !Empty($this->forms);
    }

    public function hasTable():bool
    {
        return !Empty($this->tables);
    }

    public function addHeader(string $header):void
    {
        if(!in_array($header, $this->headers)) {
            $this->headers[] = $header;
        }
    }

    public function getHeaders():array
    {
        return $this->headers;
    }

    public function getHTTPHeaders(): array
    {
        return $this->HTTPHeaders;
    }

    public function setHTTPHeaders(array $HTTPHeaders): AbstractPage
    {
        $this->HTTPHeaders = $HTTPHeaders;

        return $this;
    }

    public function getType(): PageType
    {
        return $this->type;
    }

    public function setType(PageType $type):self
    {
        $this->type = $type;

        return $this;
    }

    public function getTemplate(): string
    {
        $className = (new \ReflectionClass($this))->getShortName();

        return ($this->template ?? strtolower($className));
    }

    public function setTemplate(string $template):self
    {
        $this->template = $template;

        return $this;
    }

    public function getLayout(): string
    {
        return $this->layout;
    }

    public function setLayout(string $layout):self
    {
        $this->layout = $layout;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title):self
    {
        $this->title = $title;

        return $this;
    }

    public function getPageTitle(): string
    {
        return ($this->pageTitle ?: $this->title);
    }

    public function setPageTitle(string $title):self
    {
        $this->pageTitle = $title;

        return $this;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function setIcon(string $icon):self
    {
        $this->icon = $icon;

        return $this;
    }

    public function getBackgroundImage(): string
    {
        return $this->backgroundImage;
    }

    public function setBackgroundImage(string $backgroundImage): AbstractPage
    {
        $this->backgroundImage = $backgroundImage;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    protected function setDescription(string $description):self
    {
        $this->description = $description;

        return $this;
    }

    public function getKeywords(): string
    {
        return $this->keywords;
    }

    protected function setKeywords(string $keywords):self
    {
        $this->keywords = $keywords;

        return $this;
    }

    public function getSubTitle(): string
    {
        return $this->subTitle;
    }

    protected function setSubTitle(string $subTitle):self
    {
        $this->subTitle = $subTitle;

        return $this;
    }

    public function getMeta(): array
    {
        return $this->meta;
    }

    protected function setMeta(string $property, string $content):self
    {
        $this->meta[$property] = $content;

        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    protected function setData(array $data):self
    {
        if(!Empty($data)) {
            foreach ($data as $key => $value) {
                $this->setVariable($key, $value);
            }
        }

        return $this;
    }

    public function getRawData(): string
    {
        return $this->rawData;
    }

    protected function setRawData(string $rawData): AbstractPage
    {
        $this->rawData = $rawData;

        return $this;
    }

    protected function setVariable(string $key, mixed $data):self
    {
        $this->data[$key] = $data;

        return $this;
    }

    protected function getVariable(string $key):mixed
    {
        if(isset($this->data[$key])) {
            return $this->data[$key];
        }

        return false;
    }

    public function getUrlParams(): array
    {
        return $this->urlParams;
    }

    protected function getRawInput():?array
    {
        $json = file_get_contents('php://input');

        return json_decode($json, true);
    }

    protected function getPostData():array
    {
        if(!Empty($_POST)){
            return $_POST;
        }

        return [];
    }

    public function getCustomTemplate(): string
    {
        return $this->customTemplate;
    }

    protected function setCustomTemplate(string $customTemplate): self
    {
        $this->customTemplate = $customTemplate;

        return $this;
    }

    public function getCustomTemplatePath(): string
    {
        return $this->customTemplatePath;
    }

    protected function setCustomTemplatePath(string $customTemplatePath): self
    {
        $this->customTemplatePath = $customTemplatePath;

        return $this;
    }

    public function getCustomTemplateNameSpace(): string
    {
        return $this->customTemplateNameSpace;
    }

    protected function setCustomTemplateNameSpace(string $customTemplateNameSpace): self
    {
        $this->customTemplateNameSpace = $customTemplateNameSpace;

        return $this;
    }

    public function getMenu():AbstractMenuBuilder
    {
        return $this->menu;
    }

    protected final function addTable(AbstractTable $table, string $alias = ''):AbstractTable
    {
        $tableName = (!Empty($alias) ? $alias : $table->getName());

        $url = $this->getUrlParams();
        $action = ($url[0] ?? '');
        if(!Empty($_REQUEST['action'])){
            $action = $_REQUEST['action'];
        }

        $keyValues = (!Empty($_REQUEST['id']) ? $_REQUEST['id'] : []);
        $foreignKeyValues = (!Empty($_REQUEST['fkeys']) ? $_REQUEST['fkeys'] : []);

        if(!Empty($action)){
            $action = urldecode(trim($action));
        }

        if(!Empty($keyValues)) $keyValues = explode(',', $keyValues);
        if(!Empty($foreignKeyValues)) $foreignKeyValues = explode(',', $foreignKeyValues);

        $table->setParams($this->getUrlParams())->init(
            User::create()->getAccessLevel($this->menu->getOriginalPage()),
            $action,
            $keyValues,
            $foreignKeyValues
        );

        $this->mergeJs($table->getJs());

        $this->mergeCss($table->getCss());

        $this->tables[$tableName] = $table;

        return $table;
    }

    public function getTable(string $id):?AbstractTable
    {
        if(isset($this->tables[$id])){
            return $this->tables[$id];
        }

        return null;
    }

    /**
     * @return AbstractTable[]
     */
    public function getTables():array
    {
        return $this->tables;
    }

    protected final function addForm(AbstractForm $form, string $alias = ''):AbstractForm
    {
        $formName = (!Empty($alias) ? $alias : $form->getName());

        $form->init();

        $this->mergeJs($form->getJs());

        $this->mergeCss($form->getCss());

        $this->forms[$formName] = $form;

        return $form;
    }

    public function getForm(string $id):?AbstractForm
    {
        if(isset($this->forms[$id])){
            return $this->forms[$id];
        }

        return null;
    }

    /**
     * @return AbstractForm[]
     */
    public function getForms():array
    {
        return $this->forms;
    }

    protected function getContent(string $key):false|string
    {
        $hostConfig = HostConfig::create();

        $result = Db::create()->getFirstRow(
            DB::select(
                'contents',
                [],
                [
                    'c_page_url' => $key,
                    'c_published' => 1,
                    'c_client_id' => $hostConfig->getClientId(),
                    'c_language' => $hostConfig->getLanguage(),
                    'c_hosts' => [
                        'like' => '%|' . $hostConfig->getId() . '|%'
                    ]
                ]
            )
        );
        if($result){
            $content = $result['c_content'];

            $result['c_page_url'] = rtrim($result['c_page_url'], '/') . '/';


            $this->setContent($result);

            return $content;
        }

        return false;
    }

    protected function getHomeContent():string
    {
        $content = '';

        $hostConfig = HostConfig::create();

        $result = Db::create()->getFirstRow(
            DB::select(
                'contents',
                [],
                [
                    'c_home' => 1,
                    'c_published' => 1,
                    'c_client_id' => $hostConfig->getClientId(),
                    'c_language' => $hostConfig->getLanguage(),
                    'c_hosts' => [
                        'like' => '%|' . $hostConfig->getId() . '|%'
                    ]
                ]
            )
        );
        if($result){
            $content = $result['c_content'];

            $result['c_page_url'] = '';

            $this->setContent($result);
        }

        return $content;
    }

    private function setContent(array $data):void
    {
        $hostConfig = HostConfig::create();

        $this->setTitle($data['c_title']);

        if(!Empty($data['c_subtitle'])) {
            $this->setSubTitle($data['c_subtitle']);
        }

        if(!Empty($data['c_page_description'])) {
            $this->setDescription($data['c_page_description']);
            $this->setMeta('og:description', $data['c_page_description']);
        }

        if(!Empty($data['c_page_title'])) {
            $this->setMeta('title', $data['c_page_title']);
            $this->setMeta('og:title', $data['c_page_title']);
        }

        if(!Empty($data['c_template'])) {
            $this->setCustomTemplate($data['c_template']);
        }

        $this->setMeta('og:url', $hostConfig->getDomain() . $data['c_page_url']);

        $this->setMeta('og:type', 'website');

        if(!Empty($data['c_page_img'])) {
            $imagePath = FOLDER_UPLOAD . $hostConfig->getClientId() . '/pages/' . $data['c_id'] . '/' . $data['c_page_img'];

            $this->setMeta('og:image', rtrim($hostConfig->getDomain(), '/') . $imagePath);

            $this->setBackgroundImage($imagePath);
        }
    }
}