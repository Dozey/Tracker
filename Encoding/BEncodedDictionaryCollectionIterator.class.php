<?php
class BEncodedDictionaryCollectionIterator implements Iterator {
	private $keys;
	private $values;
	private $position;
	
	public function __construct($keys, $values) {
		$this->keys = $keys;
		$this->values = $values;
		$this->position = 0;
	}
	
	public function rewind() {
		reset ( $this->values );
	}
	
	public function current() {
		return current ( $this->values );
	}
	
	public function key() {
		return $this->keys [key ( $this->values )]->toString ();
	}
	
	public function next() {
		return next ( $this->values );
	}
	
	public function valid() {
		return $this->current () == false ? false : true;
	}
}
?>