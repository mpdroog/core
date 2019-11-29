<?php
namespace core;
class Numeric {
    /** like ceil() only with the possibility to set significance
     * https://www.php.net/manual/en/function.ceil.php#85430
     **/
    public static function ceiling($number, $significance = 1) {
        return ( is_numeric($number) && is_numeric($significance) ) ? (ceil($number/$significance)*$significance) : false;
    }
}
