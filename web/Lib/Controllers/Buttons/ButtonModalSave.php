<?php
namespace Framework\Controllers\Buttons;

use Framework\Components\Enums\ButtonType;

class ButtonModalSave extends ButtonModal
{
    const Template = 'button';

    public function __construct(string $id = 'btn-save', string $caption = 'BTN_SAVE', string $class = 'btn btn-primary')
    {
        parent::__construct($id, $caption, $class);

        $this->init();
    }

    public function init():self
    {
        $this->addClass('btn-modal-submit');

        $this->addClass('btn-progress');

        $this->setName('save');

        $this->setType(ButtonType::Button);

        return $this;
    }
}