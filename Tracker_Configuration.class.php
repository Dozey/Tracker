<?php
/**
 * Represents the top level of an INI file
 *
 */
class Tracker_Configuration extends Tracker_ConfigurationBase {
	
	/**
	 * Loads the INI file from @param $iniPath
	 * 
	 * @param string $iniPath
	 */
	public function __construct($iniPath) {
		if (is_readable ( $iniPath )) {
			$configurationSections = parse_ini_file ( $iniPath, true );
			$buffer = array ();
			foreach ( $configurationSections as $key => $configurationSection ) {
				$buffer [ucfirst ( $key )] = $configurationSection;
			}
			parent::__construct ( $buffer );
		}
	}
}
?>