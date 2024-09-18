<?php
namespace Framework\Controllers\Forms\Containers;

use Framework\Controllers\Forms\AbstractFormContainer;

class GroupInclude extends AbstractFormContainer
{
    const Type = 'include';

    private string $file;

    private array $data;

    public function __construct(string $includeFileName, array $data = [])
    {
        $this->setId($includeFileName);

        $this->file = $includeFileName;

        $this->data = $data;

        $this->isContainer = true;

        return $this;
    }

    public function getInclude():string
    {
        return $this->file;
    }

    public function setData(array $data):self
    {
        if(is_array($data) && !Empty($data)){
            foreach($data AS $key => $value){
                $this->data[$key] = $value;
            }
        }

        return $this;
    }

    public function getData():array
    {
        return $this->data;
    }

    public function getType():string
    {
        return $this::Type;
    }

    public function openTag():string
    {
        return '';
    }

    public function closeTag():string
    {
        return '';
    }
}