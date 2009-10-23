<?php
/**
 * Base class for the top level of an INI file represented by Tracker_Configuration
 *
 */
abstract class Tracker_ConfigurationBase {
	private $configurationSections;
	
	/**
	 * Populates @var $configurationSections with a Tracker_ConfigurationSection for each INI section
	 *
	 * @param array $configurationSections
	 */
	public function __construct(array $configurationSections) {
		$this->configurationSections = array ();
		foreach ( $configurationSections as $key => $configurationSection ) {
			$this->configurationSections [$key] = new Tracker_ConfigurationSection ( $configurationSection );
		}
	}
	
	/**
	 * Overloads getter to access sections of the INI file
	 *
	 * @param unknown_type $key
	 * @return Tracker_ConfigurationSection
	 */
	final public function __get($key) {
		if (array_key_exists ( $key, $this->configurationSections )) {
			return $this->configurationSections [$key];
		}
	}
	
	/**
	 * Checks whether @var $configurationSections contains the @see 
	 *
	 * @param Tracker_Configurationsection $configurationSection the Tracker_ConfigurationSection
	 * @return unknown
	 */
	final public function contains(Tracker_Configurationsection $configurationSection) {
		return in_array ( $configurationSection, $this->configurationSections );
	}
	
	/**
	 * Loads additional configuration sections into this instance
	 *
	 * @param unknown_type $configurationSections
	 */
	public function load($configurationSections) {
		foreach ( $configurationSections as $key => $configurationSection ) {
			if (array_key_exists ( $key, $this->configurationSections )) {
				$this->__get ( $key )->Load ( $configurationSection );
			}
		}
	}
}