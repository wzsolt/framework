<?php
namespace Framework\Api\Services;

use Framework\Api\ApiException;
use Framework\Api\Request;
use Framework\Helpers\Utils;
use Framework\Models\Database\Db;

class AuthService
{
    private static AuthService $instance;

    private int $clientId = 0;

    private array $client = [];

    private int $userId = 0;

    private string $token = '';

    public static function create():AuthService
    {
        if (!isset(self::$instance)) {
            self::$instance = new AuthService();
        }

        return self::$instance;
    }

    public function getUserId():int
    {
        return $this->userId;
    }

    public function getToken():string
    {
        return $this->token;
    }

    public function checkToken(?string $token):bool
    {
        if (!empty($token)) {
            if (preg_match('/Bearer\s(\S+)/', $token, $matches)) {

                $token = Db::create()->getFirstRow(
                    Db::select(
                        'api_tokens',
                        [
                            'at_id AS id',
                            'at_us_id AS userId',
                            'at_token AS token',
                        ],
                        [
                            'at_token' => $matches[1],
                            'at_disabled' => 0,
                            'at_expire>' => date('Y-m-d H:i:s'),
                            'us_enabled' => 1,
                            'us_allow_api_login' => 1,
                            'us_deleted' => 0,
                        ],
                        [
                            'users' => [
                                'on' => [
                                    'us_id' => 'at_us_id'
                                ]
                            ]
                        ]
                    )
                );
                if($token){
                    $this->userId = (int)$token['userId'];

                    $this->token = $token['token'];

                    return true;
                }else{
                    throw new ApiException('Invalid token provided', 12, API_HTTP_UNAUTHORIZED);
                }
            }else{
                throw new ApiException('Invalid token format provided in the header. (Authorization: Bearer <token>)', 11, API_HTTP_BAD_REQUEST);
            }
        }

        throw new ApiException('Token is missing', 10, API_HTTP_BAD_REQUEST);
    }

    public function closeSession()
    {
    }

    public function checkApiKey(string $key):bool
    {
        if ($key){
            $client = Db::create()->getFirstRow(
                Db::select(
                    'api_users',
                    [
                        'au_id AS id',
                        'au_client_id AS clientId',
                        'au_last_request AS lastRequest',
                        'au_ip_whitelist AS ipWhiteList',
                        'au_services AS services',
                        'au_expiry AS expiry',
                    ],
                    [
                        'au_api_key' => $key,
                        'au_enabled' => 1
                    ]
                )
            );
            if (!empty($client)) {
                if (!empty($client['expiry']) && $client['expiry'] < date('Y-m-d')) {
                    throw new ApiException('API Key expired', 9, API_HTTP_UNAUTHORIZED);
                } else {
                    $authenticated = $this->validateClient($client);
                }
            } else {
                throw new ApiException('Invalid API Key provided', 8, API_HTTP_UNAUTHORIZED);
            }
        }else{
            throw new ApiException('API Key is missing', 7, API_HTTP_UNAUTHORIZED);
        }

        return $authenticated;
    }

    public function authenticate(string $username, string $password):bool
    {
        $client = Db::create()->getFirstRow(
            Db::select(
                'api_users',
                [
                    'au_id AS id',
                    'au_username AS username',
                    'au_password AS pwd',
                    'au_client_id AS clientId',
                    'au_last_request AS lastRequest',
                    'au_ip_whitelist AS ipWhiteList',
                    'au_services AS services',
                    'au_failedlogins AS failedLogins',
                    'au_enabled AS enabled',
                ],
                [
                    'au_username' => $username,
                    'au_enabled' => [
                        'in' => [1,2]
                    ]
                ]
            )
        );

        if (!empty($client) && password_verify($password, $client['pwd']) && $client['enabled'] == 1) {
            unset($client['pwd'], $client['enabled'], $client['failedLogins']);

            return $this->validateClient($client);

        }else{
            $code = 1;
            $message = 'Invalid login credentials';
            $data = [];

            // login failed
            if (!empty($client)) {
                // user exists and enabled, password was wrong
                $updateFields = [
                    'au_failedlogins' => ($client['failedLogins'] + 1)
                ];

                $data = [
                    'loginAttemptsLeft' => API_MAX_LOGIN_ATTEMPT - $updateFields['au_failedlogins']
                ];

                if ($updateFields['au_failedlogins'] >= API_MAX_LOGIN_ATTEMPT) {
                    $data = [];
                    $code = 5;
                    $updateFields['au_enabled'] = 2;
                    $message = 'Maximum login attempts exceeded, user is blocked.';
                }

                Db::create()->sqlQuery(
                    Db::update(
                        'api_users',
                        $updateFields,
                        [
                            'au_id' => $client['id']
                        ]
                    )
                );

            }

            throw new ApiException($message, $code, API_HTTP_UNAUTHORIZED, $data);
        }
    }

    public function getClient():array
    {
        return $this->client;
    }

    public function getClientId():int
    {
        return $this->clientId;
    }

    private function validateClientIP():bool
    {
        $valid = true;

        if($this->client['ipWhiteList'] && is_array($this->client['ipWhiteList'])) {
            $ip = Utils::getClientIP();

            $valid = $this->isAllowedIp($ip, $this->client['ipWhiteList']);
        }

        return $valid;
    }

    private function isAllowedIp(string $ip, array $whitelist):bool
    {
        if (in_array($ip, $whitelist, true)) {
            return true;
        }

        foreach ($whitelist as $whitelistedIp) {
            $whitelistedIp = (string)$whitelistedIp;

            $wildcardPosition = strpos($whitelistedIp, "*");
            if ($wildcardPosition === false) {
                continue;
            }

            if (substr($ip, 0, $wildcardPosition) . "*" === $whitelistedIp) {
                return true;
            }
        }

        return false;
    }

    private function validateService():bool
    {
        $valid = true;

        if($this->client['services'] && is_array($this->client['services'])) {
            $valid = in_array(Request::create()->getServiceName(), $this->client['services']);
        }

        return $valid;
    }

    private function validateClient(array $client):bool
    {
        $this->client = $client;

        if(!Empty($client['services'])) {
            $this->client['services'] = explode('|', trim($client['services'], '|'));
        }

        if(!Empty($client['ipWhiteList'])) {
            $this->client['ipWhiteList'] = explode('|', trim($client['ipWhiteList'], '|'));
        }

        if($this->validateClientIP()){

            if($this->validateService()) {

                $this->clientId = $client['clientId'];

                Db::create()->sqlQuery(
                    Db::update(
                        'api_users',
                        [
                            'au_failedlogins' => 0,
                            'au_last_request' => 'NOW()',
                        ],
                        [
                            'au_id' => $client['id']
                        ]
                    )
                );

                return true;
            }else{
                $this->client = [];

                throw new ApiException('Service is not allowed', 6, API_HTTP_UNAUTHORIZED);
            }
        }else{
            $this->client = [];

            throw new ApiException('IP address is not allowed', 4, API_HTTP_UNAUTHORIZED);
        }
    }
}