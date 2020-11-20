<?php
namespace core;

/**
 * Calculate price.
 */
class EUTax
{
	/**
	* Check if $country is in SEPA.
	* @param string $country ISO2 country notation
	* @return bool inSEPA
	*/
	public static function in_sepa($country)
	{
		$sepaCountries = [
			"FI", "AT", "PT", "BE", "BG", "ES",
			"HR", "CY", "CZ", "DK", "EE", "FI",
			"FR", "GF", "DE", "GI", "GR", "GP",
			"GG", "HU", "IS", "IE", "IM", "IT",
			"JE", "LV", "LI", "LT", "LU", "PT",
			"MT", "MQ", "YT", "MC", "NL", "NO",
			"PL", "PT", "RE", "RO", "BL", "MF",
			"PM", "SM", "SK", "SI", "SE", "CH",
			"GB"
		];
		return in_array($country, $sepaCountries);
	}

	public static function rates()
	{
		return [
			"BE" => "21",
			"NL" => "21",
			"DE" => "19",
			"EE" => "20",
			"LU" => "15",
			"MT" => "18",
			"CY" => "19",
			"GB" => "20",
			"BG" => "20",
			"FR" => "20",
			"SK" => "20",
			"AT" => "20",
			"LT" => "21",
			"ES" => "21",
			"LV" => "21",
			"CZ" => "21",
			"SI" => "22",
			"IT" => "22",
			"IE" => "23",
			"PT" => "23",
			"PL" => "23",
			"GR" => "23",
			"FI" => "24",
			"RO" => "24",
			"DK" => "25",
			"SE" => "25",
			"HR" => "25",
			"HU" => "27"
		];
	}

	/** Strip tax from $price */
	public static function calc($country, $price)
	{
		$rates = self::rates();
		$rate = null;
		if (isset($rates[$country])) {
			$rate = $rates[$country];
		} else {
			$rate = "0";
		}

		$factor = bcadd("1", bcdiv($rate, "100", 3), 3);
		$ex = bcdiv($price, $factor, 3);
		$vat = bcsub($price, $ex, 3);
		return [
			"factor" => $factor,
			"total" => $price,
			"ex" => $ex,
			"vat" => $vat,
			"vat_percent" => $rate
		];
	}
}
