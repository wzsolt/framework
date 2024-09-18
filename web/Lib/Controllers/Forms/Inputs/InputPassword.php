<?php
namespace Framework\Controllers\Forms\Inputs;

class InputPassword extends InputText
{
    const Type = 'password';

    private bool $showTogglePassword = false;

    public function getType():string
    {
        return $this::Type;
    }

    public function getTemplate():string
    {
        return 'text';
    }

    public function showTogglePassword(bool $show = true):self
    {
        $this->showTogglePassword = $show;

        return $this;
    }

    public function isTogglePasswordVisible():bool
    {
        return $this->showTogglePassword;
    }
}