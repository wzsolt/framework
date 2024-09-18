<?php
namespace Framework\Controllers\Tables;

use Framework\Components\Enums\ColumnType;

class Column
{
    private string $id;

    private string $field;

    private string $alias = '';

    private string $caption;

    private ColumnType $type;

    private array $headerClass = [];

    private array $cellClass = [];

    private int $width;

    private int $colspan = 0;

    private string $template = '';

    private string $style = '';

    private string $icon = '';

    private string $headerStyle = '';

    protected string $tableName;

    public function __construct(string $field, string $caption = '', int $width = 1)
    {
        $this->id = $field;

        $this->field = $field;

        $this->caption = $caption;

        $this->setWidth($width);

        $this->setType(ColumnType::General);
    }

    public function setId(string $id):self
    {
        $this->id = $id;

        return $this;
    }

    public function getId():string
    {
        return $this->id;
    }

    public function getCaption():string
    {
        return $this->caption;
    }

    public function setAlias(string $alias):self
    {
        $this->alias = $alias;

        return $this;
    }

    public function getAlias():string
    {
        return $this->alias;
    }

    public function getField():string
    {
        return (!Empty($this->alias) ? $this->alias : $this->field);
    }

    public function getSelectField():string
    {
        return ($this->alias ? $this->field . ' AS ' . $this->alias : $this->field);
    }

    protected function setType(ColumnType $type):self
    {
        $this->type = $type;

        return $this;
    }

    public function getType():string
    {
        return strtolower($this->type->name);
    }

    public function addClass(string $class):self
    {
        $this->setCellClass($class);

        $this->setHeaderClass($class);

        return $this;
    }

    public function setCellClass(string $class):self
    {
        if(!in_array($class, $this->cellClass)) {
            $this->cellClass[] = $class;
        }

        return $this;
    }

    public function getCellClass():string
    {
        return implode(' ', $this->cellClass);
    }

    public function setHeaderClass(string $class):self
    {
        if(!in_array($class, $this->headerClass)) {
            $this->headerClass[] = $class;
        }

        return $this;
    }

    public function getHeaderClass():string
    {
        return implode(' ', $this->headerClass);
    }

    public function setWidth(int $width):self
    {
        $this->width = $width;

        return $this;
    }

    public function getWidth():int
    {
        return $this->width;
    }

    public function setColspan(int $colspan):self
    {
        $this->colspan = $colspan;

        return $this;
    }

    public function getColspan():int
    {
        return $this->colspan;
    }

    public function setStyle(string $style):self
    {
        $this->style = $style;

        return $this;
    }

    public function getStyle():string
    {
        return $this->style;
    }

    public function setHeaderStyle(string $style):self
    {
        $this->headerStyle = $style;

        return $this;
    }

    public function getHeaderStyle():string
    {
        return $this->headerStyle;
    }

    public function setTemplate(string $template):self
    {
        $this->template = $template;

        return $this;
    }

    public function getTemplate():string
    {
        return $this->template;
    }

    public function setIcon(string $icon):self
    {
        $this->icon = $icon;

        return $this;
    }

    public function getIcon():string
    {
        return $this->icon;
    }

    public function setTableName(string $tableName):self
    {
        $this->tableName = $tableName;

        return $this;
    }
}