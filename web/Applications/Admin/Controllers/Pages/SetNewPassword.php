<?php
namespace Applications\Admin\Controllers\Pages;

use Applications\Admin\Controllers\AbstractPageConfig;
use Applications\Admin\Controllers\Forms\ForgotPasswordForm;
use Applications\Admin\Controllers\Forms\NewPasswordForm;
use Framework\Components\User;
use Framework\Router;

class SetNewPassword extends AbstractPageConfig
{
    public function setup(): array
    {
        $data = [];

        if(User::create()->isLoggedIn()){
            Router::pageRedirect('/');
        }

        $this->addForm(New NewPasswordForm());

        $data['success'] = isset($_REQUEST['success']);

        return $data;
    }
}