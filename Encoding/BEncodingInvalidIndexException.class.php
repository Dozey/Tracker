<?php
require_once 'BEncodingException.class.php';

final class BEncodingInvalidIndexException extends BEncodingException {
	public function __construct($message) {
		parent::__construct ( $message );
	}
}
?>