<?php
namespace Framework\Api;

use Exception;

class ApiException extends \Exception
{
	private int $httpCode = API_HTTP_OK;

	private array $data = [];

	public function __construct(string $message, $code, $httpCode = API_HTTP_OK, $data = [], Exception $previous = null)
    {
		$this->httpCode = $httpCode;

		$this->data = $data;

		parent::__construct($message, $code, $previous);
	}

	public function getHttpCode():int
    {
		return $this->httpCode;
	}

    public function getData():array
    {
        return $this->data;
    }

	public function sendHttpResponseCode(): void
    {
		http_response_code($this->httpCode);
	}
}