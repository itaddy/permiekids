<?php
/**
 * This file includes several or accesses several data-sets
 * for use in core and add-on development
 *
 * @package IT_Exchange
 * @since 1.2.0
*/

/**
 * Returns a list of data-sets along with their meta data
 *
 * Meta data includes:
 * - file location
 * - function name
 *
 * @since 1.2.0
 *
 * @return array
*/
function it_exchange_get_data_set_properties( $data_key=false ) {

	$core_data_sets = array(
		'countries' => array(
			'file'     => dirname( __FILE__ ) . '/data-sets/countries.php',
			'function' => 'it_exchange_get_countries',
		),
		'provinces' => array(
			'file'     => dirname( __FILE__ ) . '/data-sets/states.php',
			'function' => 'it_exchange_get_country_states',
		),
		'states' => array(
			'file'     => dirname( __FILE__ ) . '/data-sets/states.php',
			'function' => 'it_exchange_get_country_states',
		),
		'currencies' => array(
			'file'     => dirname( __FILE__ ) . '/data-sets/currencies.php',
			'function' => 'it_exchange_get_currencies',
		),
		'address-formats' => array(
			'file'     => dirname( __FILE__ ) . '/data-sets/data-formats.php',
			'function' => 'it_exchange_get_address_formats',
		),
		'measurement-formats' => array(
			'file'     => dirname( __FILE__ ) . '/data-sets/data-formats.php',
			'function' => 'it_exchange_get_measurement_formats',
		),
	);

	// If a key was passed, just return that info.
	if ( ! empty( $data_key ) )
		$data_set = empty( $core_data_sets[$data_key] ) ? array() : $core_data_sets[$data_key];
	else
		$data_set = $core_data_sets;

	// Modify the key for the filter
	$data_key = empty( $data_key ) ? '' : '_' . $data_key;

	// Apply filter and return info.
	return apply_filters( 'it_exchange_get_data_set_properties' . $data_key, $data_set );
}

/**
 * Returns data from one of our data sets
 *
 * Add-ons can add data sets to this API with the it_exchange_get_data_set_properties filter
 *
 * @since 1.2.0
 *
 * @param string $key     the data set you are looking for
 * @param array  $options any options you want passed through to the function that retuns the data set
*/
function it_exchange_get_data_set( $key, $options=array() ) {
	$data_set_props = it_exchange_get_data_set_properties( $key );

	// Return false if we don't have a file or function
	if ( empty( $data_set_props['file'] ) || empty( $data_set_props['function'] ) )
		return false;

	// If the file is located, include it.
	if ( is_file( $data_set_props['file'] ) )
		include_once( $data_set_props['file'] );
	else
		return false;

	// Call the function if its callable. Pass the options to it as well
	if ( is_callable( $data_set_props['function'] ) )
		$data_set = call_user_func( $data_set_props['function'], $options );
	else
		return false;

	// Return the data. It should be filtered by the function. Not here.
	return $data_set;
}
