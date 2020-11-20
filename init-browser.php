<?php
ini_set('default_charset', 'utf-8');
require dirname(__FILE__) . "/init.php";

function report($errno, $errstr, $errfile, $errline)
{
	header('HTTP/1.1 500 Internal Server Error');
	// TODO: Report error to devsys

	$msg = "($errfile:$errline) $errno: $errstr";
	error_log($msg);
	exit("Error written to error log.\n");
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
		$refok = $domain === $_SERVER["SERVER_NAME"];
	}
}
