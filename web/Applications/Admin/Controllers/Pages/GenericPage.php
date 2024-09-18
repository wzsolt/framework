<?php
namespace Applications\Admin\Controllers\Pages;

use Applications\Admin\Controllers\AbstractPageConfig;

class GenericPage extends AbstractPageConfig
{
    public function setup():?array
    {
        $menu = $this->getMenu()->buildMenu();
        //dd($menu);

        $this->setVariable('params', $this->getUrlParams());

        $this->setTemplate('generic-page');

        return null;
    }
}