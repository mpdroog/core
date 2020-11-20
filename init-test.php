<?php
require dirname(__FILE__) . "/init.php";
function report($errno, $errstr, $errfile, $errline)
{
	$msg = "($errfile:$errline) $errno: $errstr";
	error_log($msg);
	exit(1);
}
$_CLIENT = [
	"test" => true
];
