<?php
namespace Framework\Controllers\Forms;

trait ElementPlaceholderTrait
{
    private string $placeholder = '';

    public function setPlaceholder(string $placeholder):self
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    public function getPlaceholder():string
    {
        return $this->placeholder;
    }
}