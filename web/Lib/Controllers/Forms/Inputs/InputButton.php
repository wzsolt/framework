<?php
namespace Framework\Controllers\Forms\Inputs;

use Framework\Controllers\Forms\AbstractFormElement;

class InputButton extends AbstractFormElement
{
    const Type = 'formButton';

    protected int $value = 1;

    protected string $type = 'button';

    public function __construct(string $id, string $caption = '', int $value = 1, string $class = 'btn btn-primary')
    {
        $this->setId($id);

        $this->setName($id);

        $this->label = $caption;

        $this->value = $value;

        $this->class[] = $class;
    }

    protected function init(): void
    {
        $this->notDBField();
    }

    public function getType(): string
    {
        return $this::Type;
    }

    public function setButtonType($type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getButtonType():string
    {
        return $this->type;
    }

    public function setValue(int $value):self
    {
        $this->value = $value;

        return $this;
    }

    public function getValue():int
    {
        return $this->value;
    }

}