<?php

namespace Framework\Controllers\Forms\Components;

use Framework\Controllers\Forms\AbstractFormElement;
use Framework\Helpers\Utils;

class TemplateString extends AbstractFormElement
{
    const Type = 'template';

    private string $html = '';

    public function __construct(string $html, string|false $id = false)
    {
        $this->setId(($id ?: Utils::generateRandomString(5)));

        $this->setName('');

        $this->setHtml($html);
    }

    public function init():void
    {
        $this->notDBField();
    }

    public function getType(): string
    {
        return $this::Type;
    }

    public function setHtml(string $html):self
    {
        $this->html = $html;

        return $this;
    }

    public function getHtml():string
    {
        return $this->html;
    }
}