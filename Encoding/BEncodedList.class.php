<?php
require_once 'BEncodedDictionary.class.php';
require_once 'BEncodedInteger.class.php';
require_once 'BEncodedListCollection.class.php';
require_once 'BEncodedListCollectionIterator.class.php';
require_once 'BEncodedString.class.php';
require_once 'BEncodingInvalidValueException.class.php';
require_once 'BEncodingParserException.class.php';
require_once 'IBEncodedValue.php';

class BEncodedList extends BEncodedListCollection implements IBEncodedValue {
	
	public function __construct($values = null) {
		parent::__construct ();
		if (! is_null ( $values )) {
			foreach ( $values as $value ) {
				if ($value instanceof IBEncodedValue) {
					$this->add ( $value );
				} elseif (is_numeric ( $value )) {
					$this->add ( new BEncodedInteger ( $value ) );
				} elseif (is_string ( $value )) {
					$this->add ( new BEncodedString ( $value ) );
				} elseif (is_array ( $value )) {
					if (is_numeric ( implode ( '', array_keys ( $value ) ) )) {
						$this->add ( new BEncodedList ( $value ) );
					} else {
						$this->add ( new BEncodedDictionary ( $value ) );
					}
				} else {
					throw new BEncodingInvalidValueException ( __CLASS__ . ' cannot be created with ' . gettype ( $values ) . ' as a default value' );
				}
			}
		}
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
		if ($bEncodedString {$offset} == 'l') {
			$offset += 1;
			$value = null;
			while ( (substr ( $bEncodedString, $offset, 1 ) === 'e') === false ) {
				$tmpOffset = $offset;
				switch (true) {
					case $bEncodedString {$offset} === 'd' :
						try {
							$value = new BEncodedDictionary ( );
							$value->parse ( $bEncodedString, $tmpOffset );
						} catch ( BEncodingParserException $e ) {
							throw new BEncodingParserException ( __CLASS__ . ' expected BEncodedDictionary at offset ' . $offset, $bEncodedString, $offset, $e );
						}
						$offset = $tmpOffset;
						break;
					case $bEncodedString {$offset} === 'l' :
						try {
							$value = new BEncodedList ( );
							$value->parse ( $bEncodedString, $tmpOffset );
						} catch ( BEncodingParserException $e ) {
							throw new BEncodingParserException ( __CLASS__ . ' expected BEncodedList at offset ' . $offset, $bEncodedString, $offset, $e );
						}
						$offset = $tmpOffset;
						break;
					case $bEncodedString {$offset} === 'i' :
						try {
							$value = new BEncodedInteger ( );
							$value->parse ( $bEncodedString, $tmpOffset );
						} catch ( BEncodingParserException $e ) {
							throw new BEncodingParserException ( __CLASS__ . ' expected BEncodedInteger at offset ' . $offset, $bEncodedString, $offset, $e );
						}
						$offset = $tmpOffset;
						break;
					case is_numeric ( $bEncodedString {$offset} ) :
						try {
							$value = new BEncodedString ( );
							$value->parse ( $bEncodedString, $tmpOffset );
						} catch ( BEncodingParserException $e ) {
							throw new BEncodingParserException ( __CLASS__ . ' expected BEncodedString at offset ' . $offset, $bEncodedString, $offset, $e );
						}
						$offset = $tmpOffset;
						break;
					default :
						throw new Exception ( __CLASS__ . ' encountered unexpected token: "' . $bEncodedString {$offset} );
				}
				$this [] = $value;
			}
		} else {
			throw new BEncodingParserException ( __CLASS__ . ' encountered unrecognised encoding', $bEncodedString, $offset );
		}
		$offset += 1;
	}
	
	public function encode() {
		$output = 'l';
		foreach ( $this->values as $value ) {
			$output .= $value->encode ();
		}
		$output .= 'e';
		return $output;
	}
	
	public function toArray() {
		$items = array ();
		foreach ( $this->values as $item ) {
			if ($item instanceof BEncodedList || $item instanceof BEncodedDictionary) {
				$items [] = $item->toArray ();
			} else {
				$items [] = $item->getValue ();
			}
		}
		return $items;
	}
	
	public function toString() {
		return $this->__toString ();
	}
	
	public function __toString() {
		return __CLASS__ . '[' . $this->count () . ']';
	}
}
?>