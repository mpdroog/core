<?php
namespace core;

use Pheanstalk\Pheanstalk;
use core\Helper;

class Queue
{
	private static $queue;

	public static function init()
	{
		self::$queue = new Pheanstalk('127.0.0.1');
	}

	/** Add job to beanstalkd and return job id */
	public static function send($tube, array $data)
	{
		if ($tube === "email") {
			$data["from"] = Helper::config("general")["mailbox_from"];
		}
		return self::$queue->useTube($tube)->put(json_encode($data));
	}
}

Queue::init();
