<?php
namespace core;

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
}
