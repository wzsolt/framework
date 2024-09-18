<?php
namespace Framework\Controllers\Forms\Containers;

use Framework\Controllers\Forms\AbstractFormContainer;
use Framework\Helpers\Utils;

class GroupRow extends AbstractFormContainer
{
    const Type = 'row';

    public function __construct(string $id = '', string $label = '', string $class = '')
    {
        $this->setId(($id ?: 'row-' . Utils::generateRandomString(5)));

        $this->label = $label;

        $this->class[] = $class;

        $this->elements = [];

        $this->isContainer = true;
    }

    public function getType():string
    {
        return $this::Type;
    }

    public function openTag():string
    {
        return '<div id="' . $this->getId() . '"' . $this->buildClass('form-row row') . $this->buildAttributes() . '>';
    }

    public function closeTag():string
    {
        return '</div>';
    }
}