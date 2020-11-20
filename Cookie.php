<?php
namespace core;

class Cookie
{
	public static function create($name, $value, $expire="+1 year")
	{
		$stamp = strtotime($expire);
		if ($stamp === false) {
			user_error("Cookie::create invalid expire=$expire");
		}
		if (setcookie(
			$name,
			base64_encode($value),
			$stamp,
			"/",
			"",
			true,
			true
		) === false) {
			user_error("Cookie::create failed setcookie-func");
		}
	}
	public static function get($name)
	{
		if (! isset($_COOKIE[$name])) {
			return false;
		}
		return base64_decode($_COOKIE[$name]);
	}
	public static function clear($name)
	{
		setcookie($name, null, -1, "/", "", true, true);
	}
}
