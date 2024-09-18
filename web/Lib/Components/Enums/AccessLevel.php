<?php

namespace Framework\Components\Enums;

enum AccessLevel: int
{
    case NoAccess       = 0;
    case Readonly       = 1;
    case ReadAndWrite   = 2;
    case FullAccess     = 3;
}
