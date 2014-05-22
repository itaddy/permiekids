<?php
/**
 * API functions pertaining to user sessions
 *
 * - IT_Exchange_Session object is stored in a global variable
 * - Sessions are only active on the frontend of the web site
 * - iThemes Exchange inits the session and loads the data for you. Add-ons should not need to start the session
 *
 * @since 0.3.3
 * @package IT_Exchange
*/

/**
 * This grabs you a copy of the IT_Exchange_Session object
 *
 * @since 0.3.3
 * @return object  instance of IT_Exchange_Session
*/
function it_exchange_get_session() {
	$session = empty( $GLOBALS['it_exchange']['session'] ) ? false : $GLOBALS['it_exchange']['session'];
	return apply_filters( 'it_exchange_get_session', $session );
}

/**
 * Returns session_data array from current session
 *
 * @since 0.3.3
 * @return array  an array of session_data stored in $_SESSION['it_exchange']
*/
function it_exchange_get_session_data( $key=false ) {
	$session = it_exchange_get_session();
	return apply_filters( 'it_exchange_get_session_data', maybe_unserialize( $session->get_session_data( $key ) ), $key );
}

/**
 * Adds session data to the iThemes Exchange Session.
 *
 * This simply adds an item to the data array of the PHP Session.
 * Shopping cart plugins are responsible for managing the structure of the data
 * If a key is passed, it will be used as the key in the data array. Otherwise, the data array will just be
 * incremented. eg: ['data'][] = $data;
 *
 * @since 0.3.7
 * @param mixed $data data as passed by the shopping cart
 * @param mixed $key optional identifier for the data.
 * @return void
*/
function it_exchange_add_session_data( $key, $data ) {
	$session = it_exchange_get_session();
	$session->add_session_data( $key, $data );
	do_action( 'it_exchange_add_session_data', $data, $key );
}

/**
 * Updates session data by key
 *
 * @since 0.3.7
 * @param mixed $key key for the data
 * @param mixed $data updated data
 * @return void
*/
function it_exchange_update_session_data( $key, $data ) {
	$session = it_exchange_get_session();
	$session->update_session_data( $key, $data );
	do_action( 'it_exchange_update_session_data', $data, $key );
}

/**
 * Removes all data from the session key
 *
 * @since 0.3.7
 * @return boolean
*/
function it_exchange_clear_session_data( $key=false ) {
	$session = it_exchange_get_session();
	$session->clear_session_data( $key );
	do_action( 'it_exchange_clear_session_data', $key );
}

/**
 * Removes all data from the session
 *
 * @since 0.3.7
 * @return boolean
*/
function it_exchange_clear_session( $hard=false ) {
	$session = it_exchange_get_session();
	$session->clear_session( $hard );
	do_action( 'it_exchange_clear_session', $hard );
}

/**
 * Returns the current session ID
 *
 * @since 1.3.0
 *
 * @return string
*/
function it_exchange_get_session_id() {
	return empty( $_COOKIE[IT_EXCHANGE_SESSION_COOKIE] ) ? false : $_COOKIE[IT_EXCHANGE_SESSION_COOKIE];
}
