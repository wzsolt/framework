<?php

namespace Framework\Components\Enums;

enum Crop:int
{
    case Simple     = 0;
    case Adaptive   = 1;
    case Center     = 2;
}