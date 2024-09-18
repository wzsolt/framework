<?php
namespace Framework\Controllers\Buttons;

use Framework\Components\Enums\ModalAction;

class ButtonYesNo extends ButtonModal
{
    const Template = 'href';

    public function getTemplate():string
    {
        return $this::Template;
    }

    public function setYesAction(ModalAction $action, int $value = 1, string $additionalAction = ''):self
    {
        if($action === ModalAction::PostForm){
            $this->postForm('action', $value, $additionalAction);
        }elseif ($action === ModalAction::PostModalForm){
            $this->postModalForm('action', $value, $additionalAction);
        }

        return $this;
    }

    public function setNoAction(string $action = ''):self
    {
        $this->addData('no-action', ($action ? $action . ';' : '') . "$('#yesno-modal').modal('hide');");

        return $this;
    }

    public function setQuestion(string $question):self
    {
        $this->addData('confirm-question', $question);

        return $this;
    }

    public function init():self
    {
        $this->addData('bs-toggle', 'modal');
        $this->addData('bs-target', '#yesno-modal');
        $this->addData('bs-backdrop', 'static');
        $this->addData('bs-keyboard', 'false');

        $this->dialogColor();

        return $this;
    }
}