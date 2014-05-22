<?php
/**
 * Enqueues Guest Checkout SW JS
 *
 * @since 1.6.0
 *
 * @return void
*/
function it_exchange_guest_checkout_enqueue_sw_js() {
	$file = ITUtility::get_url_from_file( dirname( __FILE__ ) . '/assets/js/super-widget.js' );
	wp_enqueue_script( 'it-exchange-guest-checkout-sw', $file, array( 'it-exchange-super-widget' ), false, true );
}
add_action( 'it_exchange_enqueue_super_widget_scripts', 'it_exchange_guest_checkout_enqueue_sw_js' );

/**
 * Enqueues the checkout page scripts
 *
 * @since 1.6.0
 *
 * @return void
*/
function it_exchange_guest_checkout_enqueue_checkout_scripts() {
	if ( ! it_exchange_is_page( 'checkout' ) )
		return;

	$file = ITUtility::get_url_from_file( dirname( __FILE__ ) . '/assets/js/checkout.js' );
	wp_enqueue_script( 'it-exchange-guest-checkout-checkout-page', $file, array( 'jquery' ), false, true );
}
add_action( 'wp_enqueue_scripts', 'it_exchange_guest_checkout_enqueue_checkout_scripts' );

/**
 * Init Guest Checkout Registration/Login via email
 *
 * @since 1.6.0
 *
 * @return void
*/
function it_exchange_guest_checkout_init_login() {
	if ( empty( $_POST['it-exchange-init-guest-checkout'] ) )
		return;

	// Vaidate email address
	if ( ! is_email( $_POST['email'] ) ) {
		it_exchange_add_message( 'error', __( 'Please use a properly formatted email address.', 'it-l10n-ithemes-exchange' ) );
		return;
	}

	$customer_email = $_POST['email'];

	it_exchange_init_guest_checkout_session( $customer_email );
}
add_action( 'template_redirect', 'it_exchange_guest_checkout_init_login' );

/**
 * Return true on has_transaction (for confirmation screen) if conditionals match
 *
 * Conditionals:
 * - We're doing a guest checkout
 * - Transaction was a guest checkout transaction
 * - Current guest has same email as one used in the transaction
 *
 * @since 1.6.0
 *
 * @param boolean $has_transaction the value coming in from the WP filter
 * @param integer $transaction_id  the transaction ID
 * @param mixed   $user_id         normally the WP user ID but could be something different if changed by an add-on
*/
function it_exchange_guest_checkout_guest_has_transaction( $has_transaction, $transaction_id, $user_id ) {
	if ( ! it_exchange_doing_guest_checkout() )
		return $has_transaction;

	$transaction = it_exchange_get_transaction( $transaction_id );

	if ( empty( $transaction->cart_details->is_guest_checkout ) )
		return $has_transaction;

	if ( empty( $transaction->customer_id ) || $transaction->customer_id != $user_id )
		return $has_transaction;

	return true;
}
add_filter( 'it_exchange_customer_has_transaction', 'it_exchange_guest_checkout_guest_has_transaction', 10, 3 );

/**
 * Continues the guest checkout session or ends it based on timeout
 *
 * @since 1.6.0
 *
 * @return void
*/
function it_exchange_handle_guest_checkout_session() {

	// Abandon if also initing. We have another function hooked to template_redirect for that.
	if ( ! empty( $_POST['it-exchange-init-guest-checkout'] ) )
		return;

	$guest_session = it_exchange_get_cart_data( 'guest-checkout' );
	$guest_session = empty( $guest_session ) ? false : reset( $guest_session );

	// IF we don't have a guest session, return
	if ( ! $guest_session )
		return;

	// Grab guest session timeout value from settings
	$settings      = it_exchange_get_option( 'addon-guest-checkout' );
	$timeout       = empty( $settings['cart-expiration'] ) ? 15 : $settings['cart-expiration'];

	// Do some math.
	$expires = $guest_session + ( $timeout * 60 );

	/**
	 * DISABLING SESSION TIMEOUTS FOR NOW 
	if ( ( $expires ) <= time() ) {
		it_exchange_kill_guest_checkout_session();
		it_exchange_add_message( 'notice', __( 'Session has expired.', 'it-l10n-ithemes-exchange' ) );
	} else {
		it_exchange_guest_checkout_bump_session();
	}
	*/
	it_exchange_guest_checkout_bump_session();
}
add_action( 'template_redirect', 'it_exchange_handle_guest_checkout_session', 9 );
add_action( 'it_exchange_super_widget_ajax_top', 'it_exchange_handle_guest_checkout_session', 9 );

/**
 * Modify customer billing address
 *
 * @since 1.6.0
 *
 * @param array $billing_address the billing address returned from customer meta
 * @return mixed
*/
function it_exchange_guest_checkout_handle_billing_address( $address ) {
	if ( ! it_exchange_doing_guest_checkout() )
		return $address;

	if ( ! $guest_billing = it_exchange_get_cart_data( 'guest-billing-address' ) )
		$guest_billing = false;

	return $guest_billing;
}
add_filter( 'it_exchange_get_customer_billing_address', 'it_exchange_guest_checkout_handle_billing_address' );

/**
 * Modify cart billing address
 *
 * @since 1.6.0
 *
 * @return array
*/
function it_exchange_guest_checkout_handle_cart_billing_address( $cart_billing ) {

	if ( ! it_exchange_doing_guest_checkout() )
		return $cart_billing;

	if ( ! $guest_billing = it_exchange_get_cart_data( 'guest-billing-address' ) ) {
		foreach( $cart_billing as $key => $value ) {
			$guest_billing[$key] = '';
		}
	} else {
		$guest_billing = $guest_billing;
	}

	return $guest_billing;

}
add_filter( 'it_exchange_get_cart_billing_address', 'it_exchange_guest_checkout_handle_cart_billing_address' );

/**
 * Do not update the Customer's Billing address if doing guest checkout. Add it to the session instead
 *
 * @since 1.6.0
 *
 * @param array $address the address array that is supposed to be added to the customer
 * @return array
*/
function it_exchange_guest_checkout_handle_update_billing_address( $address ) {
	if ( ! it_exchange_doing_guest_checkout() )
		return $address;

	// Add the address to our cart
	it_exchange_update_cart_data( 'guest-billing-address', $address );

	// Return false so that the customer address doesn't get updated
	return false;
}
add_action( 'it_exchange_save_customer_billing_address', 'it_exchange_guest_checkout_handle_update_billing_address' );

/**
 * Modify customer shipping address
 *
 * @since 1.6.0
 *
 * @param array $shipping_address the shipping address returned from customer meta
 * @return mixed
*/
function it_exchange_guest_checkout_handle_shipping_address( $address ) {
	if ( ! it_exchange_doing_guest_checkout() )
		return $address;

	if ( ! $guest_shipping = it_exchange_get_cart_data( 'guest-shipping-address' ) )
		$guest_shipping = false;

	return $guest_shipping;
}
add_filter( 'it_exchange_get_customer_shipping_address', 'it_exchange_guest_checkout_handle_shipping_address' );

/**
 * Returns the customer email for a guest transaction
 *
 * @since 1.6.0
 *
 * @param string $email the email passed through from the WP filter
 * @param mixed  $transaction the id or the object
*/
function it_exchange_get_guest_checkout_transaction_email( $email, $transaction ) {
	$transaction = it_exchange_get_transaction( $transaction );
	if ( empty( $transaction->cart_details->is_guest_checkout ) )
		return $email;

	return ! empty( $transaction->customer_id ) && is_email( $transaction->customer_id ) ? $transaction->customer_id : $email;
}
add_filter( 'it_exchange_get_transaction_customer_email', 'it_exchange_get_guest_checkout_transaction_email', 10, 2 );

/**
 * Returns the customer id for a guest transaction
 *
 * @since 1.6.0
 *
 * @param string $id          the id passed through from the WP filter
 * @param mixed  $transaction the id or the object
*/
function it_exchange_get_guest_checkout_transaction_id( $id, $transaction ) {
	$transaction = it_exchange_get_transaction( $transaction );
	if ( empty( $transaction->cart_details->is_guest_checkout ) )
		return $id;

	return ! empty( $transaction->customer_id ) && is_email( $transaction->customer_id ) ? $transaction->customer_id : $id;
}
add_filter( 'it_exchange_get_transaction_customer_id', 'it_exchange_get_guest_checkout_transaction_id', 10, 2 );

/**
 * Do not print link to customer details on payment transactions admin page
 *
 * @since 1.6.0
 *
 * @param  boolean $display_link yes or no
 * @param  object  $wp_post      the wp post_type for the transaction
 * @return boolean
*/
function it_exchange_hide_admin_customer_details_link_on_transaction_details_page( $display_link, $wp_post ) {
	if ( ! $transaction = it_exchange_get_transaction( $wp_post->ID ) )
		return $display_link;

	if ( ! empty( $transaction->cart_details->is_guest_checkout ) )
		return false;

	return $display_link;
}
add_filter( 'it_exchange_transaction_detail_has_customer_profile', 'it_exchange_hide_admin_customer_details_link_on_transaction_details_page', 10, 2 );

/**
 * Modify cart shipping address
 *
 * @since 1.6.0
 *
 * @return array
*/
function it_exchange_guest_checkout_handle_cart_shipping_address( $cart_shipping ) {

	if ( ! it_exchange_doing_guest_checkout() )
		return $cart_shipping;

	if ( ! $guest_shipping = it_exchange_get_cart_data( 'guest-shipping-address' ) ) {
		foreach( $cart_shipping as $key => $value ) {
			$guest_shipping[$key] = '';
		}
	} else {
		$guest_shipping = $guest_shipping;
	}

	return $guest_shipping;

}
add_filter( 'it_exchange_get_cart_shipping_address', 'it_exchange_guest_checkout_handle_cart_shipping_address' );

/**
 * Do not update the Customer's shipping address if doing guest checkout. Add it to the session instead
 *
 * @since 1.6.0
 *
 * @param array $address the address array that is supposed to be added to the customer
 * @return array
*/
function it_exchange_guest_checkout_handle_update_shipping_address( $address ) {
	if ( ! it_exchange_doing_guest_checkout() )
		return $address;

	// Add the address to our cart
	it_exchange_update_cart_data( 'guest-shipping-address', $address );

	// Return false so that the customer address doesn't get updated
	return false;
}
add_action( 'it_exchange_save_customer_shipping_address', 'it_exchange_guest_checkout_handle_update_shipping_address' );

/**
 * Flags the user as someone who registered as a guest
 *
 * @since 1.6.0
 *
 * @param  object $data        custoemr data
 * @param  int    $customer_id the wp customer_id
 * @return object
*/
function it_exchange_guest_checkout_set_customer_data( $data, $customer_id ) {
	// Set initial guest status on saved usermeta
	$data->registered_as_guest = (boolean) get_user_meta( $customer_id, 'it-exchange-registered-as-guest', true );
	return $data;
}
add_filter( 'it_exchange_set_customer_data', 'it_exchange_guest_checkout_set_customer_data', 10, 2 );

/**
 * Flag transaction object as guest checkout
 *
 * @since 1.6.0
 *
 * @param object $transaction_object the transaction object right before being added to database
 * @return object
*/
function it_exchange_flag_transaction_as_guest_checkout( $transaction_object ) {
	if ( ! it_exchange_doing_guest_checkout() )
		return $transaction_object;

	$transaction_object->is_guest_checkout = true;
	return $transaction_object;
}
add_filter( 'it_exchange_generate_transaction_object', 'it_exchange_flag_transaction_as_guest_checkout' );

/**
 * Adds post meta to flag as guest checkout after its inserted into the DB
 *
 * So that we can filter it out of queries
 *
 * @since 1.6.0
 *
 * @param integer $transaction_id
 * @return void
*/
function it_exchange_flag_transaction_post_as_guest_checkout( $transaction_id ) {
	$transaction = it_exchange_get_transaction( $transaction_id );

	if ( ! empty( $transaction->cart_details->is_guest_checkout ) )
		update_post_meta( $transaction_id, '_it-exchange-is-guest-checkout', true );

	return $transaction;
}
add_action( 'it_exchange_add_transaction_success', 'it_exchange_flag_transaction_post_as_guest_checkout' );

/**
 * Removes guest checkout transactions from User Purchases
 *
 * If a registerd user checkouts as a guest rather than logging in, the transaction
 * is still attached to them but we don't want to show it to them in their front end profile.
 *
 * @since 1.6.0
 *
 * @param array $args wp post args used for the post query
 * @return array
*/
function it_exchange_guest_checkout_filter_frontend_purchases( $args ) {
	if ( is_admin() || it_exchange_is_page( 'confirmation' ) )
		return $args;

	if ( empty( $args['meta_query'] ) )
		$args['meta_query'] = array();

	$args['meta_query'][] = array(
		'key'     => '_it-exchange-is-guest-checkout',
		'compare' => 'NOT EXISTS',
	);
	return $args;
}
add_filter( 'it_exchange_get_transactions_get_posts_args', 'it_exchange_guest_checkout_filter_frontend_purchases' );

/**
 * Modifies the Transaction Customer data when dealing with a guest checkout
 *
 * @since 1.6.0
 *
 * @param object $customer the customer object
 * @return object
*/
function it_exchange_guest_checkout_modify_transaction_customer( $customer, $transaction ) {
	if ( empty( $transaction->cart_details->is_guest_checkout ) )
		return $customer;

	$customer = ( ! empty( $transaction->customer_id ) && is_email( $transaction->customer_id ) ) ? it_exchange_guest_checkout_generate_guest_user_object( $transaction->customer_id ) : false;
	if ( ! empty( $customer ) ) {
		$customer->wp_user = new stdClass();
		$customer->wp_user->display_name = __( 'Guest Customer', 'it-l10n-ithemes-exchange' );
	}

	return $customer;
}
add_filter( 'it_exchange_get_transaction_customer', 'it_exchange_guest_checkout_modify_transaction_customer', 10, 2 );

/**
 * Modifies the Customer data when dealing with a guest checkout
 *
 * This modifies the feedback on the Checkout Page in the Logged-In purchse requirement
 *
 * @since 1.6.0
 *
 * @param object $customer the customer object
 * @return object
*/
function it_exchange_guest_checkout_modify_customer( $customer ) {

	if ( ! it_exchange_doing_guest_checkout() || is_admin() )
		return $customer;

	$email = it_exchange_get_cart_data( 'guest-checkout-user' );
	$email = is_array( $email ) ? reset( $email ) : $email;
	$customer = it_exchange_guest_checkout_generate_guest_user_object( $email, true );
	return $customer;
}
add_filter( 'it_exchange_get_customer', 'it_exchange_guest_checkout_modify_customer' );

/**
 * This modifies the loginout link generated by WP when we're doing Guest Checkout
 *
 * @since 1.6.0
 *
 * @param string $url      the html for the loginout link
 * @param string $redirect the URL we're redirecting to after logged out.
 * @return string
*/
function it_exchange_guest_checkout_modify_loginout_link( $url, $redirect ) {

	if ( ! it_exchange_doing_guest_checkout() )
		return $url;

	$url = add_query_arg( array( 'it-exchange-guest-logout' => 1 ), $redirect );
	return $url;
}
add_filter( 'logout_url', 'it_exchange_guest_checkout_modify_loginout_link', 10, 2 );

/**
 * Logs out a guest checkout session
 *
 * @since 1.6.0
 *
 * @return void
*/
function it_exchange_logout_guest_checkout_session() {
	if ( ( it_exchange_is_page( 'logout' ) && it_exchange_doing_guest_checkout() ) || ! empty( $_REQUEST['it-exchange-guest-logout'] ) ) {
		it_exchange_kill_guest_checkout_session();
		wp_redirect( remove_query_arg( 'it-exchange-guest-logout' ) );
	}
}
add_action( 'template_redirect', 'it_exchange_logout_guest_checkout_session', 1 );

/**
 * Allow downloads to be served regardless of the requirement to be logged in if user checkout out as a guest
 *
 * @since 1.6.0
 *
 * @param boolean  $setting the default setting
 * @param array    $hash_data the download has data
 * @return boolean
*/
function it_exchange_allow_file_downloads_for_guest_checkout( $setting, $hash_data ) {
	if ( ! $transaction = it_exchange_get_transaction( $hash_data['transaction_id'] ) )
		return $setting;

	return empty( $transaction->cart_details->is_guest_checkout ) ? $setting : false;
}
add_filter( 'it_exchange_require_user_login_for_download', 'it_exchange_allow_file_downloads_for_guest_checkout', 10, 2 );

/**
 * Clear guest session when an authentication attemp happens.
 *
 * @since 1.6.0
 *
 * @param  mixed $incoming Whatever is coming from WP hook API. We don't use it.
 * @return void
*/
function it_exchange_end_guest_checkout_on_login_attempt( $incoming ) {
	if ( it_exchange_doing_guest_checkout() )
		it_exchange_kill_guest_checkout_session();
	return $incoming;
}
add_filter( 'authenticate', 'it_exchange_end_guest_checkout_on_login_attempt' );

/**
 * Proccesses Guest login via superwidget
 *
 * @since 1.6.0
 *
*/
function it_exchange_guest_checkout_process_ajax_login() {

	if ( empty( $_REQUEST['sw-action'] ) || 'guest-checkout' != $_REQUEST['sw-action'] || empty( $_POST['email'] ) ) {
		it_exchange_add_message( 'error', __( 'Please use a properly formatted email address.', 'it-l10n-ithemes-exchange' ) );
		die('0');
	}

	// Vaidate email address
	if ( ! is_email( $_POST['email'] ) ) {
		it_exchange_add_message( 'error', __( 'Please use a properly formatted email address.', 'it-l10n-ithemes-exchange' ) );
		die('0');
	}

	$customer_email = $_POST['email'];

	it_exchange_init_guest_checkout_session( $customer_email );
	die('1');
}
add_action( 'it_exchange_processing_super_widget_ajax_guest-checkout', 'it_exchange_guest_checkout_process_ajax_login' );

/**
 * Remove the download page link in the email if this was a guest checkout transaction
 *
 * @since 1.6.0
 *
 * @param  boolean  $boolean incoming from WP Filter
 * @param  int      $id      the transaction ID
 * @return boolean
*/
function it_exchange_guest_checkout_maybe_remove_download_page_link_from_email( $boolean, $id ) {
	if ( ! $transaction = it_exchange_get_transaction( $id ) )
		return $boolean;

	return empty( $transaction->cart_details->is_guest_checkout );
}
add_filter( 'it_exchange_print_downlods_page_link_in_email', 'it_exchange_guest_checkout_maybe_remove_download_page_link_from_email', 10, 2 );

/**
 * Filter email for sending if its false and we're transaction was a guest checkout
 *
 * @since 1.7.12 
 *
 * @param string $to_email the email address we're sending it to
 * @param object $transaction the transaction object
 * @return string
*/
function it_exchange_guest_checkout_modify_confirmation_email_address( $to_email, $transaction ) {
	if ( ! empty( $to_email ) || empty( $transaction->cart_details->is_guest_checkout ) )
		return $to_email;

	return is_email( $transaction->customer_id ) ? $transaction->customer_id : '';
}
add_filter( 'it_exchange_send_purchase_emails_to', 'it_exchange_guest_checkout_modify_confirmation_email_address', 10, 2 );

