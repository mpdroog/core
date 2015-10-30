<?php
namespace core;

class Strings {
	/** HasPrefix tests whether the string s begins with prefix. */
	public static function has_prefix($s, $prefix) {
		return mb_substr($s, 0, mb_strlen($prefix)) === $prefix;
	}

	/** HasSuffix tests whether the string s ends with suffix. */
	public static function has_suffix($s, $suffix) {
		return mb_substr($s, -1 * mb_strlen($suffix)) === $suffix;
	}
}