<?php
namespace Framework\Controllers\Forms\Inputs;

class InputFloatingLabelText extends InputText {
    const Type = 'text';

    public function getType():string
    {
        return $this::Type;
    }

    public function getTemplate():string
    {
        return 'floatingLabelText';
    }
}