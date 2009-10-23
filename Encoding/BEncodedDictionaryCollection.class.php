<?php
class BEncodedDictionaryCollection implements ArrayAccess, Countable, IteratorAggregate {
	/**
	 * Collection of Keys for associative dictionary
	 * These are stored in string format rather than native BEncodedStrings due to php shortfallings
	 *
	 * @var string[]
	 */
	protected $keys;
	/**
	 * Collection of Values for associative dictionary
	 *
	 * @var IBEncodedValue[]
	 */
	protected $values;
	
	/**
	 * Initialise Key Value collections
	 *
	 */
	public function __construct() {
		$this->keys = array ();
		$this->values = array ();
	}
	
	/**
	 * Implementation of IteratorAggregate
	 *
	 * @return BEncodedDictionaryCollectionIterator
	 */
	public function getIterator() {
		return new BEncodedDictionaryCollectionIterator ( $this->keys, $this->values );
	}
	
	/**
	 * Implementation of ArrayAccess
	 *
	 * @param mixed $Index
	 * @return bool
	 */
	public function offsetExists($Index) {
		return array_key_exists ( $this->getIndexHash ( $Index ), $this->keys );
	}
	
	/**
	 * Implementation of ArrayAccess
	 *
	 * @param mixed $Index
	 * @return object
	 */
	public function offsetGet($Index) {
		if ($this->offsetExists ( $Index )) {
			return $this->values [$this->getIndexHash ( $Index )];
		} else {
			return null;
		}
	}
	
	/**
	 * Implementation of ArrayAccess
	 * This method has been modified to accept untyped input, such as (int), (string) and cast to the respective IBEncodedValue type
	 * Keys must be scalar or an instance of BEncodedString
	 * The collection will automatically be sorted upon any call to this method unless an exception is thrown
	 *
	 * @example offsetSet('someKey', 'stringValue');
	 * @example offsetSet('intVal', 1);
	 * @example offsetSet(new BEncodedString('Integer'), new BEncodedInteger(6969));
	 * @example offsetSet(new BEncodedString('key'), new BEncodedList(array(1,2,3));
	 * 
	 * @param mixed $Index
	 * @param mixed $Value
	 */
	public function offsetSet($index, $value) {
		if ($value instanceof IBEncodedValue || is_scalar ( $value ) || is_array ( $value )) {
			$hash = $this->getIndexHash ( $index );
			if ($index instanceof BEncodedString) {
				$this->keys [$hash] = $index;
			} else {
				$this->keys [$hash] = new BEncodedString ( $index );
			}
			if ($value instanceof IBEncodedValue) {
				$this->values [$hash] = $value;
			} elseif (is_scalar ( $value )) {
				if (is_numeric ( $value )) {
					$this->values [$hash] = new BEncodedInteger ( $value );
				} else {
					$this->values [$hash] = new BEncodedString ( $value );
				}
			} elseif (is_array ( $value )) {
				if (is_numeric ( implode ( '', array_keys ( $value ) ) )) {
					$this->values [$hash] = new BEncodedList ( $value );
				} else {
					$this->values [$hash] = new BEncodedDictionary ( $value );
				}
			}
		} else {
			throw new BEncodingInvalidValueException ( __CLASS__ . ' values must be scalar, arrays or an instance of IBEncodedValue, ' . gettype ( $value ) . ' supplied' );
		}
		array_multisort ( $this->keys, SORT_ASC, SORT_STRING, $this->values );
	}
	
	/**
	 * Implementation of ArrayAccess
	 * The collection will automatically be sorted upon any call to this method
	 * 
	 * @param mixed $Index
	 */
	public function offsetUnset($index) {
		$hash = $this->getIndexHash ( $index );
		unset ( $this->keys [$hash] );
		unset ( $this->values [$hash] );
		array_multisort ( $this->keys, SORT_ASC, SORT_STRING, $this->values );
	}
	
	/**
	 * Clears the collection
	 *
	 */
	public function clear() {
		$this->keys = array ();
		$this->values = array ();
	}
	
	/**
	 * Helper method to add values
	 * Wrapper for @see BEncodedDictionaryCollection::offsetSet()
	 *
	 * @param unknown_type $Index
	 * @param unknown_type $Value
	 */
	public function add($index, $value) {
		$this->offsetSet ( $index, $value );
	}
	
	/**
	 * Implementation of Countable
	 * Returns the number of values in this colleciton
	 *
	 * @return int
	 */
	public function count() {
		return count ( $this->values );
	}
	
	/**
	 * Implementation of IGenericObject
	 * Return a unique hash for this object
	 * 
	 * @return string
	 */
	public function getHashCode() {
		return spl_object_hash ( $this );
	}
	
	/**
	 * Generate the hash of an index value
	 *
	 * 
	 * @param mixed $Index
	 * @return string
	 */
	private function getIndexHash($index) {
		if (is_scalar ( $index )) {
			return md5 ( $index );
		} elseif ($index instanceof BEncodedString) {
			return $index->getHashCode ();
		} else {
			throw new BEncodingInvalidIndexException ( __CLASS__ . ' Indexes or Keys must be scalar or an instance of BEncodedString' );
		}
	}
}
?>