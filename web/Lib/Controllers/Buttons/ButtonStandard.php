<?php
namespace Framework\Controllers\Buttons;

use Framework\Components\Enums\ButtonType;

class ButtonStandard extends AbstractFormButton
{
    const Template = 'button';

    protected int $value = 1;

    public function __construct(string $id, string $caption = '', string $class = 'btn btn-light')
    {
        $this->setType(ButtonType::Button);

        $this->setId($id);

        $this->setName($id);

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