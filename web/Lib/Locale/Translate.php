<?php

namespace Framework\Locale;

use Framework\Components\HostConfig;
use Framework\Models\Database\Db;
use Framework\Models\Memcache\MemcachedHandler;
use Framework\Models\Memcache\MemcacheInterface;

class Translate
{
	const USE_CACHE = false;

    private static Translate $instance;

	private array $labels = [];

	private ?MemcacheInterface $mem = null;

    private string $memcacheKey = LABELS_KEY;

    private string $context = 'app';

    private string $language;

    private array $newLabels = [];

	private int $clientId = 0;

	public function __destruct()
    {
		if(!Empty($this->newLabels)){
			$this->loadLabels();
		}
	}

    public static function create():Translate
    {
        if (!isset(self::$instance)) {
            self::$instance = new Translate();
        }

        return self::$instance;
    }

	public function load(HostConfig $config, bool $forceReload = false):void
    {
        $this->initMemCache();

        //$this->setClientId($config->getClientId());
        $this->setClientId(0);

		//$this->setContext($config->getApplication());
		$this->setContext('admin');

		$this->language = $config->getLanguage();

		if(!$forceReload && self::USE_CACHE) {
			$this->labels[$this->language] = $this->mem->get($this->getMemcacheKey());
		}

		if(!isset($this->labels[$this->language]) || $forceReload){
			$this->loadLabels();
		}
	}

    public function setClientId(int $clientId):self
    {
        $this->clientId = $clientId;

        return $this;
    }

    private function initMemCache():void
    {
        if(!$this->mem){
            $this->mem = MemcachedHandler::create();
        }
    }

	public function getMemcacheKey(int|false $clientId = false, string $language = ''):string
    {
		if(!$clientId) $clientId = $this->clientId;

		if(Empty($language)) $language = $this->language;

		return $this->memcacheKey . $clientId . '-' . $this->context . '-' . $language;
	}

	public function setContext(string $context):void
    {
		$this->context = $context;
	}

	public static function _(...$args):string
    {
        return Translate::create()->getTranslation(...$args);
	}

    public static function get(...$args):string
    {
        return Translate::create()->getTranslation(...$args);
    }

	public function getTranslation(string $label):string
    {
        if(Empty($label)){
            return '';
        }

		if(!$this->isLabel($label)){
			return $label;
		}

		$args = func_get_args();
		unset($args[0]);

		$label = strtoupper($label);

		if(isset($this->labels[$this->language][$label])){
			$string = $this->labels[$this->language][$label];
		}else{
			// check db for label
			$string = $this->getLabel($label, $this->context);

			if(Empty($string)){
				$this->addLabel($label, $label, $this->context);
				$string = $label;
			}
		}

		if(!Empty($args)) {
			$cr = new CustomReplace;
			$cr->args = $args;

			$string = preg_replace_callback(
				'/%([0-9]+)/',
				[&$cr, 'replace'],
				$string
			);
		}

		return $string;
	}

	public function getTranslationTo(string $label, string $language):string
    {
		$tmpLanguage = $this->language;

		$this->language = $language;

        $args = func_get_args();
		unset($args[1]);

        $string = call_user_func_array(array($this, "getTranslation"), $args);
		$this->language = $tmpLanguage;

        return $string;
	}

	public function getAlternateTranslation(string $label, string $defaultLabel = ''):string
    {
		$args = func_get_args();
		unset($args[0], $args[1]);

		$string = '';
		$label = strtoupper($label);

		if(isset($this->labels[$this->language][$label])){
			$string = $this->labels[$this->language][$label];
		}elseif(!Empty($defaultLabel) AND isset($this->labels[$this->language][$defaultLabel])) {
			$string = $this->labels[$this->language][$defaultLabel];
		}

		if(!Empty($args)) {
			$cr = new CustomReplace;
			$cr->args = $args;

			$string = preg_replace_callback(
				'/%([0-9]+)/',
				[&$cr, 'replace'],
				$string
			);
		}

		return $string;
	}

	private function loadLabels(bool $updateCache = true):void
    {
		// Load default language
		$defaultLabels = false;

		if(DEFAULT_LANGUAGE != $this->language){
			$defaultLabels = $this->getContextLabels($this->context, DEFAULT_LANGUAGE);
		}

		// Load selected language
		$this->labels[$this->language] = $this->getContextLabels($this->context, $this->language);

		if($defaultLabels){
			// merge selected and default languages
			foreach($defaultLabels AS $key => $value){
				if(!isset($this->labels[$this->language][$key])){
					$this->labels[$this->language][$key] = $value;
				}
			}
		}

		// Store in memcache
		if($updateCache){
			$this->mem->set($this->getMemcacheKey(), $this->labels[$this->language]);
		}
	}

	private function getContextLabels(string|array $context, string $language):array
    {
		$labels = [];

		if(!is_array($context)){
			$context = [$context];
		}

		$tmp = [];

		foreach($context AS $values){
			if(!in_array("'" . $values . "'", $tmp)) array_push($tmp, "'" . $values . "'");
		}

		$sql  = "SELECT di_label, di_value FROM dictionary ";
		$sql .= "LEFT JOIN dictionary_context ON (di_label=dc_label) ";
		$sql .= "WHERE di_language='".$language."' AND di_deleted='0' ";
		$sql .= "AND dc_context IN (".implode(',', $tmp).") ";
		$sql .= "AND (di_client_id = 0 OR di_client_id = " . $this->clientId . ") ";
		$sql .= "GROUP BY di_client_id, di_label ORDER BY di_client_id DESC, di_label";

		$result = Db::create()->getRows($sql);
		if($result){
			foreach($result AS $row){
				if(!isset($labels[$row['di_label']])){
					$key = strtoupper($row['di_label']);
					$labels[$key] = $row['di_value'];
				}
			}
		}

		return $labels;
	}

	private function getLabel(string $label, string $context = ''):string
    {
		if(!Empty($label)){
			$label = strtoupper($label);

			if(!isset($this->labels[$this->language][$label])){
				$sql  = "SELECT di_value FROM dictionary ";
				if($context) $sql .= "LEFT JOIN dictionary_context ON (di_label=dc_label) ";
				$sql .= "WHERE di_label='" . $label . "' AND di_language='" . $this->language . "'  AND di_deleted='0' ";
				if($context) $sql .= "AND dc_context='" . $context . "' ";
				$sql .= " AND (di_client_id = 0 OR di_client_id = '" . $this->clientId . "') ";
				$sql .= " ORDER BY di_client_id DESC LIMIT 1";

				$row = Db::create()->getFirstRow($sql);
                if(!Empty($row['di_value'])) {
                    $this->labels[$this->language][$label] = $row['di_value'];
                    $this->newLabels[$this->language][$label] = $row['di_value'];

                    return $this->labels[$this->language][$label];
                }
			}
		}

        return '';
	}

	public function addLabel(string $label, string $value, string $context):void
    {
		if(!Empty($label)){
			$label = strtoupper($label);
			Db::create()->sqlQuery(
				Db::insert(
					'dictionary',
					[
						'di_label'    => $label,
						'di_value'    => $value,
						'di_language' => $this->language,
						'di_client_id' => $this->clientId,
						'di_changed'  => date("Y-m-d H:i:s"),
						'di_new'      => 1,
						'di_path'     => ($_REQUEST['path'] ?? '')
					],
					['di_label', 'di_client_id', 'di_language']
				)
			);

			$this->addLabelToContext($label, $context);

			$this->labels[$this->language][$label] = $this->newLabels[$this->language][$label] = $value;
		}
	}

	public function countLabels(string $langFrom, string $langTo, string $context):array
    {
		$out = [
			'total' => 0,
			'orig' => [
				'translated' => 0,
				'status' => 0,
			],
			'custom' => [
				'translated' => 0,
				'status' => 0,
			],
		];

		$items = [];

		$sql  = "SELECT di_label FROM dictionary ";
		$sql .= "LEFT JOIN dictionary_context ON (di_label=dc_label) ";
		$sql .= "WHERE di_language='" . $langFrom . "' AND di_deleted='0' ";
		$sql .= "AND di_client_id = 0 ";
		$sql .= "AND dc_context = '" . $context . "' ";
		$sql .= "GROUP BY di_label";

		$result = Db::create()->getRows($sql);
		if($result){
			foreach($result AS $row){
				$key = strtoupper($row['di_label']);
				$items[$key][$langFrom] = 1;
				$out['total']++;
			}

			// Get corresponding translation
			$sql  = "SELECT di_client_id, di_label, di_value FROM dictionary ";
			$sql .= "LEFT JOIN dictionary_context ON (di_label=dc_label) ";
			$sql .= "WHERE di_language='" . $langTo . "' AND di_deleted='0' ";
			$sql .= "AND (di_client_id = 0 OR di_client_id = '" . $this->clientId . "') ";
			$sql .= "AND dc_context = '" . $context . "' ";
			$sql .= "GROUP BY di_client_id, di_label ";
			$sql .= "ORDER BY di_client_id DESC, di_label";

			$result = Db::create()->getRows($sql);

			if($result) {
				foreach ($result AS $row) {
					$key = strtoupper($row['di_label']);

					if(isset($items[$key][$langFrom])) {
						if ($row['di_client_id'] == 0) {
							if($row['di_value'] != $key && !Empty($row['di_value'])) $out['orig']['translated']++;
						} else {
							if($row['di_value'] != $key && !Empty($row['di_value'])) $out['custom']['translated']++;
						}
					}
				}
			}

			if($out['total']>0) {
				$out['orig']['status'] = round(($out['orig']['translated'] / $out['total']) * 100);
				$out['custom']['status'] = round(($out['custom']['translated'] / $out['total']) * 100);
			}
		}

		return $out;
	}

	public function getAllLabels(string $langFrom, string $langTo, string $context, int $page = 1, bool $loadFromFirst = false, array $filters = [], string $sort = 'key', int $labelsPerPage = 20):array
    {
		$out = [
			'items' => [],
			'context' => $context,
			'stats' => $this->countLabels($langFrom, $langTo, $context)
		];

		$where = [];
		$keys = [];
		if($filters['flag'] == 'not-translated' AND $langFrom != $langTo){

			$sql  = "SELECT d1.di_label, d1.di_value, d2.di_label AS label, d2.di_value AS value, d2.di_language AS lang FROM dictionary AS d1 ";

			$sql .= "LEFT JOIN dictionary AS d2 ON (d1.di_label=d2.di_label AND d2.di_language='".$langTo."') ";
			$sql .= "LEFT JOIN dictionary_context ON (d1.di_label=dc_label) ";

			$sql .= "WHERE d1.di_language = '" . $langFrom . "' AND d1.di_deleted = 0 ";
			$sql .= "AND (d1.di_client_id = 0 OR d1.di_client_id=" . $this->clientId . ") ";
			$sql .= "AND dc_context = '" . $context . "' ";

			if(!Empty($filters['query'])){
				$sql .= "AND ((d1.di_value LIKE '%" . $filters['query'] . "%' OR d1.di_label LIKE '%" . $filters['query'] . "%') ";
				$sql .= "OR (d2.di_value LIKE '%" . $filters['query'] . "%' OR d2.di_label LIKE '%" . $filters['query'] . "%')) ";
			}
			$sql .= "GROUP BY d1.di_client_id, d1.di_label";

			$result = Db::create()->getRows($sql);
			if($result) {
				foreach($result AS $row) {
					$add = true;
					$key = strtoupper($row['label']);

					if($filters['flag'] == 'not-translated'){
						$add = false;
						if ($row['value'] == $key OR Empty($row['value']) OR Empty($row['lang'])) {
							$add = true;
						}
					}

					if(!in_array($row['di_label'], $keys) AND $add) array_push($keys, $row['di_label']);
				}

				if(!Empty($keys)) {
					foreach ($keys AS $k => $val) {
						$keys[$k] = "'" . $val . "'";
					}
				}
			}
		}

		if(!Empty($filters['query'])){
			$where[] = "(di_value LIKE '%" . $filters['query'] . "%' OR di_label LIKE '%" . $filters['query'] . "%') ";
		}
		if($filters['flag'] == 'not-translated') {
			$tmp = "(di_value = di_label OR di_value IS NULL OR di_value='')";
			if(!Empty($keys)) $tmp = "(" . $tmp . " OR di_label IN (" . implode(',', $keys) . "))";
			$where[] = $tmp;

		}elseif($filters['flag'] == 'new'){
			$where[] = "di_new='1'";
		}

		$sql  = "SELECT COUNT(di_label) AS cnt FROM dictionary ";
		$sql .= "LEFT JOIN dictionary_context ON (di_label=dc_label) ";
		$sql .= "WHERE di_language='" . $langFrom . "' AND di_deleted = 0 ";
		$sql .= "AND di_client_id = 0 ";
		$sql .= "AND dc_context = '" . $context . "' ";
		if($where) $sql .= "AND " . implode(" AND ", $where);

		$total = (int) Db::create()->getFirstRow($sql)['cnt'];
		$totalpages = ceil($total / $labelsPerPage);
		$out['stats']['totalpages'] = $totalpages;

		if($page>$totalpages) $page = $totalpages;
		if($page<1) $page = 1;

		if($loadFromFirst) {
			$start = 0;
			$labelsPerPage = $page * $labelsPerPage;
		}else {
			$start = ($page * $labelsPerPage) - $labelsPerPage;
		}

		$sql  = "SELECT di_client_id, di_label, di_value, di_changed, di_new FROM dictionary ";
		$sql .= "LEFT JOIN dictionary_context ON (di_label=dc_label) ";
		$sql .= "WHERE di_language='" . $langFrom . "' ";
		$sql .= "AND (di_client_id = 0 OR di_client_id = " . $this->clientId . ") ";

		$sql .= "AND dc_context = '" . $context . "' AND di_deleted='0'";
		if($where) $sql .= " AND " . implode(" AND ", $where);
		$sql .= " GROUP BY di_client_id, di_label";
		$sql .= " ORDER BY di_client_id DESC, ";

		if($sort == 'label') {
			$sql .= "di_value";
		}else{
			$sql .= "di_label";
		}

		$sql .= " LIMIT " . $start . ", " . $labelsPerPage;

		$result = Db::create()->getRows($sql);

		if($result){
			$keys = [];

			foreach($result AS $row){
				$key = strtoupper($row['di_label']);

				if(!in_array($row['di_label'], $keys)) array_push($keys, $row['di_label']);

				if($row['di_client_id'] == 0){

					$out['items'][$key][$langFrom]['original']['value'] = $row['di_value'];
					//$out[$key][$langfrom]['original']['date'] = $row['di_changed'];

					$out['items'][$key][$langFrom]['original']['new'] = $row['di_new'];
				}else{
					if(isset($out['items'][$key][$langFrom]['original'])) {
						$out['items'][$key][$langFrom]['custom']['value'] = $row['di_value'];
						//$out[$key][$langfrom]['custom']['date'] = $row['di_changed'];

						if ($row['di_value'] == $key OR Empty($row['di_value'])) {
							$out['items'][$key][$langFrom]['original']['new'] = $row['di_new'];
						}
					}
				}
			}

			foreach($keys AS $k => $val){
				$keys[$k] = "'" . $val . "'";
			}

			// Get corresponding translations
			$sql = "SELECT di_client_id, di_label, di_value, di_changed FROM dictionary ";
			//$sql .= "LEFT JOIN dictionary_context ON (di_label=dc_label) ";
			$sql .= "WHERE (di_client_id = 0 OR di_client_id = " . $this->clientId . ") AND di_language='" . $langTo . "' ";
			//$sql .= "AND dc_context = '" . $context . "' ";
			$sql .= "AND di_label IN (" . implode(',', $keys) . ") AND di_deleted='0' ";
			$sql .= "GROUP BY di_client_id, di_label ";
			//$sql .= "ORDER BY di_client_id DESC, di_label";

			$result = Db::create()->getRows($sql);
			if ($result) {
				foreach ($result AS $row) {
					$key = strtoupper($row['di_label']);

					if (isset($out['items'][$key][$langFrom])) {

						if ($row['di_client_id'] == 0) {
							$out['items'][$key][$langTo]['original']['value'] = $row['di_value'];
							$out['items'][$key][$langTo]['original']['date'] = $row['di_changed'];
                        }

                        $out['items'][$key][$langTo]['custom']['value'] = $row['di_value'];
                        $out['items'][$key][$langTo]['custom']['date'] = $row['di_changed'];
					}
				}
			}
		}

		return $out;
	}

	public function saveTranslation(string $langTo, string $label, string $value, string $context):void
    {
		$this->setContext($context);

		$value = trim($value);

		if(Empty($value) && $this->clientId){
			$this->markLabelForDelete($label, $langTo, $this->clientId);
		}else {
			$sql = Db::insert(
				'dictionary',
				[
					'di_label' => $label,
					'di_value' => $value,
					'di_language' => strtolower($langTo),
					'di_changed' => 'NOW()',
					'di_new' => 2,
					'di_deleted' => 0,
					'di_client_id' => $this->clientId
				],
				[
					'di_label',
					'di_language',
					'di_client_id',
				]
			);
			Db::create()->sqlQuery($sql);
		}

		// update memcache key value
		$this->mem->delete($this->getMemcacheKey(0, $langTo));
	}

	public function deleteLabels(array $labels = []):void
    {
		if(Empty($labels)) {
			Db::create()->sqlQuery(
                Db::delete(
                    'dictionary',
                    [
                        'di_deleted' => 1
                    ]
                )
            );
		}else{
			foreach($labels AS $label){
                Db::create()->sqlQuery(
                    Db::delete(
                        'dictionary',
                        [
                            'di_label' => $label
                        ]
                    )
                );
			}
		}
	}

	public function markLabelForDelete(string $label, string|false $lang = false, int $clientId = 0):void
    {
        $where = [
            'di_label' => $label
        ];

		if($clientId && $lang){
            $where['di_language'] = $lang;
            $where['di_client_id'] = $clientId;
		}

		Db::create()->sqlQuery(
		    Db::update(
                'dictionary',
                [
                    'di_deleted' => 1
                ],
                $where
            )
        );
	}

	private function addLabelToContext(string $label, string $context):void
    {
		Db::create()->sqlQuery(
			Db::insert(
				'dictionary_context',
				[
					'dc_context' => $context,
					'dc_label'   => strtoupper($label)
				],
				['dc_context']
			)
		);
	}

	/*
	 * Sync functions
	 */
	public function loadLabelSet(string|array $language, int $clientId = 0):array
    {
		$lang = [];
		$labels = [];

        if(!is_array($language)) $language = [$language];

        foreach($language AS $l){
            array_push($lang, "'" . $l . "'");
        }

		$sql  = "SELECT di_label, di_value, di_language, dc_context FROM dictionary ";
		$sql .= "LEFT JOIN dictionary_context ON (di_label=dc_label) ";
		$sql .= "WHERE di_client_id='" . $clientId . "' AND di_language IN (" . implode(',', $lang) . ") AND di_new = 2 AND di_deleted='0'";

		$result = Db::create()->getRows($sql);
		if($result){
			foreach($result AS $row){
				$lang = $row['di_language'];
				if(!isset($labels[$row['di_label']])){
					$key = strtoupper($row['di_label']);
					$labels[$lang][$key]['value'] = $row['di_value'];
					$labels[$lang][$key]['context'] = $row['dc_context'];
				}

			}
		}

		return $labels;
	}

	public function markLabelsSynced(array $labels, int $clientId = 0):bool
    {
		$out = false;

		if(!Empty($labels)){
			foreach($labels AS $lang => $data){
				foreach($data AS $key => $value){
					$sql = Db::update(
						'dictionary',
						[
							'di_new' => 0
						],
						[
							'di_label' => $key,
							'di_language' => $lang,
							'di_client_id' => $clientId
						]
					);

					Db::create()->sqlQuery($sql);

					$out .= $sql . "\n";
				}
			}

		}

		return $out;
	}

	public function updateLabelSet(array $labels, int $clientId = 0):bool
    {
		$out = false;

		if($labels){
			foreach($labels AS $lang => $data){
				foreach($data AS $key => $value){
					$sql = Db::insert(
						'dictionary',
						[
							'di_label' => $key,
							'di_language' => $lang,
                            'di_client_id' => $clientId,
                            'di_value' => $value['value'],
                            'di_changed' => 'NOW()',
							'di_new' => 0
						],
						[
							'di_label',
							'di_language',
							'di_client_id'
						]
					);

					Db::create()->sqlQuery($sql);

					$this->addLabelToContext($key, $value['context']);
				}
			}

			$out = true;
		}

		return $out;
	}

	public function listDeletedLabels(int $clientId = 0):array
    {
		$out = [];

		$result = Db::create()->getRows(
            Db::select(
                'dictionary',
                [
                    'di_label'
                ],
                [
                    'di_deleted' => 1,
                    'di_client_id' => $clientId
                ],
                [],
                'di_label'
            )
        );

		if($result) {
			foreach ($result AS $row) {
				$out[] = $row['di_label'];
			}
		}

		return $out;
	}

	public function deleteUnusedLabels():void
    {
        Db::create()->sqlQuery(
            Db::delete(
                'dictionary',
                [
                    'di_deleted' => 1,
                ]
            )
        );
	}

    public function deleteUnTranslatedLabels():void
    {
        Db::create()->sqlQuery(
            Db::delete(
                'dictionary',
                [
                    Db::CUSTOM_QUERY => 'di_label = di_value'
                ]
            )
        );

        $this->removeUnusedContextItems();
    }

	public function removeUnusedContextItems():void
    {
		$sql = "SELECT dc_context, dc_label FROM dictionary_context LEFT JOIN dictionary ON dc_label = di_label WHERE di_label IS NULL";
		$res = Db::create()->getRows($sql);

		if($res){
			foreach($res AS $row){
                Db::create()->sqlQuery(
                    Db::delete(
                        'dictionary_context',
                        [
                            'dc_context' => $row['dc_context'],
                            'dc_label' => $row['dc_label'],
                        ]
                    )
                );
			}
		}
	}

	public function clearTranslationCache(string|array $languages, int|array $clientIds = 0):void
    {
        if(!is_array($clientIds)) $clientIds = [$clientIds];
        if(!is_array($languages)) $languages = [$languages];

        $this->initMemCache();

		$sql = "SELECT DISTINCT(dc_context) AS context FROM dictionary_context";
		$res = Db::create()->getRows($sql);
		if($res){
			foreach($res AS $row){
                foreach($clientIds AS $clientId) {
                    foreach($languages AS $language) {
                        $key = $this->memcacheKey . $clientId . '-' . $row['context'] . '-' . $language;
                        $this->mem->delete($key);
                    }
                }
			}
		}
	}

	private function isLabel(string $label):bool
    {
		$check = [
			'LBL_',
			'BTN_',
			'ERR_',
			'TXT_',
			'MSG_',
			'MENU_',
			'CONFIRM_',
		];

		foreach ($check as $string){
			$pos = stripos($label, $string);
			if ($pos !== false){
				return true;
			}
		}

		return false;
	}
}

