<?php

namespace Framework\Components\Lists;

class ListDaysOfWeek extends AbstractList
{

    protected function setup(): array
    {
        $dow = [];

        for ($i = 1; $i <= 7; $i++) {
            $dow[$i] = 'LBL_DAY_' . $i;
        }

        return $dow;
    }

}