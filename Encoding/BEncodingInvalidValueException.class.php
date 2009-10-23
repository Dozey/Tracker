<?php
require_once 'BEncodingException.class.php';

final class BEncodingInvalidValueException extends BEncodingException {
	public function __construct($message) {
		parent::__construct ( $message );
	}
}
?>