<?php
namespace core;

class Diff
{
	// Differentiate recursively between two arrays and return differenced entries
	// https://stackoverflow.com/questions/3876435/recursive-array-diff
	public static function array($aArray1, $aArray2)
	{
		$aReturn = [];
		foreach ($aArray1 as $mKey => $mValue) {
			if (array_key_exists($mKey, $aArray2)) {
				if (is_array($mValue)) {
					$aRecursiveDiff = self::array($mValue, $aArray2[$mKey]);
					if (count($aRecursiveDiff)) {
						$aReturn[$mKey] = $aRecursiveDiff;
					}
				} else {
					if ($mValue != $aArray2[$mKey]) {
						$aReturn[$mKey] = $mValue;
					}
				}
			} else {
				$aReturn[$mKey] = $mValue;
			}
		}
		return $aReturn;
	}

	/*
		https://coderwall.com/p/3j2hxq/find-and-format-difference-between-two-strings-in-php

		Paul's Simple Diff Algorithm v 0.1
		(C) Paul Butler 2007 <http://www.paulbutler.org/>
		May be used and distributed under the zlib/libpng license.

		This code is intended for learning purposes; it was written with short
		code taking priority over performance. It could be used in a practical
		application, but there are a few ways it could be optimized.

		Given two arrays, the function diff will return an array of the changes.
		I won't describe the format of the array, but it will be obvious
		if you use print_r() on the result of a diff on some test data.

		htmlDiff is a wrapper for the diff command, it takes two strings and
		returns the differences in HTML. The tags used are <ins> and <del>,
		which can easily be styled with CSS.
	*/
	public static function diff($old, $new)
	{
		$matrix = [];
		$maxlen = 0;
		foreach ($old as $oindex => $ovalue) {
			$nkeys = array_keys($new, $ovalue);
			foreach ($nkeys as $nindex) {
				$matrix[$oindex][$nindex] = isset($matrix[$oindex - 1][$nindex - 1])
					? $matrix[$oindex - 1][$nindex - 1] + 1 : 1;
				if ($matrix[$oindex][$nindex] > $maxlen) {
					$maxlen = $matrix[$oindex][$nindex];
					$omax = $oindex + 1 - $maxlen;
					$nmax = $nindex + 1 - $maxlen;
				}
			}
		}
		if ($maxlen == 0) {
			return [['d'=>$old, 'i'=>$new]];
		}
		return array_merge(
			self::diff(array_slice($old, 0, $omax), array_slice($new, 0, $nmax)),
			array_slice($new, $nmax, $maxlen),
			self::diff(array_slice($old, $omax + $maxlen), array_slice($new, $nmax + $maxlen))
		);
	}

	public static function html_bychar($old, $new)
	{
		$ret = '';
		$diff = self::diff(str_split($old), str_split($new));
		foreach ($diff as $k) {
			if (is_array($k)) {
				$ret .= (!empty($k['d'])?"<del>".implode('', $k['d'])."</del>":'').
					(!empty($k['i'])?"<ins>".implode('', $k['i'])."</ins>":'');
			} else {
				$ret .= $k;
			}
		}
		return $ret;
	}
	public static function html_byspace($old, $new)
	{
		if (! $old) {
			$old = "NULL";
		}
		if (! $new) {
			$new = "NULL";
		}

		$ret = '';
		$diff = self::diff(
			preg_split("/([\s]+)/", $old, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY),
			preg_split("/([\s]+)/", $new, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY)
		);
		foreach ($diff as $k) {
			if (is_array($k)) {
				$ret .= (!empty($k['d'])?"<del>".implode('', $k['d'])."</del> ":'').
					(!empty($k['i'])?"<ins>".implode('', $k['i'])."</ins> ":'');
			} else {
				$ret .= $k;
			}
		}
		return $ret;
	}
}
