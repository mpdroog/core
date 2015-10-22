<?php
require dirname(__FILE__) . "/init.php";
function report($errno, $errstr, $errfile, $errline) {
  $msg = "($errfile:$errline) $errno: $errstr";
  error_log($msg);
  exit(1);
}

// Init $_CRON with CLI task + args
$verbose = false;
$_CRON = null;
{
	$args = $_SERVER["argv"];
	$skip = 0;

	if ($args[0] === $_SERVER["PHP_SELF"]) {
		$skip = 1;
	}
	if (count($args) < $skip+1) {
		$args[$skip] = "help";
	} else if (substr($args[$skip], 0, 1) === "-") {
		$args[] = $args[$skip];
		$args[$skip] = "help";
	}

	$_CRON = [
		"test" => getenv("TESTING") !== false,
		"task" => $args[$skip],
		"args" => [],
		"flags" => [],
		"today" => date("Y-m-d")
	];
	if ($_CRON["test"]) {
		$_CRON["today"] = "2015-09-01";
	}

	foreach (array_slice($args, $skip+1) as $kvp) {
		if (strpos($kvp, "=") !== false) {
			list($key, $value) = explode("=", $kvp);
			if (substr($key, 0, 1) === "-") {
				$_CRON["flags"][$key] = $value;
			} else {
				$_CRON["args"][$key] = $value;
			}
		} else {
			if (substr($kvp, 0, 1) === "-") {
				if ($kvp === "-v") {
					$verbose = true;
				} else if ($kvp === "--help") {
					$_CRON["task"] = "help";
				} else {
					$_CRON["flags"][substr($kvp,1)] = true;
				}
			} else {
				$_CRON["args"][$kvp] = true;
			}
		}
	}
}

if (1 !== preg_match("/^[a-z0-9_]{2,}$/", $_CRON["task"])) {
	user_error("Invalid task, value=" . $_CRON["task"]);
}

# Less variables
unset($_GET);
unset($_POST);
unset($_SERVER);

define("VERBOSE", $verbose);
define("TASK", BASE . sprintf("tasks/%s/", $_CRON["task"]));

function msg($msg, array $args = []) {
	global $verbose;
	if (! $verbose) {
		return;
	}
	if (count($args) > 0) {
		$msg .= sprintf("%s\r\n", print_r($args, true));
	}
	error_log($msg);
}

if (! isset($_CRON["flags"]["w"])) {
	echo "WARN: Running in Read/Only Mode, add -w (write) flag.\n";
}
if ($verbose) {
	var_dump($_CRON);
}
