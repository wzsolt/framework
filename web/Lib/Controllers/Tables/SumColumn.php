<?php
namespace Framework\Controllers\Tables;

class SumColumn
{
    private string $id;

    private string $field;

    private string $groupField;

    private string $caption;

    private array $cellClass = [];

    private int $colspan = 0;

    private string $template = '';

    private string $style = '';

    private bool $summarizeField = false;

    private string $query = '';

    private mixed $value = 0;

    private string $unit = '';

    public function __construct(string $caption = '', int $colspan = 0, string $id = '')
    {
        $this->setColspan($colspan);

        $this->caption = $caption;

        $this->setId($id);
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

    public function addClass(string $class):self
    {
        $this->setCellClass($class);

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

    public function setTemplate(string $template):self
    {
        $this->template = $template;

        return $this;
    }

    public function getTemplate():string
    {
        return $this->template;
    }

    public function isSummarizeField(): bool
    {
        return $this->summarizeField;
    }

    public function setField(string $fieldName, string $groupFiled = ''): self
    {
        $this->field = $fieldName;

        $this->groupField = $groupFiled;

        $this->summarizeField = true;

        return $this;
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getGroupField(): string
    {
        return $this->groupField;
    }

    public function setQuery(string $query):self
    {
        $this->query = $query;

        return $this;
    }

    public function getQuery():string
    {
        return $this->query;
    }

    public function setValue(mixed $value):self
    {
        $this->value = $value;

        return $this;
    }

    public function getValue():mixed
    {
        return $this->value;
    }

    public function setUnit(string $unit):self
    {
        $this->unit = $unit;

        return $this;
    }

    public function getUnit():string
    {
        return $this->unit;
    }

}