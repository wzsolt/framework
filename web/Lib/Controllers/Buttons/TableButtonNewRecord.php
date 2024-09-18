<?php
namespace Framework\Controllers\Buttons;

class TableButtonNewRecord extends AbstractTableButton
{
    public function __construct(string $caption, string $class = 'btn-primary')
    {
        parent::__construct(false, $caption, $class);
    }

    public function init(): void
    {
        if($this->isModal){
            $url = '/ajax/forms/' . $this->getFormName() . '/?id=0&fkeys=' . $this->getForeignKeyValues() . '&table=' . $this->getTableName();
        }else{
            $url = 'edit/?id=0&fkeys=' . $this->getForeignKeyValues() . '&table=' . $this->getTableName();
        }

        if(!$this->getId()) {
            $this->setId('btnNew' . $this->getTableName());
        }

        if(!$this->getIcon()){
            $this->setIcon('fa-solid fa-plus-circle me-1');
        }

        $this->setUrl($url);
    }

}