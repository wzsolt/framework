<?php
namespace Framework\Controllers\Buttons;

class ButtonCancel extends ButtonHref
{
    const Template = 'href';

    public function __construct($caption = 'BTN_CANCEL', $class = 'btn btn-light ml-2 ms-2')
    {
        $this->setId('cancel');

        $this->setName($this->getId());

        $this->setShowInEditor(true);

        $this->setShowInViewer(true);

        $this->caption = $caption;

        $this->class[] = $class;
    }

    public function getTemplate():string
    {
        return $this::Template;
    }
}