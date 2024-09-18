<?php
namespace Framework\Controllers\Forms;

trait ElementShowTargetTrait
{
    public function showTarget(string $elementIdPrefix, string $groupClass = ''):self
    {
        $this->addClass('show-target');

        $this->addData('prefix', $elementIdPrefix);

        if($groupClass) {
            $this->addData('group', $groupClass);
        }

        return $this;
    }
}