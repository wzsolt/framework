<?php
namespace Framework\Controllers\Forms\Inputs;

use Framework\Controllers\Forms\AbstractFormElement;

class InputHidden extends AbstractFormElement
{
    const Type = 'hidden';

    public function __construct(string $id, string|false $name = false, ?string $default = null, bool $dbField = false)
    {
        $this->isContainer = false;

        if(!$dbField){
            $this->notDBField();
        }

        $this->setId($id);

        $this->setName(($name ?: $id));

        $this->default = $default;

        $this->init();
    }

    protected function init():void
    {
    }

    public function getType():string
    {
        return $this::Type;
    }
}