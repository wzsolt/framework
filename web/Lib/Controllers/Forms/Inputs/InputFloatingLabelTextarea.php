<?php
namespace Framework\Controllers\Forms\Inputs;

class InputFloatingLabelTextarea extends InputTextarea
{
    const Type = 'textarea';

    public function getType():string
    {
        return $this::Type;
    }

    public function getTemplate():string
    {
        return 'floatingLabelTextarea';
    }
}