<?php
namespace Framework\Controllers\Buttons;

class ButtonModalClose extends ButtonModal
{
    const Template = 'href';

    public function __construct(string $id = 'btn-close', string $caption = 'BTN_CLOSE', string $class = 'btn btn-light ms-1')
    {
        parent::__construct($id, $caption, $class);

        $this->init();
    }

    public function init():self
    {
        $this->addData('dismiss', 'modal');

        $this->addData('bs-dismiss', 'modal');

        $this->addClass('waves-effect waves-light');

        $this->setUrl('javascript:;');

        return $this;
    }
}