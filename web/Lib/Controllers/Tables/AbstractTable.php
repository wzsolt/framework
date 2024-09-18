<?php
namespace Framework\Controllers\Tables;

use Framework\Components\Enums\AccessLevel;
use Framework\Components\Enums\ItemPosition;
use Framework\Components\Enums\JSAction;
use Framework\Components\Enums\MessageType;
use Framework\Components\Enums\Size;
use Framework\Components\Enums\TableType;
use Framework\Components\HostConfig;
use Framework\Components\Messages;
use Framework\Components\Traits\AddCssTrait;
use Framework\Components\Traits\AddJsTrait;
use Framework\Components\Traits\KeyFieldsTrait;
use Framework\Components\User;
use Framework\Controllers\Buttons\AbstractButton;
use Framework\Controllers\Buttons\AbstractTableButton;
use Framework\Controllers\Buttons\TableButtonModal;
use Framework\Controllers\Forms\AbstractForm;
use Framework\Models\Database\Db;
use Framework\Models\Session\Session;
use Framework\Router;
use ReflectionClass;

abstract class AbstractTable
{
    use AddJsTrait, AddCssTrait, KeyFieldsTrait;

    const TABLE_SETTING_KEY = 'table-settings-';

    const TABLE_PAGER_KEY = 'table-pager-';

    const TABLE_SELECTION_KEY = 'table-selection-';


    const FORM_TYPE_NONE    = 0;
    const FORM_TYPE_EDITOR  = 1;
    const FORM_TYPE_VIEWER  = 2;

    public string $defaultAction = 'edit'; // or view

    protected bool $debugSql = false;

    private string $name;
    private string $className;

    private string $assocIdField = '';
    private array $rows = [];

    private string $dbTable = '';
    private array $joins = [];
    private array $where = [];
    private string $groupBy = '';
    private string $having = '';

    private string $deleteField = '';
    private string $deleteDateField = '';
    private bool $showDeletedRecords = false;

    private AbstractForm $form;
    private bool $isModalForm = false;
    private string $modalId = '#ajax-modal';
    private Size $modalSize = Size::Md;
    private bool $isFormLoaded = false;
    private int $loadedFormType = self::FORM_TYPE_NONE;

    private TableType $tableType = TableType::Table;
    private string $bodyTemplate = 'table-body';

    private bool $rowClick = true;
    private string|false $target = false;
    private string $customUrl = '';
    private bool $returnAfterSave = true;
    private string $returnUrl = '';
    private ItemPosition $pagerPosition = ItemPosition::Bottom;
    private ItemPosition $buttonsPosition = ItemPosition::Bottom;
    private bool $counterHidden = false;
    private bool $headerHidden = false;
    public string $tableClass = 'table table-bordered table-hover mb-0';
    public string $tableHeaderClass = 'table-light';

    private bool $rowOptions = true;
    private int $optionsWidth = 2;
    private string $optionsTemplate = '';
    private string $additionalOptionsTemplate = '';

    private bool $delete = true;            // set false, to disable the delete option
    private bool $undelete = true;          // set false, to disable the undelete option
    private bool $edit = true;              // set false, to disable the edit option
    private bool $view = false;             // set false, to disable the view option
    private AbstractForm $viewForm;

    private bool $copy = false;             // set false, to disable the copy option
    private array $copyChangeFields = [
        'remove' 	=> [],		// list fields which need to be removed
        'replace' 	=> [],		// list field in keys to replace its value
        'add' 		=> [],		// list field in keys to add to its original value
        'callback' 	=> false	// callback method name
    ];

    private bool $isSortable = false;
    private string $sortField = '';
    private string $sortGroupField = '';
    private bool $multipleSelect = false;
    private array $selection = [];
    private array $rowGroups = [];
    private array $groupClass = [];
    private array $fieldsToUpdate = [];
    private string $includeBefore = '';
    private string $includeAfter = '';

    private array $parameters;

    private AccessLevel $accessLevel;

    private bool $readonly = false;

    private bool $isAjaxCall;

    /**
     * @var $columns Column[]
     */
    private array $columns = [];

    /**
     * @var $sumColumns SumColumn[]
     */
    private array $sumColumns = [];

    /**
     * @var $buttons AbstractButton[]
     */
    private array $buttons = [];

    private array $settings = [];

    private array $pager = [];

    protected HostConfig $hostConfig;

    protected Db $db;

    protected User $user;

    abstract protected function setupKeyFields():void;

    abstract protected function setup():void;

    public function __construct(array $parameters = [], string $alias = '')
    {
        $this->className = (new ReflectionClass($this))->getShortName();

        $this->name = ($alias ?: $this->className);

        $this->setParams($parameters);
    }

    public function setAjaxCall(bool $mode = true):self
    {
        $this->isAjaxCall = $mode;

        return $this;
    }

    public function isAjaxCall(): bool
    {
        return $this->isAjaxCall;
    }

    public function setParams(array $params):self
    {
        $this->parameters = $params;

        return $this;
    }

    public function getParams():array
    {
        return $this->parameters;
    }

    protected function setParam(string $key, string $value):self
    {
        $this->parameters[$key] = $value;

        $this->onAfterSetParam();

        return $this;
    }

    private function buildKeyValues(array $keyValues = [], array $foreignKeyValues = []):void
    {
        $this->setupKeyFields();

        if(Empty($this->keyFields)){
            throw new \Exception('Key field is missing');
        }

        $i = 0;
        foreach($this->keyFields AS $field => $default) {
            if(!Empty($default)){
                $this->keyValues[$field] = $default;
            }else {
                if (isset($keyValues[$i])) {
                    $this->keyValues[$field] = (int)$keyValues[$i];
                }
            }
            $i++;
        }

        if(!Empty($this->foreignKeyFields)){
            $i = 0;
            foreach($this->foreignKeyFields AS $field => $default) {
                if (!empty($default)) {
                    $this->foreignKeyValues[$field] = $default;
                } else {
                    if (isset($foreignKeyValues[$i])) {
                        $this->foreignKeyValues[$field] = (int)$foreignKeyValues[$i];
                    }
                }
                $i++;
            }
        }

    }

    public function init(AccessLevel $accessLevel, string|false $action = false, array $keyValues = [], array $foreignKeyValues = [], array $params = []):self
    {
        $redirect = false;

        $this->db = Db::create();

        $this->hostConfig = HostConfig::create();

        $this->user = User::create();

        $this->changeAccessLevel($accessLevel);

        $this->settings = [
            'display'       => 10,
            'orderField'    => '',
            'orderDir'      => 'asc',
            'filters'       => [],
            'accessLevel'   => $accessLevel,
        ];

        $this->pager = [
            'page' => 1
        ];

        $this->selection = $this->getSelectedIds();

        $this->buildKeyValues($keyValues, $foreignKeyValues);

        $this->setup();

        $pagerSettings = Session::get(self::TABLE_PAGER_KEY . $this->name);
        if(!Empty($pagerSettings)){
            $this->pager = $pagerSettings;
        }

        $savedSettings = Session::get(self::TABLE_SETTING_KEY . $this->name);
		if (!empty($savedSettings)) {
			$this->settings = array_merge($savedSettings, $this->settings);
        }

        $this->setAccessLevel();

        $uri = explode('?', $_SERVER['REQUEST_URI'], 2);
        if(!Empty($uri[1])) {
            parse_str($uri[1], $vars);
        }else{
            $vars = [];
        }
        $baseUrl = str_replace($action, '', rtrim($uri[0], '/'));

        switch($action) {
            case 'delete':
                //if(!$hasAction) $redirect = true;
                $this->delete();
                break;

            case 'undelete':
                //if(!$hasAction) $redirect = true;
                $this->unDelete();
                break;

            case 'copy':
                //if(!$hasAction) $redirect = true;
                $this->copy();
                break;

            case 'check':
                $this->check($params);
                break;

            case 'mark':
                $this->check($params, true);
                break;

            case 'page':
                $this->page($params['page'] ?? 1);
                break;

            case 'sort':
                $this->sort($params);
                break;

            case 'reload':
                break;

            case 'view':
                if (!empty($this->viewForm) && $this->view) {
                    $data = [
                        'viewOnly' => true,
                        'editUrl' => $baseUrl . 'edit/?id=' . implode(',', $this->getKeyValues()) . '&fkeys=' . implode(',', $this->getForeignKeyValues()) . '&table=' . $this->getName(),
                    ];

                    if(!Empty($this->settings['type'])){
                        $data['type'] = $this->settings['type'];
                    }

                    if(!Empty($this->parameters['options'])){
                        $data['options'] = $this->parameters['options'];
                    }

                    $this->viewForm->setParams($data);

                    $this->loadForm($this->viewForm, self::FORM_TYPE_VIEWER);
                }
                break;

            case 'edit':
                if (!empty($this->form) && $this->edit) {
                    $this->form->setParams([
                        'viewUrl' => $baseUrl . 'view/?id=' . implode(',', $this->getKeyValues()) . '&fkeys=' . implode(',', $this->getForeignKeyValues()) . '&table=' . $this->getName(),
                        'backUrl' => $baseUrl
                    ]);

                    $this->loadForm($this->form, self::FORM_TYPE_EDITOR);

                    if ($this->form->getFormState() == AbstractForm::STATE_SAVED && $this->returnAfterSave) {
                        Messages::create()->add(MessageType::Success, 'LBL_DATA_SAVED_SUCCESSFULLY');
                        $redirect = true;

                    }elseif($this->form->getFormState() == AbstractForm::STATE_SAVED && !$this->returnAfterSave){
                        Messages::create()->add(MessageType::Success, 'LBL_DATA_SAVED_SUCCESSFULLY');
                        $redirect = true;

                        $baseUrl .= '?' . http_build_query($vars);
                    }
                }else{
                    $redirect = true;
                }

                break;
            default:
                if (!empty($this->keyValues) && !Empty($action)) {
                    if(method_exists($this, $action)){
                        if(Empty($params)) $params = [];
                        call_user_func_array([$this, $action], [$params]);
                    }
                }
                break;
        }

        if($redirect){
            Router::pageRedirect(($this->returnUrl ?: $baseUrl));
        }

        if($this->hasDatabase() && !$this->isFormLoaded()){
            $this->loadRows();
        }

        return $this;
    }

	public function __destruct()
    {
        if(!Empty($this->settings)) {
            Session::set(self::TABLE_SETTING_KEY . $this->name, $this->settings);
        }

        if(!Empty($this->pager)) {
            Session::set(self::TABLE_PAGER_KEY . $this->name, $this->pager);
        }
	}

    public function setName(string $name):self
    {
        $this->name = $name;

        return $this;
    }

    public function getName():string
    {
        return $this->name;
    }

    public function getClassName():string
    {
        return $this->className;
    }

    public function getRows():array
    {
        return $this->rows;
    }

    public function sortByIndex(array $indexOrder):array
    {
        $oldRows = $this->rows;

        $this->rows = [];
        foreach($indexOrder AS $index){
            $this->rows[$index] = $oldRows[$index];
        }

        return $this->rows;
    }

    private function reset():void
    {
		$this->settings = [];

        $this->pager = [];

		Session::delete(self::TABLE_SETTING_KEY . $this->name);
	}

    protected final function setDatabase(string $tableName):self
    {
        $this->dbTable = $tableName;

        return $this;
    }

    public function hasDatabase():bool
    {
        return !Empty($this->dbTable);
    }

    protected final function addJoin(string $tableName, string $on):self
    {
        $this->joins[] = 'LEFT JOIN ' . $tableName . ' ON (' . $on . ')';

        return $this;
    }

    protected final function addWhere(string $where):self
    {
        $this->where[] = $where;

        return $this;
    }

    protected final function setGroupBy(string $groupBy):self
    {
        $this->groupBy = $groupBy;

        return $this;
    }


    protected final function setHaving(string $having):self
    {
        $this->having = $having;

        return $this;
    }

    protected final function setOrderBy(string $fields, string $dir = 'ASC', int $pageLimit = 0):self
    {
        $this->settings['display']    = $pageLimit;

        $this->settings['orderField'] = $fields;

        $this->settings['orderDir']   = $dir;

        return $this;
    }

    protected function setFilters(array $filters):void
    {
        $this->settings['filters'] = $filters;
    }

    protected final function setArchivable(string $fieldName, string $dateFieldName = '', bool $showDeletedRecords = false):self
    {
        $this->deleteField = $fieldName;

        $this->deleteDateField = $dateFieldName;

        $this->showDeletedRecords = $showDeletedRecords;

        return $this;
    }

    public function isArchive():bool
    {
        return !Empty($this->deleteField);
    }

    protected final function setForm(AbstractForm $form, bool $isModalForm = false, string $modalId = '#ajax-modal'):self
    {
        $this->form = $form;

        $this->isModalForm = $isModalForm;

        $this->modalId = $modalId;

        return $this;
    }

    protected final function setModalSize(Size $size = Size::Md):self
    {
        $this->modalSize = $size;

        return $this;
    }

    public function getModalSize():string
    {
        return strtolower($this->modalSize->name);
    }

    public function getFormName():string
    {
        return (!Empty($this->form) ? $this->form->getName() : '');
    }

    public function isModalForm():bool
    {
        return $this->isModalForm;
    }

    public function getModalId():string
    {
        return $this->modalId;
    }

    public function isFormLoaded():bool
    {
        return $this->isFormLoaded;
    }

    protected function setViewForm(AbstractForm $form, bool $isModalForm = false, string $modalId = '#ajax-modal'):self
    {
        $this->view = true;

        $this->viewForm = $form;

        $this->isModalForm = $isModalForm;

        $this->modalId = $modalId;

        return $this;
    }

    public function isView():bool
    {
        return $this->view;
    }

    public function getViewFormName():string
    {
        return (!Empty($this->viewForm) ? $this->viewForm->getName() : '');
    }

    protected final function addGroup(string $idField, string $textField, string|false $descriptionField = false, array $options = []):self
    {
        $this->rowGroups[] = [
            'id' => $idField,
            'name' => $textField,
            'description' => $descriptionField,
            'options' => $options,
        ];

        return $this;
    }

    protected final function setGroupClass(string $idField, string $class):self
    {
        $this->groupClass[$idField] = $class;

        return $this;
    }

    protected final function makeSortable(string $sortField, string $groupField = ''):self
    {
        $this->isSortable = true;

        $this->sortField = $sortField;

        $this->sortGroupField = $groupField;

        return $this;
    }

    public final function isSortable():bool
    {
        return $this->isSortable;
    }

    protected final function makeMultipleSelection(string|false $formName = false, Size $size = Size::Lg, string $btnLabel = 'BTN_BULK_EDIT'):self
    {
        $this->multipleSelect = true;

        $this->selection = $this->getSelectedIds();

        if($formName) {
            $button = new TableButtonModal('btnBulkEdit', $btnLabel);
            $button->setFormName($formName);
            $button->setModal(true);
            $button->setModalSize($size);
            $button->addClass('float-start btn-bulk-edit');
            $button->setIcon('fa-solid fa-pen');
            $button->setDisabled(Empty($this->selection));

            $this->addButton($button);
        }

        return $this;
    }

    public final function isMultipleSelect():bool
    {
        return $this->multipleSelect;
    }

    public final function getSelectedIds():array
    {
        $ids = Session::get(self::TABLE_SELECTION_KEY . $this->name . '-' . ($this->getForeignKeyValues() ? implode('-', $this->getForeignKeyValues()) : 0));

        return ($ids ?: []);
    }

    public final function getUpdateFields():array
    {
        return $this->fieldsToUpdate;
    }

    protected final function setUpdateField(string $selector, JSAction $action, string $value):self
    {
        $this->fieldsToUpdate[$selector][$action->event()] = $value;

        return $this;
    }

    protected final function setJSCallback(string $callback, string $arguments = ''):self
    {
        $this->fieldsToUpdate['fn']['functions']['callback'] = $callback;
        $this->fieldsToUpdate['fn']['functions']['arguments'] = $arguments;

        return $this;
    }

    protected final function setType(TableType $type, string $bodyTemplate = 'table-body'):self
    {
        $this->tableType = $type;

        $this->bodyTemplate = $bodyTemplate;

        return $this;
    }

    public final function getType():string
    {
        return strtolower($this->tableType->name);
    }

    public final function getBodyTemplate():string
    {
        return $this->bodyTemplate;
    }

    public function getAccessLevel():AccessLevel
    {
        return $this->accessLevel;
    }

    public final function changeAccessLevel(AccessLevel $accessLevel):self
    {
        $this->accessLevel = $accessLevel;

        $this->setAccessLevel();

        return $this;
    }

    private function setAccessLevel():void
    {
        if($this->accessLevel < AccessLevel::ReadAndWrite){
            $this->readonly = true;

            $this->disableDelete(false);
            $this->allowUnDelete(false);
            $this->disableEdit(false);

            $this->copy = false;

            /**
             * @var AbstractTableButton $button
             */
            foreach($this->buttons AS $index => $button){
                if(!$button->isAlwaysVisible()){
                    unset($this->buttons[$index]);
                }
            }
        }

        if($this->accessLevel < AccessLevel::ReadAndWrite){
            $this->disableDelete(false);
            $this->allowUnDelete(false);
        }
    }

    private function getAllKeysValues():array
    {
        $out = [];

        if(!Empty($this->keyValues)){
            $out = $this->keyValues;
        }

        if(!Empty($this->foreignKeyValues)){
            foreach($this->foreignKeyValues AS $field => $value){
                $out[$field] = $value;
            }
        }

        return $out;
    }

    public function getSettings():array
    {
        return $this->settings;
    }

    public function getPager():array
    {
        return $this->pager;
    }

    protected final function addColumns(Column ...$columns):void
    {
        foreach($columns AS $column){
            $this->addColumn($column);
        }
    }

    protected final function addColumn(Column $column):self
    {
        $column->setTableName($this->name);

        $this->columns[$column->getId()] = $column;

        return $this;
    }

    public function getColumns():array
    {
        return $this->columns;
    }

    public function getColumn(string $id):?Column
    {
        return ($this->columns[$id] ?? null);
    }

    protected final function addSumColumns(SumColumn ...$columns):void
    {
        foreach($columns AS $column){
            $this->addSumColumn($column);
        }
    }

    protected final function addSumColumn(SumColumn $column):self
    {
        $this->sumColumns[] = $column;

        return $this;
    }

    public function getSumColumns():array
    {
        return $this->sumColumns;
    }

    protected final function addButtons(AbstractTableButton ...$buttons):void
    {
        foreach($buttons AS $button){
            $this->addButton($button);
        }
    }

    protected final function addButton(AbstractTableButton $button):self
    {
        $button->setTableName($this->getName());

        $button->setForeignKeyValues($this->getForeignKeyValues());

        if(!Empty($this->form) && Empty($button->getFormName())) {
            $button->setFormName($this->form->getName());
            $button->setModal($this->isModalForm, $this->modalId);
        }

        if(Empty($button->getModalSize())) {
            $button->setModalSize($this->modalSize);
        }

        $button->init();

        $this->buttons[$button->getId()] = $button;

        return $this;
    }

    public function hasButtons():bool
    {
        return (count($this->buttons) > 0);
    }

    public function getButtons():array
    {
        return $this->buttons;
    }

    protected function setButtonPosition(ItemPosition $position):self
    {
        $this->buttonsPosition = $position;

        return $this;
    }

    public function getButtonPosition():int
    {
        return $this->buttonsPosition->value;
    }

    protected function setRowClick(bool $enabled = true, string|false $target = false, string $customUrl = ''):self
    {
        $this->rowClick = $enabled;

        $this->target = $target;

        $this->customUrl = $customUrl;

        return $this;
    }

    public function isRowClick():bool
    {
        return $this->rowClick;
    }

    public function getClickTarget():string|false
    {
        return $this->target;
    }

    public function getCustomUrl():string
    {
        return $this->customUrl;
    }

    protected function returnAfterSave(bool $return = true, string $url = ''):self
    {
        $this->returnAfterSave = $return;

        $this->returnUrl = $url;

        return $this;
    }

    protected function setPagerPosition(ItemPosition $position):self
    {
        $this->pagerPosition = $position;

        return $this;
    }

    public function getPagerPosition():int
    {
        return $this->pagerPosition->value;
    }

    protected function setHeaderHidden(bool $mode = true):self
    {
        $this->headerHidden = $mode;

        return $this;
    }

    public function isHeaderHidden():bool
    {
        return $this->headerHidden;
    }

    protected function setCounterHidden(bool $mode = true):self
    {
        $this->counterHidden = $mode;

        return $this;
    }

    public function isCounterHidden():bool
    {
        return $this->counterHidden;
    }

    protected function setTableClass(string $class):self
    {
        $this->tableClass = $class;

        return $this;
    }

    public function getTableClass():string
    {
        return $this->tableClass;
    }

    protected function setTableHeaderClass(string $class):self
    {
        $this->tableHeaderClass = $class;

        return $this;
    }

    public function getTableHeaderClass():string
    {
        return $this->tableHeaderClass;
    }

    protected function setRowOptions(bool $visible = true, int $width = 2, string $template = '', string $additionalTemplate = ''):self
    {
        $this->rowOptions = $visible;

        $this->optionsWidth = $width;

        $this->optionsTemplate = $template;

        $this->additionalOptionsTemplate = $additionalTemplate;

        return $this;
    }

    public function isRowOptions():bool
    {
        return $this->rowOptions;
    }

    public function getOptionsWidth():int
    {
        return $this->optionsWidth;
    }

    public function getOptionsTemplate():string
    {
        return $this->optionsTemplate;
    }

    public function getOptionsAdditionalTemplate():string
    {
        return $this->additionalOptionsTemplate;
    }

    public function getIncludeBefore(): string
    {
        return $this->includeBefore;
    }

    protected function setIncludeBefore(string $includeBefore): self
    {
        $this->includeBefore = $includeBefore;

        return $this;
    }

    public function getIncludeAfter(): string
    {
        return $this->includeAfter;
    }

    protected function setIncludeAfter(string $includeAfter): self
    {
        $this->includeAfter = $includeAfter;

        return $this;
    }

    public function isReadonly():bool
    {
        return $this->readonly;
    }

    protected function disableDelete(bool $mode = true):self
    {
        $this->delete = !$mode;

        return $this;
    }

    public function isDelete():bool
    {
        return $this->delete;
    }

    protected function allowUnDelete(bool $mode):self
    {
        $this->undelete = $mode;

        return $this;
    }

    public function isUnDelete():bool
    {
        return $this->undelete;
    }

    protected function disableEdit(bool $mode = true):self
    {
        $this->edit = !$mode;

        return $this;
    }

    public function isEdit():bool
    {
        return $this->edit;
    }

    public function getFormTitle():string
    {
        if($this->isFormLoaded) {
            return $this->form->getTitle();
        }else{
            return '';
        }
    }

    public function getFormSubtitle():string
    {
        if($this->isFormLoaded) {
            return $this->form->getSubTitle();
        }else{
            return '';
        }
    }

    protected final function allowCopy(array $removeFields = [], array $replaceFields = [], array $addValue = [], string $callbackFunction = ''):self
    {
        $this->copy = true;

        $this->copyChangeFields = [
            'remove' 	=> $removeFields,		// list fields which need to be removed
            'replace' 	=> $replaceFields,		// list field in keys to replace its value
            'add' 		=> $addValue,		    // list field in keys to add to its original value
            'callback' 	=> $callbackFunction	// callback method name
        ];

        return $this;
    }

    public function isCopy():bool
    {
        return $this->copy;
    }

	private function getWhere(bool $filtered = true):string
    {
		$where = [];

		if (!empty($this->where)) {
			$where = $this->where;
		}

		if (!empty($this->deleteField) && !$this->showDeletedRecords) {
			if (!Empty($this->settings['show-archived'])) {
				$where[] = $this->deleteField . " = 1";
			} else {
				$where[] = $this->deleteField . " != 1";
			}
		}

		if (!empty($this->foreignKeyValues)) {
			$foreignWhere = [];

			foreach($this->foreignKeyValues as $field => $fieldValue) {
				$foreignWhere[] = "$field = '" . $this->db->escapeString($fieldValue) . "'";
			}

            $where = array_merge($where, $foreignWhere);
		}

		if (!Empty($filtered)) {
			if (!empty($this->settings['filters'])) {
				$where = array_merge($where, $this->settings['filters']);
			}
		}

		return ((!empty($where)) ? ' WHERE ' . implode(' AND ', $where) : '');
	}

	private function loadRows():void
    {
		if (!empty($this->dbTable)) {
			$select = array_keys($this->keyFields);

            if(!Empty($this->foreignKeyFields)){
                foreach($this->foreignKeyFields AS $field => $value){
                    $select[] = $field;
                }
            }

			foreach($this->columns as $col) {
				if (!empty($col->getField())) $select[] = $col->getSelectField();
			}

			if(!Empty($this->deleteField)){
				$select[] = $this->deleteField;
			}

            if(!Empty($this->rowGroups)){
                foreach ($this->rowGroups AS $col) {
                    if (!empty($col['name']) && !in_array($col['name'], $select)){
                        $select[] = $col['name'];

                        if(!Empty($col['id']) && !in_array($col['id'], $select)){
                            $select[] = $col['id'];
                        }
                        if(!Empty($col['description']) && !in_array($col['description'], $select)){
                            $select[] = $col['description'];
                        }
                    }
                }
            }

			$groupBy = (!empty($this->groupBy)) ? ' GROUP BY ' . $this->groupBy : '';
			if (!empty($groupBy) && !empty($this->having)) {
				$groupBy .= " HAVING " . $this->having;
			}


			$where = $this->getWhere(false);

            $join = (!Empty($this->joins) ? ' ' . implode(' ', $this->joins) : '');

			$res = $this->db->getFirstRow(
				'SELECT COUNT(*) OVER() AS cnt FROM ' . $this->db->prepareTableName($this->dbTable) . $join . $where . $groupBy . ' LIMIT 1'
			);
			$this->pager['recordCount'] = (int) ($res['cnt'] ?? 0);

			$where = $this->getWhere();

			$orderBy = '';
			if (!empty($this->settings['orderField'])) {
				$orderBy = " ORDER BY " . $this->settings['orderField'] . ' ' . ((strtolower($this->settings['orderDir']) == 'desc') ? 'desc' : 'asc');
			}

			$limit = '';
            $page = ($this->pager['page'] ?? 1);

			if ($this->settings['display'] > 0) {
				$limit = " LIMIT " . (($page - 1) * $this->settings['display']) . ', ' . $this->settings['display'];
			}

			if($this->debugSql){
                //logData($this->settings, 'table class load rows');
				//d($this->settings, $this->name . ' settings');
                //logData("SELECT " . implode(', ', $select) . " FROM " . $this->owner->db->prepareTableName($this->dbTable) . $this->join . $where . $groupBy . $orderBy . $limit, $this->name . ' SQL dump');

				dd("SELECT " . implode(', ', $select) . " FROM " . $this->db->prepareTableName($this->dbTable) . $join . $where . $groupBy . $orderBy . $limit . '<br><br>', $this->name . ' SQL dump');
			}

            $res = $this->db->getRows(
                "SELECT SQL_CALC_FOUND_ROWS " . implode(', ', $select) . " FROM " . $this->db->prepareTableName($this->dbTable) . $join . $where . $groupBy . $orderBy . $limit
            );

            if (!empty($res)) {
				foreach($res as $dbRow) {
                    $groups = [];

                    if($this->rowGroups){
                        foreach($this->rowGroups AS $col){
                            $idField = $this->findAliasField($col['id']);
                            $textField = $this->findAliasField($col['name']);

                            if(!Empty($col['alias'])){
                                $textField = $col['alias'];
                            }

                            $groups[$dbRow[$idField]] = [
                                'id' => $dbRow[$idField],
                                'idKey' => $idField,
                                'class' => ($this->groupClass[$idField] ?? ''),
                                'text' => (!Empty($col['options']) ? $col['options'][$dbRow[$textField]] : $dbRow[$textField]),
                                'description' => ($this->findAliasField($col['description']) ? $dbRow[$this->findAliasField($col['description'])] : false)
                            ];

                            unset($dbRow[$textField]);

                            if(isset($col['description'])){
                                unset($dbRow[$this->findAliasField($col['description'])]);
                            }
                        }
                    }

                    if($this->assocIdField && isset($dbRow[$this->assocIdField])){
                        $key = $dbRow[$this->assocIdField];
                    }else {
                        $key = $dbRow[array_key_first($this->keyFields)];
                    }

                    $this->rows[$key] = $dbRow;
                    $this->rows[$key]['__groupId'] = 0;
                    $this->rows[$key]['__id'] = $key;
                    $this->rows[$key]['options']['delete'] = true;
                    $this->rows[$key]['options']['edit'] = true;

                    if($this->deleteField) {
                        $this->rows[$key]['options']['isDeleted'] = $dbRow[$this->deleteField];
                        unset($this->rows[$key][$this->deleteField]);
                    }

                    if($this->rowGroups) {
                        foreach($groups AS $groupId => $group) {
                            $idKey = $group['idKey'];
                            if(!Empty($dbRow[$idKey])) {
                                unset($group['idKey']);
                                $this->rows[$key]['__groupId'] = $groupId;
                                $this->rows[$key]['groups'][$groupId] = $group;
                            }
                            unset($this->rows[$key][$idKey]);
                        }
                    }
				}
			}

			$res = $this->db->getFirstRow("SELECT FOUND_ROWS() as filteredCount");
			$this->pager['filteredCount'] = $res['filteredCount'];

			if (!empty($this->settings['display'])) {
				$this->pager['totalPages'] = ceil($res['filteredCount'] / $this->settings['display']);
				if ($this->pager['totalPages'] < 1) {
					$this->pager['totalPages'] = 1;
					$this->pager['page'] = 1;
				} else if ($page > $this->pager['totalPages']) {
					$this->pager['page'] = $this->pager['totalPages'];
					$this->pager['page'] = $this->pager['totalPages'];
				}
			} else {
				$this->pager['totalPages'] = 1;
			}

            if(!Empty($this->sumColumns)){
                foreach($this->sumColumns AS $col){
                    if($col->isSummarizeField()){
                        $select = [];

                        if($col->getQuery()){
                            $select[] = $col->getQuery() . ' AS sumField';
                        }else {
                            $select[] = 'SUM(' . $col->getField() . ') AS sumField';
                        }

                        if($col->getGroupField()){
                            $select[] = $col->getGroupField() . ' AS unitField';
                            $groupBy = 'GROUP BY ' . $col->getGroupField();
                        }

                        $sum = $this->db->getFirstRow(
                            'SELECT ' . implode(',', $select) . ' FROM ' . $this->db->prepareTableName($this->dbTable) . $join . $where . $groupBy . ' LIMIT 1'
                        );

                        if(!Empty($sum)){
                            $col->setValue($sum['sumField']);

                            if(isset($sum['unitField'])) {
                                $col->setUnit($sum['unitField']);
                            }
                        }

                    }
                }
            }

			$this->onAfterLoad();
		}
	}

    private function loadForm(AbstractForm $form, int $type = self::FORM_TYPE_NONE):void
    {
        $form->init(array_values($this->keyValues), array_values($this->foreignKeyValues));

        $this->mergeJs($form->getJs());

        $this->mergeCss($form->getCss());

        $this->isFormLoaded = true;

        $this->loadedFormType = $type;
    }

    public function getForm():?AbstractForm
    {
        if($this->isFormLoaded && $this->loadedFormType == self::FORM_TYPE_EDITOR){
            return $this->form;
        }elseif ($this->isFormLoaded && $this->loadedFormType == self::FORM_TYPE_VIEWER){
            return $this->viewForm;
        }

        return null;
    }

    private function delete():void
    {
		if ($this->delete && $this->isDeletable()) {
			if ($this->onBeforeDelete(empty($this->deleteField))) {
				if (!empty($this->deleteField)) {
					$updateData = [
						$this->deleteField => 1
					];

					if($this->deleteDateField){
						$updateData[$this->deleteDateField] = 'NOW()';
					}

					$this->db->sqlQuery(
						Db::update($this->dbTable, $updateData, $this->getAllKeysValues())
					);
				} else {
					$tables = explode(' ', $this->dbTable);
					$this->db->sqlQuery(
						Db::delete($tables[0], $this->getAllKeysValues())
					);
				}
			}

			$this->onAfterDelete(empty($this->deleteField));
		}
	}

	private function unDelete():void
    {
		if (!empty($this->deleteField) && $this->delete) {
			$updateData = [
				$this->deleteField => 0
			];

			if($this->deleteDateField){
				$updateData[$this->deleteDateField] = NULL;
			}

			$this->db->sqlQuery(
				Db::update($this->dbTable, $updateData, $this->getAllKeysValues())
			);
		}
	}

	private function copy():void
    {
		if ($this->copy) {
            $row = $this->db->getFirstRow(
                Db::select(
                    $this->dbTable,
                    [],
                    $this->getAllKeysValues()
                )
            );

			if($row){
				unset($row[array_key_first($this->keyFields)]); // remove index field

				if(!Empty($this->copyChangeFields['remove'])){
					foreach($this->copyChangeFields['remove'] AS $field){
						unset($row[$field]);
					}
				}
				if(!Empty($this->copyChangeFields['replace'])){
					foreach($this->copyChangeFields['replace'] AS $field => $newValue){
						$row[$field] = $newValue;
					}
				}
				if(!Empty($this->copyChangeFields['add'])){
					foreach($this->copyChangeFields['add'] AS $field => $newValue){
						$row[$field] .= $newValue;
					}
				}

                if(!Empty($this->copyChangeFields['callback']) && method_exists($this, $this->copyChangeFields['callback'])){
                    $row = $this->{$this->copyChangeFields['callback']}($row);
                }

				$this->db->sqlQuery(
					Db::insert(
						$this->dbTable,
						$row
					)
				);

				$this->onAfterCopy($this->db->getInsertRecordId(), $row);
			}
		}
	}

	private function page(int $page = 1):void
    {
		switch($page) {
			case 'prev':
				if ($this->pager['page'] > 1) {
					$this->pager['page'] -= 1;
				}
				break;
			case 'next':
				if ($this->pager['page'] < $this->pager['totalPages']) {
					$this->pager['page'] += 1;
				}
				break;
			default:
				if (is_numeric($page) && $page >= 1) {
					$this->pager['page'] = (int) $page;
				}
				break;
		}
	}

	private function check(array $params, bool $mark = false):void
    {
		$validField = false;
		$field = $params['field'];
		$value = $params['value'];

		foreach($this->columns as $col) {
			if ($col->getField() == $field) {
				$validField = true;
				break;
			}
		}

		if ($validField) {
		    if($mark){
                $this->db->sqlQuery(
                    Db::update(
                        $this->dbTable,
                        [
                            $field => 0
                        ],
                        $this->getAllKeysValues()
                    )
                );
            }

            $this->db->sqlQuery(
                Db::update(
                    $this->dbTable,
                    [
                        $field => $value
                    ],
                    $this->getAllKeysValues()
                )
            );

			$this->onCheck($field, $value);
		}
	}

    private function sort(array $params):void
    {
        if(!Empty($params['order']) && is_array($params['order'])){
            $i = 1;

            foreach($params['order'] AS $id){
                if($id) {
                    $where = [
                        array_key_first($this->getKeyFields()) => (int) $id,
                    ];

                    if($this->sortGroupField && $params['groupId']){
                        $where[$this->sortGroupField] = (int) $params['groupId'];
                    }

                    $this->db->sqlQuery(
                        Db::update(
                            $this->dbTable,
                            [
                                $this->sortField => $i,
                            ],
                            $where
                        )
                    );
                    $i++;
                }
            }

            $this->onAfterSort($params);
        }
    }

    private function findAliasField($field):string
    {
        preg_match('/[\s]AS[\s](.*?)$/mi', $field, $values);

        if(!Empty($values[1])){
            return $values[1];
        }

        return $field;
    }

    protected function onBeforeDelete(bool $real = true):bool
    {
		return true;
	}

    protected function onAfterDelete(bool $real = true):void
    {
	}

    protected function onCheck(string $field, string $value):void
    {
	}

    protected function onAfterSort(array $params):void
    {
    }

    protected function onAfterLoad():void
    {
	}

    protected function onAfterSetParam():void
    {
    }

    protected function onAfterCopy(int $newId, array $newRow = []):void
    {
	}

    protected final function changeRow(string $key, mixed $value, bool $overwrite = false):void
    {
        if(isset($this->rows[$key])){
            if($overwrite){
                $this->rows[$key] = $value;
            }else {
                $this->rows[$key] = array_replace_recursive($this->rows[$key], $value);
            }
        }
    }

    protected final function deleteRow(string $key):void
    {
        if(isset($this->rows[$key])){
            unset($this->rows[$key]);
        }
    }

    protected final function addRowTable(string $key, AbstractTable $table):void
    {
        $this->rows[$key]['subTable'] = $table;
    }

	public function isDeletable():bool
    {
		return true;
	}
}
