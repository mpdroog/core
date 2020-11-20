<?php
namespace core;

/**
 * Wrap array in class.
 * It's the inverse of PHPs ArrayObject.
 *
 * WARN: This class wraps array sub-items in a new
 *  instance of itself when it's an array!
 */
class ObjectArray
{
	private $_data;

	public function __construct(array $properties=[])
	{
		$this->_data = $properties;
	}

	// magic methods!
	public function __set($property, $value)
	{
		return $this->_data[$property] = $value;
	}

	public function __get($property)
	{
		$ret = null;
		if (array_key_exists($property, $this->_data)) {
			$ret = $this->_data[$property];
			if (is_array($ret)) {
				$ret = new ClassArray($ret);
			}
		}
		return $ret;
	}
}
