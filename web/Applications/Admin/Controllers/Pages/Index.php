<?php

namespace Applications\Admin\Controllers\Pages;

use Applications\Admin\Controllers\AbstractPageConfig;
use Framework\Components\User;
use Framework\Router;

class Index extends AbstractPageConfig
{
    public function setup(): ?array
    {
        Router::pageRedirect('/maintenance/');
    }

}