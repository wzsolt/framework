<?php
namespace Applications\Admin\Controllers\Ajax;

use Framework\Components\Enums\PageType;
use Framework\Controllers\Pages\AbstractAjaxConfig;

class Cleanup extends AbstractAjaxConfig
{

    public function setup(): bool
    {
        //Session::delete('rand-id');

        return true;
    }

    protected function setAction(?array $params = [], array $post = [], ?array $rawInput = []): string|false
    {
        return false;
    }

    protected function setOutputFormat(): PageType
    {
        return PageType::Json;
    }
}