<?php
namespace core;

use Pheanstalk\Pheanstalk;

class Worker
{
	public static function listen($channel, $fn, $expect="json")
	{
		global $_CLI;

		$queue = new Pheanstalk("127.0.0.1");
		$queue->watch($channel)->ignore("default");
		msg("Process $channel(127.0.0.1)");

		if ($_CLI["test"]) {
			// Process 1 cmd in testing mode
			$job = $queue->reserve(10);
			if ($job === false) {
				user_error("No job in queue, timed-out after 10sec");
			}
			$input = $job->getData();
			msg(sprintf("Processing job (%d)", $job->getId()), [$input]);

			if ($expect === "json") {
				$input = json_decode($input, true);
			} elseif ($expect === "string") {
				// nothing to do
			} else {
				$queue->bury($job);
				user_error("Invalid expect=$expect");
			}

			$fn($input);
			$queue->delete($job);
			return;
		}
		while (true) {
			$job = $queue->reserve();
			$input = $job->getData();
			msg(sprintf("Processing job (%d)", $job->getId()), [$input]);
			// Bury by default (no retry on failure)
			// On success the job is deleted
			$queue->bury($job);

			if ($expect === "json") {
				$input = json_decode($input, true);
			} elseif ($expect === "string") {
				// nothing to do
			} else {
				user_error("Invalid expect=$expect");
			}

			$fn($input);
			$queue->delete($job);
		}
	}
}
