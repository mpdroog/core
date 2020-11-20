<?php
namespace core;

class Files
{
	/** Get temp directory */
	public static function tempdir()
	{
		$tempfile=tempnam(sys_get_temp_dir(), '');
		// you might want to reconsider this line when using this snippet.
		// it "could" clash with an existing directory and this line will
		// try to delete the existing one. Handle with caution.
		if (file_exists($tempfile)) {
			unlink($tempfile);
		}
		mkdir($tempfile);
		if (is_dir($tempfile)) {
			return $tempfile;
		}
	}

	/** Delete directory (including content - recursively) */
	public static function deldir($dir)
	{
		if (!file_exists($dir)) {
			return true;
		}
		if (!is_dir($dir)) {
			return unlink($dir);
		}

		foreach (scandir($dir) as $item) {
			if ($item == '.' || $item == '..') {
				continue;
			}
			if (!self::deldir($dir . DIRECTORY_SEPARATOR . $item)) {
				return false;
			}
		}
		return rmdir($dir);
	}

	/** Delete directory content (recursively) */
	public static function delfiles($dir)
	{
		if (!file_exists($dir)) {
			return true;
		}
		if (!is_dir($dir)) {
			return unlink($dir);
		}

		foreach (scandir($dir) as $item) {
			if ($item == '.' || $item == '..') {
				continue;
			}
			if (!self::deldir($dir . DIRECTORY_SEPARATOR . $item)) {
				return false;
			}
		}
		return true;
	}
}
