<?php
namespace Framework\Controllers\Tables;

use Framework\Components\Enums\ColumnType;

class ColumnMultipleOptions extends Column
{
    private array $options = [];

    public function __construct(string $field, string $caption = '', int $width = 1)
    {
        parent::__construct($field, $caption, $width);

        $this->setType(ColumnType::MultipleOptions);
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