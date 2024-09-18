<?php
namespace Applications\Admin\Controllers\Pages;

use Applications\Admin\Controllers\AbstractPageConfig;
use Applications\Admin\Controllers\Forms\ForgotPasswordForm;
use Applications\Admin\Controllers\Forms\LoginForm;
use Framework\Components\User;
use Framework\Router;

class Login extends AbstractPageConfig
{
    public function setup(): array
    {
        $data = [];

        if(User::create()->isLoggedIn()){
            Router::pageRedirect('/');
        }

        $loginForm = $this->addForm(New LoginForm());
        $forgotPasswordForm = $this->addForm(New ForgotPasswordForm());

        if($forgotPasswordForm->getErrors() || isset($_REQUEST['success'])){
            if(isset($_REQUEST['success'])){
                $data['success'] = true;
            }else{
                $data['success'] = false;
            }

            $data['login'] = false;
            $data['forgotPassword'] = true;
        }else{
            $data['login'] = true;
            $data['forgotPassword'] = false;
            $data['success'] = false;
        }

        $this->addInlineJs("    
	        $('a[data-bs-toggle=\"tab\"]')
		        .on('click', function() {
			        $('a[data-bs-toggle=\"tab\"]').removeClass('active')
		    });
        ");

        return $data;
    }
}