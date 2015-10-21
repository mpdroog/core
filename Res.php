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
		global $_CLIENT; // TODO: Taint?
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
		if ($_CLIENT["encoding"] === "plain") {
			// Legacy sys
			header("Content-Type: text/plain");
			echo $msg;
		} else if ($_CLIENT["encoding"] === "json") {
			// JSON (API)
			header("Content-Type: application/json");
			echo json_encode([
				"msg" => $msg,
				"errors" => $errors
			]);
		} else {
			// HTML (user friendly)
			echo Render::render(ROOT . "tpl/err.tpl", ["msg" => $msg, "errors" => $errors]);
		}
	}

	/** Show short success to client. */
	public static function ok($msg, array $meta = []) {
		global $_CLIENT;
		if ($_CLIENT["encoding"] === "json") {
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
}
