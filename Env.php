<?php
namespace core;

/**
 * Safe to read environment-variables.
 */
class Env {
    private static $server = null;

    public static function init() {
        if (! isset($_SERVER)) {
            user_error("DevErr: Env-class loaded without SERVER-var");
        }
        self::$server = $_SERVER;
    }

    // Visitor IP
    public static function ip() {
        return self::$server["HTTP_X_REAL_IP"] || self::$server["REMOTE_ADDR"];
    }
    // HTTP-protocol spoken to client.
    public static function protocol() {
         return self::$server["SERVER_PROTOCOL"];
    }
    public static function encoding() {
        $enc = "plain";
        if (isset($_SERVER["HTTP_ACCEPT"])) {
            if ($_SERVER["HTTP_ACCEPT"] === "application/json") {
                 $enc = "json";
            }
            if (strpos($_SERVER["HTTP_ACCEPT"], "text/html")) {
                 $enc = "html";
            }
        }
        return $enc;
    }
}

