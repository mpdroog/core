<?php
/**
 * Super lightweight code to abstract inboxify API.
 * (Inboxify is a cheap email sending service)
 * @see https://s3.amazonaws.com/helpscout.net/docs/assets/5980b2e7042863033a1b8aff/attachments/61c329f728e2785c351f50c6/Inboxify-REST-API-voor-contacten.pdf
 */
namespace core;
use core\Http;

/** Inboxify API abstraction */
class Inboxify {
    private static $config = null;

    public static function config(array $cfg)
    {
        self::$config = $cfg;
    }

/** Create headers for Signature based authentication */
private static function sig() {
    $salt = md5(microtime(true));
    return [
        "Accept: application/json",
        "apikey: " . self::$config["pubkey"],
        "Content-Type: application/json",
        "signature: " . rawurlencode(base64_encode(hash_hmac("sha256", $salt, self::$config["privkey"], true))),
        "salt: " . $salt,
    ];
}

public static function lists() {
    $res = self::api("GET", "/lists", null);
    // TODO: Http-statuscode check?
    return $res["body"];
}

public static function contacts($list, $filter_unsub=false, $offset=0, $limit=20, $sort="ASC") {
    $list = rawurlencode($list);
    $query = "/contacts/$list/?offset=$offset&limit=$limit&sort=$sort";
    if ($filter_unsub) $query .= "&unsubscribed=True";
    $res = self::api("GET", $query, null);

    if ($res["http"] !== 200) {
        var_dump($res);
        user_error("Inboxify/contacts invalid res");
    }
    return $res["body"];
}

/** Bulk insert N-contacts into list */
public static function bulk_insert($list, array $contacts, $overwrite) {
    $list = rawurlencode($list);
    if (count($contacts) > 1000) user_error("bulk_insert limited to 1000-contacts at a time");

    $args = "";
    if ($overwrite) {
        $args .= "overwrite=True";
    }
    $res = self::api("POST", "/contacts/$list/bulk-insert?$args", $contacts);
    if ($res["http"] !== 200) {
        var_dump($res);
        user_error("Inboxify/contacts invalid res");
    }
    return $res["body"];
}

/** Call HTTP-endpoint through cURL */
public static function api($method, $path, $data = null) {
    $opts = [
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => self::sig(),
    ];
    if ($data !== null) {
        $opts[CURLOPT_POSTFIELDS] = json_encode($data);
    }

    $res = HTTP::json(self::$config["endpoint"] . $path, $opts);
    if ($res["http"] === 401) user_error("inboxify: Invalid credentials given");
    return $res;
}

public static function feedback($list, $changeType) {
    if (! in_array($changeType, ["created", "updated", "unsubscribed", "deleted"])) user_error("No such changeType=$changeType");
    $list = rawurlencode($list);
    $res = self::api("GET", "/contacts/$list/$changeType");
    if ($res["http"] !== 200) {
        var_dump($res);
        user_error("Inboxify/feedback invalid res");
    }
    return $res["body"];
}

public static function delete_contact($list, $idOrEmail) {
    $list = rawurlencode($list);
    $idOrEmail = rawurlencode($idOrEmail);
    $res = self::api("DELETE", "/contacts/$list/$idOrEmail");
    if ($res["http"] === 404) {
        return false;
    }
    if ($res["http"] !== 204) {
        var_dump($res);
        user_error("Inboxify/delete invalid res");
    }
    return true;
}
}
