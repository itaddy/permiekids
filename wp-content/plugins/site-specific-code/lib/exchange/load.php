<?php
/**
 *
 * @package LDMW
 * @subpackage Exchange
 * @since 1.0
 */
require_once( "class.util.php" );
new LDMW_Exchange_Base();
new LDMW_Exchange_Display();

if ( is_admin() )
	new LDMW_Exchange_Admin();

if ( defined( 'DOING_AJAX' ) && DOING_AJAX === TRUE ) {
	new LDMW_Exchange_Account_Transactions();
}

add_action( 'template_redirect', function () {
	  global $wp_query;

	  $query = $wp_query->query;
	  if ( array_key_exists( 'profile', $query ) ) {
		  new LDMW_Exchange_Account_Profile();
	  }
	  else if ( array_key_exists( 'purchases', $query ) ) {
		  new LDMW_Exchange_Account_Transactions();
	  }
	  else if ( array_key_exists( 'memberships', $query ) ) {
		  $membership_post = get_post( LDMW_Options_Model::get_instance()->membership_product );

		  if ( $membership_post->post_name == $query['memberships'] )
			  new LDMW_Exchange_Account_Membership();
	  }
  }
);