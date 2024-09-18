<?php
namespace Framework\Controllers\Buttons;

class ButtonEdit extends ButtonHref
{
    const Template = 'href';

    public function __construct(string $caption = 'BTN_EDIT', string $class = 'btn btn-warning ml-2 ms-2')
    {
        $this->setId('edit');

        $this->setName($this->getId());

        $this->setShowInEditor(false);

        $this->setShowInViewer(true);

        $this->caption = $caption;

        $this->class[] = $class;
    }

    public function getTemplate():string
    {
        return $this::Template;
    }
}