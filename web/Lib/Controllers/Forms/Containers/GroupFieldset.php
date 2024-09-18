<?php
namespace Framework\Controllers\Forms\Containers;

use Framework\Controllers\Forms\AbstractFormContainer;

class GroupFieldset extends AbstractFormContainer
{
    const Type = 'fieldset';

    private string $toolsHtml = '';

    private array $legendClass = [];

    private string $legendStyle = '';

    public function getType():string
    {
        return $this::Type;
    }

    public function addTools(string $label, string $class, string|false $icon = false, string|false $action = false):self
    {
        if($icon){
            $icon = '<i class="' . $icon . ' mr-2 me-2"></i>';
        }else{
            $icon = '';
        }

        $this->toolsHtml = '<a href="' . ($action ?: 'javascript:;') . '" class="' . $class . '">' . $icon . '{{ _("' . $label . '") }}</a>';

        return $this;
    }

    public function openTag():string
    {
        $html = '<fieldset id="' . $this->getId() . '"' . $this->buildClass('form-fieldset') . $this->buildAttributes() . '>';
        if(!Empty($this->label)){
            if($this->legendClass){
                $legendClass = implode(' ', $this->legendClass);
            }else{
                $legendClass = 'text-primary';
            }

            $html .= '<legend class="' . $legendClass . '"' . ($this->legendStyle ? ' style="' . $this->legendStyle . '"' : '') . '>{{ _("' . $this->getLabel() . '") }}';

            if($this->toolsHtml){
                $html .= '<div class="formbuilder-group-tools float-right float-end">' . $this->toolsHtml . '</div>';
            }

            $html .= '</legend>';
        }
        return $html;
    }

    public function closeTag():string
    {
        return '</fieldset>';
    }

    public function addLegendClass(string $class):self
    {
        if(!in_array($class, $this->legendClass)) {
            $this->legendClass[] = $class;
        }

        return $this;
    }

    public function addLegendStyle(string $style):self
    {
        $this->legendStyle = $style;

        return $this;
    }

}