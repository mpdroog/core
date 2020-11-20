<?php
namespace core;

/** International System of Units */
class SI
{
	public static function prefix($size, $base = 'B')
	{
		$units = [ '', 'K', 'M', 'G', 'T', 'P', 'E', 'Z', 'Y'];
		$power = $size > 0 ? floor(log($size, 1024)) : 0;
		return number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power] . $base;
	}
}
