<?php
require_once 'Tracker_ConfigurationSectionBase.class.php';
require_once 'Tracker_ConfigurationBase.class.php';

/**
 * Represents a section of an INI file
 *
 */
class Tracker_ConfigurationSection extends Tracker_ConfigurationSectionBase {
	
	/**
	 * Populates the Tracker_ConfigurationSection with a dictionary of values
	 *
	 * @param array $values
	 */
	public function __construct(array $values) {
		parent::__construct ( $values );
	}
}
?>