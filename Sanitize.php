<?php
namespace core;

trait SanitizeStrippers
{
	public static function number()
	{
		return "/[^0-9]/";
	}
	public static function numberchars()
	{
		return "/[^A-Za-z0-9]/";
	}
	public static function slug()
	{
		return "/[^A-Za-z0-9_\-]/";
	}
	public static function text()
	{
		return "/[^[:alnum:][:space:]:\.\\\\\/_\-\(\)+]/u";
	}
	public static function chars()
	{
		return "/[^A-Za-z]/";
	}
	public static function xss($txt)
	{
		return htmlspecialchars(strip_tags($txt));
	}
	/**
	 * Replace non-ASCII with ASCII version else strip.
	 */
	public static function translit($txt)
	{
		return iconv('utf-8', 'us-ascii//TRANSLIT', $txt);
	}
}

class Sanitize
{
	use SanitizeStrippers;

	/**
	 * Strip off non-matching characters
	 */
	public static function strip($txt, array $rules)
	{
		// Validation on test-env
		// TODO: Skip on live?
		if (count($rules) === 0) {
			user_error("DevErr: No rules given to Sanitize");
		}
		if (is_array($txt) || is_object($txt)) {
			user_error("DevErr: txt invalid");
		}
		$sane = $txt;
		foreach ($rules as $rule) {
			$sane = preg_replace(self::$rule(), '', $sane);
		}
		return $sane;
	}

	/**
	 * Convert given string into generic key
	 */
	public static function key($txt)
	{
		return strtoupper(self::strip($txt, ["chars"]));
	}

	/**
	 * Remove/Replace risky XSS-characters from the string
	 */
	public static function xss($txt)
	{
		return htmlspecialchars(strip_tags($txt));
	}
}
