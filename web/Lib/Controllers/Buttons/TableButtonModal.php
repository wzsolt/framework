<?php
namespace Framework\Controllers\Buttons;

class TableButtonModal extends AbstractTableButton
{
    public function init(): void
    {
        if($this->disabled){
            $this->addClass('disabled');
        }

        $this->setUrl('/ajax/forms/' . $this->getFormName() . '/?fkeys=' . $this->getForeignKeyValues());
    }

}