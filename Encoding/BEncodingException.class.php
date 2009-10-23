<?php
abstract class BEncodingException extends Exception {
	protected $innerException;
	
	public function __construct($message) {
		parent::__construct ( $message );
		$this->innerException = null;
	}
	
	public function getInnerException() {
		return $this->innerException;
	}
	
	public function __toString($tabSpace = '') {
		$exceptionText = "\n";
		$exceptionText .= $tabSpace . get_class ( $this ) . " Thrown in\n";
		$exceptionText .= $tabSpace . 'File: ' . basename ( $this->getFile () ) . "\n";
		$exceptionText .= $tabSpace . 'Line: ' . $this->getLine () . "\n";
		$exceptionText .= $tabSpace . $this->getMessage () . "\n";
		$exceptionText .= "\n";
		if (is_null ( $this->innerException ) === false) {
			$exceptionText .= "Inner Exception\n";
			$exceptionText .= $this->innerException->__toString ( $tabSpace . "\t\t" );
			$exceptionText .= "\n\n";
		}
		foreach ( $this->getTrace () as $stackTrace ) {
			$exceptionText .= $tabSpace . basename ( $stackTrace ['file'] ) . ': ' . $stackTrace ['line'] . "\n";
			$exceptionText .= $tabSpace . '  ';
			if (isset ( $stackTrace ['class'] )) {
				$exceptionText .= $stackTrace ['class'] . '::';
			}
			$exceptionText .= $stackTrace ['function'] . '(' . implode ( ', ', $stackTrace ['args'] ) . ")\n";
		}
		return $exceptionText;
	}
}
?>