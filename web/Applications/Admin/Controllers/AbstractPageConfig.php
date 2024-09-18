<?php

namespace Applications\Admin\Controllers;

use Framework\Components\HostConfig;
use Framework\Components\SiteSettings;
use Framework\Controllers\Pages\AbstractPage;

abstract class AbstractPageConfig extends AbstractPage
{
    protected HostConfig $hostConfig;

    public abstract function setup():?array;

    public function __construct()
    {
        $this->hostConfig = HostConfig::create();
    }

    protected function config(): void
    {
        $addVersion = true;

        $theme =$this->hostConfig->getTheme();

        $postfix = '.min';
        if(SERVER_ID == 'development') {
            $addVersion = false;
            $postfix = '';
        }

        if($GLOBALS['THEMES'][$theme]['css']) {
            foreach($GLOBALS['THEMES'][$theme]['css'] AS $vendor => $file) {
                $this->addCss($file, false, $vendor);
            }
        }

        $this->addCss($theme . (!Empty(SiteSettings::create()->get('darkTheme')) ? '-dark' : '') . '.style' . $postfix . '.css', ($addVersion ? VERSION_CSS : false), 'app-' . APPLICATION_NAME);

        if($GLOBALS['THEMES'][$theme]['js']) {
            foreach($GLOBALS['THEMES'][$theme]['js'] AS $vendor => $file) {
                $this->addJs($file, false, $vendor);
            }
        }

        $this->addJs('app-' . $theme . $postfix . '.js', false, 'app-' . APPLICATION_NAME);

        $this->addJs('admin' . $postfix . '.js', ($addVersion ? VERSION_JS : false), 'admin-' . APPLICATION_NAME);

        $data = $this->setup();

        if(!Empty($data)){
            $this->setData($data);
        }
    }
}