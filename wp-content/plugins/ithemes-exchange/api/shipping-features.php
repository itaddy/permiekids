<?php
/**
 * This file contains methods for accessing and updating Shipping features
 * @since 1.4.0
 * @package IT_Exchange
*/

/**
 * Keeps track of all available shipping features
 *
 * @since 1.4.0
 * @param string $slug the identifying slug for the shipping feature
 * @param string $class the shipping feature class
 * @return void
*/
function it_exchange_register_shipping_feature( $slug, $class ) {
	// Don't attempt to register if class doesn't exist
	if ( ! class_exists( $class ) )
		return false;

	// Add it to the global
	$GLOBALS['it_exchange']['shipping']['shipping_features'][$slug] = $class;

	// Provide an action for 3rd parties
	do_action( 'it_exchange_register_shipping_feature', $slug, $class);
}

/**
 * Grabs all shipping registered features from GLOBALS and inits objects and returns
 *
 * @since 1.4.0
 *
 * @return array
*/
function it_exchange_get_registered_shipping_features() {
	$features = empty( $GLOBALS['it_exchange']['shipping']['shipping_features'] ) ? array() : $GLOBALS['it_exchange']['shipping']['shipping_features'];
	return $features;
}

/**
 * Prints the shipping feature boxes on the add/edit product page
 *
 * This will loop through all registered shipping features for the current product
 * and print thier feature boxes inside the shipping settings metabox.
 * If it determines that a shipping registered shipping feature is not available because
 * the shipping methods its assoicated with are not enabled, it will hide the box.
 *
 * @since 1.4.0
 *
 * @param  object $product an IT_Exchange_Product object
 * @return void
*/
function it_exchange_do_shipping_feature_boxes( $product ) {

	// Grab all shipping features needed for the passed product
	$shipping_features = it_exchange_get_shipping_features_for_product( $product );

	// Loop through returned shipping features and call the method to print the UI
	foreach( (array) $shipping_features as $feature ) {
		$feature->print_add_edit_feature_box();
	}

}

/**
 * Get a registered shipping feature object
 *
 * @since 1.4.0
 *
 * @param string $slug feature slug
 * @return object
*/
function it_exchange_get_registered_shipping_feature( $slug, $product_id=false ) {
	if ( ! $features = it_exchange_get_registered_shipping_features() )
		return false;

	if ( empty( $features[$slug] ) )
		return false;

	$class = $features[$slug];
	if ( class_exists( $class ) )
		return new $class( $product_id );
}

/**
 * Grab any features needed by all possible shipping methods applicable to this product
 *
 * - Shipping features are tied to Shipping Methods
 * - Shipping methods are associated with Shipping Providers
 * - Shipping methods are available to a product if the Provider is available to the product type
 *
 * @since 1.4.0
 *
 * @param  object $product an IT_Exchange_Product object
 * @return an array of shipping feature objects
*/
function it_exchange_get_shipping_features_for_product( $product ) {

	// Grab all available methods for this product
	$methods  = it_exchange_get_available_shipping_methods_for_product( $product );

	// Init features array
	/** @todo move this filter to lib/shipping/shipping-features/init.php. create a functiont o get core shipping features **/
	$features = apply_filters( 'it_exchange_core_shipping_features', array( 'core-available-shipping-methods' ) );

	// Loop through methods and add all required features to the array
	foreach( $methods as $method ) {
		if ( ! empty( $method->shipping_features ) && is_array( $method->shipping_features ) )
			$features = array_merge( $features, $method->shipping_features );
	}

	// Clean the array
	$features = array_values( array_unique( $features ) );

	// Grab registered feature details
	$registered_features = it_exchange_get_registered_shipping_features( $features );

	// Init return array
	$shipping_features = array();

	// Loop through array and init objects
	foreach( $registered_features as $slug => $class ) {
		if ( in_array( $slug, $features ) && $feature = it_exchange_get_registered_shipping_feature( $slug, $product->ID ) )
			$shipping_features[$slug] = $feature;
	}

	return apply_filters( 'it_exchange_get_shipping_features_for_product', $shipping_features, $product );
}

/**
 * Gets the values of a shipping feature for a specific post
 *
 * @since 1.4.0
 *
 * @param string  $feature    the registered feature slug
 * @param integer $product_id the wordpress post id for the product
 *
 * @return mixed
*/
function it_exchange_get_shipping_feature_for_product( $feature, $product_id ) {
	if ( ! $product = it_exchange_get_product( $product_id ) )
		return false;

	if ( $features = it_exchange_get_shipping_features_for_product( $product ) ) {
		return ( empty( $features[$feature]->enabled ) || empty( $features[$feature]->values ) ) ? false : $features[$feature]->values;
	}
}
