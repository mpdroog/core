<?php
namespace core;

use core\Env;
use core\Helper;
use core\Render;

/**
 * Response helpers.
 */
class Res
{
	/**
		 * Proplery set HTTP-header code.
	 * https://dev.twitter.com/overview/api/response-codes
	 */
	public static function error($http = 400)
	{
		$v = Env::protocol();
		if ($http === 400) {
			header("$v 400 Bad request");
		} elseif ($http === 500) {
			header("$v 500 Internal Server Error");
		} elseif ($http === 401) {
			header("$v 401 Unauthorized");
		} elseif ($http === 403) {
			header("$v 403 Banned");
		} elseif ($http === 404) {
			header("$v 404 Page Not Found");
		} elseif ($http === 503) {
			header("$v 503 Too Many Requests");
		} else {
			user_error(sprintf('TODO: HTTP-statusCode(%d) not implemented (or not numeric)', $http));
		}
	}
	public static function json($msg)
	{
		header("Content-Type: application/json");
		echo json_encode($msg);
	}

	/** HTTP Redirect */
	public static function redirect($relative)
	{
		$base = Helper::config("general")["baseurl"];
		header(sprintf("Location: %s/%s", $base, $relative), true, 303);
	}
	/** HTTP redirect to external domain */
	public static function redirect_external($url, $allowHTTP=false)
	{
		$parts = parse_url($url);
		if ($parts === false || !isset($parts["scheme"])) {
			user_error("Mailformed URL: $url");
		}
		if (!$allowHTTP && $parts["scheme"] !== "https") {
			user_error("Redirect without https: $url");
		}
		header(sprintf("Location: %s", $url), true, 303);
	}
}
