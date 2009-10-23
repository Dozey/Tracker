<?php
/**
 * BEncoded value
 *
 */
interface IBEncodedValue {
	/**
	 * BEncodes the value
	 *
	 */
	public function encode();
	/**
	 * Gets a unique hash representing the BEncoded value
	 *
	 */
	public function getHashCode();
	public function toString();
	public function __toString();
}
?>