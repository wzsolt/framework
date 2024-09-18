<?php
namespace Framework\Controllers\Buttons;

use Framework\Components\Enums\ButtonType;

class ButtonSave extends ButtonSubmit
{
    const Template = 'button';

    public function __construct(string $caption = 'BTN_SAVE', string $class = 'btn btn-primary btn-progress')
    {
        $this->setType(ButtonType::Submit);

        $this->setId('save');

        $this->setName($this->getId());

        $this->setShowInEditor(true);

        $this->setShowInViewer(false);

        $this->caption = $caption;

        $this->class[] = $class;

        $this->init();
    }

    public function getTemplate():string
    {
        return $this::Template;
    }
}