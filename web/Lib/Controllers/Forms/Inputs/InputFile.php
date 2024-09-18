<?php
namespace Framework\Controllers\Forms\Inputs;

use Framework\Controllers\Forms\ElementPlaceholderTrait;

class InputFile extends AbstractFileUpload
{
    use ElementPlaceholderTrait;

    const Type = 'file';

    public function init():void
    {
        $this->addClass('file');
        $this->addCss('bootstrap-fileinput/css/fileinput.min.css', false, 'fileinput');
        $this->addJs('bootstrap-fileinput/js/fileinput.min.js', false, 'fileinput');
        $this->addJs('bootstrap-fileinput/themes/fas/theme.min.js', false, 'fileinput-theme');
    }

    public function getType():string
    {
        return $this::Type;
    }

    public function setMultiple():self
    {
        $this->addAttribute('multiple', 'multiple');

        return $this;
    }
}