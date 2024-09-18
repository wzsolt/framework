<?php
namespace Framework\Controllers\Buttons;

use Framework\Components\Enums\ButtonType;
use Framework\Components\Enums\Size;
use Mpdf\Tag\Strike;

abstract class AbstractTableButton extends AbstractButton
{
    const Template = 'href';

    protected bool $isModal = false;

    protected ?Size $modalSize = null;

    protected string $modalTarget = '#ajax-modal';

    protected string $formName = '';

    protected string $tableName;

    protected array $foreignKeyValues = [];

    private bool $isAlwaysVisible = false;

    public function __construct(string $id, string $caption, string $class = 'btn-primary')
    {
        $this->setType(ButtonType::Href);

        $this->setId($id);

        $this->setName($id);

        $class = 'btn waves-effect waves-light ' . $class;

        $this->addClass($class);

        $this->caption = $caption;
    }

    public function getTemplate():string
    {
        return $this::Template;
    }

    public function isModal(): bool
    {
        return $this->isModal;
    }

    public function setModal(bool $isModal, string $target = '#ajax-modal'): self
    {
        $this->isModal = $isModal;

        if($this->isModal) {
            $this->modalTarget = $target;

            $this->addData('bs-toggle', 'modal');

            $this->addData('bs-target', $target);
        }

        return $this;
    }

    public function setModalSize(Size $size = Size::Md):self
    {
        $this->modalSize = $size;

        $this->addData('size', strtolower($size->name));

        return $this;
    }

    public function getModalSize():string
    {
        return (!Empty($this->modalSize) ? strtolower($this->modalSize->name) : '');
    }

    public function disableBackdrop(): self
    {
        $this->addData('bs-backdrop', 'static');
        $this->addData('bs-keyboard', 'false');

        return $this;
    }

    public function getFormName(): string
    {
        return $this->formName;
    }

    public function setFormName(string $formName): self
    {
        $this->formName = $formName;

        return $this;
    }

    public function getForeignKeyValues(string $separator = ',', string $prepend = '', string $append = ''): string
    {
        return (!Empty($this->foreignKeyValues) ? $prepend . implode($separator, $this->foreignKeyValues) . $append : '');
    }

    public function setForeignKeyValues(array $foreignKeyValues): self
    {
        $this->foreignKeyValues = $foreignKeyValues;

        return $this;
    }

    public function setTableName(string $tableName): self
    {
        $this->tableName = $tableName;

        return $this;
    }

    protected function getTableName():string
    {
        return $this->tableName;
    }

    public function isAlwaysVisible(): bool
    {
        return $this->isAlwaysVisible;
    }

    public function setIsAlwaysVisible(bool $isAlwaysVisible): self
    {
        $this->isAlwaysVisible = $isAlwaysVisible;

        return $this;
    }
}