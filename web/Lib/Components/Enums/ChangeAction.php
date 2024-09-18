<?php

namespace Framework\Components\Enums;

enum ChangeAction
{
    case Show;
    case Hide;
    case Disable;
    case Enable;
    case Readonly;
    case Editable;
    case SetValue;
}