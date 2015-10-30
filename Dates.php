<?php
namespace core;

class Dates {
	// Convert $date into $step intervals.
	// $step is in 5min intervals
	//
	// @param int $date unixtimestamp
	// @param int $step step * 5min
	public static function consolidate($date, $step) {
		$step = 60 * 5 * $step; // 5min intervals
		$addl = $date % $step;
		return $date - $addl;
	}
}
