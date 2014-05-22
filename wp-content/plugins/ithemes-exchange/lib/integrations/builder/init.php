<?php
/**
 * Custom integrations with Builder.
 *
 * @package IT_Exchange
 * @since 1.4.0
*/
function it_exchange_builder_integration_setup() {
	if ( isset( $GLOBALS['theme_index'] ) && 'it-builder' == $GLOBALS['theme_index'] ) {
		require_once( dirname( __FILE__ ) . '/functions.php' );
	}
}
add_action( 'after_setup_theme', 'it_exchange_builder_integration_setup' );
