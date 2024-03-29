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
		if (! isset(self::$hooks[$name])) {
			error_log(sprintf("Hook(%s) dropped, no listeners", $name));
			return;
		}

		$args["_hook"] = $name;
		foreach (self::$hooks[$name] as $fn => $meta) {
			$tok = require $fn;
			if ($tok === "STOP") {
				break;
			}
		}
	}
}
