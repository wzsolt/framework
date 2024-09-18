<?php

namespace Framework\Components\Enums;

use Framework\Components\Traits\EnumToArrayTrait;

enum ChartType
{
    use EnumToArrayTrait;

    case Line;
    case Bar;
    case Area;
    case Pie;
}