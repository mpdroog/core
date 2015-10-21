<?php
namespace core;

class AES {
	/**
	* Encode to be embedded in URLs.
	*/
	public static function encode_url($value, $privkey) {
		return urlencode(self::encode($value, $privkey));
	}

	/**
	* Encode a string with the project's AES key.
	*/
	public static function encode($value, $privKey) {
		return \base64_encode(\mcrypt_encrypt(
			MCRYPT_RIJNDAEL_256,
			$privKey,
			$value,
			MCRYPT_MODE_ECB
		));
	}

	/**
	* Decode a string with the project's AES key.
	*/
	public static function decode($value, $privKey) {
		return \trim(\mcrypt_decrypt(
			MCRYPT_RIJNDAEL_256,
			$privKey,
			\base64_decode($value),
			MCRYPT_MODE_ECB
		));
	}
}