<?php
namespace Framework\Models\Session;

class Session
{
    public static function start():void
    {
        session_start();
    }

    public static function getId():string
    {
        return session_id();
    }

    public static function set(string $key, mixed $value):void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key):mixed
    {
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        } else {
            return false;
        }
    }

    public static function delete(string $key):void
    {
        unset($_SESSION[$key]);
    }

    public static function destroy():void
    {
        unset($_SESSION);

        session_destroy();
    }
}