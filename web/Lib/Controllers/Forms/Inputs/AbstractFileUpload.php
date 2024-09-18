<?php
namespace Framework\Controllers\Forms\Inputs;

use Framework\Controllers\Forms\AbstractFormElement;

abstract class AbstractFileUpload extends AbstractFormElement
{
    abstract public function getType():string;

    abstract protected function init():void;
}