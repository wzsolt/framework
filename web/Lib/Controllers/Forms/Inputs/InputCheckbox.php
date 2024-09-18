<?php
namespace Framework\Controllers\Forms\Inputs;

use Framework\Controllers\Forms\AbstractFormElement;
use Framework\Controllers\Forms\ElementChangeStateTrait;
use Framework\Controllers\Forms\ElementColorTrait;
use Framework\Controllers\Forms\ElementInlineTrait;

class InputCheckbox extends AbstractFormElement
{
    use ElementColorTrait, ElementChangeStateTrait, ElementInlineTrait;

    const Type = 'checkbox';

    private int $valueOn = 1;

    private int $valueOff = 0;

    protected function init():void
    {
        $this->setConstraints('ui-enabled', 'false');
    }

    public function getType():string
    {
        return $this::Type;
    }

    public function setStateValues(int $on = 1, int $off = 0):self
    {
        $this->valueOn = $on;
        $this->valueOff = $off;

        return $this;
    }

    public function getValueOn():int
    {
        return $this->valueOn;
    }

    public function getValueOff():int
    {
        return $this->valueOff;
    }
}