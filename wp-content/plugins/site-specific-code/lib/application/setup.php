<?php
/**
 *
 * @package LDMW
 * @subpackage Application Approval
 * @since 1.0
 */

function ldmw_add_application_approval_page() {
	$hook = add_menu_page( "Applications", "Applications", "list_applications", "ldmw-applications", array( new LDMW_Application_Approval_Dispatcher(), 'dispatch' ), 'dashicons-portfolio', 3 );
	add_submenu_page( 'ldmw-applications', "Pending Applications", "Pending Applications", 'list_users', 'ldmw-pending-applications', array( new LDMW_Application_Pending(), 'init' ) );
}

add_action( 'admin_menu', 'ldmw_add_application_approval_page' );