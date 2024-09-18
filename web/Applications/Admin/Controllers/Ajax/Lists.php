<?php
namespace Applications\Admin\Controllers\Ajax;

use Framework\Components\Enums\PageType;
use Framework\Components\Lists\AbstractList;
use Framework\Controllers\Pages\AbstractAjaxConfig;
use Framework\Helpers\Str;

class Lists extends AbstractAjaxConfig
{
    private AbstractList $list;

    public function setup(): bool
    {
        $params = $this->getUrlParams();

        $listClass = '\\Framework\\Components\\Lists\\List' . Str::dashesToCamelCase($params[0]);
        if(class_exists($listClass)) {
            $this->list = new $listClass();
        }else{
            throw new \Exception('Class not found: ' . $listClass);
        }

        return true;
    }

    protected function setOutputFormat(): PageType
    {
        return PageType::Json;
    }

    protected function setAction(?array $params = [], array $post = [], ?array $rawInput = []): string|false
    {
        return 'getList';
    }

    protected function getList():array
    {
        $this->list->setParams($_GET);

        return $this->convert($this->list->getList());
    }

    private function convert(array $list):array
    {
        $newList = [];

        foreach($list AS $key => $value) {
            $newList[] = [
                'id' => $key,
                'text' => $value
            ];
        }

        return $newList;
    }
}