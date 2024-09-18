<?php

namespace Framework\Controllers\Forms\Sections;

use Framework\Controllers\Forms\AbstractFormSections;

class SectionBox extends AbstractFormSections
{
    const Type = 'box';

    public function __construct(string $id, string $title = '', string $icon = '', string $text = '')
    {
        $this->id = $id;
        $this->title = $title;
        $this->icon = $icon;
        $this->text = $text;
        $this->elements = [];
    }

    public function getType(): string
    {
        return self::Type;
    }
}