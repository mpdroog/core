<?php
namespace core;

use core\AES;
use core\Helper;
use prj\Shared;

/**
 * Session helpers.
 */
class Session
{
	/* Check valid UUID, return user.id */
	private static function check_uuid($uuid, $counter)
	{
		return Shared::db()->getCell(
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
	public static function session()
	{
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
	public static function req()
	{
		$sess = self::session();
		if (! $sess) {
			Res::error(401);
			echo "Failed decoding session.";
			exit;
		}
		return $sess;
	}

	/* Create sess-string for auto-login/cookie. */
	public static function create_key($uuid, $counter)
	{
		$privkey = Helper::config("security")["aeskey"];
		return AES::encode_url(json_encode([
			"uuid" => $uuid,
			"counter" => $counter
		]), $privkey);
	}

	/** Create cookie and begin session. */
	public static function begin($uuid, $counter)
	{
		$sess = json_encode([
			"uuid" => $uuid,
			"counter" => $counter,
			"rand" => Helper::rand(8) /* never have the same cookie data */
		]);

		$privkey = Helper::config("security")["aeskey"];
		$domain = Helper::config("general")["domain"];
		$ok = setcookie(
			"sessid",
			AES::encode($sess, $privkey),
			time()+60*60*24*60 /* 60 days */,
			"/action/",
			$domain,
			true,
			true
		);
		if (! $ok) {
			user_error("Could not set cookie?");
		}
	}

	/** Delete cookie and stop session. */
	public static function destroy()
	{
		$domain = Helper::config("general")["domain"];
		$ok = setcookie(
			"sessid",
			null,
			-1,
			"/action/",
			$domain,
			true,
			true
		);
		if (! $ok) {
			user_error("Could not set cookie?");
		}
	}
}
