<?php
namespace Framework\Controllers\Buttons;

abstract class AbstractFormButton extends AbstractButton
{
    protected string $form = '';

    protected bool $validate = true;

    protected bool $showInView = false;

    protected bool $showInEditor = true;

    protected bool $readonly = false;

    final public function skipValidation():self
    {
        $this->validate = false;

        return $this;
    }

    final public function validate():bool
    {
        return $this->validate;
    }

    final public function setForm(string $formName):self
    {
        $this->form = $formName;
        $this->id .= '-' . $formName;

        return $this;
    }

    final public function getForm():string
    {
        return $this->form;
    }

    final public function setShowInEditor(bool $mode):self
    {
        $this->showInEditor = $mode;

        return $this;
    }

    final public function showInEditor():bool
    {
        return $this->showInEditor;
    }

    final public function setShowInViewer(bool $mode):self
    {
        $this->showInView = $mode;

        return $this;
    }

    final public function showInViewer():bool
    {
        return $this->showInView;
    }

    final public function setReadOnly(bool $isReadonly = true):self
    {
        $this->readonly = $isReadonly;

        return $this;
    }
}