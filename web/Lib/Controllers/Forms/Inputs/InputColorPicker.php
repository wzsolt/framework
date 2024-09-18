<?php
namespace Framework\Controllers\Forms\Inputs;

use Framework\Controllers\Forms\AbstractFormElement;

class InputColorPicker extends AbstractFormElement
{
    const Type = 'colorPicker';

    protected function init():void
    {
        $this->addClass('colorpicker');
    }

    public function getType():string
    {
        return $this::Type;
    }
}