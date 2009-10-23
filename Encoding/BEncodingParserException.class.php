<?php
final class BEncodingParserException extends BEncodingException {
	private $bEncodedString;
	private $offset;
	
	public function __construct($nessage, $bEncodedString = null, $offset = null, Exception $innerException = null) {
		parent::__construct ( $nessage );
		$this->bEncodedString = $bEncodedString;
		$this->offset = $offset;
		$this->innerException = $innerException;
	}
}
?>