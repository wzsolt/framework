<?php

namespace Framework\Components;

use Framework\Models\Database\Db;
use Framework\Models\Memcache\MemcachedHandler;

class SiteSettings
{
    private static SiteSettings $instance;

    private int $clientId;

    private array $settings = [];

    public static function create():SiteSettings
    {
        if (!isset(self::$instance)) {
            self::$instance = new SiteSettings();
        }

        return self::$instance;
    }

    public function load(int $clientId):SiteSettings
    {
        $this->clientId = $clientId;

        $this->settings = $this->loadSettings($this->clientId);

        return $this;
    }

    public function getClientId(): int
    {
        return $this->clientId;
    }

    public function getSettings():array
    {
        return $this->settings;
    }

    public function get(string $key): array|string|null
    {
        return ($this->settings[$key] ?? null);
    }

    private function loadSettings(int $clientId):array
    {
        $settings = MemcachedHandler::create()->get(CACHE_SETTINGS . $clientId);

        if(!$settings){
            $settings = DB::create()->getFirstRow(
                DB::select(
                    'settings',
                    [
                        'ws_settings'
                    ],
                    [
                        'ws_client_id' => $clientId
                    ]
                )
            );

            if ($settings) {
                $settings = json_decode($settings['ws_settings'], true);

                MemcachedHandler::create()->set(CACHE_SETTINGS . $clientId, $settings);
            }else{
                $settings = [];
            }
        }

        return $settings;
    }
}