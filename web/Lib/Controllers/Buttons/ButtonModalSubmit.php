<?php
namespace Framework\Controllers\Buttons;

use Framework\Components\Enums\ButtonType;

class ButtonModalSubmit extends ButtonModal
{
    const Template = 'button';

    public function __construct(string $id, string $caption, string $class = 'btn btn-primary')
    {
        parent::__construct($id, $caption, $class);

        $this->init();

        $this->setName($this->id);
    }

    public function init():self
    {
        $this->addClass('btn-modal-submit');

        //$this->addClass('float-left');

        $this->setType(ButtonType::Button);

        return $this;
    }
}