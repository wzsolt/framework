<?php
namespace Framework\Api\Services;

use Framework\Api\ApiException;
use Framework\Api\AbstractRequester;
use Framework\Helpers\Utils;

class TestService extends AbstractRequester
{

    public function init(): void
    {
        // TODO: Implement init() method.
    }

    public function get_Ping(?array $id = null):array
    {
        return [
            'ip' => Utils::getClientIP(),
            'timestamp' => date(DATE_ATOM),
        ];
    }

    public function get_Exception(?array $id = null):void
    {
        throw new ApiException('API exception', 400, API_HTTP_BAD_REQUEST);
    }

    public function get_Wait(?array $id = null):array
    {
        $time = (int) $_REQUEST['time'];

        if(!$time) {
            $time = 10;
        }

        sleep($time);

        return [
            'wait' => $time
        ];
    }

    public function post_Wait(?array $id = null):array
    {
        $time = (int) $_REQUEST['time'];

        if(!$time) {
            $time = 10;
        }

        sleep($time);

        return [
            'wait' => $time
        ];
    }
}