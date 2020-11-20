<?php
namespace core;

class AuditSync
{
	private $pfx;    // Prefix text in case of msg
	private $time;   // time()
  private $max;    // max time in minutes before destruct is called
	public function __construct($pfx, $max)
	{
		$this->time = time();
		$this->pfx = $pfx;
		$this->max = $max;
	}
	public function __destruct()
	{
		$diff = round(abs(time() - $this->time) / 60, 2);
		if ($diff > $this->max) {
			error_log(sprintf("perf.warn(%s) max.passed.seconds (dur=%s max=%s)", $this->pfx, $diff, $this->max));
		}
		if (VERBOSE) {
			error_log(sprintf("per.debug(%s) time.passed.seconds (dur=%s max=%s)", $this->pfx, $diff, $this->max));
		}
	}
}

/** Simple code auditting */
class Audit
{
	public static function perf($prefix, $max)
	{
		global $EXITHACK;
		$EXITHACK = new AuditSync($prefix, $max);
		return true;
	}
}
