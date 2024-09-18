<?php

namespace Framework\Controllers\Forms\Components;

use Framework\Controllers\Forms\AbstractFormElement;

class Badge extends AbstractFormElement
{
    const Type = 'badge';

    private string $enum;

    private int $badgeSize = 4;

    public function getType(): string
    {
        return $this::Type;
    }

    protected function init()
    {
        // TODO: Implement init() method.
    }

    public function setEnumList(string $enumClassName):self
    {
        $this->enum = "\\Framework\\Components\\Enums\\" . $enumClassName;

        return $this;
    }

    public function setSize(int $badgeSize):self
    {
        $this->badgeSize = $badgeSize;

        return $this;
    }

    public function getSize():int
    {
        return $this->badgeSize;
    }

    public function color($value):string
    {
        return call_user_func([$this->enum, 'from'], $value)->color();
    }

    public function label($value):string
    {
        return call_user_func([$this->enum, 'from'], $value)->label();
    }
}