<?php
namespace core;
use core\Helper;
use core\Render;

/**
 * Response helpers.
 */
class Res {
	/**
	 * Show error to client.
	 * https://dev.twitter.com/overview/api/response-codes
	 */
	public static function error($msg, array $errors = [], $http="400") {
		if ($http === "400") {
			header('HTTP/1.1 400 Bad request');
		} elseif ($http === "500") {
			header('HTTP/1.1 500 Internal Server Error');
		} elseif ($http === "401") {
			header("HTTP/1.1 401 Unauthorized");
		} else if ($http === "403") {
			header("HTTP/1.1 403 Banned");
		} else {
			user_error(sprintf('TODO: HTTP-statusCode(%s) not implemented', $http));
		}
		$encoding = Helper::client()["encoding"];
		if ($encoding === "plain") {
			// Legacy sys
			header("Content-Type: text/plain");
			echo $msg;
		} else if ($encoding === "json") {
			// JSON (API)
			header("Content-Type: application/json");
			echo json_encode([
				"msg" => $msg,
				"errors" => $errors
			]);
		} else {
			// HTML (user friendly)
			echo Render::page("err", ["msg" => $msg, "errors" => $errors]);
		}
	}

	/** Show short success to client. */
	public static function ok($msg, array $meta = []) {
		if ($encoding === "json") {
			// JSON return
			echo json_encode([
				"msg" => $msg,
				"meta" => $meta
			]);
		} else {
			// Plain return
			echo $msg;
		}
	}

	/** HTTP Redirect */
	public static function redirect($relative) {
		$base = Helper::config("general")["baseurl"];
		header(sprintf("Location: %s/%s", $base, $relative));
	}
	/** HTTP redirect to external domain */
	public static function redirect_external($url, $allowHTTP=false) {
		$parts = parse_url($url);
		if ($parts === false) {
			user_error("Mailformed URL: $url");
		}
		if (!$allowHTTP && $parts["scheme"] !== "https") {
			user_error("Redirect without https: $url");
		}
		header(sprintf("Location: %s", $url));
	}
}
