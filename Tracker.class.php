<?php
require_once 'Tracker_Configuration.class.php';
require_once 'Tracker_AnnounceRequest.class.php';
require_once 'Tracker_ScrapeRequest.class.php';
require_once 'Tracker_Data.class.php';
require_once 'Tracker_Memcache.class.php';
require_once 'Tracker_Exception.class.php';
require_once 'Tracker_Peer.class.php';
require_once 'Tracker_Torrent.class.php';

/**
 * Bittorrent Tracker
 *
 */
class Tracker {
	/**
	 * Tracker Configuration object
	 *
	 * @var Tracker_Configuration
	 */
	public $configuration;
	/**
	 * Tracker Cache object
	 *
	 * @var Tracker_Cache
	 */
	private $cache;
	
	/**
	 * Ctor
	 *
	 * @param string $IniPath
	 */
	public function __construct($iniPath) {
		// Load Tracker_Configuration
		$this->configuration = new Tracker_Configuration ( $iniPath );
		
		// Set up Cache and Database connections
		if ($this->configuration->Database->Persistant) {
			if (! mysql_pconnect ( $this->configuration->Database->Hostname, $this->configuration->Database->Username, $this->configuration->Database->Password )) {
				throw new Tracker_Exception ( "Database connection failed" );
			}
			if (! mysql_select_db ( $this->configuration->Database->Database )) {
				throw new Tracker_Exception ( "Database selection failed" );
			}
		} else {
			if (! mysql_connect ( $this->configuration->Database->Hostname, $this->configuration->Database->Username, $this->configuration->Database->Password )) {
				throw new Tracker_Exception ( "Database connection failed" );
			}
			if (! mysql_select_db ( $this->configuration->Database->Database )) {
				throw new Tracker_Exception ( "Database selection failed" );
			}
		}
		if ($this->configuration->Memcache->Enabled) {
			$this->cache = new Tracker_Memcache ( $this->configuration->Memcache->Hostname, $this->configuration->Memcache->Port, $this->configuration->Memcache->Persistant );
		} else {
			$this->cache = null;
		}
	}
	
	/**
	 * Announce request handler
	 *
	 */
	public function announce() {
		if (! $this->configuration->Tracker->Online) {
			throw new Exception ( 'Tracker offline!' );
		}
		// Tracker_AnnounceRequest object, self populating
		$announceRequest = new Tracker_AnnounceRequest ( $this );
		
		$safeInfoHash = $announceRequest->getHash ( 'info_hash' );
		$safePeerId = $announceRequest->getHash ( 'peer_id' );
		
		// Check if the torrent announced actually exists
		if (Tracker_Data::getTorrentExists ( $safeInfoHash )) {
			$torrent = Tracker_Data::getTorrent ( $safeInfoHash );
		} else {
			// If not, create a new one
			$torrent = new Tracker_Torrent ( $safeInfoHash, $announceRequest->info_hash, null, null, 0, 0, 0, 0, 0, 0 );
		}
		
		// Allow torrents not in the network specified by tracker configuration "TrustedHosts"?
		if (! $this->configuration->Tracker->AllowAnonymous) {
			// Authorisation is required to announce for this torrent
			if (! $torrent->getAuthorisation ()) {
				// Torrent is not current authorised
				if (in_array ( $announceRequest->ip, $this->configuration->Tracker->getDelimited ( 'TrustedHosts' ) )) {
					// Torrent is present in trusted network, set authorisation
					Tracker_Data::setTorrentAuthorised ( $safeInfoHash );
				} else {
					// Torrent is not present in network, deny request
					throw new Tracker_Exception ( "Unregistered torrent" );
				}
			}
		}
		
		// Check if we are already tracking this peer and populate Tracker_Peer object if so
		if (Tracker_Data::getPeerExists ( $safePeerId, $safeInfoHash )) {
			$peer = Tracker_Data::GetPeer ( $safePeerId, $safeInfoHash );
			$peer->ip = $announceRequest->ip;
			$peer->port = $announceRequest->port;
			$peer->uploaded = $announceRequest->uploaded;
			$peer->downloaded = $announceRequest->downloaded;
			$peer->left = $announceRequest->left;
		} else {
			// Peer does not exist, create new Tracker_Peer object
			$peer = new Tracker_Peer ( $safePeerId, $announceRequest->peer_id, $safeInfoHash, $announceRequest->ip, $announceRequest->port, $announceRequest->uploaded, $announceRequest->downloaded, $announceRequest->left );
		}
		
		// There may have been an event passed with this request
		switch ($announceRequest->event) {
			case 'stopped' :
				// Client has gracefully stopped their download, remove them from the peer list
				Tracker_Data::removePeer ( $peer );
				break;
			case 'completed' :
				// Client has completed their download, incriment Tracker_Torrent download count
				if ($peer->left == 0) {
					$torrent->downloaded ++;
					$torrent->save ();
				}
				$peer->save ();
				break;
			default :
				$peer->save ();
		}
		// It is necessary to save Tracker_Peer data above to log stats etc.
		

		if ($this->configuration->Memcache->Enabled) {
			$cachedRequest = null;
			
			// Attempt to fetch cached copy of response
			if ($this->cache->getCache ( $announceRequest->getHashCode (), $cachedRequest )) {
				echo $cachedRequest;
			} else {
				// Cache request
				$response = $announceRequest->getResponse ( $torrent );
				$this->cache->setCache ( $announceRequest->getHashCode (), $response, $this->configuration->Tracker->CacheLifeTime );
				echo $response;
			}
		} else {
			echo $announceRequest->getResponse ( $torrent );
		}
		$torrent->save ();
	}
	
	/**
	 * Scrape request handler
	 *
	 */
	public function scrape() {
		if (! $this->configuration->Tracker->Online) {
			throw new Exception ( 'Tracker offline!' );
		}
		
		$scrapeRequest = new Tracker_ScrapeRequest ( $this );
		$safeInfoHash = is_scalar ( $scrapeRequest->info_hash ) ? $scrapeRequest->getHash ( 'info_hash' ) : md5 ( implode ( null, $scrapeRequest->info_hash ) );
		
		if (! $this->configuration->Tracker->AllowAnonymous) {
			if (is_scalar ( $scrapeRequest->info_hash )) {
				if (! Tracker_Data::getTorrentAuthorised ( $safeInfoHash )) {
					throw new Tracker_Exception ( "Unregistered torrent" );
				}
			}
		}
		
		if (is_scalar ( $scrapeRequest->info_hash )) {
			if (! Tracker_Data::getTorrentExists ( $safeInfoHash )) {
				$torrent = new Tracker_Torrent ( $safeInfoHash, $scrapeRequest->info_hash, null, null, 0, 0, 0, 0, 0, 0 );
				$torrent->Save ();
			}
		} else {
			foreach ( $scrapeRequest->info_hash as $hash ) {
				$safeHash = md5 ( $hash );
				if (! Tracker_Data::getTorrentExists ( $safeHash )) {
					$torrent = new Tracker_Torrent ( $safeHash, $hash, null, null, 0, 0, 0, 0, 0, 0 );
					$torrent->Save ();
				}
			}
		}
		
		if ($this->configuration->Memcache->Enabled) {
			$cachedRequest = null;
			
			if ($this->cache->getCache ( $scrapeRequest->getHashCode (), $cachedRequest )) {
				echo $cachedRequest;
			} else {
				$response = $scrapeRequest->GetResponse ();
				$this->cache->setCache ( $scrapeRequest->getHashCode (), $response, $this->configuration->Tracker->CacheLifeTime );
				echo $response;
			}
		} else {
			echo $scrapeRequest->getResponse ();
		}
	}
	
	/**
	 * Updates the tracker
	 *
	 */
	public function Update() {
		Tracker_Data::update ( $this->configuration->Tracker->TorrentLifetime, $this->configuration->Tracker->PeerLifetime );
	}
}
?>