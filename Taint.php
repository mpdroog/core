<?php
namespace core;
use prj\ProjectValidators;

/**
 * Hide GET/POST to enforce:
 * - input validation
 * - encoding checks
 */
trait TaintValidators {
  private static function email($val) {
    // prevent: derp@@derp.com
    // prevent: derp@derp.com.
    // prevent: .derp@derp.com
    // allow: mark+tag@gmail.com
    $ok = 1 === preg_match("/^(([a-zA-Z]|[0-9])|([-]|[_]|[.]|[+]))+[@](([a-zA-Z0-9])|([-])){2,63}[.](([a-zA-Z0-9\.]){2,63})+$/i", $val);
    if (substr($val, 0, 1) === ".") {
      // Don't allow dot at the begin
      $ok = false;
    }
    if (substr($val, -1) === ".") {
      // Don't allow dot at the end
      $ok = false;
    }
    return $ok;
  }
  private static function cmp($val) {
    return 1 === preg_match("/^[a-z0-9_]{2,}\/[a-z0-9_]{2,}$/i", $val);
  }
  private static function uuid($val) {
    return 1 === preg_match("/^[a-z0-9\-]{2,}$/i", $val);
  }
  private static function slug($val) {
    return 1 === preg_match("/^[a-z0-9_]{2,}$/i", $val);
  }
  private static function datetime($val) {
    return 1 === preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}$/i", $val);
  }
  private static function uint($val) {
    return 1 === preg_match("/^[0-9]+$/i", $val);
  }
}

/**
 * Validate user input.
 */
class Taint {
  use TaintValidators;
  use ProjectValidators;
  private static $get = [];
  private static $post = [];

  public static function init() {
    self::$get = $_GET;
    self::$post = $_POST;
    unset($_GET);
    unset($_POST);
    unset($_REQUEST);
  }

  private static function encodingOk($val) {
    return mb_check_encoding($val);
  }

  private static function opt($val) {
    return 1;
  }

  private static function check($out, array $data) {
    $fields = array_keys(get_object_vars($out));
    $errors = [];
    $rules = $out->rules();

    $count = 0;
    foreach ($fields as $field) {
      // Check if field exists
      if (! isset($data[ $field ])) {
        if (in_array("opt", $rules[$field])) {
          // Optional field and no value, skip
          continue;
        }
        $errors[] = "none.$field";
        continue;
      }

      $count++;
      $val = $data[ $field ];
      if ($val === "" && in_array("opt", $rules[$field])) {
        // Optional field and no value, skip
        continue;
      }

      // Check if encoding is OK
      if (! self::encodingOk($val)) {
        $errors[] = "encoding.$field";
        continue;
      }
      // Check if we need to 'truncate' the value?
      if (in_array("trim", $rules[$field])) {
        $val = str_replace(" ", "", trim($val));
      }
      // Check against rules
      foreach ($rules[ $field ] as $rule) {
        if ($rule === "trim") {
          // Ignore
          continue;
        }
        if (! self::$rule($val)) {
          $errors[] = "check.$field.$rule";
        }
      }
      if (count($errors) > 0) {
        continue;
      }
      // Value is safe, pass on!
      $out->$field = $val;
    }

    if ($count !== count($data)) {
      // More fields than $out, find out which
      foreach (array_keys($data) as $field) {
        if (! in_array($field, $fields, TRUE)) {
          $errors[] = "extra.$field";
        }
      }
    }

    if (count($errors) > 0) {
      return $errors;
    }
    return $out;
  }

  public static function post($out) {
    return self::check($out, self::$post);
  }
  public static function get($out) {
    return self::check($out, self::$get);
  }
  public static function json($out) {
    $input = json_decode(file_get_contents('php://input'), TRUE);
    if (! is_array($input)) {
      return [];
    }
    return self::check($out, $input);
  }

  /** Return field if valid by rules (also unset value) */
  public static function getField($name, array $rules) {
    if (! isset(self::$get[$name])) {
      return false;
    }
    $val = self::$get[$name];
    $ok = 1;
    foreach ($rules as $rule) {
      $ok &= self::$rule($val);
    }
    if ($ok === 0) {
      return false;
    }
    unset(self::$get[$name]);
    return $val;
  }
}
