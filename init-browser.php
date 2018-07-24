<?php
header('Content-Type: text/html; charset=UTF-8');
require dirname(__FILE__) . "/init.php";

function report($errno, $errstr, $errfile, $errline) {
  header('HTTP/1.1 500 Internal Server Error');
  // TODO: Report error to devsys

  $msg = "($errfile:$errline) $errno: $errstr";
  error_log($msg);
  exit("Error written to error log.\n");
}

# Security
$test = getenv("TESTING") !== false;
if ($test) {
	// Unittest fake the IP.
	$_SERVER["HTTP_X_REAL_IP"] = "127.0.0.1";
} else if (!isset($_SERVER["HTTP_X_REAL_IP"])) {
	user_error("Nginx misconfigured, missing HTTP_X_REAL_IP");
}

# Paranoia (try to expose as less as possible)
$uniq = "";
foreach (["HTTP_ACCEPT_LANGUAGE", "HTTP_USER_AGENT", "HTTP_ACCEPT"] as $key) {
	if (isset($_SERVER[$key])) {
		$uniq .= $_SERVER[$key];
	}
}

# Simple referer check
# You still need to ensure the HOST is not blindly forwarded by Nginx
$refok = false;
{
	if (isset($_SERVER["HTTP_REFERER"])) {
		$domain = str_replace("https://", "", str_replace("http://", "", $_SERVER["HTTP_REFERER"]));
		$domain = substr($domain, 0, strpos($domain, "/"));
		$refok = $domain === $_SERVER["HTTP_HOST"];
	}
}

$_CLIENT = [
	"test" => $test,
	"today" => date("Y-m-d"),
	"ip" => $_SERVER["HTTP_X_REAL_IP"],
	"uniq" => sha1($uniq),
	"referer_ok" => $refok,
	"encoding" => isset($_SERVER["HTTP_ACCEPT"]) && $_SERVER["HTTP_ACCEPT"] === "application/json" ? "json" : "html",
        "http_method" => $_SERVER['REQUEST_METHOD']
];
# Remove SERVER to force clean code
if (! isset($no_strict)) {
    unset($_SERVER);
}
if ($_CLIENT["test"]) {
	// Set date to dummy for testing
	$_CLIENT["today"] = "2015-09-01";
}

# Taint (removing GET/POST/REQUEST against unsafe reads)
core\Taint::init();
# Prevent website usage if the user has been abusive.
#core\Abuse::req($_CLIENT["ip"]);
