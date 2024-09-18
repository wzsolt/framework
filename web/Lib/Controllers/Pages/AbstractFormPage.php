<?php

namespace Framework\Controllers\Pages;

use Applications\Admin\Controllers\AbstractPageConfig;
use Framework\Controllers\Forms\AbstractForm;

abstract class AbstractFormPage extends AbstractPageConfig
{
    abstract public function init():?array;

    abstract public function setForm(AbstractForm $filter):?AbstractForm;

    public function setup(): ?array
    {
        $this->setTemplate('page-form');

        return $this->init();
    }
}