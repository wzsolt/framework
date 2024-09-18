<?php
namespace Framework\Controllers\Forms\Components;

use Framework\Controllers\Forms\AbstractForm;
use Framework\Controllers\Forms\AbstractFormContainer;

class SubForm extends AbstractFormContainer
{
    const Type = 'sub-form';

    private AbstractForm $form;

    public function add(AbstractForm $form):self
    {
        $this->form = $form;

        return $this;
    }

    public function getForm():AbstractForm
    {
        return $this->form;
    }

    public function getType():string
    {
        return $this::Type;
    }

    public function openTag():string
    {
        return '';
    }

    public function closeTag():string
    {
        return '';
    }
}