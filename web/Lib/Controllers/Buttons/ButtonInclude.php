<?php
namespace Framework\Controllers\Buttons;

class ButtonInclude extends ButtonModal
{
    private string $template;

    public function __construct(string $id, string $caption, string $template = '', string $class = '')
    {
        $this->id = $id;

        $this->setName($id);

        $this->caption = $caption;

        $this->template = $template;

        $this->class[] = $class;
    }

    public function init():self
    {
        return $this;
    }

    public function getTemplate():string
    {
        return $this->template;
    }

}