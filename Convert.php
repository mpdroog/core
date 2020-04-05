<?php
namespace core;

/**
 * Parse content-type
 */
class Convert {
  // Parse CSV into associated array
  public static function csv($str) {
    $fp = fopen("php://temp", 'r+');
    fputs($fp, $str);
    rewind($fp);

    $lines = [];
    $kv = fgetcsv($fp);
    while ( ($data = fgetcsv($fp) ) !== FALSE ) {
      $out = [];
      foreach ($data as $idx => $col) {
        $out[ trim($kv[$idx]) ] = trim($col);
      }
      $lines[] = $out;
    }
    return $lines;
  }
}
