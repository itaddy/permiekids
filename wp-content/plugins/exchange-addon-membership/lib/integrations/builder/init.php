<?php
/**
 * Custom integrations with Builder.
 *
 * @package IT_Exchange_Addon_Membership
 * @since CHANGEME
*/

function it_exchange_membership_addon_builder_integration_setup() {
	if ( isset( $GLOBALS['theme_index'] ) && 'it-builder' == $GLOBALS['theme_index'] ) {
		require_once( dirname( __FILE__ ) . '/functions.php' );
	}
}
add_action( 'after_setup_theme', 'it_exchange_membership_addon_builder_integration_setup', 100 );