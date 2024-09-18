<?php
namespace Framework\Controllers\Buttons;

use Framework\Components\Enums\IconType;

class TableButtonConfirm extends AbstractTableButton
{
    public function __construct(string $id, string $caption, string $class = 'btn-danger')
    {
        parent::__construct($id, $caption, $class);
    }

    public function init(): void
    {
        if($this->disabled){
            $this->addClass('disabled');
        }

        $this->addData('bs-toggle', 'modal');
        $this->addData('bs-target', '#confirm-delete');
    }

    public function setButtonLabel(string $label): self
    {
        $this->addData('confirm-button', $label);

        return $this;
    }

    public function setConfirmQuestion(string $confirmQuestion): self
    {
        $this->addData('confirm-question', $confirmQuestion);

        return $this;
    }

    public function setAction(string $action): self
    {
        $this->addData('href', $action);

        return $this;
    }



}