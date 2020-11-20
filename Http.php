<?php
namespace core;

use core\Strings;

class HTTP
{
	public static function json($url)
	{
		$ch = curl_init($url);
		$ok = 1;
		$ok &= curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
		$ok &= curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		$ok &= curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		$ok &= curl_setopt($ch, CURLOPT_TIMEOUT, 120);
		$ok &= curl_setopt($ch, CURLOPT_HTTPHEADER, [
		  "Accept: application/json"
	  ]);
		$ok &= curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		if ($ok !== 1) {
			user_error("curl_setopt failed");
		}

		$res = curl_exec($ch);
		if ($res === false) {
			var_dump($res);
			user_error(curl_error($ch));
		}
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$ct = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
		curl_close($ch);

		if ($res === false) {
			user_error('curl_exec fail');
		}

		$out = ["http" => $code, "ct" => $ct, "body" => $res];
		if (Strings::has_prefix($ct, "application/json")) {
			$out["body"] = json_decode($res, true);
			if (! is_array($out["body"])) {
				print_r($res);
				user_error("http_json::failed decoding raw=%s", $res);
			}
		}
		return $out;
	}
}
