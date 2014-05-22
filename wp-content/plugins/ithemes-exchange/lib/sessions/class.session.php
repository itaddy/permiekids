<?php
/**
 * This file contains the session class
 *
 * ############### WP Session Manager ##########
 * ## This is a wrapper to our lightly modified version of WP Session Manager by Eric Mann
 * ## Author: http://twitter.com/ericmann
 * ## Donate link: http://jumping-duck.com/wordpress/plugins
 * ## Github link: https://github.com/ericmann/wp-session-manager
 * ## Requires at least: WordPress 3.4.2
 * ## License: GPLv2 or later License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * #############################################
 *
 * @since 0.3.3
 * @package IT_Exchange
*/

/**
 * The IT_Exchange_Session class holds cart and purchasing details
 *
 * @since 0.3.3
*/
class IT_Exchange_Session {

	/**
	 * @param array $_session  an array of any additional data needed by iThemes Exchange
	 * @since 0.4.0
	*/
	private $_session;

	function IT_Exchange_Session() {
		if( ! defined( 'IT_EXCHANGE_SESSION_COOKIE' ) )
			define( 'IT_EXCHANGE_SESSION_COOKIE', 'it_exchange_session_' . COOKIEHASH );

		if ( ! class_exists( 'Recursive_ArrayAccess' ) )
			require_once( 'db_session_manager/class-recursive-arrayaccess.php' );

		// Only include the functionality if it's not pre-defined.
		if ( ! class_exists( 'IT_Exchange_DB_Sessions' ) ) {
			require_once( 'db_session_manager/class-db-session.php' );
			require_once( 'db_session_manager/db-session.php' );
		}

		if ( empty( $this->_session ) )
			add_action( 'plugins_loaded', array( $this, 'init' ) );
		else
			add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * Inits the DB Sessions and returns the object
	 *
	 * @since 0.4.0
	 *
	 * @return object
	*/
	function init() {
		$this->_session = IT_Exchange_DB_Sessions::get_instance();
		return $this->_session;
	}

	/**
	 * Returns session data
	 *
	 * All data or optionaly, data for a specific key
	 *
	 * @since 0.4.0
	 *
	 * @param string $key data key
	 * @return mixed. serialized string
	*/
	function get_session_data( $key=false ) {
		if ( $key ) {
			$key = sanitize_key( $key );

			if ( $key && !empty( $this->_session[$key] ) )
				return $this->_session[$key];
		} else {
			if ( $session_data = get_object_vars( json_decode( it_exchange_db_session_encode() ) ) ) {
				$session_data = array_map( 'maybe_unserialize', $session_data );
				return $session_data;
			}
		}
		return array();
	}

	/**
	 * Adds data to the session, associated with a specific key
	 *
	 * @since 0.4.0
	 *
	 * @param string $key key for the data
	 * @param mixed  $data data to be stored. will be serialized if not already
	 * @return void
	*/
	function add_session_data( $key, $data ) {
		$key = sanitize_key( $key );

		if ( ! empty( $this->_session[$key] ) ) {
			$current_data = maybe_unserialize( $this->_session[$key] );
			$this->_session[$key] = maybe_serialize( array_merge( $current_data, (array)$data ) );
		} else {
			$this->_session[$key] = maybe_serialize( (array)$data );
		}
		it_exchange_db_session_commit();
	}

	/**
	 * Updates session data by key
	 *
	 * @since 0.4.0
	 * @param string $key key for the data
	 * @param mixed  $data data to be stored. will be serialized if not already
	 * @return void
	*/
	function update_session_data( $key, $data ) {
		$key = sanitize_key( $key );
		$this->_session[$key] = maybe_serialize( (array)$data );
		it_exchange_db_session_commit();
	}

	/**
	 * Deletes session data. All or by key.
	 *
	 * @since 0.4.0
	 *
	 * @param string $key
	 * @return void
	*/
	function clear_session_data( $key=false ) {
		if ( $key ) {
			$key = sanitize_key( $key );

			if ( isset( $this->_session[$key] ) ) {
				unset( $this->_session[$key] );
				it_exchange_db_session_commit();
			}
		}
		$this->_session[$key] = $this->_session[$key];
	}

	/**
	 * Clears all session data
	 *
	 * @since 0.4.0
	*/
	function clear_session( $hard=false ) {
		if ( $hard )
			$this->init();
	}
}
$GLOBALS['it_exchange']['session'] = new IT_Exchange_Session();
