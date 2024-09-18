<?php
namespace Framework\Controllers\Forms\Containers;

use Framework\Controllers\Forms\AbstractFormContainer;

class GroupTemplateString extends AbstractFormContainer
{
    const Type = 'templateString';

    private string $html;

    public function __construct(string $id, string $html = '', string $class = '')
    {
        $this->id = $id;

        $this->html = $html;

        if($class) {
            $this->addClass($class);
        }

        $this->isContainer = true;
    }

    public function setHtml(string $html):self
    {
        $this->html = $html;

        return $this;
    }

    public function getType():string
    {
        return $this::Type;
    }

    public function getTemplateString():string
    {
        return $this->html;
    }

    public function openTag():string
    {
        return '<div class="form-group mb-2 ' . $this->getClass() . '">';
    }

    public function closeTag():string
    {
        return '</div>';
    }
}