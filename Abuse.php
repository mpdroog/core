<?php
namespace core;
use prj\Fn;
use core\Res;

/**
 * Abuse helpers.
 * Simple methods to ban users that try
 * to flood specific features of the system.
 */
class Abuse {
	/** Check if we blocked the IP. (on abuse return 403 and block user until EXPIRE removed by banclear-cron) */
	public static function req($ip) {
		$db = Fn::db();
		$blacklisted = $db->getCell("SELECT 1 FROM `blacklist_ip` WHERE ip=? AND active=?", [$ip, "1"]);
		if ($blacklisted === "1") {
			error_log(sprintf("Block IP(%s) due to abusive calls.", $ip));
			Res::error(Fn::lang("core.abuse"), [], "403");
			exit;
		}
	}

	/** Increase the abuse counter */
	public static function increase($ip, $key = "abuse", $comment = "", $attempts = 10) {
		$db = Fn::db();
		$count = $db->getCell("SELECT count FROM `blacklist_ip` WHERE `ip` = ? and `key` = ?", [$ip, $key]);
		if ($count === false) {
			$count = 1;
			$db->insert("blacklist_ip", [
				"key" => $key, "count" => $count,
				"ip" => $ip, "expire" => strtotime("+2 days"),
			]);
		} else {
			$count = intval($count);
			$count++;
			$db->update("blacklist_ip", [
				"count" => $count,
				"expire" => strtotime("+2 days"),
				"active" => $count >= $attempts ? "1" : "0"
			], [
				"ip" => $ip,
				"key" => $key
			]);
		}
		error_log(sprintf("IP(%s) Abuse++(%s) comment=%s", $ip, $count, $comment));
	}
}
