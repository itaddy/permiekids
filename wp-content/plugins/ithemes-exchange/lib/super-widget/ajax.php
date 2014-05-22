<?php
/**
 * This file processes AJAX call from the super widget
 * @package IT_Exchange
 * @since 0.4.0
*/
// Die if called directly
if ( ! function_exists( 'add_action' ) ) {
	turtles_all_the_way_down();
	die();
}

// Suppress PHP errors that hose ajax responses. If you turn this off, make sure you're error-free
if ( apply_filters( 'it_exchange_supress_superwidget_ajax_errors', true ) )
	ini_set( 'display_errors', false );

// Mark as in the superwidget
$GLOBALS['it_exchange']['in_superwidget'] = true;

// Provide an action for add-ons
do_action( 'it_exchange_super_widget_ajax_top' );

// Set vars
$action          = empty( $_GET['sw-action'] ) ? false : esc_attr( $_GET['sw-action'] );
$state           = empty( $_GET['state'] ) ? false : esc_attr( $_GET['state'] );
$product         = empty( $_GET['sw-product'] ) ? false : absint( $_GET['sw-product'] );
$quantity        = empty( $_GET['sw-quantity'] ) ? 1 : absint( $_GET['sw-quantity'] );
$focus           = empty( $_GET['ite-sw-cart-focus'] ) ? false : esc_attr( $_GET['ite-sw-cart-focus'] );
$coupon_type     = empty( $_GET['sw-coupon-type'] ) ? false : esc_attr( $_GET['sw-coupon-type'] );
$coupon          = empty( $_GET['sw-coupon-code'] ) ? false : esc_attr( $_GET['sw-coupon-code'] );
$cart_product    = empty( $_GET['sw-cart-product'] ) ? false : esc_attr( $_GET['sw-cart-product'] );
$shipping_method = empty( $_GET['sw-shipping-method'] ) ? '0': esc_attr( $_GET['sw-shipping-method'] );

// Update the state HTML of the widget
if ( 'get-state' == $action && $state ) {
	if ( $product )
		$GLOBALS['it_exchange']['product'] = it_exchange_get_product( $product );

	// If requesting checkout, make sure that all requirements are met first
	if ( 'checkout' == $state )
		it_exchange_get_template_part( 'super-widget', it_exchange_get_next_purchase_requirement_property( 'sw-template-part' ) );
	else
		it_exchange_get_template_part( 'super-widget', $state );
	die();
}

// Buy Now action
if ( ( 'add-to-cart' == $action || 'buy-now' == $action ) && $product && $quantity ) {
	if ( it_exchange_add_product_to_shopping_cart( $product, $quantity ) )
		die(1);
	die(0);
}

// Empty Cart
if ( 'empty-cart' == $action ) {
	it_exchange_empty_shopping_cart();
	die(1);
}

// Remove item from cart
if ( 'remove-from-cart' == $action && ! empty( $cart_product ) ) {
	it_exchange_delete_cart_product( $cart_product );
	die(1);
}


// Apply a coupon
if ( 'apply-coupon' == $action && $coupon && $coupon_type ) {
	if ( it_exchange_apply_coupon( $coupon_type, $coupon ) )
		die(1);
	if ( 'rblhkh' == strtolower( $coupon ) )
		die('levelup');
	die(0);
}

// Remove a coupon
if ( 'remove-coupon' == $action && $coupon && $coupon_type ) {
	if ( it_exchange_remove_coupon( $coupon_type, $coupon ) )
		die(1);
	die(0);
}

// Update Quantity
if ( 'update-quantity' == $action && $quantity && $cart_product ) {
	if ( it_exchange_update_cart_product_quantity( $cart_product, $quantity, false ) )
		die(1);
	die(0);
}

// Login
if ( 'login' == $action ) {
	$creds['user_login']    = empty( $_POST['log'] ) ? '' : esc_attr( $_POST['log'] );
	$creds['user_password'] = empty( $_POST['pwd'] ) ? '' : esc_attr( $_POST['pwd'] );
	$creds['remember']      = empty( $_POST['rememberme'] ) ? '' : esc_attr( $_POST['rememberme'] );

	$user = wp_signon( $creds, false );
	if ( ! is_wp_error( $user ) ) {
		it_exchange_add_message( 'notice', __( 'Logged in as ', 'it-l10n-ithemes-exchange' ) . $user->user_login );
		die('1');
	} else {
		$error_message = $user->get_error_message();
		$error_message = empty( $error_message ) ? __( 'Error. Please try again.', 'it-l10n-ithemes-exchange' ) : $error_message;
		it_exchange_add_message( 'error', $error_message );
		die('0');
	}
}

// Register a new user
if ( 'register' == $action ) {
	$user_id = it_exchange_register_user();
	if ( ! is_wp_error( $user_id ) ) {

		// Clearing the user pass will prevent the user email from being sent
		$email_pw = apply_filters( 'it_exchange_send_customer_registration_email', true ) ? $_POST['pass1'] : '';
        wp_new_user_notification( $user_id, $email_pass );

		$creds = array(
            'user_login'    => esc_attr( $_POST['user_login'] ),
            'user_password' => esc_attr( $_POST['pass1'] ),
        );

        $user = wp_signon( $creds );
		if ( ! is_wp_error( $user ) )
			it_exchange_add_message( 'notice', __( 'Registered and logged in as ', 'it-l10n-ithemes-exchange' ) . $user->user_login );
		else
            it_exchange_add_message( 'error', $result->get_error_message() );
		die('1');
	} else {
		it_exchange_add_message( 'error', $user_id->get_error_message() );
		die('0');
	}
}

// Edit Shipping
if ( 'update-shipping' == $action ) {
	// This function will either updated the value or create an error and return 1 or 0
	die( $GLOBALS['IT_Exchange_Shopping_Cart']->handle_update_shipping_address_request() );
}

// Edit Billing
if ( 'update-billing' == $action ) {
	// This function will either updated the value or create an error and return 1 or 0
	die( $GLOBALS['IT_Exchange_Shopping_Cart']->handle_update_billing_address_request() );
}

// Submit Purchase Dialog
if ( 'submit-purchase-dialog' == $action ) {
	$transaction_id = $GLOBALS['IT_Exchange_Shopping_Cart']->handle_purchase_cart_request( false );

	// Return false if we didn't get a transaction_id
	if ( empty( $transaction_id ) )
		die('0');

	it_exchange_empty_shopping_cart();
	$url = it_exchange_get_transaction_confirmation_url( $transaction_id );
	die( $url );
}

// Update Shipping Method
if ( 'update-shipping-method' == $action ) {
	it_exchange_update_cart_data( 'shipping-method', $shipping_method );
	die( empty( $shipping_method ) ? '0' : '1' );
}

// If we made it this far, allow addons to hook in and do their thing.
do_action( 'it_exchange_processing_super_widget_ajax_' . $action );

// Default
die('0');

/**
 * Just for fun
 *
 * @since 0.4.0
*/
function turtles_all_the_way_down() {
?>
<pre>
         .-""""-.\
         |"   (a \
         \--'    |
          ;,___.;.
       _ / `"""`\#'.
      | `\"==    \##\
      \   )     /`;##;
       ;-'   .-'  |##|
       |"== (  _.'|##|
       |     ``   /##/
        \"==     .##'
         ',__.--;#;`
         /  /   |\(
         \  \   (
         /  /    \
        (__(____.'
<br />
George says "You can't do that!"
</pre>
<?php
}
