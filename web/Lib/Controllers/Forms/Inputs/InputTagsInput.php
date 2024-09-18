<?php
namespace Framework\Controllers\Forms\Inputs;

use Framework\Controllers\Forms\AbstractFormElement;

class InputTagsInput extends AbstractFormElement
{
    const Type = 'tagsInput';

    protected function init():void
    {
        $this->addClass('form-control tags-input');

        $this->addData('free-input', true);

        //$this->addData('role', 'tagsinput');
    }

    public function getType():string
    {
        return $this::Type;
    }

    public function disableFreeInput():self
    {
        $this->addData('free-input', false);

        return $this;
    }
}