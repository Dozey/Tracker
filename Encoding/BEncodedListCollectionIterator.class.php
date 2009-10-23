<?php
class BEncodedListCollectionIterator implements Iterator {
	private $values;
	
	public function __construct($values) {
		$this->values = $values;
	}
	
	public function rewind() {
		reset ( $this->values );
	}
	
	public function current() {
		return current ( $this->values );
	}
	
	public function key() {
		return key ( $this->values );
	}
	
	public function next() {
		return next ( $this->values );
	}
	
	public function valid() {
		return $this->current () == false ? false : true;
	}
}
?>