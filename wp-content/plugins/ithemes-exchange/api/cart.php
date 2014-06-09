<?php
/**
 * This file contains functions intended for theme developers to interact with the active shopping cart plugin
 *
 * The active shopping cart plugin should add the needed hooks below within its codebase.
 *
 * @since 0.3.7
 * @package IT_Exchange
*/

/**
 * Returns an array of all data in the cart
 *
 * @since 0.3.7
 * @return array
*/
function it_exchange_get_cart_data( $key = false ) {
	$data = it_exchange_get_session_data( $key );
	return apply_filters( 'it_exchange_get_cart_data', $data );
}

/**
 * Updates the data
 *
 * @since 0.4.0
 * @return void
*/
function it_exchange_update_cart_data( $key, $data ) {
	it_exchange_update_session_data( $key, $data );
	do_action( 'it_exchange_update_cart_data', $data, $key );
}

/**
 * Removes cart data by key
 *
 * @since 0.4.0
*/
function it_exchange_remove_cart_data( $key ) {
	it_exchange_clear_session_data( $key );
	do_action( 'it_exchange_remove_cart_data', $key );
}

/**
 * Returns an array of all products in the cart
 *
 * @since 0.3.7
 * @return array
*/
function it_exchange_get_cart_products() {
	$products = it_exchange_get_session_data( 'products' );
	return ( empty( $products ) || ! array( $products ) ) ? array() : $products;
}

/**
 * Inserts product into the cart session
 *
 * @since 0.4.0
 * @return array
*/
function it_exchange_add_cart_product( $cart_product_id, $product ) {
	it_exchange_add_session_data( 'products', array( $cart_product_id => $product ) );
	do_action( 'it_exchange_add_cart_product', $product );
}

/**
 * Updates product into the cart session
 *
 * @since 0.4.0
 * @return array
*/
function it_exchange_update_cart_product( $cart_product_id, $product ) {
	$products = it_exchange_get_session_data( 'products' );
	if ( isset( $products[$cart_product_id] ) ) {
		$products[$cart_product_id] = $product;
		it_exchange_update_session_data( 'products', $products );
	} else {
		it_exchange_add_cart_product( $cart_product_id, $product );
	}
	do_action( 'it_exchange_update_cart_product', $cart_product_id, $product, $products );
}

/**
 * Deletes product from the cart session
 *
 * @since 0.4.0
 * @return array
*/
function it_exchange_delete_cart_product( $cart_product_id ) {
	$products = it_exchange_get_session_data( 'products' );
	if ( isset( $products[$cart_product_id] ) ) {
		unset( $products[$cart_product_id] );
		it_exchange_update_session_data( 'products', $products );
	}
	do_action( 'it_exchange_delete_cart_product', $cart_product_id, $products );
}

/**
 * Returns a specific product from the cart.
 *
 * The returned data is not an iThemes Exchange Product object. It is a cart-product
 *
 * @since 0.3.7
 * @param mixed $id id for the cart's product data
 * @return mixed
*/
function it_exchange_get_cart_product( $id ) {
	if ( ! $products = it_exchange_get_cart_products() )
		return false;

	if ( empty( $products[$id] ) )
		return false;

	return apply_filters( 'it_exchange_get_cart_product', $products[$id], $id );
}

/**
 * Checks if the current product being viewd is in the cart
 *
 * @since 0.4.10
 * @return bool true if in cart|false if not
*/
function it_exchange_is_current_product_in_cart() {
	$product_id = false;
	$cart_products = it_exchange_get_cart_products();
	$product = empty( $GLOBALS['post'] ) ? false : it_exchange_get_product( $GLOBALS['post'] );

	if ( ! empty( $product ) )
		$product_id = $product->ID;
	else if ( ! empty( $_GET['sw-product'] ) )
		$product_id = $_GET['sw-product'];

	foreach( $cart_products as $cart_product ) {
		if ( $product_id == $cart_product['product_id'] )
			return true;
	}

	return false;
}

/**
 * Adds a product to the shopping cart based on the product_id
 *
 * @since 0.3.7
 * @param string $product_id a valid wp post id with an iThemes Exchange product post_typp
 * @param int $quantity (optional) how many?
 * return boolean
*/
function it_exchange_add_product_to_shopping_cart( $product_id, $quantity=1 ) {

	if ( ! $product_id )
		return;

	if ( ! $product = it_exchange_get_product( $product_id ) )
		return;

	$quantity = absint( (int) $quantity );
	if ( $quantity < 1 )
		$quantity = 1; //we're going to assume they want at least 1 item

	/**
	 * The default shopping cart organizes products in the cart by product_id and a hash of 'itemized_data'.
	 * Any data like product variants or pricing mods that should separate products in the cart can be passed through this filter.
	*/
	$itemized_data = apply_filters( 'it_exchange_add_itemized_data_to_cart_product', array(), $product_id );

	if ( ! is_serialized( $itemized_data ) )
		$itemized_data = maybe_serialize( $itemized_data );
	$itemized_hash = md5( $itemized_data );

	/**
	 * Any data that needs to be stored in the cart for this product but that should not trigger a new itemized row in the cart
	*/
	$additional_data = apply_filters( 'it_exchange_add_additional_data_to_cart_product', array(), $product_id );
	if ( ! is_serialized( $additional_data ) )
		$additional_data = maybe_serialize( $additional_data );

	// Doe we have anything in the cart already?
	$session_products = it_exchange_get_cart_products();

	/**
	 * If multi-item carts are allowed, don't do antying here.
	 * If multi-item carts are NOT allowed and this is a different item, empty the cart before proceeding.
	 * If item being added to cart is already in cart, preserve that item so that quanity will be bumpped.
	*/
	if ( ! it_exchange_is_multi_item_cart_allowed() || ! it_exchange_is_multi_item_product_allowed( $product_id ) ) {
		if ( ! empty( $session_products ) ) {
			// Preserve the current item being added if its already in the cart
			if ( ! empty( $session_products[$product_id . '-' . $itemized_hash] ) )
				$preserve_for_quantity_bump = $session_products[$product_id . '-' . $itemized_hash];

			// Empty the cart to ensure only one item
			it_exchange_empty_shopping_cart();

			// Add the existing item back if found
			if ( ! empty( $preserve_for_quantity_bump ) )
				it_exchange_add_cart_product( $preserve_for_quantity_bump['product_cart_id'], $preserve_for_quantity_bump );

			// Reset the session products
			$session_products = it_exchange_get_cart_products();
		}
	}

	// If product is in cart already, bump the quanity. Otherwise, add it to the cart
	if ( ! empty ($session_products[$product_id . '-' . $itemized_hash] ) ) {
		$product = $session_products[$product_id . '-' . $itemized_hash];

		return it_exchange_update_cart_product_quantity( $product_id . '-' . $itemized_hash, $quantity );

	} else {

		// If we don't support purchase quanity, quanity will always be 1
		if ( it_exchange_product_supports_feature( $product_id, 'purchase-quantity' ) ) {

			// Get max quantity setting
			$max_purchase_quantity = it_exchange_get_product_feature( $product_id, 'purchase-quantity' );
			$max_purchase_quantity = apply_filters( 'it_exchange_max_purchase_quantity_cart_check', $max_purchase_quantity, $product_id, $itemized_data, $additional_data, $itemized_hash );
			$count = ( $max_purchase_quantity && $quantity > $max_purchase_quantity ) ? $max_purchase_quantity : $quantity;
		} else {
			$count = 1;
		}

		$product = array(
			'product_cart_id' => $product_id . '-' . $itemized_hash,
			'product_id'      => $product_id,
			'itemized_data'   => $itemized_data,
			'additional_data' => $additional_data,
			'itemized_hash'   => $itemized_hash,
			'count'           => $count,
		);

		it_exchange_add_cart_product( $product_id . '-' . $itemized_hash, $product );
		do_action( 'it_exchange_product_added_to_cart', $product_id );
		return true;
	}
	return false;
}

/**
 * Updates the quantity for a specific cart item
 *
 * @since 0.4.0

 * @param int $cart_product_id the product ID prepended to the itemized hash by a hyphen
 * @param int $quantity the incoming quantity
 * @param boolean $add_to_existing if set to false, it replaces the existing.
 * @return void
*/
function it_exchange_update_cart_product_quantity( $cart_product_id, $quantity, $add_to_existing=true ) {
	// Get cart products
	$cart_products = it_exchange_get_cart_products();

	// Update Quantity
	if ( ! empty( $cart_products[$cart_product_id] ) && is_numeric( $quantity ) ) {
		$cart_product = $cart_products[$cart_product_id];
		if ( empty( $quantity ) || $quantity < 1 ) {
			it_exchange_delete_cart_product( $cart_product_id );
		} else {

			// If we don't support purchase quanity, quanity will always be 1
			if ( it_exchange_product_supports_feature( $cart_product['product_id'], 'purchase-quantity' ) ) {
				// Get max quantity setting
				$max_purchase_quantity = it_exchange_get_product_feature( $cart_product['product_id'], 'purchase-quantity' );

				// Zero out existing if we're not adding incoming quantity to it.
				if ( ! $add_to_existing )
					$cart_product['count'] = 0;

				// If we support it but don't have it, quantity is unlimited
				if ( ! $max_purchase_quantity )
					$cart_product['count'] = $cart_product['count'] + $quantity;
				else
					$cart_product['count'] = ( ( $cart_product['count'] + $quantity ) > $max_purchase_quantity ) ? $max_purchase_quantity : $quantity + $cart_product['count'];
			} else {
				$cart_product['count'] = 1;
			}

			it_exchange_update_cart_product( $cart_product_id, $cart_product );
			do_action( 'it_exchange_cart_prouduct_count_updated', $cart_product_id );
			return true;
		}
	}
}

/**
 * Empties the cart
 *
 * @since 0.3.7
 * @return boolean
*/
function it_exchange_empty_shopping_cart() {
	it_exchange_clear_session_data( 'products' );
	do_action( 'it_exchange_empty_shopping_cart' );
}

/**
 * Are multi item carts allowed?
 *
 * Default is no. Addons must tell us yes as well as provide any pages needed for a cart / checkout / etc.
 *
 * @since 0.4.0
 * @return boolean
*/
function it_exchange_is_multi_item_cart_allowed() {
	return apply_filters( 'it_exchange_multi_item_cart_allowed', false );
}

/**
 * Is this product allowed to be added to a multi-item cart?
 *
 * Default is true.
 *
 * @since 1.3.0
 * @return boolean
*/
function it_exchange_is_multi_item_product_allowed( $product_id ) {
	return apply_filters( 'it_exchange_multi_item_product_allowed', true, $product_id );
}

/**
 * Returns the title for a cart product
 *
 * Other add-ons may need to modify the DB title to reflect variants / etc
 *
 * @since 0.3.7
 * @param array $product cart product
 * @return string product title
*/
function it_exchange_get_cart_product_title( $product ) {
	if ( ! $db_product = it_exchange_get_product( $product['product_id'] ) )
		return false;

	$title = get_the_title( $db_product->ID );
	return apply_filters( 'it_exchange_get_cart_product_title', $title, $product );
}

/**
 * Returns the quantity for a cart product
 *
 * @since 0.3.7
 * @param array $product cart product
 * @return integer quantity
*/
function it_exchange_get_cart_product_quantity( $product ) {
	$count = empty( $product['count'] ) ? 0 : $product['count'];
	return apply_filters( 'it_exchange_get_cart_product_quantity', $count, $product );
}

/**
 * Returns the quantity for a cart product
 *
 * @since 0.4.4
 * @param int $product ID
 * @return integer quantity
*/
function it_exchange_get_cart_product_quantity_by_product_id( $product_id ) {
	$products = it_exchange_get_cart_products();

	foreach ( $products as $product ) {
		if ( $product['product_id'] == $product_id )
			return $product['count'];
	}

	return 0;
}

/**
 * Returns the number of items in the cart 
 * Now including quantity for individual items w/ true_count flag
 *
 * @since 0.4.0
 *
 * @param bool $true_count Whether or not to traverse cart products to get true count of items
 * @return integer
*/
function it_exchange_get_cart_products_count( $true_count=false ) {
	if ( $true_count ) {
		$count = 0;
		$products = it_exchange_get_cart_products();
		foreach( $products as $product ) {
			$count += $product['count'];
		}
		return absint( $count );
	}
	return absint( count( it_exchange_get_cart_products() ) );
}

/**
 * Returns the base_price for the cart product
 *
 * Other add-ons may modify this on the fly based on the product's itemized_data and additional_data arrays
 *
 * @since 0.3.7
 * @param array $product cart product
 * @return integer quantity
*/
function it_exchange_get_cart_product_base_price( $product, $format=true ) {
	if ( ! $db_product = it_exchange_get_product( $product['product_id'] ) )
		return false;

	// Get the price from the DB
	$db_base_price = it_exchange_get_product_feature( $db_product->ID, 'base-price' );

	if ( $format )
		$db_base_price = it_exchange_format_price( $db_base_price );

	return apply_filters( 'it_exchange_get_cart_product_base_price', $db_base_price, $product, $format );
}

/**
 * Returns the subtotal for a cart product
 *
 * Base price multiplied by quantity and then passed through a filter
 *
 * @since 0.3.7
 * @param array $product cart product
 * @return mixed subtotal
*/
function it_exchange_get_cart_product_subtotal( $product, $format=true ) {
	$base_price = it_exchange_get_cart_product_base_price( $product, false );
	$subtotal_price = apply_filters( 'it_exchange_get_cart_product_subtotal', $base_price * $product['count'], $product );

	if ( $format )
		$subtotal_price = it_exchange_format_price( $subtotal_price );

	return $subtotal_price;
}

/**
 * Returns the cart subtotal
 *
 * @since 0.3.7
 * @return mixed subtotal of cart
*/
function it_exchange_get_cart_subtotal( $format=true ) {
	$subtotal = 0;
	if ( ! $products = it_exchange_get_cart_products() )
		return 0;

	foreach( (array) $products as $product ) {
		$subtotal += it_exchange_get_cart_product_subtotal( $product, false );
	}
	$subtotal = apply_filters( 'it_exchange_get_cart_subtotal', $subtotal );

	if ( $format )
		$subtotal = it_exchange_format_price( $subtotal );

	return $subtotal;
}

/**
 * Returns the cart total
 *
 * The cart total is essentailly going to be the sub_total plus whatever motifications other add-ons make to it.
 * eg: taxes, shipping, discounts, etc.
 *
 * @since 0.3.7
 * @return mixed total of cart
*/
function it_exchange_get_cart_total( $format=true ) {
	$total = apply_filters( 'it_exchange_get_cart_total', it_exchange_get_cart_subtotal( false ) );

	if ( 0 > $total )
		$total = 0;

	if ( $format )
		$total = it_exchange_format_price( $total );

	return $total;
}

/**
 * Returns the cart description
 *
 * The cart description is essentailly going to be a list of all products being purchased
 *
 * @since 0.4.0
 * @return mixed total of cart
*/
function it_exchange_get_cart_description() {
	$description = array();
	if ( ! $products = it_exchange_get_cart_products() )
		return 0;

	foreach( (array) $products as $product ) {
		$string = it_exchange_get_cart_product_title( $product );
		if (  1 < $count = it_exchange_get_cart_product_quantity( $product ) )
			$string .= ' (' . $count . ')';
		$description[] = $string;
	}
	$description = apply_filters( 'it_exchange_get_cart_description', implode( ', ', $description ), $description );

	return $description;
}

/**
 * Redirect to confirmation page after successfull transaction
 *
 * @since 0.3.7
 * @param integer $transaction_id the transaction id
 * @return void
*/
function it_exchange_do_confirmation_redirect( $transaction_id ) {
	$confirmation_url = it_exchange_get_page_url( 'confirmation' );
	$transaction_var  = it_exchange_get_field_name( 'transaction_id' );
	$confirmation_url = add_query_arg( array( $transaction_var => $transaction_id ), $confirmation_url );

	$redirect_options = array( 'transaction_id' => $transaction_id, 'transaction_var' => $transaction_var );
	it_exchange_redirect( $confirmation_url, 'confirmation-redirect', $redirect_options );
	die();
}

/**
 * Returns the nonce field for the cart
 *
 * @since 0.4.0
 *
 * @return string
*/
function it_exchange_get_cart_nonce_field() {
	$var = apply_filters( 'it_exchange_cart_action_nonce_var', '_wpnonce' );
	return wp_nonce_field( 'it-exchange-cart-action-' . it_exchange_get_session_id(), $var, true, false );
}

/**
 * Returns the shipping address values for the cart
 *
 * @since 1.4.0
 *
 * @return array
*/
function it_exchange_get_cart_shipping_address() {

	// If user is logged in, grab their data
	$customer = it_exchange_get_current_customer();
	$customer_data = empty( $customer->data ) ? new stdClass() : $customer->data;

	// Default values for first time use.
	$defaults = array(
		'first-name'   => empty( $customer_data->first_name ) ? '' : $customer_data->first_name,
		'last-name'    => empty( $customer_data->last_name ) ? '' : $customer_data->last_name,
		'company-name' => '',
		'address1'     => '',
		'address2'     => '',
		'city'         => '',
		'state'        => '',
		'zip'          => '',
		'country'      => '',
		'email'        => empty( $customer_data->user_email ) ? '' : $customer_data->user_email,
		'phone'        => '',
	);

	// See if the customer has a shipping address saved. If so, overwrite defaults with saved shipping address
	if ( ! empty( $customer_data->shipping_address ) )
		$defaults = ITUtility::merge_defaults( $customer_data->shipping_address, $defaults );

	// If data exists in the session, use that as the most recent
	$session_data = it_exchange_get_cart_data( 'shipping-address' );

	$cart_shipping = ITUtility::merge_defaults( $session_data, $defaults );

	// If shipping error and form was submitted, use POST values as most recent
	if ( ! empty( $_REQUEST['it-exchange-update-shipping-address'] ) && ! empty( $GLOBALS['it_exchange']['shipping-address-error'] ) ) {
		$keys = array_keys( $defaults );
		$post_shipping = array();
		foreach( $keys as $key ) {
			$post_shipping[$key] = empty( $_REQUEST['it-exchange-shipping-address-' . $key] ) ? '' : $_REQUEST['it-exchange-shipping-address-' . $key];
		}
		$cart_shipping = ITUtility::merge_defaults( $post_shipping, $cart_shipping );
	}

	return apply_filters( 'it_exchange_get_cart_shipping_address', $cart_shipping );
}

/**
 * Returns the billing address values for the cart
 *
 * @since 1.3.0
 *
 * @return array
*/
function it_exchange_get_cart_billing_address() {

	// If user is logged in, grab their data
	$customer = it_exchange_get_current_customer();
	$customer_data = empty( $customer->data ) ? new stdClass() : $customer->data;

	// Default values for first time use.
	$defaults = array(
		'first-name'   => empty( $customer_data->first_name ) ? '' : $customer_data->first_name,
		'last-name'    => empty( $customer_data->last_name ) ? '' : $customer_data->last_name,
		'company-name' => '',
		'address1'     => '',
		'address2'     => '',
		'city'         => '',
		'state'        => '',
		'zip'          => '',
		'country'      => '',
		'email'        => empty( $customer_data->user_email ) ? '' : $customer_data->user_email,
		'phone'        => '',
	);

	// See if the customer has a billing address saved. If so, overwrite defaults with saved billing address
	if ( ! empty( $customer_data->billing_address ) )
		$defaults = ITUtility::merge_defaults( $customer_data->billing_address, $defaults );

	// If data exists in the session, use that as the most recent
	$session_data = it_exchange_get_cart_data( 'billing-address' );

	$cart_billing = ITUtility::merge_defaults( $session_data, $defaults );

	// If billing error and form was submitted, use POST values as most recent
	if ( ! empty( $_REQUEST['it-exchange-update-billing-address'] ) && ! empty( $GLOBALS['it_exchange']['billing-address-error'] ) ) {
		$keys = array_keys( $defaults );
		$post_billing = array();
		foreach( $keys as $key ) {
			$post_billing[$key] = empty( $_REQUEST['it-exchange-billing-address-' . $key] ) ? '' : $_REQUEST['it-exchange-billing-address-' . $key];
		}
		$cart_billing = ITUtility::merge_defaults( $post_billing, $cart_billing );
	}

	return apply_filters( 'it_exchange_get_cart_billing_address', $cart_billing );
}
