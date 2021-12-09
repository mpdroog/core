<?php
require dirname(__FILE__) . "/init.php";
function report($errno, $errstr, $errfile, $errline)
{
	$msg = "($errfile:$errline) $errno: $errstr";
	error_log($msg);
	exit(1);
}

// Init $_CLI with CLI task + args
$verbose = false;
$_CLI = null;
{
	$args = $_SERVER["argv"];
	$skip = 0;

	if ($args[0] === $_SERVER["PHP_SELF"]) {
		$skip = 1;
	}
	if (count($args) < $skip+1) {
		$args[$skip] = "help";
	} elseif (substr($args[$skip], 0, 1) === "-") {
		$args[] = $args[$skip];
		$args[$skip] = "help";
	}

	$_CLI = [
		"test" => getenv("TESTING") !== false,
		"task" => $args[$skip],
		"args" => [],
		"flags" => [],
		"today" => date("Y-m-d")
	];
	if ($_CLI["test"]) {
		$_CLI["today"] = "2015-09-01";
		$_CLIENT = ["test" => true]; // for db mocking
	}

	foreach (array_slice($args, $skip+1) as $kvp) {
		if (strpos($kvp, "=") !== false) {
			list($key, $value) = explode("=", $kvp);
			if (substr($key, 0, 1) === "-") {
				$_CLI["flags"][$key] = $value;
			} else {
				$_CLI["args"][$key] = $value;
			}
		} else {
			if (substr($kvp, 0, 1) === "-") {
				if ($kvp === "-v") {
					$verbose = true;
				} elseif ($kvp === "--help") {
					$_CLI["task"] = "help";
				} else {
					$_CLI["flags"][substr($kvp, 1)] = true;
				}
			} else {
				$_CLI["args"][$kvp] = true;
			}
		}
	}
}
core\Cli::init($_CLI);

if (1 !== preg_match("/^[a-z0-9_\/]{2,}$/", $_CLI["task"])) {
	user_error("Invalid task, value=" . $_CLI["task"]);
}

# Less variables
unset($_GET);
unset($_POST);
unset($_SERVER);
define("VERBOSE", $verbose);
define("WRITE", isset($_CLI["flags"]["w"]));
define("DEBUG", isset($_CLI["flags"]["d"]));

function msg($msg, array $args = [])
{
	global $verbose;
	if (! $verbose) {
		return;
	}
	if (count($args) > 0) {
		$msg .= sprintf("\n%s", print_r($args, true));
	}
	echo $msg . "\n";
}

if (! isset($_CLI["flags"]["w"]) && !$_CLI["test"]) {
        if (! defined("HIDE_READONLY")) {
		echo "WARN: Running in Read/Only Mode, add -w (write) flag.\n";
	}
}
if ($verbose) {
	var_dump($_CLI);
}
