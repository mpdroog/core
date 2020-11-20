<?php
namespace core;

use core\Cli;
use core\Curl;
use core\Helper;

trait TestUtils
{
	// Get cURL instance
	private function curl()
	{
		$c = new Curl();
		$c->setJsonDecoder(function ($val) {
			$res = json_decode($val, true);
			return $res;
		});
		return $c;
	}

	private function exec_worker($name, array $args = [])
	{
		$param = array_merge([ROOT . "/workers/index.php", $name, "-i=1"], $args);
		return Cli::exec("php", $param);
	}

	private function exec_cli($name, array $args = [])
	{
		$args = array_merge([$name], $args);
		return Cli::exec("./run.sh", $args);
	}

	private static function pending($queue)
	{
		try {
			$queue = Helper::prefix($queue);
			return self::$queue->statsTube($queue)["current-jobs-ready"];
		} catch (\Exception $e) {
			return 0;
		}
	}

	private static function buried($queue)
	{
		try {
			$queue = Helper::prefix($queue);
			return self::$queue->statsTube($queue)["current-jobs-buried"];
		} catch (\Exception $e) {
			return 0;
		}
	}

	private static function verbose($task, $text, $iswrite = false)
	{
		$a = [
			"test" => true,
			"task" => $task,
			"args" => [],
			"flags" => [],
			"today" => "2015-09-01"
		];
		if ($iswrite) {
			$a["flags"]["w"] = true;
		}

		ob_start();
		{
			var_dump($a);
		}
		$txt = ob_get_clean();
		return sprintf("%s%s\n", $txt, $text);
	}
}
