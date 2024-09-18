<?php
namespace Framework\Controllers\Forms\Inputs;

use Framework\Controllers\Forms\AbstractFormElement;
use Framework\Controllers\Forms\ElementMaskTrait;
use Framework\Controllers\Forms\ElementPlaceholderTrait;

class InputText extends AbstractFormElement
{
    use ElementPlaceholderTrait, ElementMaskTrait;

    const Type = 'text';

    private int|false $maxLength = false;

    private bool $clearable = false;

    protected function init():void
    {
    }

    public function getType():string
    {
        return $this::Type;
    }

    public function setMaxLength(int $length):self
    {
        $this->maxLength = $length;

        return $this;
    }

    public function getMaxLength():int
    {
        return $this->maxLength;
    }

    public function setClearable(bool $clear = true):self
    {
        $this->clearable = $clear;

        return $this;
    }

    public function getClearable():bool
    {
        return $this->clearable;
    }

    public function onlyNumbers(string $chars = ''):self
    {
        $this->addClass('numbersonly');

        if(!Empty($chars)){
            $this->addData('chars', $chars);
        }

        return $this;
    }

    public function onlyAlphaNumeric():self
    {
        $this->addClass('alphanumeric-only');

        return $this;
    }
}