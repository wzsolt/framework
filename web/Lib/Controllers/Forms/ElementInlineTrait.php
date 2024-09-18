<?php
namespace Framework\Controllers\Forms;

trait ElementInlineTrait
{
    private bool $inline = false;

    public function setInline(bool $inline = true):self
    {
        $this->inline = $inline;

        return $this;
    }

    public function isInline():bool
    {
        return $this->inline;
    }
}