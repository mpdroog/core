<?php
namespace core;

/**
 * Parse content-type
 */
class Convert
{
	// Parse CSV into associated array
	public static function csv($str, $loose = false)
	{
		$fp = fopen("php://temp", 'r+');
		if (! is_resource($fp)) {
			user_error("fopen(temp) failed");
		}
		if (false === fputs($fp, $str)) {
			user_error("fputs(temp) failed");
		}
		if (! rewind($fp)) {
			user_error("rewind(temp) failed");
		}

		$kv = fgetcsv($fp);
		if ($kv === null || $kv === false) {
			user_error("fgetcsv(temp) failed");
		}
		$kv = fgetcsv($fp);
		if (substr($kv[0], 0, strlen("sep=")) === "sep=") {
			$kv = fgetcsv($fp); // ignore sep= line
		}

		$lines = [];
		while (($data = fgetcsv($fp)) !== false) {
			$out = [];
			foreach ($kv as $k => $v) {
				if (isset($out[ $v ])) {
					user_error(sprintf("key=%s already set", $v));
				}
				if ($loose && !isset($data[$k])) {
					$out[ $v ] = "";
					continue;
				}
				if (! isset($data[$k])) {
					user_error(sprintf("key=%s missing", $k));
				}
				$out[ $v ] = trim($data[$k]);
			}
			$lines[] = $out;
		}
		return $lines;
	}
}
