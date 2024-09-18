<?php
namespace Framework\Controllers\Forms;

trait ElementOptionsTrait
{
    private array $options = [];

    public function setOptions(array $options):self
    {
        $this->options = $options;

        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}