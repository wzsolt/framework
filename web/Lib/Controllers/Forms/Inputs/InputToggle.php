<?php
namespace Framework\Controllers\Forms\Inputs;

class InputToggle extends InputCheckbox
{
    const Type = 'checkbox';

    public function getType():string
    {
        return $this::Type;
    }

    public function getTemplate():string
    {
        return 'toggle';
    }
}