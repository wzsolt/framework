<?php
namespace Framework\Controllers\Tables;

use Framework\Components\Enums\ColumnType;
use Framework\Controllers\Forms\AbstractFormElement;

abstract class AbstractInputColumn extends Column
{
    private AbstractFormElement $control;

    protected abstract function setColumType():ColumnType;

    protected abstract function setControl():AbstractFormElement;

    public function __construct(string $field, string $caption = '', int $width = 1)
    {
        parent::__construct($field, $caption, $width);

        $this->setType($this->setColumType());

        $this->control = $this->setControl();

        $this->setCellClass('table-options');
    }

    public function getControl(string $id, string $keyValue):AbstractFormElement
    {
        $controlId = $this->tableName . '-' . $this->getField() . '-' . str_replace(['|', ','], '-', $id);
        $this->control->setId($controlId);

        $name = $this->tableName . '[' . $this->getField() . '][' . str_replace(['|', ','], '-', $id) . ']';
        $this->control->setName($name);

        $this->control->addData('id', $id);
        $this->control->addData('fkeys', $keyValue);
        $this->control->addData('table', $this->tableName);
        $this->control->addData('field', $this->getField());

        return $this->control;
    }
}