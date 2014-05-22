<?php
/**
 * API functions to deal with customer data and actions
 *
 * @since 0.3.7
 * @package IT_Exchange
*/

/**
 * Registers a customer
 *
 * @since 0.3.7
 * @param array $customer_data array of customer data to be processed by the customer management add-on when creating a customer
 * @param array $args optional array of arguments. not used by all add-ons
 * @return mixed
*/
function it_exchange_register_customer( $customer_data, $args=array() ) {
	return do_action( 'it_exchange_register_customer', $customer_data, $args );
}

/**
 * Get a customer
 *
 * Will return customer data formated by the active customer management add-on
 *
 * @since 0.3.7
 * @param integer $customer_id id for the customer
 * @return mixed customer data
*/
function it_exchange_get_customer( $customer_id ) {
    // Grab the WP User
	$customer = new IT_Exchange_Customer( $customer_id );
	if ( empty( $customer->wp_user->ID ) )
		$customer = false;

	return apply_filters( 'it_exchange_get_customer', $customer, $customer_id );
}

/**
 * Get the currently logged in customer or return false
 *
 * @since 0.3.7
 * @return mixed customer data
*/
function it_exchange_get_current_customer() {
	if ( ! is_user_logged_in() )
		return apply_filters( 'it_exchange_get_current_customer', false );

	$customer = it_exchange_get_customer( get_current_user_id() );
	return apply_filters( 'it_exchange_get_current_customer', $customer );
}

/**
 * Get the currently logged in customer ID or return false
 *
 * @since 0.4.0
 * @return mixed customer data
*/
function it_exchange_get_current_customer_id() {
	if ( ! is_user_logged_in() )
		return false;

	return apply_filters( 'it_exchange_get_current_customer_id', get_current_user_id() );
}

/**
 * Update a customer's data
 *
 * @since 0.3.7
 *
 * @param integer $customer_id id for the customer
 * @param mixed $customer_data data to be updated
 * @param array $args optional array of arguments. not used by all add-ons
 * @return mixed
*/
function it_exchange_update_customer( $customer_id, $customer_data, $args ) {
	return do_action( 'it_exchange_update_customer', $customer_id, $customer_data, $args );
}

/**
 * Returns all customer transactions
 *
 * @since 0.4.0
 *
 * @param integer ID customer id
 * @return array
*/
function it_exchange_get_customer_transactions( $customer_id ) {
	if ( ! $customer = it_exchange_get_customer( $customer_id ) )
		return array();

	// Get transactions args
	$args = array(
		'numberposts' => -1,
		'customer_id' => $customer->id,
	);
	return apply_filters( 'it_exchange_get_customer_transactions', it_exchange_get_transactions( $args ), $customer_id, $args );
}

/**
 * Returns all customer transactions
 *
 * @since 0.4.0
 *
 * @param integer ID transaction id
 * @param integer ID customer id
 * @return array
*/
function it_exchange_customer_has_transaction( $transaction_id, $customer_id = NULL ) {

	if ( is_null( $customer_id ) )
		$customer = it_exchange_get_current_customer();
	else if ( ! $customer = it_exchange_get_customer( $customer_id ) )
		return array();

	// Get transactions args
	$args = array(
		'numberposts' => -1,
		'customer_id' => $customer->id,
	);
	return apply_filters( 'it_exchange_customer_has_transaction', $customer->has_transaction( $transaction_id ), $transaction_id, $customer_id );
}

/**
 * Returns all customer products purchased across various transactions
 *
 * @since 0.4.0
 *
 * @param integer $customer_id the WP id of the customer
 * @return array
*/
function it_exchange_get_customer_products( $customer_id ) {
	// All products are attached to a transaction
	$transactions = it_exchange_get_customer_transactions( $customer_id );

	// Loop through transactions and build array of products
	$products = array();
	foreach( $transactions as $transaction ) {

		// strip array values from each product to prevent ovewriting multiple purchases of same product
		$transaction_products = (array) array_values( it_exchange_get_transaction_products( $transaction ) );

		// Add transaction ID to each products array
		foreach( $transaction_products as $key => $data ) {
			$transaction_products[$key]['transaction_id'] = $transaction->ID;
		}

		// Merge with previously queried
		$products = array_merge( $products, $transaction_products );
	}

	// Return
	return apply_filters( 'it_exchange_get_customer_products', $products, $customer_id );
}

/**
 * Handles $_REQUESTs and submits them to the profile for processing
 *
 * @since 0.4.0
 * @return void
*/
function handle_it_exchange_save_profile_action() {

	// Grab action and process it.
	if ( isset( $_REQUEST['it-exchange-save-profile'] ) ) {

		//WordPress builtin
		require_once(ABSPATH . 'wp-admin/includes/user.php');
		$customer = it_exchange_get_current_customer();
		$result = edit_user( $customer->id );

		if ( is_wp_error( $result ) ) {
			it_exchange_add_message( 'error', $result->get_error_message() );
		} else {
			it_exchange_add_message( 'notice', __( 'Successfully saved profile!', 'it-l10n-ithemes-exchange' ) );
		}

		do_action( 'handle_it_exchange_save_profile_action' );

	}

}
add_action( 'template_redirect', 'handle_it_exchange_save_profile_action', 5 );

/**
 * Register's an exchange user
 *
 * @since 0.4.0
 * @param array $user_data optional. Overwrites POST data
 * @return mixed WP_Error or WP_User object
*/
function it_exchange_register_user( $user_data=array() ) {

	// Include WP file
	require_once( ABSPATH . 'wp-admin/includes/user.php' );

	// If any data was passed in through param, inject into POST variable
	foreach( $user_data as $key => $value ) {
		$_POST[$key] = $value;
	}

	do_action( 'it_exchange_register_user' );

	// Register user via WP function
	return edit_user();
}

/**
 * Retrieve specific customer data
 *
 * @since 1.3.0
 *
 * @param string $data_key the key of the data property of the IT Exchange customer object.
 * @param integer $customer_id the customer id. leave blank to use the current customer.
 * @return mixed
*/
function it_exchange_get_customer_data( $data_key, $customer_id=false ) {
	$customer = empty( $customer_id ) ? it_exchange_get_current_customer() : it_exchange_get_customer( $customer_id );

	// Return false if no customer was found
	if ( ! $customer )
		return false;

	// Set requested data if it exists, otherwise set as false
	$data = empty( $customer->data->$data_key ) ? false : $customer->data->$data_key;

	// Return the data
	return apply_filters( 'it_exchange_get_customer_data', $data, $data_key, $customer );
}

/**
 * Get Customer Shipping Address
 *
 * Among other things this function is used as a callback for the shipping address
 * purchase requriement.
 *
 * @since 1.4.0
 *
 * @param integer $customer_id the customer id. leave blank to use the current customer.
 * @return array
*/
function it_exchange_get_customer_shipping_address( $customer_id=false ) {
	$shipping_address = it_exchange_get_customer_data( 'shipping_address', $customer_id );
	return apply_filters( 'it_exchange_get_customer_shipping_address', $shipping_address, $customer_id );
}

/**
 * Get Customer Billing Address
 *
 * Among other things this function is used as a callback for the billing address
 * purchase requriement.
 *
 * @since 1.3.0
 *
 * @param integer $customer_id the customer id. leave blank to use the current customer.
 * @return array
*/
function it_exchange_get_customer_billing_address( $customer_id=false ) {
	$billing_address = it_exchange_get_customer_data( 'billing_address', $customer_id );
	return apply_filters( 'it_exchange_get_customer_billing_address', $billing_address, $customer_id );
}

/**
 * Updates the customer billing address
 *
 * @since 1.5.0
 *
 * @param array   $addres      address obejct
 * @param integer $customer_id optional. defualts to current customer
 * @return boolean
*/
function it_exchange_save_customer_billing_address( $address, $customer_id=false ) {
	$customer_id = empty( $customer_id ) ? it_exchange_get_current_customer_id() : $customer_id;

	$billing = apply_filters( 'it_exchange_save_customer_billing_address', $address, $customer_id );

	if ( false !== $billing ) {
		update_user_meta( it_exchange_get_current_customer_id(), 'it-exchange-billing-address', $billing );
		do_action( 'it_exchange_customer_billing_address_updated', $billing, $customer_id );
		return true;
	}
	return false;
}
