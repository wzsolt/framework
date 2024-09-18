<?php
namespace Applications\Admin\Controllers\Ajax;

use Framework\Components\Autocomplete\AutocompleteInterface;
use Framework\Components\Enums\PageType;
use Framework\Controllers\Pages\AbstractAjaxConfig;
use Framework\Helpers\Str;

class Autocomplete extends AbstractAjaxConfig
{
    private AutocompleteInterface $autocomplete;

    public function setup(): bool
    {
        $params = $this->getUrlParams();

        $autocompleteClass = '\\Framework\\Components\\Autocomplete\\' . Str::dashesToCamelCase($params[0]);
        if(class_exists($autocompleteClass)) {
            $this->autocomplete = new $autocompleteClass();
        }else{
            throw new \Exception('Class not found');
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
        $query = ($_REQUEST['q'] ?? '');

        return $this->autocomplete->list($query);
    }
}