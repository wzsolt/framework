<?php
namespace Applications\Admin\Controllers\Forms;

use Framework\Components\Enums\AccessLevel;
use Framework\Components\User;
use Framework\Controllers\Buttons\ButtonSave;
use Framework\Controllers\Forms\AbstractForm;
use Framework\Controllers\Forms\Inputs\InputHidden;
use Framework\Controllers\Forms\Inputs\InputPassword;
use Framework\Router;

class NewPasswordForm extends AbstractForm
{
    protected function setAccessLevel(): AccessLevel
    {
        return AccessLevel::FullAccess;
    }

    public function setup(): void
    {
        $this->addControls(
            (new InputPassword('password1', 'LBL_NEW_PASSWORD'))
                ->setColSize('col-12')
                ->setName('password')
                ->showTogglePassword()
                ->setIcon('fa-solid fa-lock')
                ->setRequired(),

            (new InputPassword('password2', 'LBL_CONFIRM_PASSWORD'))
                ->setColSize('col-12')
                ->showTogglePassword()
                ->setName('confirm_password')
                ->setIcon('fa-solid fa-lock')
                ->setRequired(),

            (new InputHidden('token', 'token'))
        );

        $this->addButtons(
            (new ButtonSave('BTN_SET_NEW_PASSWORD'))
                ->setName('setPwd')
        );
	}

	public function onAfterLoadValues(): void
    {
        $this->isValid = false;

		if($this->getFormState() == self::STATE_LOADED) {
			if (Empty($_REQUEST['token'])) {
				$this->addError('ERR_MISSING_TOKEN');
			} else {
                $token = User::create()->checkToken(urldecode($_REQUEST['token']));

                if($token['isValid']) {
                    $this->isValid = true;
                    $this->values['token'] = $token['token'];
                }else{
                    $this->addError('ERR_INVALID_TOKEN');
                }
			}
		}
	}

	public function onValidate(): void
    {
		parent::onValidate();

		if(Empty($this->values['password']) || Empty($this->values['confirm_password'])) {
			$this->addError('ERR_MARKED_FIELDS_ARE_MISSING', self::FORM_ERROR, ['password1', 'password2']);
		}else {
			if ($this->values['password'] != $this->values['confirm_password']) {
				$this->addError('ERR_PASSWORDS_NOT_MATCH', self::FORM_ERROR, ['password2']);
			}

			if (!$this->values['token']) {
				$this->isValid = false;
				$this->addError('ERR_MISSING_TOKEN');
			}
		}
	}

	public function setPwd():void
    {
        $user = User::create();

		$token = $user->checkToken($this->values['token'], true);

		if($token['isValid']) {
            $user->setPassword($token['userId'], $this->values['password']);

            $this->setFormState(self::STATE_SAVED);

            Router::pageRedirect('/set-new-password/?success');
		}else{
			$this->isValid = false;

			$this->addError('ERR_MISSING_TOKEN');
		}
	}

    public function setupKeyFields(): void
    {
        // TODO: Implement setupKeyFields() method.
    }
}
