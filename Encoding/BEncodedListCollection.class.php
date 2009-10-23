<?php
class BEncodedListCollection implements ArrayAccess, Countable, IteratorAggregate {
	protected $values;
	
	public function __construct() {
		$this->values = array ();
	}
	
	public function getIterator() {
		return new BEncodedListCollectionIterator ( $this->values );
	}
	
	public function offsetExists($index) {
		return array_key_exists ( $index, $this->values );
	}
	
	public function offsetGet($index) {
		if ($this->offsetExists ( $index )) {
			return $this->values [$index];
		} else {
			return null;
		}
	}
	
	public function offsetSet($index, $value) {
		if (is_numeric ( $index ) || empty ( $index )) {
			if ($value instanceof IBEncodedValue || is_scalar ( $value ) || is_array ( $value )) {
				if (! empty ( $index )) {
					if ($index > ($this->count () - 1)) {
						throw new BEncodingOutOfRangeException ( 'Attempted to access out of range index ' . __CLASS__ . '[' . $index . ']' );
					}
				} elseif (empty ( $index )) {
					$index = count ( $this->values );
				}
				if ($value instanceof IBEncodedValue) {
					$this->values [$index] = $value;
				} elseif (is_scalar ( $value )) {
					if (is_numeric ( $value )) {
						$this->values [$index] = new BEncodedInteger ( $value );
					} else {
						$this->values [$index] = new BEncodedString ( $value );
					}
				} elseif (is_array ( $value )) {
					if (is_numeric ( implode ( '', array_keys ( $value ) ) )) {
						$this->values [$index] = new BEncodedList ( $value );
					} else {
						$this->values [$index] = new BEncodedDictionary ( $value );
					}
				}
			} else {
				throw new BEncodingInvalidValueException ( __CLASS__ . ' values must be scalar, arrays or an instance of IBEncodedValue, ' . gettype ( $value ) . ' supplied' );
			}
		} else {
			throw new BEncodingInvalidIndexException ( __CLASS__ . ' Indexes or Keys must be valid integers' );
		}
	}
	
	public function offsetUnset($index) {
		unset ( $this->values [$index] );
	}
	
	public function clear() {
		$this->values = array ();
	}
	
	public function add($value) {
		$this [] = $value;
	}
	
	public function count() {
		return count ( $this->values );
	}
	
	public function getHashCode() {
		return spl_object_hash ( $this );
	}
}
?>