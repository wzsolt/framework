<?php
namespace Applications\Admin\Controllers\Forms;

use Framework\Components\Enums\AccessLevel;
use Framework\Components\Enums\MessageType;
use Framework\Components\Messages;
use Framework\Controllers\Buttons\ButtonModalClose;
use Framework\Controllers\Buttons\ButtonModalSubmit;
use Framework\Controllers\Forms\AbstractForm;
use Framework\Controllers\Forms\Inputs\InputPassword;

class ChangePasswordForm extends AbstractForm
{
	public function setup(): void
    {
        $this->setTitle('LBL_CHANGE_PASSWORD');
        $this->reloadPage = true;

        $this->addControls(
            (new InputPassword('old_password', 'LBL_CURRENT_PASSWORD'))
                ->setColSize('col-12')
                ->showTogglePassword()
                ->setIcon('fa-solid fa-key')
                ->setRequired(),

            (new InputPassword('password1', 'LBL_NEW_PASSWORD'))
                ->setColSize('col-12')
                ->showTogglePassword()
                ->setIcon('fa-solid fa-lock')
                ->setRequired(),

            (new InputPassword('password2', 'LBL_CONFIRM_PASSWORD'))
                ->setColSize('col-12')
                ->showTogglePassword()
                ->setIcon('fa-solid fa-lock')
                ->setRequired()
        );

        $this->addButtons(
            new ButtonModalSubmit('setPassword', 'BTN_SAVE', 'btn btn-danger'),
            new ButtonModalClose()
        );
	}

	public function setPassword():void
    {
        $this->setFormState(self::STATE_INVALID);

        Messages::create()->clear();

		if (empty($this->values['old_password'])) $this->values['old_password'] = '';

		if (!empty($this->values['password1']) && !empty($this->values['password2'])) {
			if ($this->values['password1'] != $this->values['password2']) {
				$this->addError('ERR_PASSWORDS_NOT_MATCH', self::FORM_ERROR, ['password2']);
			} else {
				if($this->user->validatePassword($this->values['old_password'])){
					$this->user->setPassword($this->user->getId(), $this->values['password1']);
                    Messages::create()->add(MessageType::Success, 'LBL_PASSWORD_CHANGED');

                    $this->setFormState(self::STATE_SAVED);
                }else{
                    $this->addError('ERR_OLD_PASSWORD_IS_WRONG', self::FORM_ERROR, ['old_password']);
				}
			}
		}
	}

    public function setupKeyFields():void
    {
        // TODO: Implement setupKeyFields() method.
    }

    protected function setAccessLevel(): AccessLevel
    {
        return AccessLevel::FullAccess;
    }
}
