<?php
/**
 * Represents a tracker peer
 *
 */
class Tracker_Peer {
	public $id;
	public $rawId;
	public $torrentHash;
	public $ip;
	public $port;
	public $added;
	public $updated;
	public $uploaded;
	public $downloaded;
	public $left;
	
	/**
	 * ctor
	 *
	 * @param string $id
	 * @param string $rawId
	 * @param string $torrentHash
	 * @param string $ip
	 * @param int $port
	 * @param int $uploaded
	 * @param int $downloaded
	 * @param int $left
	 */
	public function __construct($id, $rawId, $torrentHash, $ip, $port, $uploaded, $downloaded, $left) {
		$this->id = $id;
		$this->rawId = $rawId;
		$this->torrentHash = $torrentHash;
		$this->ip = $ip;
		$this->port = intval ( $port );
		$this->uploaded = intval ( $uploaded );
		$this->downloaded = intval ( $downloaded );
		$this->left = intval ( $left );
	}
	
	/**
	 * Saves the peer
	 *
	 */
	public function save() {
		Tracker_Data::setPeer ( $this );
	}
	
	public function __toString() {
		return $this->id;
	}
}
?>