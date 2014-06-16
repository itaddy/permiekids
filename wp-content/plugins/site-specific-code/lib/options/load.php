<?php
/**
 *
 * @package LDMW
 * @subpackage Settings
 * @since 1.0
 */
require( "class.options-controller.php" );
require( "class.options-model.php" );

if ( is_admin() ) {

	require( "class.options-view.php" );

	function ldmw_add_options_page() {
		add_menu_page( "AAS Options", "AAS Options", "manage_options", "ldmw-options", array( new LDMW_Options_View, 'init' ) );
	}

	add_action( 'admin_menu', 'ldmw_add_options_page' );
}