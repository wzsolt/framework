<?php
namespace Framework\Controllers\Forms\Containers;

use Framework\Controllers\Forms\AbstractFormContainer;

class GroupHtml extends AbstractFormContainer
{
    const Type = 'html';

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

    public function openTag():string
    {
        return $this->html;
    }

    public function closeTag():string
    {
        return '';
    }
}