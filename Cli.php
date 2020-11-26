<?php
namespace core;

use prj\Shared;

/** Hack to clear lock on shutdown. */
class SyncShutdown
{
	private $db;
	private $key;
	private $time;

	public function __construct($db, $key)
	{
		$this->db = $db;
		$this->key = $key;
		$this->time = time();
	}
	public function __destruct()
	{
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

/** CLI Helpers */
class Cli
{
	private static $_conf = null;

	/** Please never call this func, it's used with init-cli.php */
	public static function init(array $conf)
	{
		if (self::$_conf !== null) {
			user_error("Cli::init() called twice?");
		}
		self::$_conf = $conf;
	}

	/** Ensure we're the only cron running this task */
	public static function platform_lock($key)
	{
		global $EXITHACK;
		// Sleep random so we're not racing on all machines.
		$rand = mt_rand(1, 5);
		msg("Random wait $rand sec");
		sleep($rand);

		// Attempt to acquire lock.
		$db = Shared::db();
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

	/** Ask if user is sure */
	public static function confirm($msg, $default = false)
	{
		// Display
		{
			$defaultMsg = "[y|N]";
			if ($default) {
				$defaultMsg = "[Y|n]";
			}
			echo "$msg $defaultMsg";
		}

		// Read
		$ok = false;
		{
			$handle = fopen("php://stdin", "r");
			$line = fgets($handle);
			echo "\n";
			$cmp = strtolower(trim($line));
			$ok = $cmp === "y";
			if ($cmp === "") {
				// Fallback if no value set
				$ok = $default;
			}
		}
		return $ok;
	}

	/** Show simple heading */
	public static function heading($msg)
	{
		echo "\n$msg\n=====================\n";
	}

	/** Show aligned text. */
	public static function align($key, $value)
	{
		$mask = "%15.15s | %s\n";
		echo sprintf($mask, $key, $value);
	}
	public static function text($msg)
	{
		echo $msg . "\n";
	}

	public static function cli()
	{
		$ret = self::$_conf;
		if ($ret === null) {
			var_dump(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3));
			user_error("Cli::cli() fail, init never called?");
		}
		return $ret;
	}

	public static function error($msg)
	{
		$fd = fopen('php://stderr', 'w+');
		fwrite($fd, "$msg\n");
		fclose($fd);
	}

	public static function can_write()
	{
		$cli = self::cli();
		return isset($cli["flags"]["w"]) ? $cli["flags"]["w"] : false;
	}

	public static function exec($program, array $args = [])
	{
		$fds = [
			0 => ["pipe", "r"],
			1 => ["pipe", "w"],
			2 => ["pipe", "w"]
		];
		$cmd = $program;
		foreach ($args as $arg) {
			$cmd .= " " . escapeshellarg($arg);
		}
		$proc = proc_open($cmd, $fds, $pipes, null, null);
		if ($proc === false) {
			throw new \Exception("Failed calling proc_open");
		}
		fclose($pipes[0]); // close stdin

		$out = [
			"cmd" => $cmd,
			"stdout" => stream_get_contents($pipes[1]),
			"stderr" => stream_get_contents($pipes[2])
		];
		fclose($pipes[1]);
		fclose($pipes[2]);
		$out["exit"] = proc_close($proc);
		return $out;
	}
}
