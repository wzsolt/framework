<?php

namespace Framework\Components\Lists;

class ListThemes extends AbstractList
{
    protected function setup(): array
    {
        $list = [];

        foreach ($GLOBALS['THEMES'] as $key => $theme) {
            $list[$key] = $theme['name'];
        }

        return $list;
    }

}