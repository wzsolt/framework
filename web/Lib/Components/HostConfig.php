<?php

namespace Framework\Components;

use Framework\Helpers\Str;
use Framework\Models\Database\Db;
use Framework\Models\Memcache\MemcachedHandler;

class HostConfig {
    private static HostConfig $instance;

    private int $id;

    private int $clientId = 0;

    private string $machineId;

    private string $host = DEFAULT_HOST;

    private string $domain = '';

    private string $name = '';

    private array $languages = [];

    private string $language = DEFAULT_LANGUAGE;

    private string $defaultLanguage = DEFAULT_LANGUAGE;

    private string $country;

    private string $application = DEFAULT_APPLICATION;

    private string $theme = DEFAULT_THEME;

    private string $currency = DEFAULT_CURRENCY;

    private array $currencies = [];

    private string $currencySign = '';

    private int $timeZoneCode = DEFAULT_TIMEZONE_ID;

    private string $timeZone = '';

    private bool $isProduction = false;

    private bool $isMaintenance = false;

    private bool $requireAuthentication = false;

    private bool $isShareSession = false;

    private string $publicSite = '';

    private array $authConfig = [];

    private array $smtpConfig = [];

    private array $smsConfig = [];

    private array $menu = [];

    public static function create():HostConfig
    {
        if (!isset(self::$instance)) {
            self::$instance = new HostConfig();
        }

        return self::$instance;
    }

    public function load(string $host):HostConfig
    {
        $host = strtolower(trim($host));
        if(Empty($host)) $host = DEFAULT_HOST;

        $this->host = $host;

        $protocol = 'http://';
        if(!Empty($_SERVER['HTTPS']) AND $_SERVER['HTTPS'] !== 'off' OR $_SERVER['SERVER_PORT'] == 443) {
            $protocol = 'https://';
        }

        $this->domain = $protocol . $this->host . '/';

        $this->setMachineId();

        $config = $this->loadConfig($this->host);
        if(!Empty($config)) {

            $this->id = $config['id'];

            $this->clientId = $config['clientId'];

            $this->name = $config['name'];

            $this->publicSite = $config['publicSite'];

            $this->currency = $config['defaultCurrency'];

            $this->currencies = $config['currencies'];

            $this->language = $config['defaultLanguage'];

            $this->defaultLanguage = $config['defaultLanguage'];

            $this->languages = $config['languages'];

            $this->country = $config['country'];

            $this->application = $config['application'];

            $this->theme = $config['theme'];

            $this->currencySign = $GLOBALS['CURRENCIES'][$this->currency]['sign'];

            $this->smtpConfig = $config['smtpConfig'];

            $this->smsConfig = $config['smsConfig'];

            $this->authConfig = $config['authConfig'];

            $this->isProduction = $config['isProduction'];

            $this->isMaintenance = $config['isMaintenance'];

            $this->isShareSession = $config['isShareSession'];

            $this->requireAuthentication = !empty($config['host_protect']);

            $this->setTimeZoneCode($config['timeZoneID']);
        }

        return $this;
    }

    private function loadConfig(string $host):array
    {
        $config = MemcachedHandler::create()->get(HOST_SETTINGS . $host);

        if(Empty($config)){
            $config = [];

            $h = DB::create()->getFirstRow(
                DB::select(
                    'hosts',
                    [],
                    [
                        'host_host' => $host
                    ]
                )
            );

            if($h){
                $config = [
                    'id' => (int) $h['host_id'],
                    'clientId' => (int) $h['host_client_id'],
                    'host' => $h['host_host'],
                    'name' => $h['host_name'],
                    'isMaintenance' => ($h['host_maintenance']),
                    'forceSSL' => ($h['host_force_ssl']),
                    'defaultLanguage' => $h['host_default_language'],
                    'languages' => explode('|', trim($h['host_languages'], '|')),
                    'application' => $h['host_application'],
                    'theme' => $h['host_theme'],
                    'defaultCurrency' => $h['host_default_currency'],
                    'currencies' => explode('|', trim($h['host_currencies'], '|')),
                    'timeZoneID' => $h['host_timezone'],
                    'country' => $h['host_country'],
                    'publicSite' => ($h['host_public_site'] ? rtrim($h['host_public_site'], '/') . '/' : ''),
                    'isProduction' => ($h['host_production']),
                    'isShareSession' => ($h['host_share_session']),
                    'smtpConfig' => [],
                    'smsConfig' => [],
                    'authConfig' => [],
                ];

                if(!Empty($h['host_smtp_host'])){
                    $pwd = unserialize($h['host_smtp_pwd']);
                    if($pwd) {
                        $config['smtpConfig'] = [
                            'host'         => $h['host_smtp_host'],
                            'port'         => $h['host_smtp_port'],
                            'ssl'          => $h['host_smtp_ssl'],
                            'user'         => $h['host_smtp_user'],
                            'password'     => Str::deCryptString(SECURE_SALT_KEY, $pwd),
                            'defaultEmail' => $h['host_default_email'],
                        ];
                    }
                }

                if(!Empty($h['host_sms_endpoint'])){
                    $pwd = unserialize($h['host_sms_pwd']);
                    if($pwd) {
                        $config['smsConfig'] = [
                            'endpoint' => $h['host_sms_endpoint'],
                            'user'     => $h['host_sms_user'],
                            'password' => Str::deCryptString(SECURE_SALT_KEY, $pwd),
                            'isTest'   => ($h['host_sms_testmode']),
                        ];
                    }
                }

                if(!Empty($h['host_protect'])){
                    $pwd = unserialize($h['host_auth_password']);
                    if($pwd) {
                        $config['authConfig'] = [
                            'user'  => $h['host_auth_user'],
                            'password'  => Str::deCryptString(SECURE_SALT_KEY, $pwd),
                            'realm'  => $h['host_auth_realm'],
                            'errorMessage'  => $h['host_auth_error'],
                        ];
                    }
                }

                MemcachedHandler::create()->set(HOST_SETTINGS . $host, $config);
            }
        }

        return $config;
    }

    private function setMachineId(): void
    {
        if (empty($_COOKIE[COOKIE_MACHINE_ID])) {
            $this->machineId = uniqid('', true);
            $expires = 0x7fffffff;
            $folder = '/';
            setcookie(COOKIE_MACHINE_ID, $this->machineId, $expires, $folder);
        } else {
            $this->machineId = $_COOKIE[COOKIE_MACHINE_ID];
        }
    }

    public function getMachineId():string
    {
        return $this->machineId;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getClientId(): int
    {
        return $this->clientId;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLanguages(): array
    {
        return $this->languages;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function setLanguage(string $language = ''): self
    {
        $this->language = ($language && in_array($language, $this->languages) ? $language : $this->defaultLanguage);

        return $this;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function getApplication(): string
    {
        return ucfirst(strtolower($this->application));
    }

    public function getTheme(): string
    {
        return $this->theme;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getCurrencies(): array
    {
        return $this->currencies;
    }

    public function getCurrencySign(): string
    {
        return $this->currencySign;
    }

    public function getTimeZoneCode(): int
    {
        $userTimeZone = User::create()->getTimezone();

        return (!Empty($userTimeZone) ? $userTimeZone : $this->timeZoneCode);
    }

    public function getTimeZone(): string
    {
        return $this->getTimezoneById($this->getTimeZoneCode());
    }

    public function setTimeZoneCode(int $timezone): self
    {
        $this->timeZoneCode = $timezone;

        $this->timeZone = $this->getTimezoneById($timezone);

        date_default_timezone_set($this->timeZone);

        return $this;
    }

    public function isProduction(): bool
    {
        return $this->isProduction;
    }

    public function isMaintenance(): bool
    {
        return $this->isMaintenance;
    }

    public function isRequireAuthentication(): bool
    {
        return $this->requireAuthentication;
    }

    public function isShareSession(): bool
    {
        return $this->isShareSession;
    }

    public function getPublicSite(): string
    {
        return $this->publicSite;
    }

    public function getAuthConfig(string|false $key = false): array|string
    {
        return ($key && isset($this->authConfig[$key]) ? $this->authConfig[$key] : $this->authConfig);
    }

    public function getSmtpConfig(string|false $key = false): array|string
    {
        return ($key && isset($this->smtpConfig[$key]) ? $this->smtpConfig[$key] : $this->smtpConfig);
    }

    public function getSmsConfig(string|false $key = false): array|string
    {
        return ($key && isset($this->smsConfig[$key]) ? $this->smsConfig[$key] : $this->smsConfig);
    }

    public function getMenu(): array
    {
        return $this->menu;
    }

    public function setMenu(array $menu): self
    {
        $this->menu = $menu;

        return $this;
    }



    private function getTimezoneById(int $id):string
    {
        $timezone = Db::create()->getFirstRow(
            Db::select(
                'timezones',
                [
                    'tz_code AS code'
                ],
                [
                    'tz_id' => $id
                ]
            )
        );

        return ($timezone ? $timezone['code'] : '');
    }
}