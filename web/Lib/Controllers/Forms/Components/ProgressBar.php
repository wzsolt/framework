<?php

namespace Framework\Controllers\Forms\Components;

use Framework\Controllers\Forms\AbstractFormElement;
use Framework\Controllers\Forms\ElementColorTrait;

class ProgressBar extends AbstractFormElement
{
    use ElementColorTrait;

    const Type = 'progressBar';

    private int $value = 0;

    private int $minValue = 0;

    private int $maxValue = 100;

    private bool $showPercentage = true;

    public function __construct(string $id, int $value = 1, string $class = 'progress-bar-striped')
    {
        $this->setId($id);

        $this->setName($id);

        $this->setValue($value);

        $this->class[] = $class;
    }

    protected function init():void
    {
        $this->notDBField();
    }

    public function getType(): string
    {
        return $this::Type;
    }

    public function setValue(int $value):self
    {
        $this->value = $value;

        return $this;
    }

    public function getValue():int
    {
        return $this->value;
    }

    public function getMinValue():int
    {
        return $this->minValue;
    }

    public function setMinValue(int $minValue = 0):self
    {
        $this->minValue = $minValue;

        return $this;
    }

    public function getMaxValue():int
    {
        return $this->maxValue;
    }

    public function setMaxValue(int $maxValue = 100):self
    {
        $this->maxValue = $maxValue;

        return $this;
    }

    public function hidePercentage():self
    {
        $this->showPercentage = false;

        return $this;
    }

    public function showPercentage():bool
    {
        return $this->showPercentage;
    }
}