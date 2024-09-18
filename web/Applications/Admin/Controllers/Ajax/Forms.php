<?php
namespace Applications\Admin\Controllers\Ajax;

use Framework\Components\Enums\PageType;
use Framework\Controllers\Forms\AbstractForm;
use Framework\Controllers\Pages\AbstractAjaxConfig;
use Framework\Helpers\Str;
use Framework\View;

class Forms extends AbstractAjaxConfig
{
    private string $formName = '';

    private string $tableName = '';

    private array $keyValues = [];

    private array $foreignKeyValues = [];

    private AbstractForm $form;

    public function setup(): bool
    {
        $params = $this->getUrlParams();

        $this->formName = trim($params[0]);

        if(!Empty($_REQUEST['id'])){
            $this->keyValues = explode(',', str_replace(['|', ';'], ',', $_REQUEST['id']));
        }

        if(!Empty($_REQUEST['fkeys'])) {
            $this->foreignKeyValues = explode(',', str_replace(['|', ';'], ',', $_REQUEST['fkeys']));
        }

        if(!Empty($_REQUEST['table'])){
            $this->tableName = $_REQUEST['table'];
        }

        $data = $_GET;
        unset(
            $data['id'],
            $data['fkeys'],
            $data['table'],
        );
        if(Empty($data)){
            $data = [];
        }

        $data['action'] = $_SERVER['REQUEST_URI'];
        $data['viewOnly'] = !Empty($_REQUEST['view']);

        $formClass = '\\Applications\\' . $this->hostConfig->getApplication() . '\\Controllers\\Forms\\' . Str::dashesToCamelCase($this->formName);
        if(class_exists($formClass)) {
            $this->form = new $formClass($data);

            if($this->form instanceof AbstractForm){
                $this->form->init($this->keyValues, $this->foreignKeyValues);
            }
        }

        return true;
    }

    protected function setAction(?array $params = [], array $post = [], ?array $rawInput = []): string|false
    {
        return 'state' . ucfirst($this->form->getFormState());
    }

    protected function setOutputFormat(): PageType
    {
        return PageType::Json;
    }

    protected function defaultAction(?string $action):array
    {
        return [];
    }

    protected function onBeforeRender(): array
    {
        return [];
    }

    protected function stateInited():array
    {
        return [];
    }

    protected function stateLoaded():array
    {
        $this->setType(PageType::Raw);

        $this->setRawData(
            View::renderContent('modal', [
                'content' => AbstractForm::ModalTemplate,
                'title' => $this->form->getTitle(),
                'subTitle' => $this->form->getSubTitle(),
                'buttons' => $this->form->getButtons(),
                'isReadonly' => $this->form->isReadonly(),
                'form' => $this->form,
            ])
        );

        return [];
    }

    protected function stateInvalid():array
    {
        return $this->stateReload();
    }

    protected function stateReload():array
    {
        //$this->form->removeButtons();

        return [
            '#' . $this->form->modalId . ' .modal-body' => [
                'html' => View::renderContent(AbstractForm::ModalTemplate, [
                    'form' => $this->form
                ])
            ]
        ];
    }

    protected function stateSaved():array
    {
        $data = [];

        if($this->form->reloadPage) {
            $data['#' . $this->form->modalId]['closeModal'] = true;
            $data['frm']['functions']['callback'] = 'modalFormPageRefresh';
            $data['frm']['functions']['arguments'] = ($this->form->returnData ?: $this->formName);
        }elseif($this->form->returnData) {
            $data = $this->form->returnData;
            $data['#' . $this->form->modalId]['closeModal'] = true;
        }else{
            $data['tables']['reloadTable'] = [$this->tableName, implode(',', $this->foreignKeyValues), true, $this->form->modalId];
        }

        return $data;
    }

}