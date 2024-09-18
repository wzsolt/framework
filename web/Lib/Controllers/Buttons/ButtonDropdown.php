<?php
namespace Framework\Controllers\Buttons;

use Framework\Controllers\Forms\ElementOptionsTrait;

class ButtonDropdown extends ButtonHref
{
    use ElementOptionsTrait;

    const Template = 'dropdown';

    public function __construct($id, $caption = '', $class = 'btn')
    {
        parent::__construct($id, $caption, $class);
    }

    public function getTemplate():string
    {
        return $this::Template;
    }
}