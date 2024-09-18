<?php

namespace Framework\Controllers\Forms\Sections;

use Framework\Controllers\Forms\AbstractFormSections;

class SectionTab extends AbstractFormSections
{
    const Type = 'tab';

    public function __construct(string $id, string $title = '', string $icon = '', bool $active = false)
    {
        $this->id = $id;

        $this->title = $title;

        $this->icon = $icon;

        if (isset($_REQUEST['tab'])) {
            if ($_REQUEST['tab'] == $id) {
                $this->active = true;
            } else {
                $this->active = false;
            }
        } else {
            $this->active = $active;
        }

        $this->elements = [];
    }

    public function getType(): string
    {
        return self::Type;
    }
}