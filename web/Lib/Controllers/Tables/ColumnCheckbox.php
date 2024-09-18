<?php

namespace Framework\Controllers\Tables;

use Framework\Components\Enums\ColumnType;
use Framework\Controllers\Forms\AbstractFormElement;
use Framework\Controllers\Forms\Inputs\InputCheckbox;

class ColumnCheckbox extends AbstractInputColumn
{

    protected function setColumType(): ColumnType
    {
        return ColumnType::Checkbox;
    }

    protected function setControl(): AbstractFormElement
    {
        $checkbox = new InputCheckbox('table-check');
        $checkbox->addData('method', 'check');

        $checkbox->setGroupClass('table-check');

        return $checkbox;
    }
}