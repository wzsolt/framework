<?php

namespace Framework\Controllers\Pages;

use Applications\Admin\Controllers\AbstractPageConfig;
use Framework\Controllers\Forms\AbstractFilterForm;
use Framework\Controllers\Tables\AbstractTable;

abstract class AbstractTablePage extends AbstractPageConfig
{
    abstract public function init():?array;

    abstract public function setFilter():?AbstractFilterForm;

    abstract public function setTable():AbstractTable;

    public function setup(): ?array
    {
        $this->setTemplate('page-table');

        $this->setVariable(
            'table',
            $this->addTable($this->setTable())
        );

        $filterForm = $this->setFilter();
        if(!Empty($filterForm)) {
            $this->setVariable(
                'filterForm',
                $this->addForm($filterForm)
            );
        }

        return $this->init();
    }
}