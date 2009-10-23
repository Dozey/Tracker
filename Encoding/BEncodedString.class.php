<?php
require_once 'BEncodingInvalidValueException.class.php';
require_once 'BEncodingParserException.class.php';
require_once 'BEncodingParserException.class.php';

class BEncodedString implements IBEncodedValue {
	private $value;
	
	public function __construct($value = null) {
		if (is_null ( $value )) {
			$this->value = '';
		} else {
			if (is_scalar ( $value )) {
				$this->value = strval ( $value );
			} else {
				throw new BEncodingInvalidValueException ( 'BEncodedString cannot be created with non-scalar default value (' . gettype ( $value ) . ')' );
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
		$lengthDelimeterPosition = strpos ( $bEncodedString, ':', $offset );
		if ($lengthDelimeterPosition === false || $offset < $lengthDelimeterPosition) {
			$lengthDelimeterPosition -= $offset;
			$length = substr ( $bEncodedString, $offset, $lengthDelimeterPosition );
			if (is_numeric ( $length )) {
				$offset += strlen ( $length ) + 1;
				$length = intval ( $length );
				$this->value = strval ( substr ( $bEncodedString, $offset, $length ) );
				$offset += $length;
			} else {
				throw new BEncodingParserException ( __CLASS__ . ' encountered unrecognised encoding', $bEncodedString, $offset );
			}
		} else {
			throw new BEncodingParserException ( __CLASS__ . ' could not find length delimiter' );
		}
	}
	
	public function encode() {
		return strlen ( $this->value ) . ':' . $this->value;
	}
	
	public function getHashCode() {
		return md5 ( $this->value );
	}
	
	public function toString() {
		return $this->__toString ();
	}
	
	public function __toString() {
		return $this->value;
	}
}
?>