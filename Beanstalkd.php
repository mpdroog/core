<?php
declare (strict_types=1);
namespace core;

/**
 Beanstalk is a simple, fast work queue.
 This class is a small implementation of the protocol
 to interact with this small daemon.
 @see https://beanstalkd.github.io/
 Installation should be as easy as apt-get install beanstalkd
 */
class Beanstalkd {
public static function conn() {
$errno = null;
$errstr = null;
$conn = fsockopen("127.0.0.1", 11300, $errno, $errstr, 2);
if ($conn === false) {
    echo "CRITICAL - stream_socket_client fail: $errno $errstr\n";
    exit(2);
}
return $conn;
}

public static function write($conn, string $cmd, int $timeout = 3): void {
  self::assert_resource($conn);
  if (stream_set_timeout($conn, $timeout) === false) {
    echo "UNKNOWN - stream_set_timeout fail\n";
    exit(3);
  }
  if (VERBOSE) echo ">> $cmd\n";
  if (fwrite($conn, $cmd."\r\n", strlen($cmd."\r\n")) === false) {
    echo "UNKNOWN - fwrite($cmd) fail\n";
    exit(3);
  }
}
public static function read($conn): ?string {
	self::assert_resource($conn);
        $res = stream_get_line($conn, 16384, "\r\n");
	if (VERBOSE) echo "<< $res\n";
	return $res ?: null;
}

public static function assert_resource($resource)
{
    if (false === is_resource($resource)) {
        throw new InvalidArgumentException(
            sprintf(
                'Argument must be a valid resource type. %s given.',
                gettype($resource)
            )
        );
    }
}

public static function res_kvp($conn, string $cmd): array {
    self::write($conn, $cmd);

    $bytes = 0;
    {
        $res = self::read($conn);
        if ($res === false || substr($res, 0, 3) !== "OK ") {
            echo "CRITICAL - reply($cmd) wrong: $res\n";
            exit(2);
        }
        $bytes = substr($res, 3);
    }

    $lines = [];
    {
    $res = trim(fread($conn, $bytes+2));
    if ($res === false) {
        echo "CRITICAL - reply($cmd) wrong: $res\n";
        exit(2);
    }
    if (VERBOSE) echo "<< $res\n";
    foreach (explode("\n", $res) as $line) {
        if (strlen($line) === 0) {
            // Empty line
            continue;
        }
        if ($line === "---") {
            // Skip separation-thingy
            continue;
        }
        $sep = strpos($line, ":");
        if ($sep === false) {
            echo "CRITICAL - expected separation char on line: $line\n";
            exit(2);
        }
        $lines[ substr($line, 0, $sep) ] = substr($line, $sep+2);

    }
    }
    return $lines;
}
public static function res_list($conn, string $cmd): array {
    self::write($conn, $cmd);

    $bytes = 0;
    {
        $res = self::read($conn);
        if ($res === false || substr($res, 0, 3) !== "OK ") {
            echo "CRITICAL - reply($cmd) wrong: $res\n";
            exit(2);
        }
        $bytes = substr($res, 3);
    }

    $lines = [];
    {
    $res = trim(fread($conn, $bytes+2));
    if ($res === false) {
        echo "CRITICAL - reply($cmd) wrong: $res\n";
        exit(2);
    }
    foreach (explode("\n", $res) as $line) {
        if (VERBOSE) echo "<< $line\n";
        if (strlen($line) === 0) {
            // Empty line
            continue;
        }
        if ($line === "---") {
            // Skip separation-thingy
            continue;
        }
        $sep = strpos($line, "- ");
        if ($sep === false) {
            echo "CRITICAL - expected separation char on line: $line\n";
            exit(2);
        }
        $lines[] = substr($line, $sep+2);

    }
    }
    return $lines;
}

public static function close($conn): void {
    self::write($conn, "quit");
    fclose($conn);
}

    public static function tube($conn, string $tube): void {
	self::write($conn, "use $tube");
        $res = self::read($conn);

        if ($res === false || trim($res) !== "USING $tube") {
            echo "tube($tube) wrong: $res\n";
            exit(2);
	}
    }

    public static function kick($conn, int $n): void {
        self::write($conn, "kick $n");
        $res = self::read($conn);
        if ($res === false || substr(trim($res), 0, 6) !== "KICKED") {
            echo "CRITICAL - reply(KICK 50) wrong: $res\n";
            exit(2);
        }
    }

    public static function delete($conn, int $id): void {
        self::write($conn, "delete $id");
        $res = self::read($conn);
        if ($res === false || substr(trim($res), 0, 7) !== "DELETED") {
            echo "CRITICAL - reply(DELETE) wrong: $res\n";
            exit(2);
        }
    }

    public static function put($conn, string $data, int $delay = 5, int $timeToRun = 120, int $prio = 10): int {
        self::write($conn, sprintf("put %d %d %d %d", $prio, $delay, $timeToRun, strlen($data)));
	self::write($conn, $data);
	$res = self::read($conn);

        if ($res === false || substr(trim($res), 0, 8) !== "INSERTED") {
            echo "CRITICAL - INSERTED wrong: $res\n";
            exit(2);
	}
	return intval(explode(" ", $res)[1]);
    }

    public static function watch($conn, string $tube): void {
        self::write($conn, "watch $tube");
        $res = self::read($conn);
        if ($res === false || substr(trim($res), 0, 8) !== "WATCHING") {
            echo "CRITICAL - reply(WATCH) wrong: $res\n";
            exit(2);
        }
    }

    public static function reserve($conn, int $timeout = 60): ?array {
	self::write($conn, "reserve-with-timeout $timeout", $timeout+1);
	$res = self::read($conn);
	/*$info = stream_get_meta_data($conn);
	if ($info['timed_out']) {
		var_dump($info);
		user_error("stream timed out");
	}*/
	if ($res === "TIMED_OUT") return null;

	/* RESERVED <id> <bytes>\r\n<data>\r\n */
	$cmd = explode(" ", $res, 3);
	if ($cmd[0] !== "RESERVED") {
		echo "CMD invalid=$res\n";
		exit(2);
	}

	$lines = fread($conn, intval($cmd[2])+2);
	$json = json_decode($lines, true);
	return [
		"id" => intval($cmd[1]),
		"data" => $json ?? $lines,
	];
    }
}
