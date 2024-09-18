<?php
namespace Framework\Controllers\Forms;

use Framework\Components\Enums\Color;
use Framework\Components\Enums\IconType;
use Framework\Components\Enums\Size;

abstract class AbstractFormElement extends AbstractFormControl
{
    protected ?string $default;

    protected bool $required = false;

    protected string $prepend = '';

    protected string $append = '';

    protected array $constraints = [];

    protected array $groupClasses = [];

    protected string|false $size = false;

    protected string|false $icon = false;

    protected string $iconType = 'fa';

    protected bool $iconPosition = false;

    protected ?Color $iconColor;

    abstract protected function init();

    public function __construct(string $id, string $label = '', string $default = '', string $class = ''){
        parent::__construct();

        $this->isContainer = false;

        $this->setId($id);

        $this->setName($id);

        $this->label = $label;

        $this->default = $default;

        $this->class[] = $class;

        $this->init();
    }

    public function getTemplate():string
    {
        return $this->getType();
    }

    public function setInlineJs():string|false
    {
        return false;
    }

    final public function getDefault():string
    {
        return ($this->default ?? '');
    }

    final public function setRequired(bool $required = true):self
    {
        if($required) {
            $this->setConstraints('required', $required);
        }

        return $this;
    }

    final public function isRequired():bool
    {
        return (bool)($this->constraints['required'] ?? false);
    }

    final public function notDBField():self
    {
        $this->dbField = false;

        return $this;
    }

    final public function isDBField():bool
    {
        return $this->dbField;
    }

    final public function setConstraints(string $key, string$value):self
    {
        $this->constraints[$key] = $value;

        $this->addAttribute('data-parsley-' . $key, $value);

        return $this;
    }

    final public function getConstraints():array
    {
        return $this->constraints;
    }

    final public function setPrepend(string $tag):self
    {
        $this->prepend = $tag;

        return $this;
    }

    final public function getPrepend():string
    {
        return $this->prepend;
    }

    final public function setAppend(string $tag):self
    {
        $this->append = $tag;

        return $this;
    }

    final public function getAppend():string
    {
        return $this->append;
    }

    final public function setColSize(string $columnSize):self
    {
        $this->setGroupClass($columnSize);

        return $this;
    }

    final public function setInputSize(Size $inputSize):self
    {
        $this->size = strtolower($inputSize->name);

        return $this;
    }

    final public function getInputSize():string
    {
        return $this->size;
    }

    final public function setGroupClass(string $class):self
    {
        $this->groupClasses[] = $class;

        return $this;
    }

    final public function addEmptyLabel():self
    {
        $this->setGroupClass('pt-empty-label');

        return $this;
    }

    final public function getGroupClasses():string
    {
        return ($this->groupClasses ? ' ' . implode(' ', $this->groupClasses) : '');
    }

    final public function setIcon($icon, IconType $type = null, Color $color = null, bool $positionRight = false):self
    {
        $this->icon = $icon;

        if(!$type) {
            $this->iconType = IconType::FontAwesome->key();
        }else{
            $this->iconType = $type->key();
        }

        $this->iconColor = $color;

        $this->iconPosition = $positionRight;

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

    final public function getIconColor():string
    {
        return ($this->iconColor->name ?? '');
    }

    final public function getIconPosition():bool
    {
        return $this->iconPosition;
    }
}