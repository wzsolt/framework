<?php
namespace Framework\Controllers\Forms;

use Exception;
use Framework\Components\Enums\AccessLevel;
use Framework\Components\HostConfig;
use Framework\Components\SiteSettings;
use Framework\Components\Traits\AddCssTrait;
use Framework\Components\Traits\AddJsTrait;
use Framework\Components\Traits\KeyFieldsTrait;
use Framework\Components\User;
use Framework\Controllers\Buttons\AbstractFormButton;
use Framework\Controllers\Forms\Inputs\AbstractFileUpload;
use Framework\Controllers\Forms\Inputs\InputRecaptcha;
use Framework\Controllers\Forms\Sections\SectionBox;
use Framework\Controllers\Forms\Sections\SectionTab;
use Framework\Controllers\Tables\AbstractTable;
use Framework\Helpers\Utils;
use Framework\Models\Database\Db;
use Framework\Models\Session\Session;
use ReflectionClass;
use ReflectionMethod;

abstract class AbstractForm
{
    use AddJsTrait, AddCssTrait, KeyFieldsTrait;

    const ModalTemplate = 'formBuilderModal';

    const FORM_ERROR    = 3;
    const FORM_WARNING  = 2;
    const FORM_INFO     = 1;
    const FORM_SUCCESS  = 0;

    const SAVE_TYPE_INSERT      = 'insert';
    const SAVE_TYPE_UPDATE      = 'update';

    const STATE_INITED          = 'inited';
    const STATE_LOADED          = 'loaded';
    const STATE_REQUEST         = 'request';
    const STATE_VALIDATED       = 'validated';
    const STATE_INVALID         = 'invalid';
    const STATE_RELOAD          = 'reload';
    const STATE_SAVED           = 'saved';
    const STATE_BUTTON_ACTION   = 'buttonAction';
    const STATE_RESETED         = 'reseted';

    public string $action = '';

    protected string $name;

    public string|false $view = false;
    public string|false $viewTemplate = false;
    public string|false $toolsTemplate = false;

    public string $modalId = 'ajax-modal';
    public string $formWidth = 'col-12';
	public bool $boxed = true;

	public $cssClass = [
		'card'    => '',
		'header'  => '',
		'body'    => 'p-3',
		'footer'  => ''
	];

	public bool $useSession = false;
	public bool $reloadPage = false;
	public array $returnData = [];
    public bool $submitOnEnter = false;
	public bool $displayErrors = true;

	public false|string $includeBefore = false;
	public false|string $includeAfter = false;

    private string $title = '';
    private string $subTitle = '';

    private array $reCaptcha = [];
	private string $reCaptchaTokenName = 'token';
	private string $reCaptchaActionName = 'homepage';

    private bool $readonly = false;

    private bool $isUpload = false;

    private array $buttons = [];

    private array $extraFields = [];

    private array $customActions = [];

    private array $locale = [];

    private array $errors = [];

    private array $sections = [];
    private string $sectionType = '';
    private bool $showSidebar = true;

    private array $controls = [];

    private array $inputs = [];


    private string $dbTable;
    private string $fieldPrefix;
    private array $joins = [];

    private string $state;

    protected array $parameters = [];

    private array $keyFields = [];
    private array $keyValues = [];
    private array $foreignKeyFields = [];
    private array $foreignKeyValues = [];

    protected array $values = [];

    protected bool $isValid = true;

    protected bool $inView = false;

    protected HostConfig $hostConfig;

    protected Db $db;

    protected User $user;

    private AccessLevel $accessLevel;

	abstract protected function setupKeyFields():void;

	abstract protected function setup():void;

	abstract protected function setAccessLevel():AccessLevel;

	public function __construct(array $params = [])
    {
    	$this->setParams($params);

        $this->name = (new ReflectionClass($this))->getShortName();

        $this->db = Db::create();

        $this->hostConfig = HostConfig::create();

        $this->user = User::create();

        $this->locale = Utils::getLocaleSettings($this->hostConfig->getLanguage());

        $this->state = self::STATE_INITED;
    }

    private function buildKeyValues(array $keyValues = [], array $foreignKeyValues = []):void
    {
        $this->setupKeyFields();

        if(!Empty($this->keyFields)){
            $i = 0;
            foreach($this->keyFields AS $field => $default) {
                if(!Empty($default)){
                    $this->keyValues[$field] = $default;
                }else {
                    if (isset($keyValues[$i])) {
                        $this->keyValues[$field] = $keyValues[$i];
                    } else {
                        $this->keyValues[$field] = 0;
                    }
                }
                $i++;
            }
        }

        if(!Empty($this->foreignKeyFields)){
            $i = 0;
            foreach($this->foreignKeyFields AS $field => $default) {
                if(!Empty($default)) {
                    $this->foreignKeyValues[$field] = $default;
                }else {
                    if (isset($foreignKeyValues[$i])) {
                        $this->foreignKeyValues[$field] = $foreignKeyValues[$i];
                    }
                }
                $i++;
            }
        }

        $uri = explode('?', $_SERVER['REQUEST_URI'], 2);
        $path = rtrim($uri[0], '/') . '/';
        if(!Empty($uri[1])) {
            parse_str($uri[1], $params);
        }else{
            $params = [];
        }

        $this->action = rtrim($path, '/') . '/?' . http_build_query($params);
    }

	public function init(array $keyValues = [], array $foreignKeyValues = []):self
    {
        $this->buildKeyValues($keyValues, $foreignKeyValues);

        $this->onBeforeSetup();

        $this->accessLevel = $this->setAccessLevel();

        $accessLevel = $this->accessLevel->value;
        if(!$accessLevel) $accessLevel = AccessLevel::NoAccess->value;

        if($accessLevel < AccessLevel::ReadAndWrite->value){
            $this->readonly = true;
        }

        $this->setup();

		if(isset($this->parameters['viewOnly']) && $this->viewTemplate){
			$this->view = $this->viewTemplate;
		}

        $this->onBeforeLoadValues();

		if (empty($_REQUEST[$this->name])) {
			if ($this->useSession) {
				$this->values = Session::get('form-values-' . $this->name);
			}

			if (empty($this->values)) {
				$this->loadValues();
			}

			$this->state = self::STATE_LOADED;

            if(isset($_REQUEST['reload'])){
                $this->state = self::STATE_RELOAD;
            }
        }else {
            $this->loadExtraValues();
        }
		if (empty($this->values)) {
			foreach($this->inputs as $cVal) {
			    if (!str_contains($cVal['name'], '][')) {
                    $this->values[$cVal['name']] = $cVal['default'];
			    }else{
                    list($key, $index) = explode('][', $cVal['name']);
                    $this->values[$key][$index] = $cVal['default'];
                }
			}
		}

		$this->onAfterLoadValues();

		if (!empty($_REQUEST[$this->name])) {
			$this->state = self::STATE_REQUEST;

			$this->handleRequest($_REQUEST[$this->name]);
			$this->onAfterHandleRequest();

            if($this->buttons || $this->customActions){
				$validActions = [];
				if($this->buttons) {
					foreach ($this->buttons as $button) {
						if (!in_array($button->getName(), $validActions) && !Empty($button->getName())) {
							$validActions[] = [
								'name' => $button->getName(),
								'type' => 'button',
								'skipValidation' => !$button->validate(),
							];
						}
					}
				}

				if($this->customActions){
					foreach ($this->customActions as $action) {
						if (!in_array($action, $validActions)) {
							$validActions[] = [
								'name' => $action['name'],
								'type' => 'custom',
                                'skipValidation' => ($action['skipValidation'] ?: false),
							];
						}
					}
				}

                foreach($validActions as $action) {
                    if (isset($_REQUEST[$this->name][$action['name']])) {
                        if ($this->validate($action['skipValidation'])) {
							$this->state = self::STATE_VALIDATED;

							if (strtolower($action['name']) == 'save' && !$this->readonly) {
								$this->state = self::STATE_SAVED;

								$this->saveValues();
							} else if (method_exists($this, $action['name'])) {
								$this->state = self::STATE_BUTTON_ACTION;

                                call_user_func_array([$this, $action['name']], []);
                                break;
							}
						} else {
							$this->state = self::STATE_INVALID;
						}
					}
				}
			}
		}

		$this->onAfterInit();

        return $this;
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

    public function setTitle(string $title):self
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle():string
    {
        return $this->title;
    }

    protected function setReadonly(bool $mode):self
    {
        $this->readonly = $mode;

        return $this;
    }

    public function isReadonly():bool
    {
        return $this->readonly;
    }

    public function getAction():string
    {
        return $this->action;
    }

    protected function setDatabaseTable(string $dbTable, string $fieldPrefix = ''):self
    {
        $this->dbTable = $dbTable;

        $this->fieldPrefix = $fieldPrefix;

        return $this;
    }

    public function setParams(array $params):self
    {
        foreach($params AS $key => $value){
            $this->parameters[$key] = $value;
        }

        if (!empty($this->parameters['action'])) {
            $this->action = $this->parameters['action'];
        }

        if (!empty($this->parameters['viewOnly'])) {
            $this->inView = true;
        }

        return $this;
    }

    protected function getParams():array
    {
        return $this->parameters;
    }

    protected function getParam(string $key):mixed
    {
        return ($this->parameters[$key] ?? false);
    }

	public function __destruct()
    {
		if (!empty($this->values) && $this->useSession) {
            Session::set('form-values-' . $this->name, $this->values);
		}
	}

	public function reset():void
    {
		$this->values = [];

		if ($this->useSession) {
            Session::delete('form-values-' . $this->name);
		}

		$this->state = self::STATE_RESETED;
	}

	private function handleRequest(string|array $request):void
    {
        foreach($this->inputs as $val) {
			if (in_array($val['type'], ['checkbox', 'switch']) && Empty($val['name'])) $this->values[ $val['id'] ] = $val['valueoff'];

			if (!empty($val['name'])) {
				if (isset($request[$val['name']])) {
					$this->values[$val['name']] = $request[$val['name']];
				} else if (str_contains($val['name'], '][')) {
					$vPath = explode('][', $val['name']);
                    $mainKey = $vPath[0];
                    unset($vPath[0]);
                    if(!Empty($vPath)){
                        foreach($vPath as $p) {
                            if(isset($request[$mainKey][$p])){
                                $this->values[$mainKey][$p] = $request[$mainKey][$p];
                                break;
                            }
                        }
                    }
				}
			}
		}

        foreach($this->extraFields AS $key => $val){
            if(isset($request[$key]) && !$val['exclude']){
                $this->values[$key] = $request[$key];
            }
        }
    }

    public function getErrors():array
    {
        return $this->errors;
    }

    public function setFormState(string $state):self
    {
        $this->state = $state;

        return $this;
    }

    public function getFormState():string
    {
        return $this->state;
    }

    public function getIncludeBefore(): false|string
    {
        return $this->includeBefore;
    }

    protected function setIncludeBefore(false|string $includeBefore): self
    {
        $this->includeBefore = $includeBefore;

        return $this;
    }

    public function getIncludeAfter(): false|string
    {
        return $this->includeAfter;
    }

    protected  function setIncludeAfter(false|string $includeAfter): self
    {
        $this->includeAfter = $includeAfter;

        return $this;
    }

    /**
     * @throws Exception
     */
    protected function addError(string $message, int $type = self::FORM_ERROR, array $controls = []):void
    {
		$this->isValid = false;

		foreach($controls as $id) {
            $element = $this->changeControlProperty($id, 'setError');
            if($sectionId = $element->getSectionId()){
                $this->resetSections();
                $this->sections[$sectionId]->setActive();
            }
		}

        $key = md5($message);

		$this->errors[$key] = [
			'message'  => $message,
			'type'     => $type,
			'controls' => $controls,
        ];
	}

	private function loadExtraValues():void
    {
		$select = [];

		if($this->extraFields){
            foreach($this->extraFields AS $query){
                $field = $query['field'];

                if($query['query']){
                    $field = $query['query'];
                }

                if(!in_array($field, $select)) {
                    $select[] = $field;
                }
            }

			if($select) {
                $res = $this->db->getFirstRow(
                    Db::select(
                        $this->dbTable,
                        $select,
                        $this->getAllKeysValues(),
                        $this->joins
                    )
                );
                if (!empty($res)) {
					foreach($this->extraFields AS $field => $query){
						if(!isset($this->values[$field])) {
							$this->values[$field] = $res[$field];
						}
					}
				}
			}
		}
	}

	protected function loadValues():void
    {
        $isFound = false;

        if (!empty($this->dbTable) && !empty($this->keyFields)) {
			$select = [];

			foreach($this->inputs as $val) {
				if (!empty($val['name']) && $val['DbField']) {
					if (!empty($val['sql_select'])) {
						$select[] = $val['sql_select'] . ' as ' . $val['name'];
					} else if (!str_contains($val['name'], '/') && empty($val['db'])) {
						$select[] = $val['name'];
					}
				}
			}

			if($this->extraFields){
				foreach($this->extraFields AS $query){
                    $field = $query['field'];

                    if($query['query']){
                        $field = $query['query'];
                    }

                    if(!in_array($field, $select)) {
                        $select[] = $field;
                    }
				}
			}

            $res = $this->db->getFirstRow(
                Db::select(
                    $this->dbTable,
                    $select,
                    $this->getAllKeysValues(),
                    $this->joins
                )
            );
			if (!empty($res)) {
                $isFound = true;

                foreach($this->inputs as $val) {
					if (!empty($val['name']) && isset($res[$val['name']])) {
						switch ($val['type']) {
							case 'checkgroup':
								$res[$val['name']] = explode('|', trim($res[$val['name']], '|'));
								if (count($res[$val['name']]) == 1 && Empty($res[$val['name']][0])) $res[$val['name']] = [];
								break;

							case 'date':
								//$res[$val['name']] = $this->owner->lib->formatDate($res[$val['name']]);
								break;

							case 'text':
								if(!Empty($val['select']['name']) && isset($res[$val['select']['name']])){
									$this->values[$val['select']['name']] = $res[$val['select']['name']];
								}
								break;
						}
                        
						$this->values[$val['name']] = $res[$val['name']];
					}
				}

				if($this->extraFields){
					foreach($this->extraFields AS $field => $query){
					    if(!isset($this->values[$field])) {
							$this->values[$field] = $res[$field];
						}
					}
				}
			}
		}

		$this->onLoadValues($isFound);
	}

    protected function setJoin(string $tableName, array $conditions):void
    {
        $this->joins[$tableName] = [
            'on' => $conditions
        ];
    }

	private function validate(bool $skipValidation = false):bool
    {
		$this->errors = [];

		$this->isValid = true;

        if($skipValidation){
            return $this->isValid;
        }

        if(!Empty($this->reCaptcha)){
			$this->isValid = $this->checkReCaptchaToken();
		}

		foreach($this->inputs as $input) {
            if (!str_contains($input['name'], '][')) {
                $valueToCheck = ($this->values[$input['name']] ?? false);
            }else{
                list($key, $index) = explode('][', $input['name']);
                $valueToCheck = $this->values[$key][$index];
            }

            if (!empty($valueToCheck)) {
				switch($input['type']) {
					case 'email':
						if (!Utils::checkEmail($valueToCheck)) {
							$this->addError('ERR_WRONG_EMAIL_FORMAT', self::FORM_ERROR, [$input['id']]);
						}
						break;

					case 'number':
                        $valueToCheck *= 1;
						if (!is_int($valueToCheck)) {
							$this->addError('ERR_WRONG_NUMBER_FORMAT', self::FORM_ERROR, [$input['id']]);
						}
						break;

					case 'url':
						if (!filter_var($valueToCheck, FILTER_VALIDATE_URL)) {
							$this->addError('ERR_WRONG_URL_FORMAT', self::FORM_ERROR, [$input['id']]);
						}
						break;
				}
			}

			if (!empty($input['constraints'])) {
				foreach($input['constraints'] as $constraint => $cValue) {
					switch($constraint) {
						case 'required':
							if (!empty($cValue) && empty($valueToCheck)
								&& ($input['type'] != 'number' || $valueToCheck == '')) {

								$this->addError('ERR_MARKED_FIELDS_ARE_MISSING', self::FORM_ERROR, [$input['id']]);
							}
							break;

						case 'equalto':
							$cValue = str_replace('#', '', $cValue);
							if (!empty($cValue) && !empty($valueToCheck)) {
								$checkValue = '';
								foreach($this->inputs AS $imp){
									if($imp['id']==$cValue){
										$name = $imp['name'];
										$checkValue = $this->values[$name];
										break;
									}
								}

								if($checkValue != $valueToCheck){
									$this->addError('ERR_MARKED_FIELDS_ARE_NOT_MATCHING', self::FORM_ERROR, [$input['id']]);
								}
							}
							break;
					}
				}
			}
		}

		$this->onValidate();

		return $this->isValid;
	}

	protected function saveValues():void
    {
		$this->onBeforeSave();

		if(!Empty($this->reCaptcha)){
			$tokenName = ($this->reCaptcha['token'] ?: $this->reCaptchaTokenName);
			unset($this->values[$tokenName]);
		}

		$statement = '';

		if (!empty($this->dbTable) && !empty($this->keyFields)) {
			$values = $this->values;

            if($this->extraFields) {
                foreach ($this->extraFields as $alias => $query) {
                    if(is_array($query) && $query['exclude']){
                        unset($values[$alias]);
                    }
                }
            }

			foreach($this->inputs as $val) {
                if (Empty($val['DbField']) || (!Empty($val['readonly']) && !empty($values[$val['name']]))) {
                    if (str_contains($val['name'], '][')) {
                        $k = strstr($val['name'], '][', true);
                        unset($values[$k]);
                    }else {
                        unset($values[$val['name']]);
                    }
                } else {
                    if (str_contains($val['name'], '][')) {
                        unset($values[strstr($val['name'], '][', true)]);
                    }

                    switch($val['type']) {
                        case 'checkgroup':
                            if (isset($values[$val['name']]) && is_array($values[$val['name']])) {
                                $values[$val['name']] = '|' . implode('|', $values[$val['name']]) . '|';
                            }
                            break;
                        case 'date':
                            if (empty($values[$val['name']])) {
                                $values[$val['name']] = null;
                            }
                            break;
                    }
                }
            }

            $keyField = array_key_first($this->keyFields);

            if (empty($this->keyValues[$keyField])) {

				$statement = self::SAVE_TYPE_INSERT;

                if(!Empty($this->foreignKeyValues)){
                    foreach($this->foreignKeyValues AS $field => $value){
                        if(!Empty($value)) {
                            $values[$field] = $value;
                        }
                    }
                }

				$this->db->sqlQuery(
					Db::insert($this->dbTable, $values),
				);

                $this->keyValues[$keyField] = $this->db->getInsertRecordId();
			} else {
				$statement = self::SAVE_TYPE_UPDATE;

				$this->db->sqlQuery(
					Db::update($this->dbTable, $values, $this->getAllKeysValues())
				);
			}
		}

		$this->onAfterSave($statement);
	}

    /**
     * @todo refactoring
     * @param $formName
     * @param $alias
     * @param $params
     */
    protected function loadSubForm($formName, $alias = false, $params = [])
    {
        /*
        $params['keyValues'] = ($params['keyValues'] ?? array_values($this->keyFields));
        $params['parentFormName'] = $this->name;
        $params['isReadonly'] = $this->readonly;

        return $this->owner->loadForm($formName, $params, $alias);
        */
    }

    /**
     * @throws Exception
     */
    private function checkReCaptchaToken():bool
    {
		$out = false;

		if(!Empty($this->reCaptcha) && !Empty($this->reCaptcha['secret'])){
			$actionName = ($this->reCaptcha['action'] ?: $this->reCaptchaActionName);
			$tokenName = ($this->reCaptcha['token'] ?: $this->reCaptchaTokenName);
			$response = file_get_contents(
				"https://www.google.com/recaptcha/api/siteverify?secret=" . $this->reCaptcha['secret'] . "&response=" . $this->values[$tokenName] . "&remoteip=" . $_SERVER['REMOTE_ADDR']
			);
            $this->reCaptcha['response'] = json_decode($response, true);

			if(!Empty($this->reCaptcha['response']['success']) && $this->reCaptcha['response']['action'] == $actionName){
				$out = true;
			}else{
				$this->addError('ERR_RECAPTCHA', 2);
			}
		}

		return $out;
	}

    protected function useCaptcha($action = false):self
    {
        $settings = SiteSettings::create();

        $this->reCaptcha = [];

        if($settings->get('captcha') && $settings->get('googleSiteKey') && $settings->get('googleSecret')) {
            $this->setRecaptchaCredentials(
                $settings->get('googleSiteKey'),
                $settings->get('googleSecret'),
                false,
                $action
            );
        }

        return $this;
    }

    protected function setRecaptchaCredentials(string $siteKey, string $secret, string|false $token = false, string|false $action = false):self
    {
        $this->reCaptcha = [
            'siteKey' => $siteKey,
            'secret' => $secret,
            'token' => ($token ?: $this->reCaptchaTokenName),
            'action' => ($action ?: $this->reCaptchaActionName)
        ];

        $this->addControl(
            (new InputRecaptcha(
                ($this->reCaptcha['token'] ?: $this->reCaptchaTokenName),
                $this->reCaptcha['siteKey'],
                ($this->reCaptcha['action'] ?: $this->reCaptchaActionName)
            ))
        );

        return $this;
    }

    protected function getRecaptchaResponse():string
    {
        return $this->reCaptcha['response'];
    }

    public function hasCaptcha():bool
    {
        return (!Empty($this->reCaptcha['siteKey']));
    }

    protected function addButtons(AbstractFormButton ...$buttons):void
    {
        foreach ($buttons as $button) {
            if ($button->getName() === 'cancel' && !Empty($this->parameters['backUrl']) && Empty($button->getUrl())) {
                $button->setUrl($this->parameters['backUrl']);
            }
            if ($button->getName() === 'edit' && !Empty($this->parameters['editUrl'])) {
                $button->setUrl($this->parameters['editUrl']);
            }

            $button->setReadOnly($this->readonly)
                    ->setForm($this->name);

            $this->buttons[$button->getId()] = $button->init();
        }
    }

    public function getButtons():array
    {
        return $this->buttons;
    }

    public function removeButtons():self
    {
        $this->buttons = [];

        return $this;
    }

    protected function addSections(SectionBox  ...$boxes):self
    {

        foreach ($boxes as $section) {
            $this->addSection($section);

            foreach($section->getElements() AS $element) {
                $this->addControl($element, $section->getId());
            }
        }

        return $this;
    }

    protected function addTabs(SectionTab ...$tabs):self
    {
        foreach ($tabs as $section) {
            $this->addSection($section);

            foreach($section->getElements() AS $element) {
                $element->setSectionId($section->getId());
                $this->addControl($element, $section->getId());
            }
        }

        return $this;
    }

    protected function setActiveTab(string $id):void
    {
        if(isset($this->sections[$id])){
            foreach($this->sections AS $key => $section){
                if($section->getId() == $id){
                    $this->sections[$key]->setActive();
                }else{
                    $this->sections[$key]->setActive(false);
                }
            }
        }
    }

    protected function removeSection(string $id):void
    {
        unset($this->sections[$id]);
    }

    protected function hideSidebar(bool $hide = true):self
    {
        $this->showSidebar = !$hide;

	    return $this;
    }

    public function isSidebarVisible():bool
    {
        return $this->showSidebar;
    }

    public function isUpload():bool
    {
        return $this->isUpload;
    }

	protected function addControls(AbstractFormControl ...$controls):void
    {
        foreach ($controls as $control) {
            $this->addControl($control);
        }
    }

    protected function removeControls():void
    {
        $this->controls = [];
        $this->inputs = [];
    }

    /**
     * @throws Exception
     */
    protected function getControl(string $id):false|AbstractFormControl
    {
        if(!$element = $this->findElement($this->controls, $id)) {
            return false;
        }

        return $element;
    }

    public function getControls(false|string $section = false):array
    {
        if($section && isset($this->controls[$section])) {
            return $this->controls[$section];
        }

        return $this->controls;
    }

    /**
     * @throws Exception
     */
    protected function changeControlProperty(string $id, string $method, ...$values):AbstractFormControl
    {
	    if($element = $this->findElement($this->controls, $id)) {
            if (method_exists($element, $method)) {
                if($values) {
                    try {
                        $reflectionMethod = new ReflectionMethod($element, $method);
                        $reflectionMethod->invokeArgs($element, $values);
                    } catch (Exception $e) {
                        die('Invalid method call. Error: ' . $e->getMessage());
                    }
                }else{
                    $element->$method();
                }
            }else{
                throw new Exception("The given method ($method) is not exist!");
            }
        }else{
            throw new Exception("The selected element ID ($id) not found!");
        }

	    return $element;
    }

    protected function removeControl(string $id):self
    {
        $this->deleteElement($this->controls, $id);

        return $this;
    }

    /**
     * @throws Exception
     */
    protected function insertElementToGroup(string $id, AbstractFormControl ...$controls):void
    {
        if($element = $this->findElement($this->controls, $id)) {
            if($element instanceof AbstractFormControl) {
                if ($element->isContainer()) {
                    foreach ($controls as $control) {
                        $this->addInputs($control);
                        $element->addElements($control);
                    }
                } else {
                    throw new Exception("The selected element ID ($id) must be a container (formContainer)!");
                }
            }else{
                throw new Exception("The selected element ID ($id) must be a formControl type!");
            }
        }else{
            throw new Exception("The selected element ID ($id) not found!");
        }
    }

    protected function setSubtitle($subtitle):void
    {
	    if(!Empty($subtitle)) {
            $this->subTitle = $subtitle;
        }
    }

    public function getSubTitle():string
    {
        return $this->subTitle;
    }

    protected function addExtraField(string $field, bool $excludeFromUpdate = true, string|false $selectQuery = false):void
    {
        $this->extraFields[$field] = [
            'field' => $field,
            'query' => ($selectQuery ? $selectQuery . ' AS ' . $field : false),
            'exclude' => $excludeFromUpdate
        ];
    }

    private function findElement(&$object, string $id)
    {
	    $result = false;

        foreach($object AS $elementId => &$element){
            if($elementId == $id){
                return $element;
            }else {
                if ($element instanceof AbstractFormControl) {
                    if ($element->isContainer() && !$result) {
                        $result = $this->findElement($element->getElementsByRef(), $id);
                    }
                } else {
                    if(!$result) {
                        $result = $this->findElement($element, $id);
                    }
                }
            }
        }

        return $result;
    }

    private function deleteElement(&$object, $id):void
    {
        unset($object[$id]);
        unset($this->inputs[$id]);

        foreach($object AS &$element){
            if ($element instanceof AbstractFormControl) {
                if ($element->isContainer()) {
                    $this->deleteElement($element->getElementsByRef(), $id);
                }
            } else {
                $this->deleteElement($element, $id);
            }
        }
    }

    private function resetSections():void
    {
	    foreach($this->sections AS $id => $section){
            $this->sections[$id]->setActive(false);
        }
    }

    private function addSection(AbstractFormSections $section):void
    {
        $this->sectionType = $section->getType();

        $this->sections[$section->getId()] = $section;
    }

    public function getSections():array
    {
        return $this->sections;
    }

    public function getSectionType():string
    {
        return $this->sectionType;
    }

    private function addControl(AbstractFormControl $control, false|string $section = false):void
    {
        $control->onAfterAdded();

        $this->addInputs($control);

        if($section) {
            $this->controls[$section][$control->getId()] = $control;
        }else{
            $this->controls[$control->getId()] = $control;
        }
    }

    protected function addCustomAction($name, $skipValidation = false):void
    {
        $this->customActions[] = [
            'name' => $name,
            'type' => 'custom',
            'skipValidation' => $skipValidation,
        ];
    }

    private function addInputs(AbstractFormControl $control):void
    {
	    if($control->isContainer()){
            $this->addArrayOfInputs($control->getElements());
        }else{
            if($this->readonly){
                $control->setDisabled();
            }

            $inputName = $control->getName();

            if(str_contains($inputName, '][')){
                $isDbField = false;
            }else{
                $isDbField = $control->isDBField();
            }

            $this->inputs[$control->getId()] = [
                'id' => $control->getId(),
                'name' => $control->getName(),
                'type' => $control->getType(),
                'default' => $control->getDefault(),
                'constraints' => $control->getConstraints(),
                'DbField' => $isDbField,
            ];

            $this->mergeJs($control->getJs());

            $this->mergeCss($control->getCss());

            if($control instanceof AbstractFileUpload){
                $this->isUpload = true;
            }
        }
    }

    private function addArrayOfInputs(array $controls):void
    {
        if(!Empty($controls)) {
            foreach ($controls as $control) {
                $this->addInputs($control);
            }
        }
    }

    protected final function addSubTable(AbstractTable $table, array $params = []):AbstractTable
    {
        $table->setParams($params);

        $table->init($this->accessLevel, false, [], array_values($this->getAllKeysValues()));

        $this->mergeJs($table->getJs());

        $this->mergeCss($table->getCss());

        return $table;
    }

    /**
     * @throws Exception
     */
    public final function hasError(string $controlId):bool
    {
        return $this->getControl($controlId)->hasError();
    }

    public final function get(string $key):mixed
    {
        return ($this->values[$key] ?? null);
    }

    public final function getValues():array
    {
        return $this->values;
    }

	protected function onBeforeLoadValues():void
    {
	}

    protected function onAfterLoadValues():void
    {
	}

    protected function onAfterHandleRequest():void
    {
	}

    protected function onValidate():void
    {
	}

    protected function onBeforeSave():void
    {
	}

    protected function onAfterSave(string $statement):void
    {
	}

    protected function onLoadValues(bool $isFound = false):void
    {
	}

    protected function onAfterInit():void
    {
	}

    protected function onBeforeSetup()
    {
    }
}
