<?php
namespace Framework\Controllers\Buttons;

use Framework\Components\Enums\ButtonType;
use Framework\Components\Enums\FormEvent;
use Framework\Components\Enums\IconType;

abstract class AbstractButton
{
    protected string $id;

    protected string $name = '';

    protected string $type;

    protected string $url = '';

    protected string $caption = '';

    protected array $class = [];

    protected array $attributes = [];

    protected string $icon = '';

    protected string|false $iconType = false;

    protected bool $disabled = false;

    protected bool $hidden = false;

    protected int $value = 1;

    protected string $target = '';

    abstract public function getTemplate():string;

    abstract public function init();

    final protected function setType(ButtonType $type):self
    {
        $this->type = $type->name;

        return $this;
    }

    final public function getType():string
    {
        return $this->type;
    }

    final public function setId($id):self
    {
        $this->id = $id;

        return $this;
    }

    final public function getId():string
    {
        return $this->id;
    }

    final public function setValue(int $value):self
    {
        $this->value = $value;

        return $this;
    }

    final public function getValue():int
    {
        return $this->value;
    }

    public function setName(string $name):self
    {
        $this->name = $name;

        return $this;
    }

    final public function getName():string
    {
        return $this->name;
    }

    final public function getCaption():string
    {
        return $this->caption;
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

    final public function setHidden(bool $hidden = true):self
    {
        $this->hidden = $hidden;

        return $this;
    }

    final public function isHidden():bool
    {
        return $this->hidden;
    }

    final public function addClass(string $class):self
    {
        $this->class[] = $class;

        return $this;
    }

    final public function getClass():string
    {
        return implode(' ', $this->class);
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

    final public function addAttribute(string $key, string $value):self
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    final public function getAttributes():array
    {
        return $this->attributes;
    }

    final public function setIcon(string $icon, ?IconType $type = null):self
    {
        $this->icon = $icon;

        if(!$type) {
            $this->iconType = IconType::FontAwesome->key();
        }else{
            $this->iconType = $type->key();
        }

        return $this;
    }

    final public function getIcon():string
    {
        return $this->icon;
    }

    final public function getIconType():string
    {
        return $this->iconType;
    }

    final public function setUrl(string $url):self
    {
        $this->url = $url;

        return $this;
    }

    final public function getUrl():string
    {
        return $this->url;
    }

    final public function getTarget(): string
    {
        return $this->target;
    }

    final public function setTarget(string $target): self
    {
        $this->target = $target;

        return $this;
    }


}