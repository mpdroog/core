<?php
namespace core;
use prj\ProjectValidators;

/**
 * Hide GET/POST to enforce:
 * - input validation
 * - encoding checks
 */
trait TaintValidators {
  /* prevent: derp@@derp.com
   * prevent: derp@derp.com.
   * prevent: derp.@derp.com
   * prevent: derp..@derp.com
   * allow: mark+tag@gmail.com
   */
  private static function email($val) {
    return filter_var($val, FILTER_VALIDATE_EMAIL);
  }
  private static function cmp($val) {
    return 1 === preg_match("/^[a-z0-9_]{2,}\/[a-z0-9_]{2,}$/i", $val);
  }
  private static function uuid($val) {
    return 1 === preg_match("/^[a-z0-9\-]{2,}$/i", $val);
  }
  private static function slug($val) {
    return 1 === preg_match("/^[a-z0-9_\-]{2,}$/i", $val);
  }
  private static function date($val) {
    return 1 === preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/i", $val);
  }
  private static function datetime($val) {
    return 1 === preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}$/i", $val);
  }
  private static function datetimesec($val) {
    return 1 === preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/i", $val);
  }
  private static function uint($val) {
    return 1 === preg_match("/^[0-9]+$/i", $val);
  }
  private static function int($val) {
    return 1 === preg_match("/^-?[0-9]+$/i", $val);
  }
  private static function ip($val) {
    return false !== filter_var($val, FILTER_VALIDATE_IP);
  }
  private static function money($val) {
    return 1 === preg_match("/^[0-9]+\.[0-9]+$/i", $val);
  }
  private static function url($val) {
    return false !== filter_var($val, FILTER_VALIDATE_URL);
  }
  private static function iso2($val) {
    return 1 === preg_match("/^[a-zA-Z]{2}$/", $val);
  }
  private static function fragment($val) {
    return is_array($val);
  }
  private static function text($val) {
    return !is_array($val) && !is_object($val);
  }
  private static function bytes($val) {
    return 1 === preg_match("/^[0-9]+\.*[0-9]* (B|KB|GB|TB)$/i", $val);
  }
}

trait ArrayValidators {
  private static function min(array $val, $count) {
    return count($val) >= $count;
  }
  private static function max(array $val, $count) {
    return count($val) <= $count;
  }
}

/**
 * Validate user input.
 */
class Taint {
  use TaintValidators;
  use ProjectValidators;
  use ArrayValidators;
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

  private static function check_array(array $val, array $rules, $prefix="") {
    $errors = ["type" => "err"];
    $output = ["type" => "ok"];
    // Check against rules
    foreach ($rules as $rule) {
      if ($rule === "subarray") {
        continue;
      }
      $idx = mb_strpos($rule, "=");
      if ($idx !== false) {
        // Key=value
        $k = mb_substr($rule, 0, $idx);
        $v = mb_substr($rule, $idx+1);

        if ($k !== "subtype") {
          if (! self::$k($val, $v)) {
            $errors[] = "$prefix.subtype.$k.$v";
          }
          continue;
        }

        if (! class_exists($v)) {
          $errors[] = "$prefix.subtype.nosuchclass.$v";
          continue;
        }
        if (! is_array($val)) {
          $errors[] = "$prefix.subtype.notsubarray.$v";
          continue;
        }

        $res = self::check(new $v, $val);
        if (is_array($res)) {
          $errors = array_merge($errors, $res);
        } else {
          $output["val"] = $res;
        }
      } else {
        var_dump($data);
        user_error("Only supporting array with subtype validation");
      }
    }

    if (count($errors) > 1) {
      return $errors;
    }
    return $output;
  }

  private static function check_fragment(array $val, array $rules, $prefix="") {
    $errors = ["type" => "err"];
    $output = ["type" => "ok"];
    // Check against rules
    foreach ($rules as $rule) {
      if ($rule === "fragment") {
        continue;
      }
      $idx = mb_strpos($rule, "=");
      if ($idx !== false) {
        // Key=value
        $k = mb_substr($rule, 0, $idx);
        $v = mb_substr($rule, $idx+1);

        if ($k !== "subtype") {
          if (! self::$k($val, $v)) {
            $errors[] = "$prefix.subtype.$k.$v";
          }
          continue;
        }

        if (! class_exists($v)) {
          $errors[] = "$prefix.subtype.nosuchclass.$v";
          continue;
        }
        // Start recursion
        foreach ($val as $idx => $line) {
          if (! is_array($line)) {
            $errors[] = "$prefix.subtype.notfragment.$v";
            continue;
          }
          $res = self::check(new $v, $line, "[$idx]");
          if (is_array($res)) {
            $errors = array_merge($errors, $res);
          } else {
            $output[] = $res;
          }
        }
      } else {
        var_dump($val);
        user_error("Only supporting array with subtype validation");
      }
    }

    if (count($errors) > 1) {
      return $errors;
    }
    return $output;
  }

  private static function check($out, array $data, $prefix="") {
    $fields = array_keys(get_object_vars($out));
    $errors = [];
    $rules = $out->rules();

    $count = 0;
    foreach ($fields as $field) {
      // Check if field exists
      if (! isset($data[ $field ])) {
        if (in_array("opt", $rules[$field]) || in_array("fragment", $rules[$field])) {
          // Optional field and no value, skip
          continue;
        }
        $errors[] = "none.$field$prefix";
        continue;
      }

      $count++;
      $val = $data[ $field ];

      if (is_array($val)) {
        if (in_array("subarray", $rules[$field])) {
          $val = self::check_array($val, $rules[$field], $field);
          $type = $val["type"]; unset($val["type"]);
          if ($type === "err") {
            $errors = array_merge($errors, $val);
            continue;
          }
          $val = $val["val"];
        } else if (in_array("fragment", $rules[$field])) {
          $val = self::check_fragment($val, $rules[$field], $field);
          $type = $val["type"]; unset($val["type"]);
          if ($type === "err") {
            $errors = array_merge($errors, $val);
            continue;
          }
        } else {
          if (! in_array("fragment", $rules[$field])) {
            $errors[] = "array.$field$prefix";
            continue;
          }
        }

      } else {
        if ($val === "" && in_array("opt", $rules[$field])) {
          // Optional field and no value, skip
          continue;
        }

        // Check if encoding is OK
        if (! self::encodingOk($val)) {
          $errors[] = "encoding.$field$prefix";
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
            $errors[] = "check.$field$prefix.$rule";
          }
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
          $errors[] = "extra.$field$prefix";
        }
      }
    }

    if (count($errors) > 0) {
      return $errors;
    }
    return $out;
  }

  public static function post_count() {
    return count(self::$post);
  }
  public static function get_count() {
    return count(self::$get);
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
  public static function raw($out, $input) {
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
