<?php
namespace Framework\Controllers\Tables;

use Framework\Components\Enums\ColumnType;

class ColumnIcon extends Column
{
    private array $icons = [];

    public function __construct(string $field, string $caption = '', int $width = 1)
    {
        parent::__construct($field, $caption, $width);

        $this->setType(ColumnType::Icon);
    }

    public function setIcons(array $icons):Column
    {
        $this->icons = $icons;

        return $this;
    }

    public function getIcons():array
    {
        return $this->icons;
    }
}