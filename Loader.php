<?php
namespace core;

use core\Cli;
use core\Taint;
use core\Res;

class Loader {
	public static function action() {
		$req = Taint::getField("req", ["cmp"]);
		if ($req === false) {
			Res::error("Requested invalid page");
			exit;	
		}
		$path = BASE . "cmp/$req/index.php";

		if (file_exists($path)) {
			define("CMP", BASE . "cmp/$req/");
			require $path;
		} else {
			Res::error("Page $req does not exist.");
			exit;
		}
	}

	public static function cli() {
		global $_CRON;
		# Load requested cron
		$path = TASK . "index.php";
		if (! file_exists($path)) {
			user_error("No such task: " . $_CRON["task"]);
		}
		if (! Cli::platform_lock($_CRON["task"])) {
			msg("Other server already processing task=" . $_CRON["task"]);
			exit(0);
		}
		require $path;
	}

	public static function worker() {
		global $_CRON;
		# Load requested worker
		$path = TASK . "index.php";
		if (! file_exists($path)) {
			user_error("No such task: " . $_CRON["task"]);
		}
		# TODO: Abuse Redis to ensure only one instance is running?
		require $path;
	}
}
