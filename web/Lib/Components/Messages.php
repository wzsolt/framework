<?php

namespace Framework\Components;

use Framework\Components\Enums\MessageType;
use Framework\Locale\Translate;
use Framework\Models\Session\Session;

class Messages
{
    private static Messages $instance;

    private array $items = [];

    private function __construct()
    {
        if($messages = Session::get(SESSION_MESSAGES)) {
            $this->items = $messages;
        }
    }

    public function __destruct()
    {
        Session::set(SESSION_MESSAGES, $this->items);
    }

    public static function create():Messages
    {
        if (!isset(self::$instance)) {
            self::$instance = new Messages();
        }

        return self::$instance;
    }

    public function add(MessageType $type, string $title, string $label = ''):void
    {
        $args = func_get_args();
        unset($args[0], $args[1]);

        $this->items[] = [
            'type' 		=> strtolower($type->name),
            'title' 	=> Translate::get($title),
            'message' 	=> ($args ? call_user_func_array([Translate::create(), 'get'], $args) : '')
        ];
    }

    public function get(bool $asJson = false):array|string
    {
        $result = $this->items;

        $this->clear();

        return ($asJson ? json_encode($result) : $result);
    }

    public function clear():void
    {
        $this->items = [];
    }
}