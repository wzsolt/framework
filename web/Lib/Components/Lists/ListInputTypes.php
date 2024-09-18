<?php

namespace Framework\Components\Lists;

use Framework\Components\Enums\InputType;

class ListInputTypes extends AbstractList
{

    protected function setup(): array
    {
        return InputType::toArray();
    }

}