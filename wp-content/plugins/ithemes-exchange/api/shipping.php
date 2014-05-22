<?php
/**
 * This file contains functions related to the shipping API
 * See also: api/shipping-features.php
 * @since 1.4.0
 * @package IT_Exchagne
*/

/**
 * Register a shipping provider
 *
 * @since 1.4.0
 *
 * @param  string  $slug    provider slug
 * @param  array   $options options for the provider
 * @return boolean
*/
function it_exchange_register_shipping_provider( $slug, $options ) {

	// Lets just make sure the slug is in the options
	$options['slug'] = $slug;

	// Store the initiated class in our global
	$GLOBALS['it_exchange']['shipping']['providers'][$slug] = $options;

	// Return the object
	return true;
}

/**
 * Returns all registered shipping providers
 *
 * @since 1.4.0
 *
 * @param  mixed $filtered a string or an array of strings to limit returned providers to specific providers
 * @return array
*/
function it_exchange_get_registered_shipping_providers( $filtered=array() ) {
	$providers = empty( $GLOBALS['it_exchange']['shipping']['providers'] ) ? array() : $GLOBALS['it_exchange']['shipping']['providers'];
	if ( empty( $filtered ) )
		return $providers;

	foreach( (array) $filtered as $provider ) {
		if ( isset( $providers[$provider] ) )
			unset( $providers[$provider] );
	}
	return $providers;
}

/**
 * Returns a specific registered shipping provider object
 *
 * @since 1.4.0
 *
 * @param  string $slug the registerd slug
 * @return mixed  false or object
*/
function it_exchange_get_registered_shipping_provider( $slug ) {
	// Return false if we don't have one registered
	if ( empty( $GLOBALS['it_exchange']['shipping']['providers'][$slug] ) )
		return false;

	// Retrieve the provider details
	$options = $GLOBALS['it_exchange']['shipping']['providers'][$slug];

	// Include the class
	include_once( dirname( dirname( __FILE__ ) ) . '/lib/shipping/class-provider.php' );

	// Init the class
	return new IT_Exchange_Shipping_Provider( $slug, $options );

	// Return false if no object was found
	return false;
}

/**
 * Is the requested shipping provider registered?
 *
 * @since 1.4.0
 *
 * @param  string  $slug the registerd slug
 * @return boolean
*/
function it_exchange_is_shipping_provider_registered( $slug ) {
	return (boolean) it_exchange_get_registered_shipping_provider( $slug );
}

/**
 * Register a shipping method
 *
 * @since 1.4.0
 *
 * @param string  $slug    method slug
 * @param array   $options options for the slug
 * @return boolean
*/
function it_exchange_register_shipping_method( $slug, $class ) {
	// Validate opitons
	if ( ! class_exists( $class ) )
		return false;

	// Store the initiated class in our global
	$GLOBALS['it_exchange']['shipping']['methods'][$slug] = $class;

	// Return the object
	return true;
}

/**
 * Returns a specific registered shipping method object
 *
 * @since 1.4.0
 *
 * @param  string $slug the registerd slug
 * @return mixed  false or object
*/
function it_exchange_get_registered_shipping_method( $slug, $product_id=false ) {

	// Return false if we don't have one registered
	if ( empty( $GLOBALS['it_exchange']['shipping']['methods'][$slug] ) )
		return false;

	// Retrieve the method class
	$class = $GLOBALS['it_exchange']['shipping']['methods'][$slug];

	// Make sure we have a class index and it corresponds to a defined class
	if ( empty( $class ) || ! class_exists( $class ) )
		return false;

	// Init the class
	return new $class( $product_id );

	// Return false if no object was found
	return false;
}

/**
 * Returns all registered shipping methods
 *
 * @since 1.4.0
 *
 * @param  mixed $filtered a string or an array of strings to limit returned methods to specific methods
 * @return array
*/
function it_exchange_get_registered_shipping_methods( $filtered=array() ) {
	$methods = empty( $GLOBALS['it_exchange']['shipping']['methods'] ) ? array() : $GLOBALS['it_exchange']['shipping']['methods'];

	if ( empty( $filtered ) )
		return $methods;

	foreach( (array) $filtered as $method ) {
		if ( isset( $methods[$method] ) )
			unset( $methods[$method] );
	}
	return $methods;
}

/**
 * Save the shipping address based on the User's ID
 *
 * @since 1.4.0
 *
 * @param array $address the shipping address as an array
 * @param int   $customer_id optional. if empty, will attempt to get he current user's ID
 * @return boolean Will fail if no user ID was provided or found
*/
function it_exchange_save_shipping_address( $address, $customer_id=false ) {
	$customer_id = empty( $customer_id ) ? it_exchange_get_current_customer_id() : $customer_id;

	if ( ! it_exchange_get_customer( $customer_id ) )
		return false;

	$address = apply_filters( 'it_exchange_save_customer_shipping_address', $address, $customer_id );

	// Add to usermeta
	if ( false !== $address ) {
		update_user_meta( $customer_id, 'it-exchange-shipping-address', $address );
		do_action( 'it_exchange_shipping_address_updated', $address, $customer_id );
		return true;
	}
	return false;
}

/**
 * Returns the value of an address field for the address form.
 *
 * @since 1.4.0
 *
 * @param string $field       the form field we are looking for the value
 * @param int    $customer_id the wp ID of the customer
 *
 * @return string
*/
function it_exchange_print_shipping_address_value( $field, $customer_id=false ) {
    $customer_id = empty( $customer_id ) ? it_exchange_get_current_customer_id() : $customer_id;
    $saved_address = get_user_meta( $customer_id, 'it_exchange_shipping_address', true );
    $cart_address = it_exchange_get_cart_shipping_address();

    $value = empty( $saved_address[$field] ) ? '' : $saved_address[$field];
    $value = empty( $cart_address[$field] ) ? $value : $cart_address[$field];
    echo 'value="' . esc_attr( $value ) . '" ';
}

/**
 * Formats the Shipping Address for display
 *
 * @todo this function sucks. Lets make a function for formatting any address. ^gta
 * @since 1.4.0
 *
 * @return string HTML
*/
function it_exchange_get_formatted_shipping_address( $shipping_address=false ) {
	$formatted   = array();
	$shipping     = empty( $shipping_address ) ? it_exchange_get_cart_shipping_address() : $shipping_address;
	$formatted[] = implode( ' ', array( $shipping['first-name'], $shipping['last-name'] ) );
	if ( ! empty( $shipping['company-name'] ) )
		$formatted[] = $shipping['company-name'];
	if ( ! empty( $shipping['address1'] ) )
		$formatted[] = $shipping['address1'];
	if ( ! empty( $shipping['address2'] ) )
		$formatted[] = $shipping['address2'];
	if ( ! empty( $shipping['city'] ) || ! empty( $shipping['state'] ) || ! empty( $shipping['zip'] ) ) {
		$formatted[] = implode( ' ', array( ( empty( $shipping['city'] ) ? '': $shipping['city'] .',' ),
			( empty( $shipping['state'] ) ? '': $shipping['state'] ),
			( empty( $shipping['zip'] ) ? '': $shipping['zip'] ),
		) );
	}
	if ( ! empty( $shipping['country'] ) )
		$formatted[] = $shipping['country'];

	$formatted = implode( '<br />', $formatted );
	return apply_filters( 'it_exchange_get_formatted_shipping_address', $formatted );
}

/**
 * Grabs all the shipping methods available to the passed product
 *
 * 1) Grab all shipping methods
 * 2) Check to see if they're enabled
 * 3) Return an arry of ones that are enabled.
 *
 * @since 1.4.0
 *
 * @param  object product an IT_Exchange_Product object
 * @return an array of shipping methods
*/
function it_exchange_get_available_shipping_methods_for_product( $product ) {

	$providers         = it_exchange_get_registered_shipping_providers();
	$provider_methods  = array();
	$available_methods = array();

	// Grab all registerd shipping methods for all providers
	foreach( (array) $providers as $provider ) {
		$provider         = it_exchange_get_registered_shipping_provider( $provider['slug'] );
		$provider_methods = array_merge( $provider_methods, $provider->shipping_methods );
	}

	// Loop through provider methods and only use the ones that are available for this product
	foreach( $provider_methods as $slug ) {
		if ( $method = it_exchange_get_registered_shipping_method( $slug, $product->ID ) ) {
			if ( $method->available )
				$available_methods[$slug] = $method;
		}
	}

	return apply_filters( 'it_exchange_get_available_shipping_methods_for_product', $available_methods, $product );
}

function it_exchange_get_enabled_shipping_methods_for_product( $product, $return='object' ) {

	// Are we viewing a new product?
	$screen         = is_admin() ? get_current_screen() : false;
	$is_new_product = is_admin() && ! empty( $screen->action ) && 'add' == $screen->action;

	// Return false if shipping is turned off for this product
	if ( ! it_exchange_product_has_feature( $product->ID, 'shipping' ) && ! $is_new_product )
		return false;

	$enabled_methods                    = array();
	$product_overriding_default_methods = it_exchange_get_shipping_feature_for_product( 'core-available-shipping-methods', $product->ID );

	foreach( (array) it_exchange_get_available_shipping_methods_for_product( $product ) as $slug => $available_method ) {
		// If we made it here, the method is available. Check to see if it has been turned off for this specific product
		if ( false !== $product_overriding_default_methods ) {
			if ( ! empty( $product_overriding_default_methods->$slug ) )
				$enabled_methods[$slug] = ( 'slug' == $return ) ? $slug : $available_method;
		} else {
			$enabled_methods[$slug] = ( 'slug' == $return ) ? $slug : $available_method;
		}
	}
	return $enabled_methods;
}

/**
 * Is cart address valid?
 *
 * @since 1.4.0
 *
 * @return boolean
*/
function it_exchange_is_shipping_address_valid() {
	$cart_address  = it_exchange_get_cart_data( 'shipping-address' );
	$cart_customer = empty( $cart_address['customer'] ) ? 0 : $cart_address['customer'];
	$customer_id   = it_exchange_get_current_customer_id();
	$customer_id   = empty( $customer_id ) ? $cart_customer : $customer_id;

	return (boolean) get_user_meta( $customer_id, 'it_exchange_shipping_address', true );
}

/**
 * Returns the selected shipping method saved in the cart Session
 *
 * @since 1.4.0
 *
 * @return string method slug
*/
function it_exchange_get_cart_shipping_method() {
	$method = it_exchange_get_cart_data( 'shipping-method' );
	$method = empty( $method[0] ) ? false : $method[0];

	// If there is only one possible shippign method for the cart, set it and return it.
	$cart_methods         = it_exchange_get_available_shipping_methods_for_cart();
	$cart_product_methods = it_exchange_get_available_shipping_methods_for_cart_products();

	if ( ( count( $cart_methods ) === 1 && count( $cart_product_methods ) === 1 ) || count( $cart_product_methods ) === 1 ) {
		$single_method = reset($cart_methods);
		it_exchange_update_cart_data( 'shipping-method', $single_method->slug );
		return $single_method->slug;
	}

	return $method;
}

/**
 * This returns available shipping methods for the cart
 *
 * By default, it only returns the highest common denominator for all products.
 * ie: If product one supports methods A and B but product two only supports method A,
 *     this function will only return method A.
 * Toggling the first paramater to false will return a composite of all available methods across products
 *
 * @since 1.4.0
 *
 * @parma boolean $only_return_methods_available_to_all_cart_products defaults to true.
*/
function it_exchange_get_available_shipping_methods_for_cart( $only_return_methods_available_to_all_cart_products=true ) {
	$methods   = array();
	$product_i = 0;

	// Grab all the products in the cart
	foreach( it_exchange_get_cart_products() as $product ) {
		// Skip foreach element if it isn't an exchange product - just to be safe
		if ( false === ( $product = it_exchange_get_product( $product['product_id'] ) ) )
			continue;

		// Skip product if it doesn't have shipping.
		if ( ! it_exchange_product_has_feature( $product->ID, 'shipping' ) )
			continue;

		// Bump product incrementer
		$product_i++;
		$product_methods = array();

		// Loop through shipping methods available for this product
		foreach( (array) it_exchange_get_enabled_shipping_methods_for_product( $product ) as $method ) {
			// Skip if method is false
			if ( empty( $method->slug ) )
				continue;

			// If this is the first product, put all available methods in methods array
			if ( ! empty( $method->slug ) && 1 === $product_i ) {
				$methods[$method->slug] = $method;
			}

			// If we're returning all methods, even when they aren't available to other products, tack them onto the array
			if ( ! $only_return_methods_available_to_all_cart_products )
				$methods[$method->slug] = $method;

			// Keep track of all this products methods
			$product_methods[] = $method->slug;
		}

		// Remove any methods previously added that aren't supported by this product
		if ( $only_return_methods_available_to_all_cart_products ) {
			foreach( $methods as $slug => $object ) {
				if ( ! in_array( $slug, $product_methods ) )
					unset( $methods[$slug] );
			}
		}
	}

	return $methods;
}

/**
 * Returns all available shipping methods for all cart products
 *
 * @since 1.4.0
 *
 * @return array an array of shipping methods
*/
function it_exchange_get_available_shipping_methods_for_cart_products() {
	return it_exchange_get_available_shipping_methods_for_cart( false );
}

/**
 * Returns the cost of shipping for the cart based on selected shipping method(s)
 *
 * If called without the method param, it uses the selected cart method. Use with a param to get estimates for an unselected method
 *
 * @since 1.4.0
 *
 * @param string $shipping_method optional method.
*/
function it_exchange_get_cart_shipping_cost( $shipping_method=false, $format_price=true ) {
	if ( ! $cart_products = it_exchange_get_cart_products() )
		return false;

	$cart_shipping_method = empty( $shipping_method ) ? it_exchange_get_cart_shipping_method() : $shipping_method;
	$cart_cost       = 0;

	foreach( (array) $cart_products as $cart_product ) {
		if ( ! it_exchange_product_has_feature( $cart_product['product_id'], 'shipping' ) )
			continue;

		if ( 'multiple-methods' == $cart_shipping_method )
			$shipping_method = it_exchange_get_multiple_shipping_method_for_cart_product( $cart_product['product_cart_id'] );
		else
			$shipping_method = $cart_shipping_method;

		$cart_cost = $cart_cost + it_exchange_get_shipping_method_cost_for_cart_item( $shipping_method, $cart_product );
	}
	return empty( $format_price ) ? $cart_cost : it_exchange_format_price( $cart_cost );
}

/**
 * This will return the shipping cost for a specific method/product combination in the cart.
 *
 * @since CHAGNEME
 *
 * @param string  $method_slug  the shipping method slug
 * @param array   $cart_product the cart product array
 * @param boolean $format_price format the price for a display
*/
function it_exchange_get_shipping_method_cost_for_cart_item( $method_slug, $cart_product, $format_price=false ) {
	$method = it_exchange_get_registered_shipping_method( $method_slug, $cart_product['product_id'] );
	if ( empty( $method->slug ) )
		return 0;

	$cost = $method->get_shipping_cost_for_product( $cart_product );
	$cost = empty( $cost ) ? 0 : $cost;

	return empty( $format_price ) ? $cost : it_exchange_format_price( $cost );
}

/**
 * Returns the shipping method slug used by a specific cart product
 *
 * Only applicable when the cart is using multiple shipping methods for multiple products
 *
 * @since 1.4.0
 *
 * @param string $product_cart_id the product_cart_id in the cart session. NOT the database ID of the product
 * @return string
*/
function it_exchange_get_multiple_shipping_method_for_cart_product( $product_cart_id ) {
	$selected_multiple_methods = it_exchange_get_cart_data( 'multiple-shipping-methods' );
	$selected_multiple_methods = empty( $selected_multiple_methods ) ? false : $selected_multiple_methods;

	$method = empty( $selected_multiple_methods[$product_cart_id] ) ? false : $selected_multiple_methods[$product_cart_id];
	return $method;
}

/**
 * This function updates the shipping method being used for a specific product in the cart
 *
 * Only applicable when the cart is using multiple shipping methods for multiple products
 *
 * @since 1.4.0
 *
 * @param string $product_cart_id the product_cart_id in the cart session. NOT the database ID of the product
 * @param string $method_slug     the slug of the method this cart product will use
 * @return void
*/
function it_exchange_update_multiple_shipping_method_for_cart_product( $product_cart_id, $method_slug ) {
	$selected_multiple_methods = it_exchange_get_cart_data( 'multiple-shipping-methods' );
	$selected_multiple_methods = empty( $selected_multiple_methods ) ? array() : $selected_multiple_methods;

	$selected_multiple_methods[$product_cart_id] = $method_slug;

	it_exchange_update_cart_data( 'multiple-shipping-methods', $selected_multiple_methods );
}
