<?php

namespace Framework\Controllers\Forms\Components;

use Framework\Components\Enums\Color;
use Framework\Controllers\Forms\AbstractFormElement;
use Framework\Controllers\Forms\ElementColorTrait;
use Framework\Helpers\Utils;

class AlertBox extends AbstractFormElement
{
    use ElementColorTrait;

    const Type = 'alertBox';

    private array $content = [];

    public function __construct(string $id, Color $color) {
        $this->isContainer = false;

        $this->setId(($id ?: 'alert-' . Utils::generateRandomString(5)));

        $this->name = $this->getId();

        $this->notDBField();

        $this->setColor($color);

        $this->init();
    }

    protected function init() {}

    public function getType(): string
    {
        return $this::Type;
    }

    public function setContent(string $content):self
    {
        if (!empty($content)) {
            $this->content[] = $content;
        }

        return $this;
    }

    public function clearContent(): self
    {
        $this->content = [];

        return $this;
    }

    public function getContent():array
    {
        return $this->content;
    }
}