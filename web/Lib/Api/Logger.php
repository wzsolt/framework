<?php
namespace Framework\Api;

use Framework\Helpers\Utils;

class Logger
{
    public static function saveRequest():void
    {
        if(API_LOG_REQUESTS) {
            $url = self::getUrl();

            $header = Request::create()->getHeader();

            $data  = $_SERVER['SERVER_PROTOCOL'] . "\n";
            $data .= $_SERVER['REQUEST_METHOD'] . ' ' . $url['url'] . "\n\n";

            foreach ($header as $key => $value) {
                $data .= $key . ': ' . $value . "\n";
            }

            $data .= "Client IP: " . Utils::getClientIP() . "\n";
            $data .= "\n";

            if (!Empty($_SERVER['QUERY_STRING'])) {
                $data .= "Query string:\n";
                $data .= "--------------------------------------------------------------------------------\n";
                parse_str($_SERVER['QUERY_STRING'], $query);
                foreach ($query as $key => $value) {
                    if (is_array($value)) {
                        $data .= str_replace('Array', $key . '=', print_r($value, true));
                    } else {
                        $data .= $key . '=' . $value . "\n";
                    }
                }
                $data .= "--------------------------------------------------------------------------------\n";
            }

            $body = file_get_contents('php://input');

            if (!Empty($body)) {
                $data .= "Request body:\n";
                $data .= "--------------------------------------------------------------------------------\n";
                $data .= $body;
                $data .= "\n--------------------------------------------------------------------------------\n";
            }

            self::saveLog($data, API_REQUEST);
        }
    }

    public static function saveResponse(string $data, string $fileFormat):void
    {
        if(API_LOG_RESPONSES) {
            self::saveLog($data, API_RESPONSE, $fileFormat);
        }
    }

    private static function saveLog(string $data, string $method, string $fileFormat = 'txt'):string|false
    {
        if ( defined('DIR_LOG') ) {
            $request = Request::create();

            $service = str_replace('_', '', $request->getServiceName());
            $action = str_replace('_', '', $request->getActionName());

            $fileName    = (microtime(true) * 10000) . '_' . $service . '_' . $action . '_' . $request->getMessageId() . '_'. SERVER_ID . '_' . $method;
            $fileName	 .= '.' . $fileFormat;
            $folderName  = DIR_LOG . 'api/' . date( 'Ym' ) . '/' . date( 'd' ) . '/';

            if(!is_dir($folderName)){
                @mkdir($folderName, 0777, true);
                @chmod($folderName, 0777);
            }

            @file_put_contents( $folderName . '/' . $fileName, $data, FILE_APPEND );

            return $folderName . '/' . $fileName;
        }

        return false;
    }

    private static function getUrl():array
    {
        $url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $path = explode('/', trim(parse_url ( $url, PHP_URL_PATH ), '/'));
        array_shift($path);

        return [
            'url' => $url,
            'path' => $path,
        ];
    }
}