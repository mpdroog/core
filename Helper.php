<?php
namespace core;
use Lifo\IP\CIDR;

/**
 * Global Helpers.
 */
class Helper {
	// Read conf.d/$name.json
	public static function config($name) {
		return json_decode(file_get_contents(ROOT . "conf.d/$name.json"), true);
	}

	// Random value having a length of $len.
	public static function rand($len) {
		return \bin2hex(\mcrypt_create_iv($len+1, MCRYPT_DEV_URANDOM));
	}

	/** Check if $ip is in $cidrs (ranges) */
	public static function in_range($ip, array $cidrs) {
		foreach ($cidrs as $cidr) {
			if (CIDR::INTERSECT_YES === CIDR::cidr_intersect($ip, $cidr)) {
				return true;
			}
		}
		return false;
	}

	public static function client() {
		global $_CLIENT;
		return $_CLIENT;
	}

	public static function client_new($replacement) {
		global $_CLIENT;
		$_CLIENT = $replacement;
	}

	public static function prefix($txt) {
		return sprintf(
			"%s-%s",
			self::config("general")["syskey"]
		);
	}
}
