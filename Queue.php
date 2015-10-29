<?php
namespace core;
use Pheanstalk\Pheanstalk;

class Queue {
	private static $queue;

	public static function init() {
		self::$queue = new Pheanstalk('127.0.0.1');
	}

	/** Add job to beanstalkd and return job id */
	public static function send($tube, array $data) {
		return self::$queue->useTube($tube)->put(json_encode($data));
	}
}

Queue::init();