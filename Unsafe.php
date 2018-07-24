<?php
namespace core;

/**
 * Cache get/post/server-vars
 * for messy systems we need to support.
 */
class Unsafe {
    private static $get = null;
    private static $post = null;
    private static $server = null;

    public static function init() {
        if (! isset($_SERVER)) {
            user_error("DevErr: Unsafe-class loaded without SERVER-var");
        }
        self::$get = $_GET;
        self::$post = $_POST;
        self::$server = $_SERVER;
    }

    public static function get() {
        return self::$get;
    }
    public static function post() {
        return self::$post;
    }
    public static function server() {
        return self::$server;
    }
}

