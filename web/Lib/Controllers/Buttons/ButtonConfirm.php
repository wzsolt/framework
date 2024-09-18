<?php
namespace Framework\Controllers\Buttons;

use Framework\Components\Enums\ButtonType;
use Framework\Components\Enums\ModalAction;

class ButtonConfirm extends ButtonModal
{
    const Template = 'button';

    public function getTemplate():string
    {
        return $this::Template;
    }

    public function setAction(ModalAction $action, $value = 1, $additionalAction = ''):self
    {
        if($action == ModalAction::PostForm){
            $this->postForm('action', $value, $additionalAction);
        }elseif ($action == ModalAction::PostModalForm){
            $this->postModalForm('action', $value, $additionalAction);
        }

        return $this;
    }

    public function setTexts(string $question, string $buttonCaption = ''):self
    {
        $this->addData('confirm-question', $question, true);

        if($buttonCaption){
            $this->addData('confirm-button', $buttonCaption, true);
        }else{
            $this->addData('confirm-button', $this->caption, true);
        }

        return $this;
    }

    public function requestReason(string $fieldId):self
    {
        $this->addData('confirm-reason', true);
        $this->addData('confirm-reason-field', $fieldId);

        return $this;
    }

    public function init():self
    {
        $this->setType(ButtonType::Button);

        $this->addData('bs-toggle', 'modal');
        $this->addData('bs-target', '#confirm-delete');

        $this->dialogColor();

        return $this;
    }
}