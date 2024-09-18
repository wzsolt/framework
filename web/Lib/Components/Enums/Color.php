<?php

namespace Framework\Components\Enums;

use Framework\Components\Traits\EnumToArrayTrait;

enum Color
{
    use EnumToArrayTrait;

    case Info;
    case Warning;
    case Danger;
    case Success;
    case Primary;
    case Secondary;
}