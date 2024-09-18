<?php

namespace Framework\Components\Enums;

enum ItemPosition:int
{
    case Hidden = 0;
    case Top    = 1;
    case Bottom = 2;
    case Both   = 3;
}