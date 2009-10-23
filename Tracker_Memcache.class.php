<?php
/**
 * Wrapper for PHP Memcache
 *
 */
class Tracker_Memcache {
	private $memcache;
	
	/**
	 * Initialize a new Memcache instance and connect to the server
	 *
	 * @param string $hostName
	 * @param int $port
	 * @param bool $persistant
	 */
	public function __construct($hostName, $port, $persistant = false) {
		$this->memcache = new Memcache ( );
		if ($persistant) {
			if (! $this->memcache->pconnect ( $hostName, $port, 5 )) {
				throw new Tracker_Exception ( "Memcache connection failed" );
			}
		} else {
			if (! $this->memcache->connect ( $hostName, $port, 5 )) {
				throw new Tracker_Exception ( "Memcache connection failed" );
			}
		}
	}
	
	/**
	 * Gets a cached item matching key @param $key and returns a boolean value indicating success
	 *
	 * @param string $key
	 * @param string $data
	 * @return bool
	 */
	public function getCache($key, &$data) {
		$data = $this->memcache->get ( $key );
		if ($data == false) {
			return false;
		} else {
			return true;
		}
	}
	
	/**
	 * Sets a cached item with key @param $key
	 *
	 * @param string $key
	 * @param string $data
	 * @param int $lifetime
	 */
	public function setCache($key, $data, $lifetime) {
		$this->memcache->set ( $key, $data, 0, $lifetime );
	}
	
	/**
	 * Updates a cached item with key @param $key
	 *
	 * @param string $key
	 * @param string $data
	 * @param int $lifetime
	 */
	public function updateCache($key, $data, $lifetime) {
		$this->memcache->replace ( $key, $data, 0, $lifetime );
	}
}
?>