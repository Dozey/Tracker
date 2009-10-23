<?php
/**
 * Tracker_ConfigurationSection base class
 *
 */
abstract class Tracker_ConfigurationSectionBase {
	private $values;
	
	/**
	 * Populates the instance with a dictionary of values represented by the Tracker_ConfigurationSection
	 *
	 * @param array $values
	 */
	public function __construct(array $values) {
		$this->values = $values;
	}
	
	/**
	 * Overloads getter to access INI sections
	 *
	 * @param string $key
	 * @return string
	 */
	final public function __get($key) {
		if (array_key_exists ( $key, $this->values )) {
			return $this->values [$key];
		} else {
			throw new Exception ( 'Class ' . get_class ( $this ) . ' does not contain configuration key \'' . $key . '\'' );
		}
	}
	
	/**
	 * Returns a delimited value as an array
	 *
	 * @param string $key
	 * @param string $delimiter
	 * @param bool $skipEmpty
	 * @param bool $trim
	 * @return array
	 */
	final public function getDelimited($key, $delimiter = ',', $skipEmpty = true, $trim = true) {
		$canonicalValue = $this->__get ( $key );
		$values = explode ( $delimiter, $canonicalValue );
		if ($skipEmpty) {
			$buffer = array ();
			foreach ( $values as $value ) {
				if ($trim) {
					$value = trim ( $value );
				}
				if (! empty ( $value )) {
					$buffer [] = $value;
				}
			}
			return $buffer;
		} elseif ($trim) {
			$buffer = array ();
			foreach ( $values as $value ) {
				$buffer [] = trim ( $value );
			}
			return $buffer;
		} else {
			return $values;
		}
	}
	
	/**
	 * Loads an dictionary of additional values into this instance
	 *
	 * @param array $values
	 */
	public function Load(array $values) {
		$this->values = array_merge ( $this->values, $values );
	}
}
?>