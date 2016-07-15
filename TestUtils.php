<?php
namespace core;
use core\Curl;

trait TestUtils {
	// Get cURL instance
	private function curl() {
		$c = new Curl();
		$c->setJsonDecoder(function($val) {
			$res = json_decode($val, true);
			return $res;
		});
		return $c;
	}
}
