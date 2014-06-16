<?php

/**
 *
 * @package LDMW
 * @subpackage Settings
 * @since 1.0
 */
class LDMW_Options_Model {
	/**
	 * @var array
	 */
	private $options = array();

	/**
	 * @var LDMW_Options_Model|null
	 */
	private static $instance = null;

	/**
	 * Class is responsible for holding the plugin's options.
	 *
	 * Is singleton to prevent options clashing
	 */
	private function __construct() {
		$controller = LDMW_Options_Controller::get_instance();

		$this->options = array_merge( $controller->get_defaults(), get_option( 'ldmw_options', array() ) );
	}

	/**
	 * @return LDMW_Options_Model
	 */
	public static function get_instance() {
		if ( self::$instance == null )
			self::$instance = new self;

		return self::$instance;
	}

	/**
	 * Save current object's state to the database
	 */
	public function save() {
		update_option( 'ldmw_options', $this->options );
	}

	/**
	 * @param $data
	 */
	public function update( $data ) {
		$this->options = $data;
	}

	/**
	 * @return array
	 */
	public function get_options() {
		return $this->options;
	}

	/**
	 * Magic method to get data from options
	 *
	 * @param $key
	 *
	 * @return string
	 */
	public function __get( $key ) {
		if ( isset( $this->options[$key] ) ) {
			$value = $this->options[$key];
			if ( $key == 'logo_url' && is_ssl() ) {
				$value = str_replace( "http://", "https://", $value );
			}

			return $value;
		}
		else
			return "";
	}

	/**
	 * Magic method to set options data if that option exists.
	 * Does not allow for the addition of new values.
	 *
	 * @param $key
	 * @param $value
	 */
	public function __set( $key, $value ) {
		if ( isset( $this->options[$key] ) )
			$this->options[$key] = $value;
	}

}