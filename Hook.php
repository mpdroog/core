<?php
namespace core;

class Hook
{
	private static $hooks = [];

	public static function add($name)
	{
		if (isset(self::$hooks[$name])) {
			die("DevErr: hook[$name] already added");
		}
		self::$hooks[$name] = [];
	}

	public static function listen($name, $file)
	{
		if (isset(self::$hooks[$name][$file])) {
			die("DevErr: hook[$name][$file] already added");
		}
		self::$hooks[$name][$file] = true;
	}

	public static function trigger($name, array $args)
	{
		foreach (self::$hooks[$name] as $fn => $meta) {
			require $fn;
		}
	}
}
