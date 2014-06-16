<?php
/**
 *
 * @package Conferneces
 * @subpackage Papers
 * @since 5/29
 */

new LDMW_Conference_Papers_Admin_CPT();
new LDMW_Conference_Papers_Admin_Taxonomy();
new LDMW_Conference_Papers_Admin_Base();

new LDMW_Conference_Papers_Display_Base();

new LDMW_Conference_Papers_Exchange_Base();
new LDMW_Conference_Papers_Exchange_Display();

if ( !is_admin() )
	require_once( LDMW_Plugin::$dir . "lib/conference/papers/exchange/class.api.php" );