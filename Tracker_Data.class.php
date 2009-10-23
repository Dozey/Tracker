<?php
/**
 * Database layer
 *
 */
class Tracker_Data {
	
	/**
	 * Fetches the torrent with hash @param $torrentHash
	 *
	 * @param string $torrentHash
	 * @return Tracker_Torrent
	 */
	public static function getTorrent($torrentHash) {
		$result = mysql_query ( "SELECT * FROM `Torrents` WHERE `Torrents_Hash` = '" . mysql_real_escape_string ( $torrentHash ) . "' LIMIT 1" );
		if (! $result) {
			throw new Tracker_Exception ( mysql_error () );
		}
		if (mysql_num_rows ( $result ) == 1) {
			$row = mysql_fetch_object ( $result );
			return new Tracker_Torrent ( $row->Torrents_Hash, $row->Torrents_RawHash, $row->Torrents_Added, $row->Torrents_Updated, $row->Torrents_Downloaded, $row->Torrents_Double1, $row->Torrents_Double2, $row->Torrents_Double3, $row->Torrents_Double4, $row->Torrents_Long1 );
		} else {
			throw new Tracker_Exception ( "Torrent does not exist" );
		}
	}
	
	/**
	 * Checks whether the torrent with hash @param $torrentHash is associated with the tracker
	 *
	 * @param string $torrentHash
	 * @return bool
	 */
	public static function getTorrentExists($torrentHash) {
		$result = mysql_query ( "SELECT * FROM `Torrents` WHERE `Torrents_Hash` = '" . mysql_real_escape_string ( $torrentHash ) . "' LIMIT 1" );
		if (! $result) {
			throw new Tracker_Exception ( mysql_error () );
		}
		if (mysql_num_rows ( $result ) == 1) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Fetches all torrents associated with the tracker
	 *
	 * @return Tracker_Torrent[]
	 */
	public static function getTorrents() {
		$torrents = array ();
		$result = mysql_query ( "SELECT * FROM `Torrents`" );
		if (! $result) {
			throw new Tracker_Exception ( mysql_error () );
		}
		if (mysql_num_rows ( $result ) > 0) {
			while ( $row = mysql_fetch_object ( $result ) ) {
				$torrents [] = new Tracker_Torrent ( $row->Torrents_Hash, $row->Torrents_RawHash, $row->Torrents_Added, $row->Torrents_Updated, $row->Torrents_Downloaded, $row->Torrents_Double1, $row->Torrents_Double2, $row->Torrents_Double3, $row->Torrents_Double4, $row->Torrents_Long1 );
			}
		}
		return $torrents;
	}
	
	/**
	 * Fetches all peers currently associated with the torrent @param $torrent
	 *
	 * @param Tracker_Torrent $torrent
	 * @return unknown
	 */
	public static function getTorrentPeers(Tracker_Torrent $torrent) {
		$peers = array ();
		$result = mysql_query ( "SELECT * FROM `Peers` WHERE `Torrents_Hash` = '" . mysql_real_escape_string ( $torrent->Hash ) . "'" );
		if (! $result) {
			throw new Tracker_Exception ( mysql_error () );
		}
		if (mysql_num_rows ( $result ) > 0) {
			while ( $row = mysql_fetch_object ( $result ) ) {
				$peers [] = new Tracker_Peer ( $row->Peers_Id, $row->Peers_RawId, $row->Torrents_Hash, long2ip ( $row->Peers_Ip ), $row->Peers_Port, $row->Peers_Uploaded, $row->Peers_Downloaded, $row->Peers_Left );
			}
		}
		return $peers;
	}
	
	/**
	 * Fetches all peers currently seeding the torrent @param $torrent
	 *
	 * @param Tracker_Torrent $torrent
	 * @return Tracker_Peer[]
	 */
	public static function getTorrentSeeds(Tracker_Torrent $torrent) {
		$seeders = array ();
		$result = mysql_query ( "SELECT * FROM `Peers` WHERE `Torrents_Hash` = '" . mysql_real_escape_string ( $torrent->Hash ) . "' AND `Peers_Left` = 0" );
		if (! $result) {
			throw new Tracker_Exception ( mysql_error () );
		}
		if (mysql_num_rows ( $result ) > 0) {
			while ( $row = mysql_fetch_object ( $result ) ) {
				$seeders [] = new Tracker_Peer ( $row->Peers_Id, $row->Peers_RawId, $row->Torrents_Hash, long2ip ( $row->Peers_Ip ), $row->Peers_Port, $row->Peers_Uploaded, $row->Peers_Downloaded, $row->Peers_Left );
			}
		}
		return $seeders;
	}
	
	/**
	 * Fetches all peers currently leeching the torrent @param $torrent
	 *
	 * @param Tracker_Torrent $torrent
	 * @return Tracker_Peer[]
	 */
	public static function getTorrentLeechers(Tracker_Torrent $torrent) {
		$leechers = array ();
		$result = mysql_query ( "SELECT * FROM `Peers` WHERE `Torrents_Hash` = '" . mysql_real_escape_string ( $torrent->Hash ) . "' AND `Peers_Left` > 0" );
		if (! $result) {
			throw new Tracker_Exception ( mysql_error () );
		}
		if (mysql_num_rows ( $result ) > 0) {
			while ( $row = mysql_fetch_object ( $result ) ) {
				$leechers [] = new Tracker_Peer ( $row->Peers_Id, $row->Peers_RawId, $row->Torrents_Hash, long2ip ( $row->Peers_Ip ), $row->Peers_Port, $row->Peers_Uploaded, $row->Peers_Downloaded, $row->Peers_Left );
			}
		}
		return $leechers;
	}
	
	/**
	 * Counts how mayn torrents are currently associated with the tracker
	 *
	 * @return int
	 */
	public static function getTorrentCount() {
		$result = mysql_query ( "SELECT Count(*) as `TorrentCount` FROM `Torrents`" );
		if (! $result) {
			throw new Tracker_Exception ( mysql_error () );
		}
		if (mysql_num_rows ( $result ) > 0) {
			$row = mysql_fetch_object ( $result );
			return $row->TorrentCount;
		} else {
			return 0;
		}
	}
	
	/**
	 * Gets the number of peers currently associated with the torrent @param $torrent
	 *
	 * @param Tracker_Torrent $torrent
	 * @return unknown
	 */
	public static function getTorrentPeerCount(Tracker_Torrent $torrent) {
		$result = mysql_query ( "SELECT Count(*) as `PeerCount` FROM `Peers` WHERE `Torrents_Hash` = '" . mysql_real_escape_string ( $torrent->Hash ) . "'" );
		if (! $result) {
			throw new Tracker_Exception ( mysql_error () );
		}
		if (mysql_num_rows ( $result ) > 0) {
			$row = mysql_fetch_object ( $result );
			return $row->PeerCount;
		} else {
			return 0;
		}
	}
	
	/**
	 * Gets the number of peers currently seeding the torrent @param $torrent
	 *
	 * @param Tracker_Torrent $torrent
	 * @return int
	 */
	public static function getTorrentSeedCount(Tracker_Torrent $torrent) {
		$result = mysql_query ( "SELECT Count(*) as `PeerCount` FROM `Peers` WHERE `Torrents_Hash` = '" . mysql_real_escape_string ( $torrent->Hash ) . "' AND `Peers_Left` = 0" );
		if (! $result) {
			throw new Tracker_Exception ( mysql_error () );
		}
		if (mysql_num_rows ( $result ) > 0) {
			$row = mysql_fetch_object ( $result );
			return $row->PeerCount;
		} else {
			return 0;
		}
	}
	
	/**
	 * Gets teh number of leechers currently associated with the torrent @param $torrent
	 *
	 * @param Tracker_Torrent $torrent
	 * @return int
	 */
	public static function getTorrentLeechCount(Tracker_Torrent $torrent) {
		$result = mysql_query ( "SELECT Count(*) as `PeerCount` FROM `Peers` WHERE `Torrents_Hash` = '" . mysql_real_escape_string ( $torrent->Hash ) . "' AND `Peers_Left` <> 0" );
		if (! $result) {
			throw new Tracker_Exception ( mysql_error () );
		}
		if (mysql_num_rows ( $result ) > 0) {
			$row = mysql_fetch_object ( $result );
			return $row->PeerCount;
		} else {
			return 0;
		}
	}
	
	/**
	 * Get the number of times the torrent @param $torrent has been downloaded
	 *
	 * @param Tracker_Torrent $torrent
	 * @return unknown
	 */
	public static function getTorrentDownloadedCount(Tracker_Torrent $torrent) {
		$result = mysql_query ( "SELECT * FROM `Torrents` WHERE `Torrents_Hash` = '" . mysql_real_escape_string ( $torrent->Hash ) . "' LIMIT 1" );
		if (! $result) {
			throw new Tracker_Exception ( mysql_error () );
		}
		if (mysql_num_rows ( $result ) == 1) {
			$row = mysql_fetch_object ( $result );
			return $row->Downloads;
		} else {
			return 0;
		}
	}
	
	/**
	 * Checks whether the torrent with hash @package $torrentHash is authorised for usage with the tracker
	 *
	 * @param string $torrentHash
	 * @return bool
	 */
	public static function getTorrentAuthorised($torrentHash) {
		$result = mysql_query ( "SELECT * FROM `Authorisation` WHERE `Torrents_Hash` = '" . mysql_real_escape_string ( $torrentHash ) . "' LIMIT 1" );
		if (! $result) {
			throw new Tracker_Exception ( mysql_error () );
		}
		if (mysql_num_rows ( $result ) > 0) {
			return true;
		} else {
			return false;
		}
	}
	
	public static function setTorrentAuthorised($torrentHash) {
		if (! self::getTorrentAuthorised ( $torrentHash )) {
			$result = mysql_query ( "INSERT INTO `Authorisation` (`Torrents_Hash`) VALUES ('" . mysql_real_escape_string ( $torrentHash ) . "')" );
			if (! $result) {
				throw new Tracker_Exception ( mysql_error () );
			}
		}
	}
	
	/**
	 * Sets the torrent @param $torrent
	 *
	 * @param Tracker_Torrent $torrent
	 */
	public static function setTorrent(Tracker_Torrent $torrent) {
		if (self::getTorrentExists ( $torrent->Hash )) {
			$sql = "UPDATE `Torrents` SET 
						`Torrents_Downloaded` = '" . mysql_real_escape_string ( $torrent->downloaded ) . "',
				    WHERE `Torrents`.`Torrents_Hash` = '" . mysql_real_escape_string ( $torrent->hash ) . "' LIMIT 1";
		} else {
			$sql = "INSERT INTO `Torrents` 
						(
						`Torrents_Hash` ,
						`Torrents_RawHash` ,
						`Torrents_Added` ,
						`Torrents_Updated` ,
						`Torrents_Downloaded` ,
						)
						VALUES (
						'" . mysql_real_escape_string ( $torrent->hash ) . "', 
						'" . mysql_real_escape_string ( $torrent->rawHash ) . "',
						NOW(), 
						NOW(), 
						'0', 
						)";
		}
		$result = mysql_query ( $sql );
		try {
			if (! $result) {
				throw new Tracker_Exception ( mysql_error () );
			}
		} catch ( Exception $e ) {
			// ignore
		}
	}
	
	/**
	 * Removes the torrent @param $torrent from the tracker
	 *
	 * @param Tracker_Torrent $torrent
	 */
	public static function removeTorrent(Tracker_Torrent $torrent) {
		$result = mysql_query ( "DELETE FROM `Torrents` WHERE `Torrents_Hash` = '" . mysql_real_escape_string ( $torrent->hash ) . "' LIMIT 1" );
		if (! $result) {
			throw new Tracker_Exception ( mysql_error () );
		}
	}
	
	/**
	 * Fetches the torrent peer matching @param $peerId for the torrent with hash @param $torrentHash
	 *
	 * @param string $peerId
	 * @param string $torrentHash
	 * @return Tracker_Peer
	 */
	public static function getPeer($peerId, $torrentHash) {
		$result = mysql_query ( "SELECT * FROM `Peers` WHERE `Peers_Id` = '" . mysql_real_escape_string ( $peerId ) . "' AND `Torrents_Hash` = '" . mysql_real_escape_string ( $torrentHash ) . "' LIMIT 1" );
		if (! $result) {
			throw new Tracker_Exception ( mysql_error () );
		}
		if (mysql_num_rows ( $result ) == 1) {
			$row = mysql_fetch_object ( $result );
			return new Tracker_Peer ( $row->Peers_Id, $row->Peers_RawId, $row->Torrents_Hash, long2ip ( $row->Peers_Ip ), $row->Peers_Port, $row->Peers_Uploaded, $row->Peers_Downloaded, $row->Peers_Left );
		} else {
			throw new Tracker_Exception ( "Peer does not exist" );
		}
	}
	
	/**
	 * Checks whether the torrent peer with @param $peerId exists for the torrent with hash @param $torrentHash
	 *
	 * @param string $peerId
	 * @param string $torrentHash
	 * @return bool
	 */
	public static function getPeerExists($peerId, $torrentHash) {
		$result = mysql_query ( "SELECT * FROM `Peers` WHERE `Peers_Id` = '" . mysql_real_escape_string ( $peerId ) . "' AND `Torrents_Hash` = '" . mysql_real_escape_string ( $torrentHash ) . "' LIMIT 1" );
		if (! $result) {
			throw new Tracker_Exception ( mysql_error () );
		}
		if (mysql_num_rows ( $result ) == 1) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Fetches the torrent peer with ip @param $ip for the torrent @param $torrent
	 *
	 * @param Tracker_Torrent $torrent
	 * @param string $ip
	 * @return Tracker_Peer
	 */
	public static function getPeerByNetwork(Tracker_Torrent $torrent, $ip) {
		$result = mysql_query ( "SELECT * FROM `Peers` WHERE `Peers_Ip` = '" . mysql_real_escape_string ( ip2long ( $ip ) ) . "' AND `Torrents_Hash` = '" . mysql_real_escape_string ( $torrent->Hash ) . "' LIMIT 1" );
		if (! $result) {
			throw new Tracker_Exception ( mysql_error () );
		}
		if (mysql_num_rows ( $result ) == 1) {
			$row = mysql_fetch_object ( $result );
			return new Tracker_Peer ( $row->Peers_Id, $row->Peers_RawId, $row->Torrents_Hash, long2ip ( $row->Peers_Ip ), $row->Peers_Port, $row->Peers_Uploaded, $row->Peers_Downloaded, $row->Peers_Left );
		} else {
			throw new Tracker_Exception ( "Peer does not exist" );
		}
	}
	
	/**
	 * Fetches all peers currently associated with the tracker
	 *
	 * @return Tracker_Peer[]
	 */
	public static function getPeers() {
		$peers = array ();
		$result = mysql_query ( "SELECT * FROM `Peers`" );
		if (! $result) {
			throw new Tracker_Exception ( mysql_error () );
		}
		if (mysql_num_rows ( $result ) > 0) {
			while ( $row = mysql_fetch_object ( $result ) ) {
				$peers [] = new Tracker_Peer ( $row->Peers_Id, $row->Peers_RawId, long2ip ( $row->Peers_Ip ), $row->Peers_Port, $row->Peers_Uploaded, $row->Peers_Left );
			}
		}
		return $peers;
	}
	
	/**
	 * Fetches all torrents the peer @param $peer is currently associated with
	 *
	 * @param Tracker_Peer $peer
	 * @return Tracker_Torrent[]
	 */
	public static function getPeerTorrents(Tracker_Peer $peer) {
		$torrents = array ();
		$result = mysql_query ( "SELECT * FROM `Torrents` WHERE `Torrents_Hash` IN(SELECT `Torrents_Hash` FROM `Peers` WHERE `Peers`.`Peers_Id` = '" . mysql_real_escape_string ( $peer->Id ) . "')" );
		if (! $result) {
			throw new Tracker_Exception ( mysql_error () );
		}
		if (mysql_num_rows ( $result ) > 0) {
			while ( $row = mysql_fetch_object ( $result ) ) {
				$torrents [] = new Tracker_Torrent ( $row->Torrents_Hash, $row->Torrents_RawHash, $row->Torrents_Added, $row->Torrents_Updated, $row->Torrents_Downloaded, $row->Torrents_Double1, $row->Torrents_Double2, $row->Torrents_Double3, $row->Torrents_Double4, $row->Torrents_Long1 );
			}
		}
		return $torrents;
	}
	
	/**
	 * Sets the peer @param $peer
	 *
	 * @param Tracker_Peer $peer
	 */
	public static function setPeer(Tracker_Peer $peer) {
		if (self::getPeerExists ( $peer->id, $peer->torrentHash )) {
			$sql = "UPDATE `Peers` SET 
						`Peers_Ip` = '" . mysql_real_escape_string ( ip2long ( $peer->ip ) ) . "',
						`Peers_Port` = '" . mysql_real_escape_string ( $peer->port ) . "',
						`Peers_Uploaded` = '" . mysql_real_escape_string ( $peer->uploaded ) . "',
						`Peers_Downloaded` = '" . mysql_real_escape_string ( $peer->downloaded ) . "',
						`Peers_Left` = '" . mysql_real_escape_string ( $peer->left ) . "' 
					WHERE `Peers_Id` = '" . mysql_real_escape_string ( $peer->id ) . "' AND `Torrents_Hash` = '" . mysql_real_escape_string ( $peer->torrentHash ) . "' LIMIT 1";
		} else {
			$sql = "INSERT INTO `Peers` (
						`Peers_Id` ,
						`Peers_RawId` ,
						`Torrents_Hash`,
						`Peers_Ip` ,
						`Peers_Port` ,
						`Peers_Added` ,
						`Peers_Updated` ,
						`Peers_Uploaded` ,
						`Peers_Downloaded` ,
						`Peers_Left` 
						)
						VALUES (
						'" . mysql_real_escape_string ( $peer->id ) . "',
						'" . mysql_real_escape_string ( $peer->rawId ) . "',
						'" . mysql_real_escape_string ( $peer->torrentHash ) . "', 
						'" . mysql_real_escape_string ( ip2long ( $peer->ip ) ) . "', 
						'" . mysql_real_escape_string ( $peer->port ) . "', 
						NOW(), 
						NOW(), 
						'" . mysql_real_escape_string ( $peer->uploaded ) . "', 
						'" . mysql_real_escape_string ( $peer->downloaded ) . "', 
						'" . mysql_real_escape_string ( $peer->left ) . "'
				   )";
		}
		$result = mysql_query ( $sql );
		try {
			if (! $result) {
				throw new Tracker_Exception ( mysql_error () );
			}
		} catch ( Exception $e ) {
			// ignore
		}
	}
	
	/**
	 * Removes the peer @param $peer from all torrents on the tracker
	 *
	 * @param Tracker_Peer $peer
	 */
	public static function removePeer(Tracker_Peer $peer) {
		$result = mysql_query ( "DELETE FROM `Peers` WHERE `Peers_Id` = '" . mysql_real_escape_string ( $peer->id ) . "' AND `Torrents_Hash` = '" . mysql_real_escape_string ( $peer->torrentHash ) . "' LIMIT 1" );
		if (! $result) {
			throw new Tracker_Exception ( mysql_error () );
		}
	}
	
	/**
	 * Removes expired peers and torrents from the tracker
	 *
	 * @param int $torrentLifetime
	 * @param int $peerLifetime
	 */
	public static function update($torrentLifetime, $peerLifetime) {
		$result = mysql_query ( "peerLifetime$torrentLifetime" );
		if (! $result) {
			throw new Tracker_Exception ( mysql_error () );
		}
		$result = mysql_query ( "DELETE FROM `Peers` WHERE UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(`Peers_Updated`) > $peerLifetime" );
		if (! $result) {
			throw new Tracker_Exception ( mysql_error () );
		}
	}
}
?>