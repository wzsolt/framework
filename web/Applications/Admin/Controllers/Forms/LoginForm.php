<?php
namespace Applications\Admin\Controllers\Forms;

use Framework\Components\Enums\AccessLevel;
use Framework\Components\User;
use Framework\Controllers\Buttons\ButtonSave;
use Framework\Controllers\Forms\AbstractForm;
use Framework\Controllers\Forms\Inputs\InputHidden;
use Framework\Controllers\Forms\Inputs\InputPassword;
use Framework\Controllers\Forms\Inputs\InputText;
use Framework\Helpers\Utils;
use Framework\Router;

class LoginForm extends AbstractForm
{
	public function setup(): void
    {
        $this->addControls(
            (new InputText('email', 'LBL_EMAIL'))
                ->setRequired(),
            (new InputPassword('password', 'LBL_PASSWORD'))
                ->setRequired(),
            new InputHidden('redirect', false, ($_GET['path'] ?? ''))
        );

        $this->addButtons(
            (new ButtonSave('BTN_LOGIN'))
                ->setName('signIn')
        );
	}

	public function onValidate(): void
    {
		if (!empty($this->values['email']) && !Utils::checkEmail($this->values['email'])) {
			$this->addError('ERR_WRONG_EMAIL_FORMAT', self::FORM_ERROR, ['email']);
		}
	}

    /**
     * @throws \Exception
     */
    public function signIn():void
    {
		if (!empty($this->values['email']) && !empty($this->values['password'])) {
			$login = User::create()->login($this->values['email'], $this->values['password']);

			if (!empty($login)) {
				$redirect = trim($this->values['redirect']);

				if(!Empty($redirect)) {
					$redirect = '/' . ltrim($redirect, '/');
				}else {
					$redirect = '/';
				}

				$this->reset();

				Router::pageRedirect($redirect);

			} else {
				$this->addError('ERR_WRONG_EMAIL_OR_PASSWORD', self::FORM_ERROR, ['email', 'password']);
			}
		}else{
			$this->addError('ERR_EMAIL_PASSWORD_MISSING', self::FORM_ERROR, ['email', 'password']);
		}
	}

    public function setupKeyFields(): void
    {
        // TODO: Implement setupKeyFields() method.
    }

    protected function setAccessLevel(): AccessLevel
    {
        return AccessLevel::FullAccess;
    }
}
