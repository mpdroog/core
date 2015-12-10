<?php
namespace core;
use Pheanstalk\Pheanstalk;

class Worker {
	public static function listen($channel, $fn) {
		global $_CLI;

		$queue = new Pheanstalk("127.0.0.1");
		$queue->watch($channel)->ignore("default");
		msg("Process $channel(127.0.0.1)");

		if ($_CLI["test"]) {
			// Process 1 cmd in testing mode
			$job = $queue->reserve(10);
			msg(sprintf("Processing job (%d)", $job->getId()), [$job->getData()]);
			$input = json_decode($job->getData(), true);

			$fn($input);
			$queue->delete($job);
			return;
		}
		while (true) {
			$job = $queue->reserve();
			msg(sprintf("Processing job (%d)", $job->getId()), [$job->getData()]);
			// Bury by default (no retry on failure)
			// On success the job is deleted
			$queue->bury($job);
			$input = json_decode($job->getData(), true);

			$fn($input);
			$queue->delete($job);
		}
	}
}
