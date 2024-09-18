<?php
namespace Framework\Controllers\Buttons;

class ButtonHref extends AbstractFormButton
{
    const Template = 'href';

    public function __construct($id, $caption = '', $class = 'btn btn-primary')
    {
        $this->id = $id;

        $this->setName($this->getId());

        $this->caption = $caption;

        $this->class[] = $class;
    }

    public function getTemplate():string
    {
        return $this::Template;
    }

    public function init():self
    {
        return $this;
    }
}