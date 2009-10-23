<?php
require_once 'BEncodedList.class.php';
require_once 'BEncodedDictionary.class.php';
require_once 'Tracker.class.php';
require_once 'Tracker_Torrent.class.php';
require_once 'Tracker_Request.class.php';
require_once 'Tracker_Data.class.php';

/**
 * Tracker announce request handler
 *
 */
class Tracker_AnnounceRequest extends Tracker_Request {
	private $tracker;
	
	/**
	 * ctor
	 *
	 * @param Tracker $tracker
	 */
	public function __construct(Tracker $tracker) {
		$this->tracker = $tracker;
		$this->requireParameters ( 'info_hash', 'peer_id', 'port', 'uploaded', 'downloaded', 'left' );
	}
	
	/**
	 * Overrides Tracker_Request::mapParameter
	 * Maps a request parameter to an alternate interpretation
	 *
	 * @param string $parameter
	 * @return string
	 */
	protected function mapParameter($parameter) {
		switch ($parameter) {
			case 'ip' :
				return $this->getParameter ( 'ip', $_SERVER ['REMOTE_ADDR'] );
				break;
			case 'event' :
				return $this->getParameter ( 'event', 'none' );
				break;
			case 'compact' :
				return $this->getParameter ( 'compact', false );
				break;
			case 'numwant' :
				$numWant = $this->getParameter ( 'numwant', 30 );
				return $numWant < 30 ? $numWant : 30;
				break;
			case 'passkey' :
				return $this->getParameter ( 'passkey', '' );
				break;
			default :
				return parent::mapParameter ( $parameter );
		}
	}
	
	/**
	 * Gets the tracker response
	 *
	 * @param Tracker_Torrent $torrent
	 * @return string
	 */
	public function getResponse(Tracker_Torrent $torrent) {
		
		$seeders = Tracker_Data::GetTorrentSeeds ( $torrent );
		$leechers = Tracker_Data::GetTorrentLeechers ( $torrent );
		shuffle ( $seeders );
		shuffle ( $leechers );
		
		$response = new BEncodedDictionary ( );
		
		$response ['interval'] = $this->tracker->Configuration->Tracker->AnnounceInterval;
		$response ['complete'] = count ( $seeders );
		$response ['incomplete'] = count ( $leechers );
		
		$numWant = $this->numwant;
		
		if ($this->compact) {
			$response ['peers'] = "";
			$buffer = array ();
			
			// Respond with seeders first
			for($i = 0, $j = count ( $seeders ); $i < $j && $numWant > 0; $i ++, $numWant --) {
				$buffer [] = pack ( "N", ip2long ( $seeders [$i]->ip ) );
				$buffer [] = pack ( "n", $seeders [$i]->port );
			}
			
			// Pad response with leechers	
			for($i = 0, $j = count ( $leechers ); $i < $j && $numWant > 0; $i ++, $numWant --) {
				$buffer [] = pack ( "N", ip2long ( $leechers [$i]->ip ) );
				$buffer [] = pack ( "n", $leechers [$i]->port );
			}
			
			$response ['peers'] = implode ( '', $buffer );
		
		} else {
			
			$response ['peers'] = new BEncodedList ( );
			
			//Respond with seeders first
			for($i = 0, $j = count ( $seeders ); $i < $j && $numWant > 0; $i ++, $numWant --) {
				$peerDictionary = new BEncodedDictionary ( );
				$peerDictionary ['peer id'] = $seeders [$i]->rawId;
				$peerDictionary ['ip'] = $seeders [$i]->ip;
				$peerDictionary ['port'] = $seeders [$i]->port;
				$response ['peers'] [] = $peerDictionary;
			}
			
			// Pad response with leechers
			for($i = 0, $j = count ( $leechers ); $i < $j && $numWant > 0; $i ++, $numWant --) {
				$peerDictionary = new BEncodedDictionary ( );
				$peerDictionary ['peer id'] = $leechers [$i]->rawId;
				$peerDictionary ['ip'] = $leechers [$i]->ip;
				$peerDictionary ['port'] = $leechers [$i]->port;
				$response ['peers'] [] = $peerDictionary;
			}
		}
		
		return $response->encode ();
	}
	
	/**
	 * Gets a unique hash code representing the request
	 *
	 * @return string
	 */
	public function getHashCode() {
		$hash = __CLASS__ . $this->numwant . $this->passkey . $this->info_hash;
		return md5 ( $hash );
	}
}
?>