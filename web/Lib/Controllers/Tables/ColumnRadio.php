<?php

namespace Framework\Controllers\Tables;

use Framework\Components\Enums\ColumnType;
use Framework\Controllers\Forms\AbstractFormElement;
use Framework\Controllers\Forms\Inputs\InputRadio;

class ColumnRadio extends AbstractInputColumn
{

    protected function setColumType(): ColumnType
    {
        return ColumnType::Radio;
    }

    protected function setControl(): AbstractFormElement
    {
        $radio = new InputRadio('table-radio');
        $radio->addData('method', 'mark');

        $radio->setGroupClass('table-check');

        $radio->setOptions([1 => '']);

        return $radio;
    }
}