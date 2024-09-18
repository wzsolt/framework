<?php

namespace Framework\Components\Lists;

class ListTitles extends AbstractList
{
    protected function setup(): array
    {
        return array_combine($GLOBALS['PERSONAL_TITLES'], $GLOBALS['PERSONAL_TITLES']);
    }

}