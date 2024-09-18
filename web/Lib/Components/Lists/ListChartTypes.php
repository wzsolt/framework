<?php

namespace Framework\Components\Lists;

use Framework\Components\Enums\ChartType;

class ListChartTypes extends AbstractList
{
    protected function setup(): array
    {
        return ChartType::toArray();
    }

}