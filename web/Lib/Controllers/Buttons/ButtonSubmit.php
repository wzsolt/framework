<?php
namespace Framework\Controllers\Buttons;

use Framework\Components\Enums\ButtonType;

class ButtonSubmit extends AbstractFormButton
{
    const Template = 'button';

    public function __construct($id, $caption = '', $class = 'btn btn-primary')
    {

        $this->setType(ButtonType::Submit);

        $this->setShowInEditor(true);

        $this->setShowInViewer(false);

        $this->setId($id);

        $this->setName($id);

        $this->caption = $caption;

        $this->class[] = $class;
    }

    public function getTemplate(): string
    {
        return $this::Template;
    }

    public function init(): self
    {
        if($this->readonly){
            $this->setHidden();
        }

        return $this;
    }
}