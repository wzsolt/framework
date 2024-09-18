<?php
namespace Framework\Controllers\Forms\Inputs;

use Framework\Controllers\Forms\AbstractFormElement;
use Framework\Controllers\Forms\ElementPlaceholderTrait;

class InputTextarea extends AbstractFormElement
{
    use ElementPlaceholderTrait;

    const Type = 'textarea';

    private int|false $maxLength = false;

    private int$rows = 4;

    protected function init():void
    {
        $this->addJs('autosize/autosize.min.js', false, 'autosize');
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

    public function setRows(int $rows):self
    {
        $this->rows = $rows;

        return $this;
    }

    public function getRows():int
    {
        return $this->rows;
    }

    public function setAutosize():self
    {
        $this->addClass('autosize');

        return $this;
    }
}