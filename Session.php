<?php
namespace core;
use core\AES;
use core\Helper;
use core\Fn;

/**
 * Session helpers.
 */
class Session {
	/* Check valid UUID, return user.id */
	private static function check_uuid($uuid, $counter) {
		return Fn::db()->getCell(
			"SELECT
				id
			FROM
				user
			WHERE
				uuid = ?
			AND
				autologin_counter = ?",
			[$uuid, $counter]
		);
	}

	/* Read 'raw' session from cookie */
	public static function session() {
		if (! isset($_COOKIE["sessid"])) {
			return false;
		}
		$privkey = Helper::config("security")["aeskey"];

		$struct = json_decode(AES::decode($_COOKIE["sessid"], $privkey), true);
		if (! is_array($struct) || ! isset($struct["uuid"])) {
			return false;
		}
		$user_id = self::check_uuid($struct["uuid"], $struct["counter"]);
		if ($user_id === false) {
			return false;
		}
		$struct["user_id"] = $user_id;
		return $struct;
	}

	/* Require session and return session (on no session return 401 and stop) */
	public static function req_session() {
		$sess = self::session();
		if (! $sess) {
			self::client_error("Please login and try again.", [], "401");
			exit;
		}
		return $sess;
	}

	/* Create sess-string for auto-login/cookie. */
	public static function create_key($uuid, $counter) {
		$privkey = Helper::config("security")["aeskey"];
		return AES::encode_url(json_encode([
			"uuid" => $uuid,
			"counter" => $counter
		]), $privkey);
	}
}
