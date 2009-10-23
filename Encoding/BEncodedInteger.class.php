<?php
require_once 'BEncodingInvalidValueException.class.php';
require_once 'BEncodingParserException.class.php';
require_once 'IBEncodedValue.php';

class BEncodedInteger implements IBEncodedValue {
	private $value;
	
	public function __construct($value = null) {
		if (is_null ( $value )) {
			$this->value = null;
		} else {
			if (is_numeric ( $value )) {
				$this->value = intval ( $value );
			} else {
				throw new BEncodingInvalidValueException ( __CLASS__ . ' cannot be created with non-numeric default value (' . gettype ( $value ) . ')' );
			}
		}
	}
	
	public function getValue() {
		return $this->value;
	}
	
	public function setValue($value) {
		$this->value = $value;
	}
	
	public function fromString($bEncodedString) {
		$offset = 0;
		$this->parse ( $bEncodedString, $offset );
		if ($offset != strlen ( $bEncodedString )) {
			throw new BEncodingParserException ( 'Unknown error parsing ' . __CLASS__, $bEncodedString, $offset );
		}
	}
	
	public function tryParse($bEncodedString) {
		try {
			$this->fromString ( $bEncodedString );
		} catch ( Exception $e ) {
			return false;
		}
		return true;
	}
	
	public function parse(&$bEncodedString, &$offset) {
		if ($bEncodedString {$offset} == 'i') {
			$offset += 1;
			$delimeterPosition = strpos ( $bEncodedString, 'e', $offset );
			if ($delimeterPosition === false || $offset < $delimeterPosition) {
				$delimeterPosition -= $offset;
				$value = substr ( $bEncodedString, $offset, $delimeterPosition );
				if (is_numeric ( $value )) {
					$offset += strlen ( $value ) + 1;
					$this->value = intval ( $value );
				} else {
					throw new BEncodingParserException ( __CLASS__ . ' encountered non-numeric value', $bEncodedString, $offset );
				}
			} else {
				throw new BEncodingParserException ( __CLASS__ . ' could not locate field delimiter' );
			}
		} else {
			throw new BEncodingParserException ( __CLASS__ . ' encountered unrecognised encoding', $bEncodedString, $offset );
		}
	}
	
	public function encode() {
		return 'i' . strval ( $this->value ) . 'e';
	}
	
	public function getHashCode() {
		return spl_object_hash ( $this );
	}
	
	public function toString() {
		return $this->__toString ();
	}
	
	public function __toString() {
		return strval ( $this->value );
	}
}
?>