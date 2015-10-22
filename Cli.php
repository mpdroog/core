<?php
namespace core;

/** Hack to clear lock on shutdown. */
class SyncShutdown {
	private $db;
	private $key;
	private $time;

	public function __construct($db, $key) {
		$this->db = $db;
		$this->key = $key;
		$this->time = time();
	}
	public function __destruct() {
		$diff = time() - $this->time;
		if ($diff > 60*5) {
			error_log(sprintf("WARN: Cron(%s) took long, %s min!", $this->key, $diff/60));
		}
		$this->db->exec(
			"UPDATE
				cron_lock
			SET
				`expire` = 0
			WHERE
				`key` = ?",
			[$this->key]
		);
	}
}

class Cli {
	/** Ensure we're the only cron running this task */
	public static function platform_lock($key) {
		global $EXITHACK;
		// Sleep random so we're not racing on all machines.
		$rand = mt_rand(1,5);
		msg("Random wait $rand sec");
		sleep($rand);

		// Attempt to acquire lock.
		$db = self::db();
		// TODO: Suppress lock error in log here?
		$res = $db->exec(
			"UPDATE
				cron_lock
			SET
				`expire` = ?,
				`server` = ?
			WHERE
				`key` = ?
			AND
				`expire` < ?",
			[
				strtotime("+1 hour"), gethostname(),
				$key, time()
			]
		);
		if ($res->rowCount() === 0) {
			return false;
		}
		// We got the lock, now keep it till shutdown.
		$EXITHACK = new SyncShutdown($db, $key);
		return true;
	}

}