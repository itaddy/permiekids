<?php
/**
 * Shopping cart class.
 * @since 0.3.8
 * @package IT_Exchange
*/
class IT_Exchange_Shopping_Cart {

	/**
	 * Class constructor.
	 *
	 * Hooks default filters and actions for cart
	 *
	 * @since 0.3.8
	 * @return void
	*/
	function IT_Exchange_Shopping_Cart() {
		add_action( 'template_redirect', array( $this, 'handle_it_exchange_cart_function' ) );
		add_filter( 'it_exchange_process_transaction', array( $this, 'handle_purchase_cart_request' ) );
	}

	/**
	 * Handles $_REQUESTs and submits them to the cart for processing
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function handle_it_exchange_cart_function() {

		$this->redirect_checkout_if_empty_cart(); //if on checkout but have empty cart, redirect

		// Grab action and process it.
		if ( isset( $_REQUEST['it-exchange-action'] ) ) {
			call_user_func( array( $this, 'handle_' . esc_attr( $_REQUEST['it-exchange-action'] ) . '_request' ) );
			return;
		}

		// Possibly Handle Remove Product Request
		$remove_from_cart_var = it_exchange_get_field_name( 'remove_product_from_cart' );
		if ( ! empty( $_REQUEST[$remove_from_cart_var] ) ) {
			$this->handle_remove_product_from_cart_request();
			return;
		}

		// Possibly Handle Update Cart Request
		$update_cart_var = it_exchange_get_field_name( 'update_cart_action' );
		if ( ! empty( $_REQUEST[$update_cart_var] ) ) {
			$this->handle_update_cart_request();
			return;
		}

		// Possibly Handle Proceed to checkout
		$proceed_var = it_exchange_get_field_name( 'proceed_to_checkout' );
		if ( ! empty( $_REQUEST[$proceed_var] ) ) {
			$this->proceed_to_checkout();
			return;
		}

		// Possibly Handle Empty Cart request
		$empty_var = it_exchange_get_field_name( 'empty_cart' );
		if ( ! empty( $_REQUEST[$empty_var] ) ) {
			$this->handle_empty_shopping_cart_request();
			return;
		}

		// Possibly Handle Continue Shopping Request
		$empty_var = it_exchange_get_field_name( 'continue_shopping' );
		if ( ! empty( $_REQUEST[$empty_var] ) ) {
			if ( $url = it_exchange_get_page_url( 'store' ) ) {
				wp_redirect( $url );
				die();
			}
			return;
		}

		// Possibly handle update shipping address request
		if ( ! empty( $_REQUEST['it-exchange-update-shipping-address'] ) ) {
			$this->handle_update_shipping_address_request();
			return;
		}

		// Possibly handle update billing address request
		if ( ! empty( $_REQUEST['it-exchange-update-billing-address'] ) ) {
			$this->handle_update_billing_address_request();
			return;
		}
	}

	/**
	 * Listens for $_REQUESTs to buy a product now
	 *
	 * @since 0.3.8
	 * @return void
	*/
	function handle_buy_now_request() {

		$buy_now_var = it_exchange_get_field_name( 'buy_now' );
		$product_id = empty( $_REQUEST[$buy_now_var] ) ? 0 : $_REQUEST[$buy_now_var];
		$product    = it_exchange_get_product( $product_id );
		$quantity_var    = it_exchange_get_field_name( 'product_purchase_quantity' );
		$requested_quantity = empty( $_REQUEST[$quantity_var] ) ? 1 : absint( $_REQUEST[$quantity_var] );
		$cart = it_exchange_get_page_url( 'cart' );

		// Vefify legit product
		if ( ! $product )
			$error = 'bad-product';

		// Verify nonce
		$nonce_var = apply_filters( 'it_exchange_purchase_product_nonce_var', '_wpnonce' );
		if ( empty( $_REQUEST[$nonce_var] ) || ! wp_verify_nonce( $_REQUEST[$nonce_var], 'it-exchange-purchase-product-' . $product_id ) )
			$error = 'product-not-added-to-cart';

		// Add product
		if ( empty( $error ) && it_exchange_add_product_to_shopping_cart( $product_id, $requested_quantity ) ) {
			$sw_state = is_user_logged_in() ? 'checkout' : 'login';
			// Get current URL without exchange query args
			$url = it_exchange_clean_query_args();
			$url = ( it_exchange_is_multi_item_cart_allowed() && it_exchange_get_page_url( 'checkout' ) ) ? it_exchange_get_page_url( 'checkout' ) : add_query_arg( 'ite-sw-state', $sw_state, $url );
			wp_redirect( $url );
			die();
		}

		$error = empty( $error ) ? 'product-not-added-to-cart' : $error;
		it_exchange_add_message( 'error', __( 'Product not added to cart', 'it-l10n-ithemes-exchange' ) );
		wp_redirect( $url );
		die();
	}

	/**
	 * Listens for $_REQUESTs to add a product to the cart and processes
	 *
	 * @since 0.3.8
	 * @return void
	*/
	function handle_add_product_to_cart_request() {

		$add_to_cart_var = it_exchange_get_field_name( 'add_product_to_cart' );
		$product_id = empty( $_REQUEST[$add_to_cart_var] ) ? 0 : $_REQUEST[$add_to_cart_var];
		$product    = it_exchange_get_product( $product_id );
		$quantity_var    = it_exchange_get_field_name( 'product_purchase_quantity' );
		$requested_quantity = empty( $_REQUEST[$quantity_var] ) ? 1 : absint( $_REQUEST[$quantity_var] );
		$cart = it_exchange_get_page_url( 'cart' );

		// Vefify legit product
		if ( ! $product )
			$error = 'bad-product';

		// Verify nonce
		$nonce_var = apply_filters( 'it_exchange_purchase_product_nonce_var', '_wpnonce' );
		if ( empty( $_REQUEST[$nonce_var] ) || ! wp_verify_nonce( $_REQUEST[$nonce_var], 'it-exchange-purchase-product-' . $product_id ) )
			$error = 'product-not-added-to-cart';

		// Add product
		if ( empty( $error ) && it_exchange_add_product_to_shopping_cart( $product_id, $requested_quantity ) ) {
			$sw_state = is_user_logged_in() ? 'cart' : 'login';
			// Get current URL without exchange query args
			$url = it_exchange_clean_query_args();
			$url = ( it_exchange_is_multi_item_cart_allowed() && it_exchange_get_page_url( 'cart' ) ) ? it_exchange_get_page_url( 'cart' ) : add_query_arg( 'ite-sw-state', $sw_state, $url );
			it_exchange_add_message( 'notice', __( 'Product added to cart', 'it-l10n-ithemes-exchange' ) );
			wp_redirect( $url );
			die();
		}

		$error_var = it_exchange_get_field_name( 'error_message' );
		$error = empty( $error ) ? 'product-not-added-to-cart' : $error;
		$url  = add_query_arg( array( $error_var => $error ), $cart );
		wp_redirect( $url );
		die();
	}

	/**
	 * Empty the iThemes Exchange shopping cart
	 *
	 * @since 0.3.8
	 * @return void
	*/
	function handle_empty_shopping_cart_request() {
		// Verify nonce
		$nonce_var   = apply_filters( 'it_exchange_cart_action_nonce_var', '_wpnonce' );
		$error_var   = it_exchange_get_field_name( 'error_message' );
		$message_var = it_exchange_get_field_name( 'alert_message' );
		$session_id  = it_exchange_get_session_id();

		if ( it_exchange_is_multi_item_cart_allowed() )
			$cart = it_exchange_get_page_url( 'cart' );
		else
			$cart = it_exchange_clean_query_args();

		if ( empty( $_REQUEST[$nonce_var] ) || ! wp_verify_nonce( $_REQUEST[$nonce_var], 'it-exchange-cart-action-' . $session_id ) ) {
			$url = add_query_arg( array( $error_var => 'cart-not-emptied' ), $cart );
			$url = remove_query_arg( it_exchange_get_field_name( 'empty_cart' ), $url );
			wp_redirect( $url );
			die();
		}

		it_exchange_empty_shopping_cart();

		$url = remove_query_arg( $error_var, $cart );
		$url = add_query_arg( array( $message_var => 'cart-emptied' ), $url );
		$url = remove_query_arg( it_exchange_get_field_name( 'empty_cart' ), $cart );
		wp_redirect( $url );
		die();
	}

	/**
	 * Removes a single product from the shopping cart
	 *
	 * This listens for REQUESTS to remove a product from the cart, verifies the request, and passes it along to the correct function
	 *
	 * @since 0.3.8
	 * @return void
	*/
	function handle_remove_product_from_cart_request() {
		$var             = it_exchange_get_field_name( 'remove_product_from_cart' );
		$car_product_ids = empty( $_REQUEST[$var] ) ? array() : $_REQUEST[$var];
		$session_id      = it_exchange_get_session_id();

		// Base URL
		if ( it_exchange_is_multi_item_cart_allowed() )
			$cart_url = it_exchange_get_page_url( 'cart' );
		else
			$cart_url = it_exchange_clean_query_args();

		// Verify nonce
		$nonce_var = apply_filters( 'it_exchange_remove_product_from_cart_nonce_var', '_wpnonce' );
		if ( empty( $_REQUEST[$nonce_var] ) || ! wp_verify_nonce( $_REQUEST[$nonce_var], 'it-exchange-cart-action-' . $session_id ) ) {
			$var = it_exchange_get_field_name( 'error_message' );
			$url  = add_query_arg( array( $var => 'product-not-removed' ), $cart_url );
			wp_redirect( $url );
			die();
		}

		foreach( (array) $car_product_ids as $car_product_id ) {
			it_exchange_delete_cart_product( $car_product_id );
		}

		$var = it_exchange_get_field_name( 'alert_message' );
		$url = add_query_arg( array( $var => 'product-removed' ), $cart_url );
		wp_redirect( $url );
		die();
	}

	/**
	 * Listens for the REQUEST to update the shopping cart, verifies it, and calls the correct function
	 *
	 * @since 0.3.8
	 * @return void
	*/
	function handle_update_cart_request( $redirect=true ) {
		$session_id = it_exchange_get_session_id();
		// Verify nonce
		$nonce_var = apply_filters( 'it_exchange_cart_action_nonce_var', '_wpnonce' );
		if ( it_exchange_is_multi_item_cart_allowed() ) {
			$cart = it_exchange_get_page_url( 'cart' );
		} else {
			$cart = it_exchange_clean_query_args( array( it_exchange_get_field_name( 'sw_cart_focus' ) ) );
			if ( it_exchange_in_superwidget() )
				$cart = add_query_arg( 'ite-sw-state', 'cart', $cart );
		}
		if ( empty( $_REQUEST[$nonce_var] ) || ! wp_verify_nonce( $_REQUEST[$nonce_var], 'it-exchange-cart-action-' . $session_id ) ) {
			$var = it_exchange_get_field_name( 'error_message' );

			$url = add_query_arg( array( $var => 'cart-not-updated' ), $cart );
			$url = remove_query_arg( it_exchange_get_field_name( 'empty_cart' ), $url );
			wp_redirect( $url );
			die();
		}

		// Are we updating any quantities
		$var_name = it_exchange_get_field_name( 'product_purchase_quantity' );
		if ( ! empty( $_REQUEST[$var_name] ) ) {
			foreach( (array) $_REQUEST[$var_name] as $cart_product_id => $quantity ) {
				it_exchange_update_cart_product_quantity( $cart_product_id, $quantity, false );
			}
		}

		do_action( 'it_exchange_update_cart' );

		$message_var = it_exchange_get_field_name( 'alert_message' );
		if ( ! empty ( $message_var ) && $redirect ) {
			$url = remove_query_arg( $message_var, $cart );
			$url = add_query_arg( array( $message_var => 'cart-updated' ), $url );
			$url = remove_query_arg( it_exchange_get_field_name( 'empty_cart' ), $url );

			wp_redirect( $url );
			die();
		}
	}

	/**
	 * Handles updating a Shipping address
	 *
	 * @since 1.4.0
	 *
	 * @return void
	*/
	function handle_update_shipping_address_request() {

		// Validate nonce
		if ( empty( $_REQUEST['it-exchange-update-shipping-address'] ) || ! wp_verify_nonce( $_REQUEST['it-exchange-update-shipping-address'], 'it-exchange-update-checkout-shipping-address-' . it_exchange_get_session_id() ) ) {
			it_exchange_add_message( 'error', __( 'Error adding Shipping Address. Please try again.', 'it-l10n-ithemes-exchange' ) );
			$GLOBALS['it_exchange']['shipping-address-error'] = true;
			return false;
		}

		// Validate required fields
		$required_fields = apply_filters( 'it_exchange_required_shipping_address_fields', array( 'first-name', 'last-name', 'address1', 'state', 'country', 'zip' ) );
		foreach( $required_fields as $field ) {
			if ( empty( $_REQUEST['it-exchange-shipping-address-' . $field] ) ) {
				it_exchange_add_message( 'error', __( 'Please fill out all required fields', 'it-l10n-ithemes-exchange' ) );
				$GLOBALS['it_exchange']['shipping-address-error'] = true;
				return false;
			}
		}

		/** @todo This is hardcoded for now. will be more flexible at some point **/
		$shipping = array();
		$fields = array(
			'first-name',
			'last-name',
			'company-name',
			'address1',
			'address2',
			'city',
			'state',
			'zip',
			'country',
			'email',
			'phone',
		);
		foreach( $fields as $field ) {
			$shipping[$field] = empty( $_REQUEST['it-exchange-shipping-address-' . $field] ) ? '' : $_REQUEST['it-exchange-shipping-address-' . $field];
		}

		if ( it_exchange_save_shipping_address( $shipping, it_exchange_get_current_customer_id() ) ) {
			it_exchange_add_message( 'notice', __( 'Shipping Address Saved', 'it-l10n-ithemes-exchange' ) );
			return true;
		}
		return false;
	}

	/**
	 * Handles updating a billing address
	 *
	 * @since 1.3.0
	 *
	 * @return void
	*/
	function handle_update_billing_address_request() {

		// Validate nonce
		if ( empty( $_REQUEST['it-exchange-update-billing-address'] ) || ! wp_verify_nonce( $_REQUEST['it-exchange-update-billing-address'], 'it-exchange-update-checkout-billing-address-' . it_exchange_get_session_id() ) ) {
			it_exchange_add_message( 'error', __( 'Error adding Billing Address. Please try again.', 'it-l10n-ithemes-exchange' ) );
			$GLOBALS['it_exchange']['billing-address-error'] = true;
			return false;
		}

		// Validate required fields
		$required_fields = apply_filters( 'it_exchange_required_billing_address_fields', array( 'first-name', 'last-name', 'address1', 'city', 'state', 'country', 'zip' ) );
		foreach( $required_fields as $field ) {
			if ( empty( $_REQUEST['it-exchange-billing-address-' . $field] ) ) {
				it_exchange_add_message( 'error', __( 'Please fill out all required fields', 'it-l10n-ithemes-exchange' ) );
				$GLOBALS['it_exchange']['billing-address-error'] = true;
				return false;
			}
		}

		/** @todo This is hardcoded for now. will be more flexible at some point **/
		$billing = array();
		$fields = array(
			'first-name',
			'last-name',
			'company-name',
			'address1',
			'address2',
			'city',
			'state',
			'zip',
			'country',
			'email',
			'phone',
		);
		foreach( $fields as $field ) {
			$billing[$field] = empty( $_REQUEST['it-exchange-billing-address-' . $field] ) ? '' : $_REQUEST['it-exchange-billing-address-' . $field];
		}

		if ( it_exchange_save_customer_billing_address( $billing ) ) {
			it_exchange_add_message( 'notice', __( 'Billing Address Saved', 'it-l10n-ithemes-exchange' ) );

			// Update Shipping if checked
			if ( ! empty( $_REQUEST['it-exchange-ship-to-billing'] ) && '1' == $_REQUEST['it-exchange-ship-to-billing'] )
				it_exchange_save_shipping_address( $billing, it_exchange_get_current_customer_id() );
				
		}
		return true;
	}

	/**
	 * Advances the user to the checkout screen after updating the cart
	 *
	 * @since 0.3.8
	 * @return void
	*/
	function proceed_to_checkout() {

		// Update cart info before redirecting.
		$this->handle_update_cart_request( false );

		// Redirect to Checkout
		if ( $checkout = it_exchange_get_page_url( 'checkout' ) ) {
			wp_redirect( $checkout );
			die();
		}
	}

	/**
	 * Process checkout
	 *
	 * Formats data and hands it off to the appropriate tranaction method
	 *
	 * @since 0.3.8
	 * @param bool $status
	 * @return boolean
	*/
	function handle_purchase_cart_request( $status ) {

		if ( $status ) //if this has been modified as true already, return.
			return $status;

		// Verify transaction method exists
		$method_var = it_exchange_get_field_name( 'transaction_method' );
		$requested_transaction_method = empty( $_REQUEST[$method_var] ) ? false : $_REQUEST[$method_var];
		$enabled_addons = it_exchange_get_enabled_addons( array( 'category' => 'transaction-methods' ) );
		if ( ! $requested_transaction_method || empty( $enabled_addons[$requested_transaction_method] ) ) {
			do_action( 'it_exchange_error_bad_transaction_method_at_purchase', $requested_transaction_method );
			it_exchange_add_message( 'error', $this->get_cart_message( 'bad-transaction-method' ) );
			return false;
		}

		if ( $transaction_object = it_exchange_generate_transaction_object() ) {

			$transaction_object = apply_filters( 'it_exchange_transaction_object', $transaction_object, $requested_transaction_method );

			// Do the transaction
			return it_exchange_do_transaction( $requested_transaction_method, $transaction_object );

		}

		return false;
	}

	/**
	 * Redirect from checkout to cart if there are no items in the cart
	 *
	 * @since 0.3.8
	 * @return void
	*/
	function redirect_checkout_if_empty_cart() {
		$cart     = it_exchange_get_page_url( 'cart' );
		$checkout = it_exchange_get_page_url( 'checkout' );

		if ( empty( $checkout ) || ! is_page( $checkout ) )
			return;

		$products = it_exchange_get_cart_products();
		if ( empty( $products ) ){
			wp_redirect( $cart );
			die();
		}
	}

	/**
	 * Gets message for given key
	 *
	 * @since 0.4.0
	 * @param string $key
	 * @return string
	*/
	function get_cart_message( $key ) {
		$message = $this->default_cart_messages();
		return ( !empty( $message[$key] ) ) ? $message[$key] : __( 'Unknown error. Please try again.', 'it-l10n-ithemes-exchange' );;
	}

	/**
	 * Sets up default messages
	 *
	 * @since 0.4.0
	 * @return array
	*/
	function default_cart_messages() {
		$messages['bad-transaction-method'] = __( 'Please select a payment method', 'it-l10n-ithemes-exchange' );
		$messages['failed-transaction']     = __( 'There was an error processing your transaction. Please try again.', 'it-l10n-ithemes-exchange' );
		$messages['product-not-removed']    = __( 'Product not removed from cart. Please try again.', 'it-l10n-ithemes-exchange' );
		$messages['cart-not-emptied']       = __( 'There was an error emptying your cart. Please try again.', 'it-l10n-ithemes-exchange' );
		$messages['cart-not-updated']       = __( 'There was an error updating your cart. Please try again.', 'it-l10n-ithemes-exchange' );
		$messages['cart-updated']          = __( 'Cart Updated.', 'it-l10n-ithemes-exchange' );
		$messages['cart-emptied']          = __( 'Cart Emptied', 'it-l10n-ithemes-exchange' );
		$messages['product-removed']       = __( 'Product removed from cart.', 'it-l10n-ithemes-exchange' );
		$messages['product-added-to-cart'] = __( 'Product added to cart', 'it-l10n-ithemes-exchange' );

		return apply_filters( 'it_exchange_default_cart_messages', $messages );
	}
}

if ( ! is_admin() )
	$GLOBALS['IT_Exchange_Shopping_Cart'] = new IT_Exchange_Shopping_Cart();
