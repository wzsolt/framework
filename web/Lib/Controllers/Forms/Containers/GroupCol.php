<?php
namespace Framework\Controllers\Forms\Containers;

use Framework\Controllers\Forms\AbstractFormContainer;
use Framework\Helpers\Utils;

class GroupCol extends AbstractFormContainer
{
    const Type = 'col';

    private string $size;

    public function __construct(string $id = '', string $size = 'col-12', string $class = '')
    {
        $this->setId(($id ?: 'col-' . Utils::generateRandomString(5)));

        $this->size = $size;

        $this->label = '';

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
        return '<div id="' . $this->getId() . '"' . $this->buildClass($this->size) . $this->buildAttributes() . '>';
    }

    public function closeTag():string
    {
        return '</div>';
    }
}