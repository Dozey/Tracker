<?php
/**
 * abstract request handler
 *
 */
abstract class Tracker_Request {
	
	/**
	 * Checks whether the specified parameters exist for the request
	 *
	 */
	final protected function requireParameters() {
		$requiredParameters = func_get_args ();
		foreach ( $requiredParameters as $RequiredParameter ) {
			if (! array_key_exists ( $RequiredParameter, $_REQUEST )) {
				throw new Tracker_Exception ( "Missing request parameter \"$RequiredParameter\"" );
			}
		}
	}
	
	/**
	 * Maps a request parameter to an alternate interpretation
	 *
	 * @param string $parameter
	 * @return string
	 */
	protected function mapParameter($parameterName) {
		if (array_key_exists ( $parameterName, $_REQUEST )) {
			// No reliable way to count string as byte arrray in PHP
			//if(strlen(urldecode($_REQUEST[$Parameter])) == 20){
			//	return urldecode($_REQUEST[$Parameter]);
			//}else{
			//	return $_REQUEST[$Parameter];
			//}
			return $_REQUEST [$parameterName];
		} else {
			throw new Tracker_Exception ( "Missing request parameter \"$parameterName\"" );
		}
	}
	
	/**
	 * Gets the specified request parameter
	 *
	 * @param string $parameterName
	 * @param string $defaultValue
	 * @return string
	 */
	protected function getParameter($parameterName, $defaultValue = null) {
		if (array_key_exists ( $parameterName, $_REQUEST )) {
			//if(strlen(urldecode($_REQUEST[$Parameter])) == 20){
			//	return urldecode($_REQUEST[$Parameter]);
			//}else{
			//	return $_REQUEST[$Parameter];
			//}
			return $_REQUEST [$parameterName];
		} else if (! is_null ( $defaultValue )) {
			return $defaultValue;
		} else {
			throw new Tracker_Exception ( "Missing request parameter \"$parameterName\"" );
		}
	}
	
	/**
	 * Gets a hash code for a request parameter
	 *
	 * @param string $parameterName
	 * @return string
	 */
	final public function getHash($parameterName) {
		return md5 ( $this->__get ( $parameterName ) );
	}
	
	/**
	 * Overloads getter to return request parameters
	 *
	 * @param string $parameterName
	 * @return string
	 */
	final public function __get($parameterName) {
		return $this->mapParameter ( $parameterName );
	}
	
	/**
	 * Gets the request query string
	 *
	 * @return string
	 */
	final public function getQueryString() {
		return $_SERVER ['QUERY_STRING'];
	}
	
	/**
	 * Checks whether the parameter exists for the request
	 *
	 * @param string $parameterName
	 * @return bool
	 */
	final public function hasParameter($parameterName) {
		try {
			$this->mapParameter ( $parameterName );
		} catch ( Exception $e ) {
			return false;
		}
		return true;
	}
	
	/**
	 * Gets the response for the request
	 *
	 */
	public function getResponse() {
	}
	
	/**
	 * Returns a unique hash code for the request
	 *
	 */
	public function getHashCode() {
	}
}
?>
