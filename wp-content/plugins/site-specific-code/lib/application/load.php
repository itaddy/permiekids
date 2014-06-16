<?php
/**
 *
 * @package LDMW
 * @subpackage Application Approval
 * @since 1.0
 */
require( "setup.php" );
require( "class.dispatch.php" );
require( "class.base.php" );

if ( is_admin() && isset( $_GET['ldmw_applications_export'] ) && current_user_can( 'export_applications' ) ) {
	$export = new LDMW_Application_Export( LDMW_Application_Util::parse_search( $_GET ) );
	$export->render_csv();
}