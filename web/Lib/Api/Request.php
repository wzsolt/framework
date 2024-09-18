<?php

namespace Framework\Api;

use Framework\Components\Uuid;

class Request
{
    private static Request $instance;

    private string $messageId;

    private false|string $serviceName = false;

    private string $actionName = '';

    private string $version = API_CURRENT_VERSION;

    private array|false $headers = [];

    public static function create():Request
    {
        if (!isset(self::$instance)) {
            self::$instance = new Request();

            self::$instance->init();
        }

        return self::$instance;
    }

    public function getHeader(string|false $header = false):string|array
    {
        return ($header ? ($this->headers[$header] ?? false) : $this->headers);
    }

    private function init():void
    {
        $this->headers = getallheaders();

        if(Empty($this->headers['Version'])){
            $this->version = API_CURRENT_VERSION;
        }else{
            $version = $this->headers['Version'];
            if(!in_array($version, $GLOBALS['API_VALID_VERSIONS'])){
                $this->version = API_CURRENT_VERSION;
            }else{
                $this->version = $version;
            }
        }

        $this->createMessageId();
    }

    public function isValidService():bool
    {
        if (!empty($_REQUEST['path'])) {
            $path = explode('/', trim($_REQUEST['path'], '/'));
            if(strtolower($path[0]) === 'api'){
                array_shift($path);
            }

            $serviceName = strtolower($path[0]);
            if(isset($GLOBALS['API_SERVICES'][$serviceName])){
                $this->serviceName = $serviceName;

                return true;
            }
        }

        return false;
    }

    public function getService(string $serviceName):array
    {
        if(isset($GLOBALS['API_SERVICES'][$serviceName])){
            return $GLOBALS['API_SERVICES'][$serviceName][$this->getVersion()];
        }

        return [];
    }

    private function createMessageId():void
    {
        if(!Empty($this->headers['Message-Id'])) {
            $this->messageId = $this->headers['Message-Id'];
        }else {
            $this->messageId = Uuid::v4();
        }
    }

    public function getMessageId():string
    {
        return $this->messageId;
    }

    public function getVersion():string
    {
        return $this->version;
    }

    public function getServiceName():string
    {
        return $this->serviceName;
    }

    public function getActionName():string
    {
        return $this->actionName;
    }

    public function setActionName(string $action):void
    {
        $this->actionName = $action;
    }
}