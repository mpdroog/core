<?php
namespace core;

use Defuse\Crypto\Key;
use Defuse\Crypto\Crypto;

/**
 * Safe
 */
class Safe
{
	/**
	 * Encode a string.
	 */
	public static function encode($value, $privKey)
	{
		$key = Key::loadFromAsciiSafeString($privKey);
		return base64_encode(Crypto::encrypt($value, $key, true));
	}

	/**
	 * Decode a string.
	 */
	public static function decode($value, $privKey)
	{
		$key = Key::loadFromAsciiSafeString($privKey);
		try {
			return Crypto::decrypt(base64_decode($value), $key, true);
		} catch (\Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException $ex) {
			return false;
		}
	}
}
