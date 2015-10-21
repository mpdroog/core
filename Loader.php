<?php
namespace core;

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
}
