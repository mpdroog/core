<?php
namespace core;

use Lifo\IP\CIDR;

/**
 * Global Helpers.
 */
class Helper
{
	// Read conf.d/$name(-test).json
	// We use the -test version for keeping our
	//  tests static and simple.
	public static function config($name)
	{
		$files = [];
		if (self::client()["test"]) {
			$files[] = ROOT . "/conf.d/$name-test.json";
		}
		$files[] = ROOT . "/conf.d/$name.json";

		foreach ($files as $file) {
			if (file_exists($file)) {
				$res = json_decode(file_get_contents($file), true);
				if (! is_array($res)) {
					user_error(sprintf("Helper::config(%s): File could not be decoded to array.", $file));
				}
				return $res;
			}
		}
		user_error(sprintf("Helper::config(%s): File does not exist.", $name));
	}

	// Random value having a length of $len.
	public static function rand($len)
	{
		$token = "";
		$codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
		$codeAlphabet.= "0123456789";
		$max = strlen($codeAlphabet); // edited

		for ($i=0; $i < $len; $i++) {
			$token .= $codeAlphabet[random_int(0, $max-1)];
		}
		return $token;
	}

	// Generate uniqueid to generate up to million of unique ids (userstr could be: clientip, userid, nodename)
	public static function unique($userstr)
	{
		return base_convert(md5(uniqid("", true) . time() . "1" . $userstr), 16, 36);
	}

	/** Check if $ip is in $cidrs (ranges) */
	public static function in_range($ip, array $cidrs)
	{
		foreach ($cidrs as $cidr) {
			if (CIDR::INTERSECT_YES === CIDR::cidr_intersect($ip, $cidr)) {
				return true;
			}
		}
		return false;
	}

	public static function client()
	{
		global $_CLIENT;
		return $_CLIENT;
	}

	public static function client_new(array $replacement)
	{
		global $_CLIENT;
		$_CLIENT = $replacement;
	}

	public static function prefix($txt)
	{
		return sprintf(
			"%s-%s",
			self::config("general")["syskey"],
			$txt
		);
	}
}
