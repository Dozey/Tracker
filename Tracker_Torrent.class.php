<?php
require_once 'Tracker_Data.class.php';

/**
 * Represents a tracker torrent
 *
 */
class Tracker_Torrent {
	public $hash;
	public $rawHash;
	public $added;
	public $updated;
	public $downloaded;
	
	/**
	 * ctor
	 *
	 * @param string $hash
	 * @param string $rawHash
	 * @param string $added
	 * @param string $updated
	 * @param int $downloaded
	 */
	public function __construct($hash, $rawHash, $added, $updated, $downloaded) {
		$this->hash = $hash;
		$this->rawHash = $rawHash;
		$this->added = $added;
		$this->updated = $updated;
		$this->downloaded = $downloaded;
	}
	
	/**
	 * Checks whether the torrent is authorised for use with the tracker
	 *
	 * @return unknown
	 */
	public function getAuthorisation() {
		return Tracker_Data::GetTorrentAuthorised ( $this->hash );
	}
	
	/**
	 * Saves the torrent
	 *
	 */
	public function save() {
		Tracker_Data::setTorrent ( $this );
	}
	
	public function __toString() {
		return $this->hash;
	}
}
?>