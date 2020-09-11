<?php
namespace core;

/**
 * Parse content-type
 */
class Convert {
  // Parse CSV into associated array
  public static function csv($str) {
    $fp = fopen("php://temp", 'r+');
    if (! is_resource($fp)) {
      user_error("fopen(temp) failed");
    }
    if (false === fputs($fp, $str)) {
      user_error("fputs(temp) failed");
    }
    if (! rewind($fp)) {
      user_error("rewind(temp) failed");
    }

    $kv = fgetcsv($fp);
    if ($kv === null || $kv === false) {
      user_error("fgetcsv(temp) failed");
    }
    foreach ($kv as $k => &$v) {
      $v = trim($v);
    }

    $lines = [];
    while ( ($data = fgetcsv($fp) ) !== FALSE ) {
      $out = [];
      foreach ($data as $idx => $col) {
        if (isset($out[ $kv[$idx] ])) {
          user_error(sprintf("key=%s already set", $kv[$idx]));
        }
        $out[ $kv[$idx] ] = trim($col);
      }
      $lines[] = $out;
    }
    return $lines;
  }
}
