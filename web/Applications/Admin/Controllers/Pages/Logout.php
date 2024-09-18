<?php
namespace Applications\Admin\Controllers\Pages;

use Applications\Admin\Controllers\AbstractPageConfig;
use Framework\Models\Session\Session;
use Framework\Router;

class Logout extends AbstractPageConfig
{
    public function setup(): array
    {
        Session::destroy();

        Router::pageRedirect('/');
    }
}