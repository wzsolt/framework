<?php

namespace Framework\Controllers\Pages;

use Framework\Components\Enums\PageType;
use Framework\Components\HostConfig;
use Framework\Helpers\Str;

abstract class AbstractAjaxConfig extends AbstractPage
{
    protected HostConfig $hostConfig;

    protected array $params = [];

    protected ?array $post = [];

    protected ?array $raw = [];

    public abstract function setup():bool;

    protected abstract function setAction(?array $params = [], array $post = [], ?array $rawInput = []):string|false;

    protected abstract function setOutputFormat():PageType;

    public function __construct()
    {
        $this->hostConfig = HostConfig::create();
    }

    protected function config(): void
    {
        $this->params = $this->getUrlParams();
        $this->post = $this->getPostData();
        $this->raw = $this->getRawInput();

        $this->setType( $this->setOutputFormat() );

        if($this->setup()) {

            $action = $this->setAction($this->params, $this->post, $this->raw);

            if (!$action && !empty($this->params[1])) {
                $action = Str::dashesToCamelCase(strtolower(trim($this->params[1])));
                array_shift($this->params);
            }

            if (!empty($action)) {
                if (method_exists($this, $action)) {
                    //$data = call_user_func_array([$this, $action]);
                    $data = $this->{$action}();
                } else {
                    $data = $this->defaultAction($action);
                }

                if (!empty($data)) {
                    $this->setData($data);
                }
            }

            $this->setData(
                $this->onBeforeRender()
            );
        }
    }

    protected function defaultAction(?string $action):array
    {
        return [];
    }

    protected function onBeforeRender():array
    {
        return [];
    }
}