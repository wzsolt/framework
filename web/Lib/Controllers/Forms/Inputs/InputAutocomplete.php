<?php
namespace Framework\Controllers\Forms\Inputs;

class InputAutocomplete extends InputText
{
    const Type = 'autocomplete';

    private string $valueFieldName = 'value';

    private string $textFieldName = 'text';

    protected function init(): void
    {
        $this->addClass('autocomplete');
    }

    public function getType():string
    {
        return $this::Type;
    }

    public function getTemplate():string
    {
        return 'autocomplete';
    }

    public function setList(string $list):self
    {
        $this->addData('list', $list);

        return $this;
    }

    public function setUrl(string $listUrl):self
    {
        $this->addData('url', $listUrl);

        return $this;
    }

    public function connectTo(array $elementIds):self
    {
        $elementIds = implode(',', $elementIds);

        $this->addData('connected-select', $elementIds);

        return $this;
    }

    public function postFields(array $elementIds):self
    {
        $elementIds = implode(',', $elementIds);

        $this->addData('extra-params', $elementIds);

        return $this;
    }

    public function insertFields(array $elementIds):self
    {
        $elementIds = implode(',', $elementIds);

        $this->addData('insert-fields', $elementIds);

        return $this;
    }

    public function callback(string $action):self
    {
        $this->addData('callback', $action);

        return $this;
    }

    public function setValueFieldName(string $name):self
    {
        $this->valueFieldName = $name;

        return $this;
    }

    public function getValueFieldName():string
    {
        return $this->valueFieldName;
    }


    public function setTextFieldName(string $name):self
    {
        $this->textFieldName = $name;

        return $this;
    }

    public function getTextFieldName():string
    {
        return $this->textFieldName;
    }
}