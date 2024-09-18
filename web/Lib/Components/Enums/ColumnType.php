<?php

namespace Framework\Components\Enums;

enum ColumnType
{
    case General;
    case Hidden;
    case Options;
    case MultipleOptions;
    case Checkbox;
    case Radio;
    case Switch;
    case YesNo;
    case Icon;
}