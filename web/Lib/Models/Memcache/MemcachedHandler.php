<?php

namespace Framework\Models\Memcache;

class MemcachedHandler
{
    private static $instance;

    public static function create():MemcacheInterface
    {
        if (!isset(self::$instance)) {
            if (class_exists('Memcache')) {
                self::$instance = new FwMemcache(MEMCACHE_HOST, MEMCACHE_PORT, MEMCACHE_COMPRESS);
            } else if (class_exists('Memcached')) {
                self::$instance = new FwMemcached(MEMCACHE_HOST, MEMCACHE_PORT);
            }

            self::$instance->connect();
        }

        return self::$instance;
    }

}