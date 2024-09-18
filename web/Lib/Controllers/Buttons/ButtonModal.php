<?php
namespace Framework\Controllers\Buttons;

class ButtonModal extends AbstractFormButton
{
    const Template = 'button';

    public function __construct(string $id, string $caption = '', string $class = 'btn btn-danger')
    {
        $this->id = $id;

        $this->setName($id);

        $this->caption = $caption;

        $this->class[] = $class;

        $this->init();
    }

    public function init():self
    {
        return $this;
    }

    public function getTemplate():string
    {
        return $this::Template;
    }

    protected function postForm(string $action, string $value = '', string $additionalAction = ''):self
    {
        if($value){
            $this->setValue($value);
        }

        $params = [
            $this->getForm() . "[" . $this->name . "]" => $this->value,
            'id' => ($_REQUEST['id'] ?? 0),
            'fkeys' => ($_REQUEST['fkeys'] ?? 0),
            'table' => ($_REQUEST['table'] ?? ''),
        ];

        $this->addData('confirm-' . $action, ($additionalAction ? $additionalAction . ';' : '') . "$('#" . $this->getForm() . "-form').attr('action', './?" . http_build_query($params) . "').submit();");

        return $this;
    }

    protected function postModalForm(string $action, string $value = '', string $additionalAction = ''):self
    {
        if($value){
            $this->setValue($value);
        }

        $this->addData('confirm-' . $action, ($additionalAction ? $additionalAction . ';' : '') . 'postModalForm("#' . $this->getForm() . '-form", ' . $this->value . ', "' . $this->name . '")');

        return $this;
    }

    protected function dialogColor():void
    {
        $color = 'btn-danger';

        if($classes = explode(' ', $this->getClass())){
            $validClasses = ['btn-default', 'btn-info', 'btn-warning', 'btn-danger', 'btn-success'];
            foreach ($classes AS $class){
                if(in_array($class, $validClasses)){
                    $color = $class;
                    break;
                }
            }
        }

        $this->addData('color', substr($color, 4));
    }

}