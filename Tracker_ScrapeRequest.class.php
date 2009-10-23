<?php
require_once 'BEncodedDictionary.class.php';
require_once 'Tracker.class.php';
require_once 'Tracker_Request.class.php';
require_once 'Tracker_Data.class.php';

/**
 * Tracker scrape request handler
 *
 */
class Tracker_ScrapeRequest extends Tracker_Request {
	private $tracker;
	
	/**
	 * ctor
	 *
	 * @param Tracker $tracker
	 */
	public function __construct(Tracker $tracker) {
		$this->tracker = $tracker;
		//$this->RequireParameters('info_hash');
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
			case 'info_hash' :
				$matches = array ();
				preg_match_all ( '/info_hash=(.+?)(?:&|\\z){1}/', $_SERVER ['QUERY_STRING'], $matches );
				for($i = 0; $i < count ( $matches [1] ); $i ++) {
					if (strlen ( $matches [1] [$i] ) != 20) {
						$matches [1] [$i] = urldecode ( $matches [1] [$i] );
					}
				}
				return $matches [1];
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
	public function getResponse() {
		$response = new BEncodedDictionary ( );
		$response ['files'] = new BEncodedDictionary ( );
		
		$infoHash = $this->info_hash;
		
		if (is_scalar ( $infoHash )) {
			$safeInfoHash = $this->getHash ( 'info_hash' );
			
			if (Tracker_Data::GetTorrentExists ( $safeInfoHash )) {
				$torrent = Tracker_Data::GetTorrent ( $safeInfoHash );
				
				$response ['files'] [$infoHash] = new BEncodedDictionary ( );
				$response ['files'] [$infoHash] ['complete'] = Tracker_Data::getTorrentSeedCount ( $torrent );
				$response ['files'] [$infoHash] ['downloaded'] = $torrent->downloaded;
				$response ['files'] [$infoHash] ['incomplete'] = Tracker_Data::getTorrentLeechCount ( $torrent );
			} else {
				$response ['files'] [$infoHash] = new BEncodedDictionary ( );
				$response ['files'] [$infoHash] ['complete'] = 0;
				$response ['files'] [$infoHash] ['downloaded'] = 0;
				$response ['files'] [$infoHash] ['incomplete'] = 0;
			}
		} else {
			if (count ( $infoHash ) > 0) {
				foreach ( $infoHash as $hash ) {
					$safeInfoHash = md5 ( $hash );
					$response ['files'] [$hash] = new BEncodedDictionary ( );
					
					if (Tracker_Data::GetTorrentExists ( $safeInfoHash )) {
						$torrent = Tracker_Data::GetTorrent ( $safeInfoHash );
						
						$response ['files'] [$hash] = new BEncodedDictionary ( );
						$response ['files'] [$hash] ['complete'] = Tracker_Data::getTorrentSeedCount ( $torrent );
						$response ['files'] [$hash] ['downloaded'] = $torrent->downloaded;
						$response ['files'] [$hash] ['incomplete'] = Tracker_Data::getTorrentLeechCount ( $torrent );
					} else {
						$response ['files'] [$hash] = new BEncodedDictionary ( );
						$response ['files'] [$hash] ['complete'] = 0;
						$response ['files'] [$hash] ['downloaded'] = 0;
						$response ['files'] [$hash] ['incomplete'] = 0;
					}
				}
			} else if ($this->tracker->Configuration->Tracker->AllowFullScrape) {
				$torrents = Tracker_Data::GetTorrents ();
				$buffer = array ();
				
				foreach ( $torrents as $torrent ) {
					if (! $this->tracker->Configuration->Tracker->AllowAnonymous && ! $torrent->getAuthorisation ()) {
						continue;
					}
					
					// Optimise response generation for heap
					$ResponseBuffer = new BEncodedDictionary ( );
					$ResponseBuffer ['complete'] = Tracker_Data::getTorrentSeedCount ( $torrent );
					$ResponseBuffer ['downloaded'] = $torrent->downloaded;
					$ResponseBuffer ['incomplete'] = Tracker_Data::getTorrentLeechCount ( $torrent );
					$buffer [] = strlen ( $torrent->rawHash ) . ':' . $torrent->rawHash . $ResponseBuffer->encode ();
					
				// Removed to optimise out stack usage
				//$Response['files'][$Torrent->RawHash] = new BEncodedDictionary();
				//$Response['files'][$Torrent->RawHash]['complete'] = Tracker_Data::getTorrentSeedCount($torrent);
				//$Response['files'][$Torrent->RawHash]['downloaded'] = $Torrent->downloaded;
				//$Response['files'][$Torrent->RawHash]['incomplete'] = Tracker_Data::getTorrentLeechCount($torrent);
				}
				
				// Faster than $Response->Encode() on the stack - Research possibility of value types on the heap
				return 'd5:filesd' . implode ( '', $buffer ) . 'ee';
			} else {
				throw new Exception ( 'Full scrape is not permitted' );
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
		$hash = __CLASS__ . $this->passkey;
		if (is_array ( $this->info_hash )) {
			$hash .= implode ( null, $this->info_hash );
		} else {
			$hash .= $this->info_hash;
		}
		return md5 ( $hash );
	}
}
?>
