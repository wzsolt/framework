<?php

namespace Framework\Models\Memcache;

interface MemcacheInterface
{
    public function connect():void;

    public function disconnect():void;

    public function add(string $key, mixed $data, int $expire = 0):bool;

    public function set(string $key, mixed $data, int $expire = 0):bool;

    public function get(string $key):false|array|string;

    public function delete(string $key):bool;

    public function increment(string $key, mixed $value):int|false;
}