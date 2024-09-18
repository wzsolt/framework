<?php

namespace Framework\Components\Lists;

use Framework\Locale\Translate;
use Framework\Models\Database\Db;

abstract class AbstractList
{
    private array $list = [];

    private array $tempList = [];

    private array $options = [];

    private array $emptyOptions = [];

    private bool $json = false;

    protected bool $translateItems = false;

    protected array $params;

    abstract protected function setup():array;

    public function __construct(array $params = [])
    {
        $this->setParams($params);
    }

    public function setParams(array $params): self
    {
        $this->params = $params;

        return $this;
    }

    public function getParams():array
    {
        return $this->params;
    }

    public function setJson(bool $json = true):self
    {
        $this->json = $json;

        return $this;
    }

    public function addEmptyItem(string $label, int $value = 0):self
    {
        if (!$value) $value = 0;

        $this->emptyOptions[$value] = $label;

        return $this;
    }

    /**
     * sql fields:
     *  key: for ID
     *  value: for data
     *  groupId: for group identifier
     *  groupName: for group name
     *  subtext: for sub text value
     *  title: for title
     *  tokens: for tokens
     *  icon: for icons
     *  image: for image
     *
     * @param string $sql
     * @param mixed $preprocessor
     * @return array
     */
    protected function listFromSqlQuery(string $sql, mixed $preprocessor = null):array
    {
        $this->tempList = [];

        $res = Db::create()->getRows($sql);
        if (!empty($res)) {
            $i = count($this->emptyOptions);

            foreach ($res as $row) {
                if ($preprocessor && method_exists($this, $preprocessor)) {
                    $row = $this->$preprocessor($row);
                }
                if ($this->json) {
                    $data = ['id' => $row['key'], 'text' => $row['value']];

                    if (!empty($row['groupName'])) {
                        $data['groupId'] = $row['groupId'];
                        $data['groupName'] = $row['groupName'];
                    }
                    if (!empty($row['subtext'])) {
                        $data['data']['subText'] = $row['subtext'];
                    }
                    if (!empty($row['tokens'])) {
                        $data['data']['tokens'] = $row['tokens'];
                    }
                    if (!empty($row['icon'])) {
                        $data['data']['icon'] = $row['icon'];
                    }
                    if (!empty($row['image'])) {
                        $data['data']['image'] = $row['image'];
                    }

                    $this->addItem($i, $data);

                } else {
                    $attributes = [];

                    if (!empty($row['subtext']) || !empty($row['tokens']) || !empty($row['icon']) || !empty($row['title'])) {
                        $attributes = [];
                        if (!empty($row['subtext'])) {
                            $attributes['data-subtext'] = $row['subtext'];
                        }
                        if (!empty($row['tokens'])) {
                            $attributes['data-tokens'] = $row['tokens'];
                        }
                        if (!empty($row['icon'])) {
                            $attributes['data-icon'] = $row['icon'];
                        }
                        if (!empty($row['title'])) {
                            $attributes['title'] = $row['title'];
                        }
                    }

                    $this->addItem($row['key'], $row['value'], ($row['groupName'] ?? false), $attributes);
                }
                $i++;
            }
        }

        return $this->tempList;
    }

    private function addItem(string $key, mixed $value, string|false $group = false, array $attributes = []):void
    {
        $item = [];

        if ($attributes) {
            $item['name'] = $value;
            $item['data'] = $attributes;
        } else {
            $item = $value;
        }

        if ($group) {
            if (isset($this->options[$group])) {
                $group = $this->options[$group];
            }

            $this->tempList[$group][$key] = $item;
        } else {
            $this->tempList[$key] = $item;
        }
    }

    private function translateItems():self
    {
        if ($this->list) {
            foreach ($this->list as $key => $val) {
                if (empty($val)) continue;

                if (is_array($val)) {
                    foreach ($val as $key2 => $val2) {
                        $this->list[$key][$key2] = Translate::get($val2);
                    }
                } else {
                    $this->list[$key] = Translate::get($val);
                }
            }
        }

        return $this;
    }

    public function reset():self
    {
        $this->list = [];

        $this->json = false;

        $this->emptyOptions = [];

        return $this;
    }

    protected function setGroupOptions(array $options):self
    {
        $this->options = $options;

        return $this;
    }

    private function setList(array $list): void
    {
        $this->list = $list;
    }

    public function getList():array
    {
        $this->setList( $this->setup() );

        $this->addEmptyListItems();

        if($this->translateItems){
            $this->translateItems();
        }

        return $this->list;
    }

    public function getItem(string|int $key):string|array|false
    {
        $this->setList( $this->setup() );

        return ($this->list[$key] ?? false);
    }

    private function addEmptyListItems():void
    {
        if ($this->emptyOptions) {
            $firstItem = [];
            foreach ($this->emptyOptions as $value => $label) {
                if ($this->json) {
                    $firstItem[] = ['id' => $value, 'text' => Translate::get($label),];
                } else {
                    $firstItem[$value] = Translate::get($label);
                }
            }
            $this->list = $firstItem + $this->list;
        }
    }
}