<?php
namespace Framework\Controllers\Forms\Inputs;

use Framework\Controllers\Forms\AbstractFormElement;
use Framework\Controllers\Forms\ElementChangeStateTrait;
use Framework\Controllers\Forms\ElementColorTrait;
use Framework\Controllers\Forms\ElementOptionsTrait;

class InputRadioList extends AbstractFormElement
{
    use ElementOptionsTrait, ElementColorTrait, ElementChangeStateTrait;

    const Type = 'radio-list';

    protected function init():void
    {
        $this->setConstraints('ui-enabled', 'false');
    }

    public function getType():string
    {
        return $this::Type;
    }
}