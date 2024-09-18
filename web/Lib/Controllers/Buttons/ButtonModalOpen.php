<?php
namespace Framework\Controllers\Buttons;

use Framework\Components\Enums\Size;

class ButtonModalOpen extends ButtonStandard
{
    public function __construct($id, $caption = '', $class = 'btn btn-light')
    {
        parent::__construct($id, $caption, $class);
    }

    public function setFormData(string $formName, int $keyField = 0, array $foreignFields = [], string $tableName = ''):self
    {
        $this->addData('bs-toggle', 'modal');

        $this->addData('bs-target', '#ajax-modal');

        $this->addAttribute('href', '/ajax/forms/' . $formName . '/?id=' . $keyField . '&fkeys=' . implode(',', $foreignFields) . '&table=' . $tableName);

        return $this;
    }

    public function setSize(Size $size):self
    {
        $this->addData('size', strtolower($size->name));

        return $this;
    }
}