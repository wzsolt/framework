<?php
namespace Applications\Admin\Controllers\Forms;

use Framework\Components\Email;
use Framework\Components\Enums\AccessLevel;
use Framework\Components\User;
use Framework\Controllers\Buttons\ButtonSave;
use Framework\Controllers\Forms\AbstractForm;
use Framework\Controllers\Forms\Inputs\InputText;
use Framework\Helpers\Utils;
use Framework\Router;

class ForgotPasswordForm extends AbstractForm
{
    protected function setAccessLevel(): AccessLevel
    {
        return AccessLevel::FullAccess;
    }

    public function setup():void
    {
        $this->addControls(
            (new InputText('email', 'LBL_EMAIL'))
                ->setRequired()
        );

        $this->addButtons(
            (new ButtonSave('BTN_OK'))
                ->setName('sendPwd')
        );
	}

	public function onValidate(): void
    {
		if(Empty($this->values['email'])){
			$this->addError('ERR_UNRECOGNIZED_EMAIL', self::FORM_ERROR, ['email']);
		}elseif(!Utils::checkEmail($this->values['email'])){
			$this->addError('ERR_WRONG_EMAIL_FORMAT', self::FORM_ERROR, ['email']);
		}
	}

	public function sendPwd(): void
    {
        $user = User::create();

		$result = $user->validateUserByEmail($this->values['email']);

		if($result['isValid']){
			$data = [
				'id' => $result['userId'],
				'link' => $user->getPasswordChangeLink($result['userId']),
			];

            Email::create()->setTemplate(Email::MAIL_REQUEST_NEW_PASSWORD, $data)->addUser($data['id'])->send();

            Router::pageRedirect('/login/?success');
		}else{
			$this->addError('ERR_UNRECOGNIZED_EMAIL', self::FORM_ERROR, ['email']);
		}
	}

    public function setupKeyFields(): void
    {
        // TODO: Implement setupKeyFields() method.
    }
}
