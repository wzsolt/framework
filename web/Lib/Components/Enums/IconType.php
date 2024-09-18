<?php

namespace Framework\Components\Enums;

enum IconType
{
    case FontAwesome;
    case Feather;

    public function key(): string
    {
        return match($this) {
            IconType::FontAwesome => 'fa',
            IconType::Feather => 'feather',
        };
    }
}