<?php
namespace Framework\Controllers\Forms\Inputs;

class InputFloatingLabelPassword extends InputText
{
    const Type = 'password';

    public function getType():string
    {
        return $this::Type;
    }

    public function getTemplate():string
    {
        return 'floatingLabelText';
    }
}