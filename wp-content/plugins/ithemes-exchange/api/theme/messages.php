<?php
/**
 * Messages class for THEME API
 *
 * @since 0.4.0
*/

class IT_Theme_API_Messages implements IT_Theme_API {

	/**
	 * API context
	 * @var string $_context
	 * @since 0.4.0
	*/
	private $_context = 'messages';

	/**
	 * Do we have any messages right now?
	 * @var string $_context
	 * @since 0.4.0
	*/
	private $_has_messages = false;

	/**
	 * Do we have any error messages right now?
	 * @var string $_context
	 * @since 0.4.0
	*/
	private $_has_errors = false;

	/**
	 * Do we have any notice messages right now?
	 * @var string $_has_notices
	 * @since 0.4.0
	*/
	private $_has_notices = false;

	/**
	 * Maps api tags to methods
	 * @var array $_tag_map
	 * @since 0.4.0
	*/
	var $_tag_map = array(
		'errors'  => 'errors',
		'error'   => 'error',
		'notices' => 'notices',
		'notice'  => 'notice',
	);

	/**
	 * Constructor
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function IT_Theme_API_Messages() {
		// Set the current has_ properties
		$this->_has_errors   = it_exchange_has_messages( 'error' );
		$this->_has_notices  = it_exchange_has_messages( 'notice' );
		$this->_has_messages = $this->_has_errors || $this->_has_errors;
	}

	/**
	 * Returns the context. Also helps to confirm we are an iThemes Exchange theme API class
	 *
	 * @since 0.4.0
	 *
	 * @return string
	*/
	function get_api_context() {
		return $this->_context;
	}

	/**
	 * Loops through errors
	 *
	 * If has option is true, returns boolean
	 *
	 * @since 0.4.0
	 *
	 * @return boolean
	*/
	function errors( $options=array() ) {
        // Return boolean if has flag was set
		if ( $options['has'] )
			return $this->_has_errors;

		// If we made it here, we're doing a loop of errors
		// This will init/reset the errors global and loop through them. The error method will return the current one
		if ( ! isset( $GLOBALS['it_exchange']['error'] ) && $this->_has_errors ) {
			$GLOBALS['it_exchange']['errors'] = it_exchange_get_messages( 'error' );
			$GLOBALS['it_exchange']['error'] = reset( $GLOBALS['it_exchange']['errors'] );
			return true;
		} else {
			if ( next( $GLOBALS['it_exchange']['errors'] ) ) {
				$GLOBALS['it_exchange']['error'] = current( $GLOBALS['it_exchange']['errors'] );
				return true;
			} else {
				end( $GLOBALS['it_exchange']['errors'] );
				$GLOBALS['it_exchange']['errors'] = array();
				it_exchange_clear_messages( 'error' );
				return false;
			}
		}

		return false;
	}

	/**
	 * Returns current error
	 *
	 * @since 0.4.0
	 *
	 * @return mixed boolean or string
	*/
	function error( $options=array() ) {
        // Return boolean if has flag was set
		if ( $options['has'] )
			return empty( $GLOBALS['it_exchange']['error'] );

		return empty( $GLOBALS['it_exchange']['error'] ) ? false : $GLOBALS['it_exchange']['error'];
	}

	/**
	 * Loops through Notices
	 *
	 * If has option is true, returns boolean
	 *
	 * @since 0.4.0
	 *
	 * @return boolean
	*/
	function notices( $options=array() ) {
        // Return boolean if has flag was set
		if ( $options['has'] )
			return $this->_has_notices;

		// If we made it here, we're doing a loop of notices
		// This will init/reset the notices global and loop through them. The notice method will return the current one
		if ( ! isset( $GLOBALS['it_exchange']['notice'] ) && $this->_has_notices ) {
			$GLOBALS['it_exchange']['notices'] = it_exchange_get_messages( 'notice' );
			$GLOBALS['it_exchange']['notice'] = reset( $GLOBALS['it_exchange']['notices'] );
			return true;
		} else {
			if ( next( $GLOBALS['it_exchange']['notices'] ) ) {
				$GLOBALS['it_exchange']['notice'] = current( $GLOBALS['it_exchange']['notices'] );
				return true;
			} else {
				$GLOBALS['it_exchange']['notices'] = array();
				end( $GLOBALS['it_exchange']['notices'] );
				it_exchange_clear_messages( 'notice' );
				return false;
			}
		}

		return false;
	}

	/**
	 * Returns current notice
	 *
	 * @since 0.4.0
	 *
	 * @return mixed boolean or string
	*/
	function notice( $options=array() ) {
        // Return boolean if has flag was set
		if ( $options['has'] )
			return empty( $GLOBALS['it_exchange']['notice'] );

		return empty( $GLOBALS['it_exchange']['notice'] ) ? false : $GLOBALS['it_exchange']['notice'];
	}
}
