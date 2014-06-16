<?php
/**
 *
 * @package LDMW
 * @subpackage Journal
 * @since 5/3
 */

if ( !function_exists( 'http_build_url' ) )
	require_once LDMW_Plugin::$dir . "lib/journal/shim.php";

require_once LDMW_Plugin::$dir . "lib/journal/admin/class.fm_author.php";

new LDMW_Journal_Admin_Taxonomy();
new LDMW_Journal_Admin_CPT();
new LDMW_Journal_Admin_Base();

new LDMW_Journal_Exchange_Base();
new LDMW_Journal_Exchange_Display();

if ( !is_admin() )
	require_once( LDMW_Plugin::$dir . "lib/journal/exchange/class.api.php" );

new LDMW_Journal_Display_Base();

remove_action( 'admin_enqueue_scripts', 'fieldmanager_enqueue_scripts' );
add_action( 'load-edit.php', function () {
	  add_action( 'admin_enqueue_scripts', 'fieldmanager_enqueue_scripts' );
  }
);
add_action( 'admin_enqueue_scripts', function () {

	  wp_dequeue_style( 'fm-jquery-ui' );

  }, 15
);