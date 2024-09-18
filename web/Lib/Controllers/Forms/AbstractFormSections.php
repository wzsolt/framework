<?php
namespace Framework\Controllers\Forms;

abstract class AbstractFormSections
{
    protected string $id;

    protected string $type;

    protected string $title = '';

    protected bool $active = false;

    protected string $text = '';

    protected string $icon = '';

    protected array $elements = [];

    protected array $class = [];

    protected string $style = '';

    protected array $tabPaneClass = [];

    protected array $attributes = [];

    private string $link = '';

    abstract public function getType():string;

    final public function getId():string
    {
        return $this->id;
    }

    public function getTitle():string
    {
        return $this->title;
    }

    public function getIcon():string
    {
        return $this->icon;
    }

    public function getText():string
    {
        return $this->text;
    }

    final public function isActive():bool
    {
        return $this->active;
    }

    final public function setActive(bool $isActive = true):self
    {
        $this->active = $isActive;

        return $this;
    }

    final public function addAttribute(string $key, string $value):self
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    final public function getAttributes():array
    {
        return $this->attributes;
    }

    final public function addData(string $key, string $value, bool $translate = false):self
    {
        $this->addAttribute('data-' . $key, ($translate ? '_' : '') . $value);

        return $this;
    }

    public function addElements(AbstractFormControl ...$elements):self
    {
        foreach($elements AS $element) {
            $this->elements[$element->getId()] = $element;
        }

        return $this;
    }

    public function getElements():array
    {
        return $this->elements;
    }

    public function hasElements():bool
    {
        return !Empty($this->elements);
    }

    final public function addClass(string $class):self
    {
        if(!in_array($class, $this->class)) {
            $this->class[] = $class;
        }

        return $this;
    }

    final public function addTabPaneClass(string $class):self
    {
        if(!in_array($class, $this->tabPaneClass)) {
            $this->tabPaneClass[] = $class;
        }

        return $this;
    }

    final public function removeClass(string $class):self
    {
        foreach($this->class AS $key => $value){
            if($value == $class){
                unset($this->class[$key]);
                break;
            }
        }

        return $this;
    }

    final public function getClass():string
    {
        return implode(' ', $this->class);
    }

    final public function getTabPaneClass():string
    {
        return implode(' ', $this->tabPaneClass);
    }

    public function setLink(string $link):self
    {
        $this->link = $link;

        return $this;
    }

    public function getLink():string
    {
        return $this->link;
    }

    public function setStyle($style):self
    {
        $this->style = $style;

        return $this;
    }

    public function getStyle():string
    {
        return $this->style;
    }
}