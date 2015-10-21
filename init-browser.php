<?php
/**
 * Initialize the basics.
 */

# Error handling
error_reporting(E_STRICT);
function report($errno, $errstr, $errfile, $errline) {
  header('HTTP/1.1 500 Internal Server Error');
  // TODO: Report error to devsys

  $msg = "($errfile:$errline) $errno: $errstr";
  error_log($msg);
  exit("Error written to error log.\n");
}
function report_ex($e) {
	report($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine());
}
function report_fatal() {
	$error = error_get_last();
	if( $error !== NULL) {
		report(
			E_CORE_ERROR, $error["message"],
			$error["file"], $error["line"]
		);
	}
}
set_error_handler("report");
set_exception_handler("report_ex");
register_shutdown_function("report_fatal");

# Encoding
header('Content-Type: text/html; charset=UTF-8');
mb_internal_encoding("UTF-8");

# Security
$test = getenv("TESTING") !== false;
if (!$test && !isset($_SERVER["HTTP_X_REAL_IP"])) {
	user_error("Nginx misconfigured, missing HTTP_X_REAL_IP");
} else {
	// Unittest fake the IP.
	$_SERVER["HTTP_X_REAL_IP"] = "127.0.0.1";
}

# Paranoia (try to expose as less as possible)
$uniq = "";
foreach (["HTTP_ACCEPT_LANGUAGE", "HTTP_USER_AGENT", "HTTP_ACCEPT"] as $key) {
	if (isset($_SERVER[$key])) {
		$uniq .= $_SERVER[$key];
	}
}

$_CLIENT = [
	"test" => $test,
	"today" => date("Y-m-d"),
	"ip" => $_SERVER["HTTP_X_REAL_IP"],
	"uniq" => sha1($uniq),
	"encoding" => isset($_SERVER["HTTP_ACCEPT"]) && $_SERVER["HTTP_ACCEPT"] === "application/json" ? "json" : "html"
];
# Remove SERVER to force clean code
unset($_SERVER);
if ($_CLIENT["test"]) {
	// Set date to dummy for testing
	$_CLIENT["today"] = "2015-09-01";
}

# Require path
define("ROOT", realpath(dirname(__FILE__) . "/../../../") . "/");
require BASE . 'vendor/autoload.php';
# Taint (removing GET/POST/REQUEST against unsafe reads)
core\Taint::init();
# Prevent website usage if the user has been abusive.
core\Abuse::req($_CLIENT["ip"]);
