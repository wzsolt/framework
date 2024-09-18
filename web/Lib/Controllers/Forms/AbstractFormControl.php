<?php
namespace Framework\Controllers\Forms;

use Framework\Components\Enums\FormEvent;
use Framework\Components\HostConfig;
use Framework\Components\Traits\AddCssTrait;
use Framework\Components\Traits\AddJsTrait;
use Framework\Helpers\Utils;

abstract class AbstractFormControl
{
    use AddJsTrait, AddCssTrait;

    protected string $id;

    protected bool $readonly = false;

    protected bool $disabled = false;

    protected string $sectionId = '';

    protected string $name;

    protected string $label;

    protected array $helpText = [];

    protected string $infoText = '';

    protected array $class = [];

    protected bool $isContainer = false;

    protected bool $dbField = true;

    protected bool $hasError = false;

    protected array $attributes = [];

    protected string $language;

    protected array $locals;

    abstract public function getType():string;

    public function __construct()
    {
        $language = HostConfig::create()->getLanguage();

        $this->locals = Utils::getLocaleSettings($language);

        $this->setLanguage($language);
    }

    public function setId(string $id):self
    {
        $this->id = $id;

        return $this;
    }

    public function onAfterAdded():void
    {
    }

    final public function getId():string
    {
        return str_replace('/', '-', $this->id);
    }

    public function setName(string $name):self
    {
        $this->name = $name;

        return $this;
    }

    final public function getName():string
    {
        $name = $this->name;

        if(str_contains($this->name, '/')){
            $name = str_replace('/', '][', $name);
        }

        return $name;
    }

    public function setLabel(string $label):self
    {
        $this->label = $label;

        return $this;
    }

    public function getLabel():string
    {
        return $this->label;
    }

    final public function setHelpText(string $text, string $icon = ''):self
    {
        if($text) {
            $this->helpText = [
                'text' => $text,
                'icon' => $icon,
            ];
        }

        return $this;
    }

    final public function getHelpText():array
    {
        return $this->helpText;
    }

    final public function setInfoText(string $text):self
    {
        $this->infoText = $text;

        return $this;
    }

    final public function getInfoText():string
    {
        return $this->infoText;
    }

    final public function addClass(string $class):self
    {
        if(!in_array($class, $this->class)) {
            $this->class[] = $class;
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

    final public function isContainer():bool
    {
        return $this->isContainer;
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

    final public function addEvent(FormEvent $eventType, string $action):self
    {
        $this->addAttribute($eventType->event(), $action);

        return $this;
    }

    final public function addData(string $key, string $value, bool $translate = false):self
    {
        $this->addAttribute('data-' . $key, ($translate ? '_' : '') . $value);

        return $this;
    }

    final public function setError():self
    {
        $this->hasError = true;

        return $this;
    }

    final public function hasError():bool
    {
        return $this->hasError;
    }

    final public function setLanguage(string $language):self
    {
        $this->language = $language;

        return $this;
    }

    final public function setSectionId(string $id):self
    {
        $this->sectionId = $id;

        return $this;
    }

    final public function getSectionId():string
    {
        return $this->sectionId;
    }

    final public function setDisabled(bool $disabled = true):self
    {
        $this->disabled = $disabled;

        return $this;
    }

    final public function isDisabled():bool
    {
        return $this->disabled;
    }

    final public function setReadonly(bool $readonly = true):self
    {
        $this->readonly = $readonly;

        return $this;
    }

    final public function isReadonly():bool
    {
        return $this->readonly;
    }
}