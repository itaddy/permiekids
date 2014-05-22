<?php
/**
 * Functions for defining, initiating, and displaying iThemes Exchange Errors and Notices
 *
 * Core types are 'notice' and 'error'
 *
 * @since 0.4.0
 * @package IT_Exchange
*/

/**
 * Adds messages to Exchange session
 *
 * @since 0.4.0
 *
 * @param string $type Type of message you want displayed
 * @param string $message the message you want displayed
*/
function it_exchange_add_message( $type, $message ) {
	it_exchange_add_session_data( $type, $message );
	do_action( 'it_exchange_add_message', $type, $message );
}

/**
 * Gets messages to Exchange session
 *
 * @since 0.4.0
 *
 * @param string $type Type of message you want displayed
*/
function it_exchange_get_messages( $type, $clear=true ) {
	$messages = it_exchange_get_session_data( $type );
	if ( $clear )
		it_exchange_clear_messages( $type );
	return apply_filters( 'it_exchange_get_messages', $messages, $type, $clear );
}

/**
 * Checks if messages are in the to Exchange session
 *
 * @since 0.4.0
 *
 * @param string $type Type of message you want displayed
*/
function it_exchange_has_messages( $type ) {
	return (bool) apply_filters( 'it_exchange_has_messages', it_exchange_get_session_data( $type, false ), $type );
}

/**
 * Checks if messages are in the to Exchange session
 *
 * @since 0.4.0
 *
 * @param string $type Type of message you want displayed
*/
function it_exchange_clear_messages( $type ) {
	it_exchange_clear_session_data( $type );
	do_action( 'it_exchange_clear_messages', $type );
}
