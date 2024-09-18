<?php
namespace Framework\Api\Clients;

use Framework\Api\ApiException;
use Framework\Components\Uuid;

abstract class AbstractApiClient
{
    const CALL_METHOD_GET       = 'GET';
    const CALL_METHOD_POST      = 'POST';
    const CALL_METHOD_PUT       = 'PUT';
    const CALL_METHOD_DELETE    = 'DELETE';
    const CALL_METHOD_PATCH     = 'PATCH';

    const LOG_REQUEST           = 'rq';
    const LOG_RESPONSE          = 'rs';

    const CLIENT_TIMEOUT        = 60;

    private string $url = '';

    private string $endPoint = '';

    private string $userName = '';

    private string $password = '';

    private array $headers = [];

    private int $timeOut = 0;

    private string $messageId = '';

    private string $payload = '';

    private ?array $response = null;

    private ?array $errors = [];

    public function setEndPoint(string $url):self
    {
        $this->endPoint = rtrim($url, '/') . '/';

        return $this;
    }

    public function setServiceUrl(string $url, array $params = []):self
    {
        $this->url = rtrim($url, '/') . '/' . ($params ? '?' . http_build_query($params) : '');

        return $this;
    }

    public function setApiKey(string $key):self
    {
        $this->addHeader('Api-Key', $key);

        return $this;
    }

    public function setVersion(string $version):self
    {
        $this->addHeader('Version', $version);

        return $this;
    }

    public function setMessageId(string $id):self
    {
        $this->messageId = $id;

        return $this;
    }

    public function setTimeOut(int $sec):self
    {
        $this->timeOut = $sec;

        return $this;
    }

    public function setPayload($data):self
    {
        $this->payload = (is_array($data) ? json_encode($data, JSON_PRETTY_PRINT) : $data);

        return $this;
    }

    public function addHeader(string $key, string $value):self
    {
        $this->headers[$key] = $value;

        return $this;
    }

    public function setCredentials(string $user, $password):self
    {
        $this->userName = $user;

        $this->password = $password;

        return $this;
    }

    public function getErrors():array
    {
        return $this->errors;
    }

    private function getHeaders():array
    {
        $headers = [];

        if($this->headers){
            foreach($this->headers AS $key => $value){
                $headers[] = $key . ': ' . $value;
            }
        }

        return $headers;
    }

    protected function callService(string $method = self::CALL_METHOD_GET):?array
    {
        if(!$this->endPoint){
            throw new ApiException('Endpoint is missing', 1);
        }

        $ch = curl_init($this->endPoint . $this->url);
        $action = str_replace('/', '_', $this->url);

        $this->addHeader('Content-Type', 'application/json');
        $this->addHeader('Accept', 'application/json');
        $this->addMessageIdToHeader();

        if($this->userName && $this->password){
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, $this->userName . ":" . $this->password);
        }

        curl_setopt($ch, CURLOPT_TIMEOUT, ($this->timeOut ?: self::CLIENT_TIMEOUT));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getHeaders());
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if(in_array($method, [self::CALL_METHOD_POST, self::CALL_METHOD_PUT, self::CALL_METHOD_PATCH]) && $this->payload) {
            $this->saveLog($this->payload, $action);

            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->payload);
        }

        $response = curl_exec($ch);

        $this->saveLog($response, $action, self::LOG_RESPONSE);

        $this->processResponse($response);

        curl_close($ch);

        return $this->getResponse();
    }

    protected function getResponse():?array
    {
        return $this->response;
    }

    private function addMessageIdToHeader(): void
    {
        if(!$this->messageId){
            $this->messageId = Uuid::v4();
        }

        $this->addHeader('Message-Id', $this->messageId);

    }

    private function processResponse(string $result):void
    {
        $result = json_decode($result, true);

        $this->response = ($result['data'] ?? []);

        $this->errors = ($result['error'] ?? []);
    }

    private function saveLog($data, $action, $method = self::LOG_REQUEST):void
    {
        if (defined('DIR_LOG') ) {
            $className = (new \ReflectionClass($this))->getShortName();
            $fileName = (microtime(true) * 10000) . '_' . $action . '_' . $method . '.json';
            $folderName = DIR_LOG . 'api-client/' . strtolower( $className ) . '/' . date( 'Ym' ) . '/' . date( 'd' );

            if(!is_dir($folderName)){
                @mkdir($folderName, 0777, true);
                @chmod($folderName, 0777);
            }

            @file_put_contents( $folderName . '/' . $fileName, $data, FILE_APPEND );
        }
    }
}
