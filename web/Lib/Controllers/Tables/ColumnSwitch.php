<?php

namespace Framework\Controllers\Tables;

use Framework\Components\Enums\ColumnType;
use Framework\Controllers\Forms\AbstractFormElement;
use Framework\Controllers\Forms\Inputs\InputSwitch;

class ColumnSwitch extends AbstractInputColumn
{

    protected function setColumType(): ColumnType
    {
        return ColumnType::Switch;
    }

    protected function setControl(): AbstractFormElement
    {
        $switch = new InputSwitch('table-switch');
        $switch->addData('method', 'check');

        $switch->setGroupClass('table-check');

        return $switch;
    }
}