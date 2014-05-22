<?php
/**
 * Resets the session activity to the current timestamp
 *
 * @since 1.6.0
 *
 * @return void
*/
function it_exchange_guest_checkout_bump_session() {
	$now            = time();
	$customer_email = it_exchange_get_cart_data( 'guest-checkout-user' );
	$customer_email = is_array( $customer_email ) ? reset( $customer_email ) : $customer_email;

	it_exchange_update_cart_data( 'guest-checkout', $now );

	if ( it_exchange_is_page( 'checkout' ) || it_exchange_is_page( 'transaction' ) || it_exchange_is_page( 'confirmation' ) || it_exchange_in_superwidget() )
		$GLOBALS['current_user'] = it_exchange_guest_checkout_generate_guest_user_object( $customer_email );
}

/**
 * Generates a fake WP_User for Guest Chekcout
 *
 * @since 1.6.0
 *
 * @param string $email
 * @return object
*/
function it_exchange_guest_checkout_generate_guest_user_object( $email, $return_exchange_customer=false ) {
	$user     = new WP_User();
	$user->ID = $email;

	$data               = new stdClass();
	$data->ID           = $email;
	$data->user_login   = false;
	$data->user_pass    = false;
	$data->user_email   = $email;
	$data->display_name = $email;
	$data->email        = $email;
	$data->is_guest     = true;
	$user->data         = $data;

	if ( ! empty( $return_exchange_customer ) )
		return new IT_Exchange_Customer( $user );

	return $user;
}

/**
 * Kills a guest checkout session by removing vars from the session global
 *
 * @since 1.6.0
 *
 * @return void
*/
function it_exchange_kill_guest_checkout_session() {
	it_exchange_remove_cart_data( 'guest-checkout' );
	it_exchange_remove_cart_data( 'guest-checkout-user' );
	it_exchange_remove_cart_data( 'guest-billing-address' );
	it_exchange_remove_cart_data( 'guest-shipping-address' );
	do_action( 'it_exchange_kill_guest_checkout_session' );
}

/**
 * Init a guest session
 *
 * @since 1.6.0
 *
 * @param string $customer_email the customer's email
 * @return boolean
*/
function it_exchange_init_guest_checkout_session( $customer_email ) {
	if ( empty( $customer_email ) || ! is_email( $customer_email ) )
		return false;

	// Set the user ID in the cart session
	it_exchange_update_cart_data( 'guest-checkout-user', $customer_email );

	// Bump the timeout var
	it_exchange_guest_checkout_bump_session();

	do_action( 'it_exchange_init_guest_checkout', $customer_email );
}

/**
 * Are we doing guest checkout?
 *
 * @since 1.6.0
 *
 * @return boolean
*/
function it_exchange_doing_guest_checkout() {
	$data = it_exchange_get_cart_data( 'guest-checkout' );
	return ! empty( $data[0] );
}
