<?php
namespace Applications\Admin\Controllers\Ajax;

use Framework\Components\Enums\AccessLevel;
use Framework\Components\Enums\PageType;
use Framework\Controllers\Pages\AbstractAjaxConfig;
use Framework\Controllers\Tables\AbstractTable;
use Framework\Helpers\Str;
use Framework\Models\Session\Session;
use Framework\View;

class Table extends AbstractAjaxConfig
{
    private string $tableName;
    private AbstractTable $table;

    private array $keyValues = [];
    private array $foreignKeyValues = [];
    private AccessLevel $accessLevel;

    private bool $updateBody = false;
    private bool $updatePager = false;
    private bool $updateTotals = false;
    private bool $updateCounter = false;
    private bool $updateButtons = false;

    public function setup(): bool
    {
        $params = $this->getUrlParams();

        $this->tableName = $tableName = trim($params[0]);
        if(!Empty($_REQUEST['alias'])){
            $tableName = trim($_REQUEST['alias']);
        }

        if(!Empty($_REQUEST['id'])){
            $this->keyValues = explode(',', str_replace(['|', '-'], ',', $_REQUEST['id']));
        }

        if(!Empty($_REQUEST['fkeys'])) {
            $this->foreignKeyValues = explode(',', str_replace(['|', '-'], ',', $_REQUEST['fkeys']));
        }

        $data = [];
        if(!Empty($_REQUEST['options'])) {
            $data['options'] = $_REQUEST['options'];
        }

        $tableClass = '\\Applications\\' . $this->hostConfig->getApplication() . '\\Controllers\\Tables\\' . Str::dashesToCamelCase($tableName);
        if(class_exists($tableClass)) {
            $this->table = new $tableClass($data, $this->tableName);

            if($this->table instanceof AbstractTable){
                $this->table->setAjaxCall();

                $settings = Session::get(AbstractTable::TABLE_SETTING_KEY . $this->tableName);
                $this->accessLevel = $settings['accessLevel'];

                return true;
            }
        }

        return false;
    }

    protected function setAction(?array $params = [], array $post = [], ?array $rawInput = []): string|false
    {
        $this->post = ($post['params'] ?? []);

        return (!Empty($post['action']) ? 'do'. ucfirst($post['action']) : 'doReload');
    }

    protected function setOutputFormat(): PageType
    {
        return PageType::Json;
    }

    protected function defaultAction(?string $action):array
    {
        $action = strtolower(substr($action, 2));

        if (method_exists($this->table, $action)) {
            $this->table->init($this->accessLevel, $action, $this->keyValues, $this->foreignKeyValues, $this->post);
            $this->updateBody = true;
        }

        return [];
    }

    protected function doPage():array
    {
        if (!empty($this->post['page'])) {
            $this->updateBody = true;
            $this->updatePager = true;

            $this->table->init($this->accessLevel, 'page', $this->keyValues, $this->foreignKeyValues, $this->post);
        }

        return [];
    }

    protected function doCheck():array
    {
        if (!empty($this->post['field'])) {
            $this->table->init($this->accessLevel, 'check', $this->keyValues, $this->foreignKeyValues, $this->post);
        }

        return [];
    }

    protected function doMark():array
    {
        if (!empty($this->post['field'])) {
            $this->table->init($this->accessLevel, 'mark', $this->keyValues, $this->foreignKeyValues, $this->post);
        }

        return [];
    }

    protected function doDelete():array
    {
        $this->updatePager = true;
        $this->updateBody = true;
        $this->updateCounter = true;
        $this->updateTotals = true;

        $this->table->init($this->accessLevel, 'delete', $this->keyValues, $this->foreignKeyValues, $this->post);

        /**
         * @todo check
         *
        if($this->table->isModalForm()) {
            array_shift($this->keyValues);

            $params = [
                'foreignKeys' => $this->keyValues
            ];

            $this->table->reInit($params);
        }
        */

        if($fieldsToUpdate =  $this->table->getUpdateFields()){
            return $fieldsToUpdate;
        }

        return [];
    }

    protected function doUndelete():array
    {
        $this->updateBody = true;
        $this->updatePager = true;
        $this->updateCounter = true;
        $this->updateTotals = true;

        $this->table->init($this->accessLevel, 'unDelete', $this->keyValues, $this->foreignKeyValues, $this->post);

        return [];
    }

    protected function doCopy():array
    {
        $this->updateBody = true;
        $this->updatePager = true;
        $this->updateCounter = true;
        $this->updateTotals = true;

        $this->table->init($this->accessLevel, 'copy', $this->keyValues, $this->foreignKeyValues, $this->post);

        return [];
    }

    protected function doReload():array
    {
        $this->updateBody = true;
        $this->updatePager = true;
        $this->updateButtons = true;
        $this->updateCounter = true;
        $this->updateTotals = true;

        $this->table->init($this->accessLevel, 'reload', $this->keyValues, $this->foreignKeyValues, $this->post);

        return [];
    }

    protected function doSort():array
    {
        $this->table->init($this->accessLevel, 'sort', $this->keyValues, $this->foreignKeyValues, $this->post);

        return [];
    }

    protected function doSelectRow():array
    {
        $list = Session::get(AbstractTable::TABLE_SELECTION_KEY . $this->tableName . '-' . ($this->foreignKeyValues ? implode('-', $this->foreignKeyValues) : 0));
        if(!$list) $list = [];

        if(!Empty($this->post['ids']) && is_array($this->post['ids'])){
            foreach($this->post['ids'] AS $id => $val){
                if(!in_array($id, $list) && $val){
                    $list[] = $id;
                }elseif(in_array($id, $list) && !$val){
                    unset($list[array_search($id, $list)]);
                }
            }
        }

        Session::set(AbstractTable::TABLE_SELECTION_KEY . $this->tableName . '-' . ($this->foreignKeyValues ? implode('-', $this->foreignKeyValues) : 0), $list);

        if(!Empty($list)) {
            $data = [
                'fields' => [
                    '.btn-table-bulk-edit' => [
                        'removeClass' => 'disabled',
                    ],
                    '.table-row-selector-counter' => [
                        'html' => count($list)
                    ]
                ],
                'data' => $list,
            ];
        }else{
            $data = [
                'fields' => [
                    '.btn-table-bulk-edit' => [
                        'addClass' => 'disabled',
                    ],
                    '.table-row-selector-counter' => [
                        'html' => 0
                    ]
                ],
            ];
        }

        return $data;
    }

    protected function doUnselectRow():array
    {
        Session::set(AbstractTable::TABLE_SELECTION_KEY . $this->tableName . '-' . ($this->foreignKeyValues ? implode('-', $this->foreignKeyValues) : 0), []);

        return [
            'fields' => [
                '.btn-table-bulk-edit' => [
                    'addClass' => 'disabled',
                ],
                '.table-row-selector-counter' => [
                    'html' => 0
                ]
            ],
        ];
    }

    protected function onBeforeRender(): array
    {
        $data = [];

        if($this->updateBody) {
            $data['#table-' . $this->tableName . ' .table-content'] = View::renderContent($this->table->getBodyTemplate(), ['table' => $this->table]);

            /*
            if ($this->table->getType() == 'div') {
                $data['#table-' . $this->tableName . ' .table-content'] = View::renderContent($this->table->getBodyTemplate(), ['table' => $this->table]);
            } else {
                $data['#table-' . $this->tableName . ' tbody'] = View::renderContent($this->table->getBodyTemplate(), ['table' => $this->table]);
            }
            */

            $fields = $this->table->getUpdateFields();
            if($fields){
                foreach ($fields AS $selector => $actions){
                    foreach ($actions AS $action => $value){
                        $data['fields'][$selector][$action] = $value;
                    }
                }
            }
        }

        if ($this->updatePager) {
            $data['.table-' . $this->tableName . '-pager'] = View::renderContent('table-pager', ['table' => $this->table]);
        }

        if ($this->updateCounter) {
            $data['.table-' . $this->tableName . '-counter'] = View::renderContent('table-row-counter', ['table' => $this->table]);
        }

        if ($this->updateTotals) {
            $data['#table-' . $this->tableName . ' tfoot'] = View::renderContent('table-totals', ['table' => $this->table]);
        }

        if ($this->updateButtons) {
            $data['.table-' . $this->tableName . '-buttons'] = View::renderContent('table-buttons', ['table' => $this->table]);
        }

        return $data;
    }
}