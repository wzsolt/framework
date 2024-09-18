<?php
namespace Framework\Controllers\Forms\Inputs;

use Framework\Controllers\Forms\AbstractFormElement;
use Framework\Controllers\Forms\ElementColorTrait;
use Framework\Controllers\Forms\ElementOptionsTrait;

class InputCheckGroup extends AbstractFormElement
{
    use ElementOptionsTrait, ElementColorTrait;

    const Type = 'checkgroup';

    private bool $isBinary = false;

    protected function init():void
    {
    }

    public function getType():string
    {
        return $this::Type;
    }

    public function setBinary(bool $isBinary):self
    {
        $this->isBinary = $isBinary;

        return $this;
    }

    public function isBinary():bool
    {
        return $this->isBinary;
    }
}