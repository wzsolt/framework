<?php
namespace Framework\Controllers\Forms\Inputs;

use Framework\Controllers\Forms\AbstractFormElement;
use Framework\Controllers\Forms\ElementChangeStateTrait;
use Framework\Controllers\Forms\ElementOptionsTrait;
use Framework\Controllers\Forms\ElementShowTargetTrait;

class InputSelect extends AbstractFormElement
{
    use ElementOptionsTrait, ElementChangeStateTrait, ElementShowTargetTrait;

    const Type = 'select';

    private bool $multiple = false;

    protected function init():void
    {
        $this->addClass('custom-select');
    }

    public function getType():string
    {
        return $this::Type;
    }

    public function setMultiple(bool $actionBox = false)
    {
        $this->multiple = true;

        if($actionBox){
            $this->addData('actions-box', 'true');
        }

        return $this;
    }

    public function isMultiple(): bool
    {
        return $this->multiple;
    }

    public function connectTo(string|array $elementIds, bool $forceLoad = false):self
    {
        if(!$this->isReadonly() || $forceLoad) {
            if (is_array($elementIds)) {
                $elementIds = implode(',', $elementIds);
            }

            $this->addClass('connected-select');
            $this->addData('connected-select', $elementIds);
        }

        return $this;
    }

    public function makeSelectPicker(bool $search = true, int $maxVisibleItems = 0, bool $ticker = false):self
    {
        $this->removeClass('custom-select')->addClass('select-picker')->addClass('form-control');

        if($search){
            $this->searchable();
        }

        if($maxVisibleItems){
            $this->maxVisibleItems($maxVisibleItems);
        }

        if($ticker){
            $this->ticker();
        }

        return $this;
    }

    public function makeSelect2(bool $isModal = false):self
    {
        $this->addClass('select2');
        if ($isModal) {
            $this->addData('parent', '#ajax-modal');
        }

        return $this;
    }

    public function setClearable():self
    {
        $this->addData('allow-clear', 'true');

        return $this;
    }

    public function setSource(string $url, string|false $default = false):self
    {
        $this->addData('list', $url);

        if($default === false){
            $default = $this->default;
        }

        $this->addData('default-value', $default);

        return $this;
    }

    public function showSubtext():self
    {
        $this->addData('show-subtext', 'true');

        return $this;
    }

    private function searchable():self
    {
        $this->addData('live-search', 'true');

        return $this;
    }

    private function ticker():self
    {
        $this->addClass('show-tick');

        return $this;
    }

    private function maxVisibleItems(int $number = 10):self
    {
        $this->addData('size', $number);

        return $this;
    }
}