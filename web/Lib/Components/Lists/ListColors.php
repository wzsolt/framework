<?php

namespace Framework\Components\Lists;

use Framework\Components\Enums\Color;

class ListColors extends AbstractList
{

    protected function setup(): array
    {
        return Color::toArray();
    }

}