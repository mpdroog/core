<?php
namespace core;

class Numeric
{
        /**
         * Calculate amount of decimals we want to show.
         * You can forward this function result to the round/number_format funcs
         *
         * i.e. 5000.5555   = 5000.56
         *      0.000055555 = 0.000056
         */
        public static function calc_decimals($val) {
                $val = strval($val);
                $first_zero = $val[0] === "0";

                if (! $first_zero) {
                        // 3. 30. 300. 3000. etc
                        return 2;
                }

                $dot = false;
                for ($i = 0; $i < strlen($val); $i++) {
                        $c = $val[$i];
                        if ($c === ".") {
                                $dot = true;
                                continue;
                        }

                        $is_nonzero = $c !== "0";
                        if ($dot && $is_nonzero) {
                                break;
                        }
                }
                // 0. 0.0 0.00 0.000
                return $i;
        }

	/** like ceil() only with the possibility to set significance
	 * https://www.php.net/manual/en/function.ceil.php#85430
	 **/
	public static function ceiling($number, $significance = 1)
	{
		return (is_numeric($number) && is_numeric($significance)) ? (ceil($number/$significance)*$significance) : false;
	}
}
