<?php

namespace Framework\Locale;

class CustomReplace
{
    public array $args;

    function replace($matches)
    {
        return $this->args[$matches[1]];
    }
}