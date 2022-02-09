<?php
namespace core;

class Encoding
{
	public static function decodeBase58($input) {
		$alphabet = "123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz";

		$out = array_fill(0, 25, 0);
		for ($i=0;$i<strlen($input);$i++) {
			if (($p=strpos($alphabet, $input[$i]))===false) {
				return false;
			}
			$c = $p;
			for ($j = 25; $j--; ) {
				$c += (int)(58 * $out[$j]);
				$out[$j] = (int)($c % 256);
				$c /= 256;
				$c = (int)$c;
			}
			if ($c != 0) {
				//throw new \Exception("address too long");
				return false;
			}
		}

		$result = "";
		foreach ($out as $val) {
			$result .= chr($val);
		}
		return $result;
	}
}
