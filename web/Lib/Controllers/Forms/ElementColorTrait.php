<?php
namespace Framework\Controllers\Forms;

use Framework\Components\Enums\Color;

trait ElementColorTrait
{
    private Color $color = Color::Info;

    public function setColor(Color $color):self
    {
        $this->color = $color;

        return $this;
    }

    public function getColor():string
    {
        return strtolower($this->color->name);
    }
}