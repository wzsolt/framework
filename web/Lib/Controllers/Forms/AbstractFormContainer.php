<?php
namespace Framework\Controllers\Forms;

abstract class AbstractFormContainer extends AbstractFormControl
{
    protected array $elements = [];

    abstract public function openTag():string;

    abstract public function closeTag():string;

    public function __construct(string $id, string $label = '', string $class = ''){
        parent::__construct();

        $this->setId($id);

        $this->label = $label;

        $this->class[] = $class;

        $this->elements = [];

        $this->isContainer = true;
    }

    public function addElements(AbstractFormControl ...$elements):AbstractFormControl
    {
        foreach($elements AS $element) {
            $element->onAfterAdded();

            $this->elements[$element->getId()] = $element;
        }

        return $this;
    }

    public function hasElements():bool
    {
        return !Empty($this->elements);
    }

    public function getElements():array
    {
        return $this->elements;
    }

    public function &getElementsByRef():array
    {
        return $this->elements;
    }

    protected function buildAttributes():string
    {
        $attr = [];

        if(!Empty($this->attributes)){
            foreach($this->attributes AS $key => $value){
                $attr[] = $key . '="' . $value . '"';
            }
        }

        return (!Empty($attr) ? ' ' . implode(' ', $attr) : '');
    }

    protected function buildClass(string $additionalClasses = ''):string
    {
        $class = $this->getClass();
        if(!Empty($additionalClasses)){
            $class .= ' ' . $additionalClasses;
        }

        return (!Empty($class) ? ' class="' . trim($class) . '"' : '');
    }
}