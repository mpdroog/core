<?php
namespace core;

class HIBP
{
	public static function safe_pass($pass)
	{
		$hash = strtoupper(sha1($pass));
		$hashprefix = substr($hash, 0, 5);

		$ch = curl_init("https://api.pwnedpasswords.com/range/" . $hashprefix);
		if ($ch === false) {
			user_error("hibp curl_init fail");
		}
		$ok = 1;
		$ok &= curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		if (! $ok) {
			user_error("hibp curl_setopt fail");
		}
		$res = curl_exec($ch);
		if ($res === false) {
			user_error("hibp curl_exec e=" . curl_error($ch));
		}
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		if ($http_code !== 200) {
			user_error(sprintf("hibp(%s) http=%d err=%s\n", $pass, $http_code, $res));
		}

		foreach (explode("\r\n", $res) as $line) {
			$m = substr($hashprefix . $line, 0, 40);
			if ($m === $hash) {
				// Password was breached in past
				return false;
			}
		}
		return true;
	}
}
