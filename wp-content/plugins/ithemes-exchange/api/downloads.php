<?php
/**
 * API functions for downloads
 * @package IT_Exchange
 * @package IT_Exchange
 * @since 0.4.0
*/

/**
 * Adds metadata associated with a transaction to the download
 *
 * Doesn't work if hash already exists
 *
 * @since 0.4.0
 *
 * @param integer $download_id ID of the download post
 * @param string $hash
 * @param array $hash_data
*/
function it_exchange_add_download_hash_data( $download_id, $hash, $hash_data ) {
	// If hash already exists, something went wrong
	if ( it_exchange_get_download_data_from_hash( $hash ) )
		return false;

	// Attach hash and data to downlod
	if ( $pm_id = update_post_meta( $download_id, '_download_hash_' . $hash, $hash_data ) ) {

		// Update the hash index for the transaction
		it_exchange_update_transaction_download_hash_index( $hash_data['transaction_id'], $hash_data['product_id'], $download_id, $hash );

		return apply_filters( 'it_exchange_add_download_hash_data', $pm_id, $download_id, $hash, $hash_data  );
	}

	return false;
}

/**
 * Updates meta-data associated with a specific file hash
 *
 * Hash has to already exist
 *
 * @since 0.4.0
 *
 * @param string $hash
 * @param array $hash_data
*/
function it_exchange_update_download_hash_data( $hash, $hash_data ) {
	if ( ! $old_data = it_exchange_get_download_data_from_hash( $hash ) )
		return;

	// Not allowed to change a couple key vars
	$hash_data['hash']        = $old_data['hash'];
	$hash_data['product_id']  = $old_data['product_id'];
	$hash_data['file_id']     = $old_data['file_id'];
	$hash_data['customer_id'] = $old_data['customer_id'];

	update_post_meta( $hash_data['file_id'], '_download_hash_' . $hash, $hash_data );

	do_action( 'it_exchange_update_download_hash_data', $hash, $hash_data );
}
/**
 * Get a requested file hash
 *
 * @since 0.4.0
 *
 * @param string $hash The hash holding the meta for the file
 * @return array hash data
*/
function it_exchange_get_download_info( $download_id ) {
	return apply_filters( 'it_exchange_get_download_info', get_post_meta( $download_id, '_it-exchange-download-info', true ), $download_id );
}

/**
 * Get a requested file hash
 *
 * @since 0.4.0
 *
 * @param string $hash The hash holding the meta for the file
 * @return array hash data
*/
function it_exchange_get_download_data( $download_id, $hash ) {
	return apply_filters( 'it_exchange_get_download_data', get_post_meta( $download_id, '_download_hash_' . $hash, true ), $download_id, $hash );
}

/**
 * Get a requested file hash
 *
 * @since 0.4.0
 *
 * @param string $hash The hash holding the meta for the file
 * @return array hash data
*/
function it_exchange_get_download_data_from_hash( $hash ) {
	global $wpdb;
	$meta_key = '_download_hash_' . $hash;
	$sql = $wpdb->prepare( "SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = %s LIMIT 1;", $meta_key );
	if ( $data = $wpdb->get_var( $sql ) )
		return apply_filters( 'it_exchange_get_download_data_from_hash', maybe_unserialize( $data ), $hash );

	return false;
}

/**
 * Grabs download data for a specific transaction / product / file combination
 *
 * Fourth param is opitonal.
 *
 * @param  mixed   $transaction transaction ID or object
 * @param  array   $transaction_product this is the product array found in cart_details property in the transaction object
 * @param  integer $download_id the id of the download attached to the product passed in param 2
 * @return mixed   array of all data or a specific key
*/
function it_exchange_get_download_hashes_for_transaction_product( $transaction, $transaction_product, $download_id ) {
	// Grab the transaction or return false
	if ( false === ( $transaction = it_exchange_get_transaction( $transaction ) ) )
		return false;

	// Grab the product key from the tranaction product or return false
	if ( false === ( $product_id = empty( $transaction_product['product_id'] ) ? false : $transaction_product['product_id'] ) )
		return false;

	// Grab an array of all download hashes for this transaction, grouped by product
	$transaction_hash_index = it_exchange_get_transaction_download_hash_index( $transaction->ID );

	// If the requested download / product / transaction combination is in the hash_index, use that to look up the hash data
	$hashes = empty( $transaction_hash_index[$product_id][$download_id] ) ? array() : $transaction_hash_index[$product_id][$download_id];
	return apply_filters( 'it_exchange_get_download_hashes_for_transaction_product', $hashes, $transaction, $transaction_product, $download_id );
}

/**
 * Get all download hashes attached to a specific transaction
 *
 * @since 0.4.0
 *
 * @param mixed $transaction ID or object
 * @return array
*/
function it_exchange_get_transaction_download_hash_index( $transaction ) {
	$transaction = it_exchange_get_transaction( $transaction );
	return apply_filters( 'it_exchange_get_transaction_download_hash_index', get_post_meta( $transaction->ID, '_it_exchange_download_hash_index', true ), $transaction );
}

/**
 * This updates the index of hashes per product per transaction stored in the transaction
 *
 * @param mixed   $transaction         transaction ID or object
 * @param array   $transaction_product this is the product array found in cart_details property in the transaction object
 * @param integer $download_id         the id of the download attached to the product passed in param 2
 * @param string  $hash                the has we're adding to the index
 * @return boolean true for success | false for failure
*/
function it_exchange_update_transaction_download_hash_index( $transaction, $product, $download_id, $hash ) {
	// Grab transaction object
	if ( ! $transaction = it_exchange_get_transaction( $transaction ) )
		return false;

	// Grab existing hash index
	$hash_index = (array) it_exchange_get_transaction_download_hash_index( $transaction );

	// Add hash to existing hash index
	if ( empty( $hash_index[$product][$download_id] ) || ! is_array( $hash_index[$product][$download_id] ) )
		$hash_index[$product][$download_id] = array();

	$hash_index[$product][$download_id][] = $hash;

	// Update hash index
	return update_post_meta( $transaction->ID, '_it_exchange_download_hash_index', $hash_index );
}

/**
 * Deletes a hash from a transaction index
 *
 * This function doesn't care what product its attached to. If it finds it, it deletes it.
 *
 * @param mixed  $transaction the ID or object
 * @param string $hash        the hash we're looking for
 * @return boolean true for success | false for failure
*/
function it_exchange_delete_hash_from_transaction_hash_index( $transaction, $hash ) {
	// Grab transaction object
	if ( ! $transaction = it_exchange_get_transaction( $transaction ) )
		return false;

	// Grab existing hash index
	$hash_index = (array) it_exchange_get_transaction_download_hash_index( $transaction );

	// Delete if it exists
	foreach( $hash_index as $product ) {
		foreach( $product as $download => $hashes ) {
			if ( in_array( $hash, $download ) )
				unset( $hash_index[$product][$download][$hash] );
		}
	}

	// Update
	return update_post_meta( $transaction->ID, '_it_exchange_download_hash_index', $hash_index );
}

/**
 * Clear the hash index for this transaction
 *
 * @since 0.4.0
 *
 * @param mixed $transaction ID or object
 * @return boolean
*/
function it_exchange_clear_transaction_hash_index( $transaction ) {
	// Grab transaction object
	if ( ! $transaction = it_exchange_get_transaction( $transaction ) )
		return false;

	delete_post_meta( $transaction->ID, '_it_exchange_download_hash_index' );
	return true;
}

/**
 * Get expire_time
 *
 * @since 0.4.0
 *
 * @param array $hash_data from download hash
 * @param string $purchase_date post_date from transaction post_type
 * @param string $date_foramt optional. the format to display the date in.
 * @return string
*/
function it_exchange_get_download_expiration_date( $hash_data, $date_format=false ) {
	if ( empty( $hash_data['expire_time'] ) )
		return false;

	$date_format = empty( $date_format ) ? get_option( 'date_format' ) : $date_format;

	return apply_filters( 'it_exchange_get_download_expiration_date', date_i18n( $date_format, $hash_data['expire_time'] ), $hash_data, $date_format );
}

/**
 * Convert 5 months or 30 days to date from transaction
 *
 * Not currently used but will be used when admin can reset dates.
 *
 * @since 0.4.0
 *
 * @param array $hash_data from download hash
 * @param string $purchase_date post_date from transaction post_type
 * @param string $date_foramt optional. the format to display the date in.
 * @return string
*/
function it_exchange_get_download_expiration_date_from_settings( $hash_data, $purchase_date, $date_format=false ) {
	if ( empty( $hash_data['expire_int'] ) || empty( $hash_data['expire_units'] ) )
		return __( "Download doesn't expire", 'it-l10n-ithemes-exchange' );

	$date_format = empty( $date_format ) ? get_option( 'date_format' ) : $date_format;

	$expiration_time = strtotime( $purchase_date. '+' . esc_attr( $hash_data['expire_int'] ) . ' ' . esc_attr( $hash_data['expire_units'] ) );
	return apply_filters( 'it_exchange_get_download_expiration_date_from_settings', date_i18n( $date_format, $expiration_time ), $hash_data, $purchase_date, $date_format );
}

/**
 * Serves a file from its URL
 *
 * Uses wp_remote_get to locate the file and force download.
 *
 * @since 0.4.0
 *
 * @param array $download_info download hash data
*/
function it_exchange_serve_product_download( $hash_data ) {
	// Grab the download info
	$download_info = get_post_meta( $hash_data['file_id'], '_it-exchange-download-info', true );
	$url           = empty( $download_info['source'] ) ? false : $download_info['source'];

	/**
	 * Allow addons to override this.
	 * If you override this, you need to tick the download counts with it_exchange_increment_download_count( $download_info )
	*/
	do_action( 'it_exchange_serve_download_file', $download_info );

	// Attempt to grab file
	if ( $response = wp_remote_head( str_replace( ' ', '%20', $url ) ) ) {
		if ( ! is_wp_error( $response ) ) {
			$valid_response_codes = array(
				200,
			);
			$valid_response_codes = apply_filters( 'it_exchange_valid_response_codes_for_downloadable_files', $valid_response_codes, $download_info );
			if ( in_array( wp_remote_retrieve_response_code( $response ), (array) $valid_response_codes ) ) {

				// Increment Download count if not Admin
				it_exchange_increment_download_count( $hash_data );

				// Get Resource Headers
				$headers = wp_remote_retrieve_headers( $response );

				// White list of headers to pass from original resource
				$passthru_headers = array(
					'accept-ranges',
					'content-length',
					'content-type',
				);
				apply_filters( 'it_exchange_file_download_passthru_headers', $passthru_headers, $download_info );

				// Set Headers for download from original resource
				foreach ( (array) $passthru_headers as $header ) {
					if ( isset( $headers[$header] ) )
						header( esc_attr( $header ) . ': ' . esc_attr( $headers[$header] ) );
				}

				// Set headers to force download
				header( 'Content-Description: File Transfer' );
				header( 'Content-Disposition: attachment; filename=' . basename( $url ) );
				header( 'Content-Transfer-Encoding: binary' );
				header( 'Expires: 0' );
				header( 'Cache-Control: must-revalidate' );
				header( 'Pragma: public' );

				// Clear buffer
				flush();

				// Deliver the file: readfile, curl, redirect
				if ( ini_get( 'allow_url_fopen' ) ) {
					// Use readfile if allow_url_fopen is on
					readfile( str_replace( ' ', '%20', $url )  );
				} else if ( is_callable( 'curl_init' ) ) {
					// Use cURL if allow_url_fopen is off and curl is available
					$ch = curl_init( str_replace( ' ', '%20', $url ) );
					curl_exec( $ch );
					curl_close( $ch );
				} else {
					// Just redirect to the file becuase their host <strike>sucks</strike> doesn't support allow_url_fopen or curl.
					wp_redirect( str_replace( ' ', '%20', $url ) );
				}
				die();

			}
			die( __( 'Download Error: Invalid response: ', 'it-l10n-ithemes-exchange' ) . wp_remote_retrieve_response_code( $response ) );
		} else {
			die( __( 'Download Error:', 'it-l10n-ithemes-exchange' ) . ' ' . $response->get_error_message() );
		}
	}
}

/**
 * Increments download counts
 *
 * @since 0.4.0
 *
 * @param array   $hash_data file hash data
 * @param boolean $increment_admin_downloads Default is false
 * @return void
*/
function it_exchange_increment_download_count( $hash_data, $increment_admin_downloads=false ) {
	if ( current_user_can( 'administrator' ) && $increment_admin_downloads )
		return;

	$hash_data['downloads']++;
	it_exchange_update_download_hash_data( $hash_data['hash'], $hash_data );
	do_action( 'it_exchange_increment_download_count', $hash_data, $increment_admin_downloads );
}
