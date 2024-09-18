<?php

namespace Applications\Admin\Controllers\Pages;

use Applications\Admin\Controllers\Tables\TemplatesTable;
use Framework\Controllers\Forms\AbstractFilterForm;
use Framework\Controllers\Pages\AbstractTablePage;
use Framework\Controllers\Tables\AbstractTable;

class Templates extends AbstractTablePage
{
    public function init(): ?array
    {
        return [];
    }

    public function setFilter(): ?AbstractFilterForm
    {
        return null;
    }

    public function setTable(): AbstractTable
    {
        return new TemplatesTable();
    }
}