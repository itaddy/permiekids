<?php
/**
 * Handles storage of options
 *
 * @since 0.3.6
 * @package IT_Exchange
*/

/**
 * Retrieve options
 *
 * Default values can be set for any option by adding a filter:
 * - it_storage_get_defaults_$key
 *
 * @since 0.3.6
 * @param string $key option key
 * @param boolean $break_cache clear the ITStorage2 cache before returning options?
 * @param boolean $merge_defaults attempt to merge with default values
 * @return mixed value of passed key
*/
function it_exchange_get_option( $key, $break_cache=false, $merge_defaults=true ) {
	$storage = it_exchange_get_storage( $key );

	if ( $break_cache )
		$storage->clear_cache();

	$data = $storage->load( $merge_defaults );
	if ( is_array( $data) && isset( $data['storage_version'] ) )
		unset( $data['storage_version'] );

	return apply_filters( 'it_exchange_get_option', $data, $key, $break_cache, $merge_defaults );
}

/**
 * Save options
 *
 * @since 0.3.6
 * @param string $key the options key
 * @param mixed $value the values to save to the options key
 * @return void
*/
function it_exchange_save_option( $key, $value ) {
	$storage = it_exchange_get_storage( $key );
	return apply_filters( 'it_exchange_save_option', $storage->save( $value ), $key, $value );
}

/**
 * Clear the cache for a key
 *
 * @since 0.4.0
 *
 * @return void
*/
function it_exchange_clear_option_cache( $key ) {
	$storage = it_exchange_get_storage( $key );
	$storage->clear_cache();
	do_action( 'it_exchange_clear_option_cache', $key );
}

/**
 * Return the ITStorage object for a given key
 *
 * $args options:
 *  - version  default is 0
 *  - autoload default is true
 *
 * @since 0.3.6
 * @param string $key options key
 * @param mixed $args Either a version number (string) or an array of args passed to class constructor for ITStorage2
 * @return object instance of ITStorage2
*/
function it_exchange_get_storage( $key, $args=array() ) {
	it_classes_load( 'it-storage.php' );
	$key = 'exchange_' . $key;
	return apply_filters( 'it_exchange_get_storage', new ITStorage2( $key, $args ), $key, $args );
}