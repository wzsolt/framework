<?php
namespace Framework\Controllers\Tables;

use Framework\Components\Enums\ColumnType;

class ColumnHidden extends Column
{
    private array $options = [];

    public function __construct(string $field)
    {
        parent::__construct($field, false, false);

        $this->setType(ColumnType::Hidden);
    }

    public function setOptions(array $options):Column
    {
        $this->options = $options;

        return $this;
    }

    public function getOptions():array
    {
        return $this->options;
    }
}