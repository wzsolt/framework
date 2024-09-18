<?php
namespace Framework\Controllers\Forms\Components;

use Framework\Controllers\Forms\AbstractFormContainer;
use Framework\Controllers\Tables\AbstractTable;

class SubTable extends AbstractFormContainer
{
    const Type = 'table';

    private AbstractTable $table;

    public function add(AbstractTable $table):self
    {
        $this->table = $table;

        return $this;
    }

    public function getTable():AbstractTable
    {
        return $this->table;
    }

    public function getType():string
    {
        return $this::Type;
    }

    public function openTag():string
    {
        return '<div id="' . $this->getId() . '"' . $this->buildClass() . '>' . ($this->getLabel() ? '<h4 class="text-primary">' . $this->getLabel() . '<h4>' : '');
    }

    public function closeTag():string
    {
        return '</div>';
    }
}