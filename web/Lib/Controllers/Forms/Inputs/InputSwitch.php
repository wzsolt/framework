<?php
namespace Framework\Controllers\Forms\Inputs;

use Framework\Components\Enums\Color;

class InputSwitch extends InputCheckbox
{
    const Type = 'checkbox';

    public function init():void
    {
        parent::init();

        if(!$this->getColor()){
            $this->setColor(Color::Primary);
        }
    }

    public function getType():string
    {
        return $this::Type;
    }

    public function getTemplate():string
    {
        return 'switch';
    }
}