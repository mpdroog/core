<?php
namespace core;

use prj\HtmlMail;

/**
 * Quick'n'Dirty Markdown parser.
 *
 * Why?
 * To have one template (Markdown plain/text) for an email
 * and easily extend it with HTML+inline CSS for 'rich emails'
 */
class DirtyMarkdown
{
	use HtmlMail;

	/** Check if line only exists of '=' chars */
	private static function is_heading($line)
	{
		if (mb_strlen($line) === 0) {
			return false;
		}
		$match = 1;
		for ($i = 0; $i < mb_strlen($line); $i++) {
			$str = mb_substr($line, $i, 1);
			$match &= ($str === "=");
		}
		return $match === 1;
	}

	/** Check for [..](..) pattern */
	private static function is_link($line)
	{
		$txtBegin = mb_strpos($line, "[");
		$txtEnd = mb_strpos($line, "]");
		$urlBegin = mb_strpos($line, "(");
		$urlEnd = mb_strpos($line, ")");

		if ($txtBegin === false || $urlBegin === false || $txtEnd === false || $urlEnd === false) {
			return false;
		}
		return [
			"pre" => mb_substr($line, 0, $txtBegin),
			"txt" => mb_substr($line, $txtBegin+1, $txtEnd - $txtBegin - 1),
			"url" => mb_substr($line, $urlBegin+1, $urlEnd - $urlBegin - 1),
			"post" => mb_substr($line, $urlEnd+1)
		];
	}

	/* Check for 'empty line' */
	private static function is_newline($line)
	{
		return mb_strlen(trim($line)) === 0;
	}

	/* Convert 'simple' Markdown-syntaxis to HTML email */
	public static function parse($fmt, $input)
	{
		$lines = [];
		$lines[] = self::section_begin($fmt);

		$prev_newline = false;
		foreach (mb_split("\n", $input) as $line) {
			$first_space = mb_substr($line, 0, 1) === ' ';
			if (self::is_heading($line)) {
				// H1. =====
				// Override previous line
				$prev = count($lines)-1;
				$lines[$prev] = self::heading($fmt, $lines[$prev]);
				continue;
			}

			$link = self::is_link($line);
			if ($link) {
				// A. [....](..)
				$line = $link["pre"] . self::link($fmt, $link["url"], $link["txt"]) . $link["post"];
			}

			if (self::is_newline($line)) {
				// New block?
				if ($prev_newline) {
					// Yes, begin new section
					$lines[] = self::section_end($fmt);
					$lines[] = self::section_begin($fmt);
					continue;
				} else {
					// No, remember to see if next is also newline
					$prev_newline = true;
					continue;
				}
			}
			if ($prev_newline) {
				$prev_newline = false;
				$line = "<br>" . $line;
			}

			if (! $first_space) {
				$line .= "<br>";
			}
			$lines[] = $line;
		}

		$lines[] = self::section_end($fmt);
		return implode("", $lines);
	}
}
