<?php
namespace Framework\Api;

use Framework\Api\Services\AuthService;
use Framework\Helpers\Utils;
use Respect\Rest\Router as ApiRouter;

class Api
{
    private ApiRouter $router;

    private Request $request;

    private array $services = [];

    private array $headers = [];

    /**
     * setup routing
     */
    public function start():void
    {
        $this->router = new ApiRouter((API_HOST_NAME ? null : '/api'));

        $this->request = Request::create();

        if($this->request->isValidService()) {
            $serviceName = $this->request->getServiceName();

            $service = $this->request->getService($serviceName);

            $route = $this->router->any('/' . $serviceName . '/**', $this->addService($service))
                ->accept(array(
                    'application/json' => function ($data) {
                        return $this->sendOutput($data, 'json');
                    }
                ))
                ->through(function () {
                    // Log request data
                    Logger::saveRequest();
                });

            if(($service['auth'] & API_AUTH_TYPE_BASIC) == API_AUTH_TYPE_BASIC){
                $route->authBasic('Access restricted', function ($user, $pass) {
                    // Basic authentication
                    return AuthService::create()->authenticate($user, $pass);
                });
            }

            if(($service['auth'] & API_AUTH_TYPE_TOKEN) == API_AUTH_TYPE_TOKEN){
                $route->by(function () {
                    // Check access Token
                    AuthService::create()->checkToken($this->request->getHeader('Authorization'));
                });
            }

            if(($service['auth'] & API_AUTH_TYPE_APIKEY) == API_AUTH_TYPE_APIKEY){
                $route->by(function () {
                    // Check access API Key
                    AuthService::create()->checkApiKey($this->request->getHeader('Api-Key'));
                });
            }
        }

        $this->handleExceptions();
    }

    private function handleExceptions():string
    {
        $this->router->exceptionRoute('Exception', function ($e) {
            if(method_exists($e, 'getData')) {
                $response = $this->setPayloadHeader($e->getData());
            }

            $response['error'] = [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ];

            if (defined('API_LOG_EXCEPTIONS') && API_LOG_EXCEPTIONS) {
                Logger::saveRequest();

                Logger::saveResponse(print_r($response, true), 'txt');
            }

            $e->sendHttpResponseCode();

            AuthService::create()->closeSession();

            return $this->encodeOutput($response);
        });

        return '';
    }


    private function addService(array $service):?AbstractRequester
    {
        if(!Empty($service['class'])) {
            $className = '\\Framework\\Api\\Services\\' . $service['class'];

            if (Empty($this->services[$className]) && class_exists($className)) {
                $this->services[$className] = new $className;
            }

            return $this->services[$className];
        }

        return null;
    }

    private function encodeOutput(array $data, string|false $encoding = false):string
    {
        if(!$encoding){
            $encoding = $this->getAcceptedEncoding();
        }

        switch($encoding){
            case 'html':
                break;
            case 'json':
            default:
                $data = json_encode($data);
                break;
        }

        return $data;
    }

    private function getAcceptedEncoding():string
    {
        $accept = $this->request->getHeader('Accept');

        switch ($accept){
            case 'text/html':
                $encoding = 'html';
                break;
            case 'application/json':
            default:
                $encoding = 'json';
                break;
        }

        return $encoding;
    }

    public function getResponseHeader(string|false $header = false):string|array
    {
        return ($header ? ($this->headers[$header] ?? false) : $this->headers);
    }

    private function setResponseHeader(string $header, string $value):void
    {
        $this->headers[$header] = $value;
    }

    private function sendOutput(array $data, string $encoding = 'json'):string
    {
        if(!$this->getResponseHeader('Cache-Control')) {
            $this->setResponseHeader('Last-Modified', 	gmdate('D, d M Y H:i:s T'));
            $this->setResponseHeader('Expires', 		gmdate('D, d M Y H:i:s T'));
            $this->setResponseHeader('Pragma', 			'no-cache');
            $this->setResponseHeader('Cache-Control', 	'no-cache, must-revalidate');
        }

        $out = $this->setPayloadHeader();
        $out['data'] = ($data ?: []);

        $this->sendHeaders();
        $output = $this->encodeOutput($out, $encoding);

        Logger::saveResponse($output, $encoding);

        AuthService::create()->closeSession();

        return $output;
    }

    private function setPayloadHeader(array $data = []):array
    {
        $out = [
            'messageId' => $this->request->getMessageId(),
            'version' => $this->request->getVersion(),
            'serverDate' => date(DATE_ATOM),
            'server' => SERVER_ID,
            'clientIp' => Utils::getClientIP(),
            'error' => [],
            'data' => [],
        ];

        if($data){
            foreach($data AS $key => $value){
                if(!isset($out[$key])){
                    $out[$key] = $value;
                }
            }
        }

        return $out;
    }

    private function sendHeaders():void
    {
        $headers = $this->getResponseHeader();
        if($headers){
            foreach($headers AS $header => $value){
                header($header . ': ' . $value);
            }
        }
    }

}