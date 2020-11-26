<?php
namespace core;

use prj\Shared;
use core\Db;
use core\Res;
use core\Env;

/** Force 24hour wait after last attempt (Useful for user specific limits) */
const STRATEGY_24H_WAIT = "STRATEGY_24H_WAIT";
/** Force 24hour wait from the first attempt (Useful for generic limits i.e. 100 lostpass a day) */
const STRATEGY_24H_ADD = "STRATEGY_24H_ADD";

/**
 * Simple abuse counter by IP.
 */
class Abuse
{
	public static function whitelisted($ip)
	{
		return in_array($ip, []); // TODO: Shared::whitelist() ?
	}

	public static function incr($ip, $max=60, $strategy = STRATEGY_24H_WAIT)
	{
		$now = time();
		$db = Shared::Db();
		if ($strategy === STRATEGY_24H_WAIT) {
			$db->exec(
				"DELETE FROM abuselimit WHERE ratelimit_time_updated < ?",
				[strtotime("-1 day")]
			);
		} elseif ($strategy === STRATEGY_24H_ADD) {
			$db->exec(
				"DELETE FROM abuselimit WHERE ratelimit_time_added < ?",
				[strtotime("-1 day")]
			);
		} else {
			user_error("DevErr: Invalid Abuse strategy=$strategy");
		}

		$whitelisted = self::whitelisted(Env::ip());
		$count = $db->getCell("SELECT ratelimit_count FROM abuselimit WHERE ratelimit_ip = ? LIMIT 1", [$ip]);
		if (!$whitelisted && $count > $max) {
			Res::error(503);
			echo "Err, too many requests.";
			exit;
		}

		$db->exec(
			"INSERT INTO abuselimit (`ratelimit_ip`, `ratelimit_count`, `ratelimit_time_updated`, `ratelimit_time_added`) VALUES(?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE ratelimit_count=ratelimit_count+1,ratelimit_time_updated=?",
			[$ip, 1, $now, $now, $now]
		);
	}
}
