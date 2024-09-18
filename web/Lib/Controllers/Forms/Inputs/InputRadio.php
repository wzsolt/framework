<?php
namespace Framework\Controllers\Forms\Inputs;

use Framework\Controllers\Forms\AbstractFormElement;
use Framework\Controllers\Forms\ElementChangeStateTrait;
use Framework\Controllers\Forms\ElementColorTrait;
use Framework\Controllers\Forms\ElementInlineTrait;
use Framework\Controllers\Forms\ElementOptionsTrait;

class InputRadio extends AbstractFormElement
{
    use ElementOptionsTrait, ElementColorTrait, ElementChangeStateTrait, ElementInlineTrait;

    const Type = 'radio';

    protected function init():void
    {
        $this->setConstraints('ui-enabled', 'false');
    }

    public function getType():string
    {
        return $this::Type;
    }
}