<?php
require_once 'BEncodedDictionaryCollection.class.php';
require_once 'BEncodedDictionaryCollectionIterator.class.php';
require_once 'BEncodedInteger.class.php';
require_once 'BEncodedList.class.php';
require_once 'BEncodedString.class.php';
require_once 'BEncodingParserException.class.php';
require_once 'IBEncodedValue.php';

class BEncodedDictionary extends BEncodedDictionaryCollection implements IBEncodedValue {
	
	public function __construct($values = null) {
		parent::__construct ();
		if (! is_null ( $values )) {
			foreach ( $values as $key => $value ) {
				$key = new BEncodedString ( $key );
				if ($value instanceof IBEncodedValue) {
					$this->add ( $key, $value );
				} elseif (is_scalar ( $value )) {
					if (is_numeric ( $value )) {
						$this->add ( $key, new BEncodedInteger ( $value ) );
					} else {
						$this->add ( $key, new BEncodedString ( $value ) );
					}
				} elseif (is_array ( $value )) {
					if (is_numeric ( implode ( '', array_keys ( $value ) ) )) {
						$this->add ( $key, new BEncodedList ( $value ) );
					} else {
						$this->add ( $key, new BEncodedDictionary ( $value ) );
					}
				} else {
					throw new Exception ( 'Unable to parse non-BEncoded value' );
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
		if (strlen ( $bEncodedString ) > 0) {
			if ($bEncodedString {$offset} == 'd') {
				$offset += 1;
				$key = null;
				$value = null;
				while ( (substr ( $bEncodedString, $offset, 1 ) === 'e') === false ) {
					$tmpOffset = $offset;
					try {
						$key = new BEncodedString ( );
						$key->parse ( $bEncodedString, $tmpOffset );
					} catch ( BEncodingParserException $e ) {
						throw new BEncodingParserException ( __CLASS__ . ' expected BEncodedString Index or Key at offset ' . $offset, $bEncodedString, $offset, $e );
					}
					$offset = $tmpOffset;
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
					$this [$key] = $value;
				}
				$offset += 1;
			} else {
				throw new BEncodingParserException ( __CLASS__ . ' encountered unrecognised encoding', $bEncodedString, $offset );
			}
		}
	}
	
	public function encode() {
		$output = 'd';
		foreach ( $this->values as $hash => $value ) {
			$output .= $this->keys [$hash]->encode ();
			$output .= $value->encode ();
		}
		$output .= 'e';
		return $output;
	}
	
	public function toArray() {
		$items = array ();
		foreach ( $this->values as $hash => $item ) {
			if ($item instanceof BEncodedList || $item instanceof BEncodedDictionary) {
				$items [$this->keys [$hash]->toString ()] = $item->toArray ();
			} else {
				$items [$this->keys [$hash]->toString ()] = $item->getValue ();
			}
		}
		return $items;
	}
	
	public function toString() {
		return $this->__toString ();
	}
	
	public function __toString() {
		return __CLASS__ . '[' . count ( $this ) . ']';
	}
}
?>