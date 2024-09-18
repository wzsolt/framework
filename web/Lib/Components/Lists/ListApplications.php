<?php

namespace Framework\Components\Lists;

class ListApplications extends AbstractList
{

    protected function setup(): array
    {
        return $GLOBALS['APPLICATIONS'];
    }
}