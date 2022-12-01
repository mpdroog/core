<?php
namespace core;

use core\Strings;

class HTTP
{
	public static function json($url, array $opts)
	{
		$headers = [];
		$ch = curl_init($url);
		$ok = 1;

		$is_nodecode = isset($opts["NO_DECODE"]);
                unset($opts["NO_DECODE"]);

                // Strict defaults
                $ok &= curl_setopt_array($ch, [
                    CURLOPT_ENCODING => 'UTF-8',
                    CURLOPT_FOLLOWLOCATION => 1,
                    CURLOPT_AUTOREFERER => 1,
                    CURLOPT_TIMEOUT => 120,
                    CURLOPT_CONNECTTIMEOUT => 10,
                    CURLOPT_HTTPHEADER => ["Accept: application/json"],
                    CURLOPT_RETURNTRANSFER => 1,
                    CURLOPT_SSL_VERIFYHOST => 2,
                    CURLOPT_SSL_VERIFYPEER => 1,
                    CURLOPT_CAINFO => ROOT . "/lib/cacert.pem", // Testing it from the xsnews-PHP view
                    CURLOPT_SSLVERSION => 6,
                ]);
		$ok &= curl_setopt($ch, CURLOPT_HEADERFUNCTION, function($curl, $header) use (&$headers) {
			$len = strlen($header);
			$header = explode(':', $header, 2);
			if (count($header) < 2) return $len; // ignore invalid
			$headers[strtolower(trim($header[0]))][] = trim($header[1]);
			return $len;
		});
		if ($ok !== 1) {
			user_error("curl_setopt failed");
		}
		foreach ($opts as $opt => $val) {
			if (true !== curl_setopt($ch, $opt, $val)) user_error("curl_setopt $opt failed");
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

		$out = ["http" => $code, "ct" => $ct, "head" => $headers, "body" => $res];
		if (!$is_nodecode && Strings::has_prefix($ct, "application/json")) {
			$out["body"] = json_decode($res, true);
			if (! is_array($out["body"])) {
				user_error(sprintf("http_json::failed decoding raw=%s", print_r($res, true)));
			}
		}
		return $out;
	}
}
