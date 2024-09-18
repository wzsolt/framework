<?php

namespace Framework\Components\Lists;

use Framework\Locale\Translate;

class ListUserGroups extends AbstractList
{
    protected function setup(): array
    {
        $list = [];

        foreach ($GLOBALS['USER_GROUPS'] as $key => $value) {
            $list[$key] = [
                'name'  => Translate::get($value['label']),
                'class' => $value['color'],
                'app'   => $value['app']
            ];
        }

        return $list;
    }

}