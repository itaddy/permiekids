<?php
/**
 * This will associate downloads with any product types who register download support.
 * By default, it registers a metabox on the product's add/edit screen and provides HTML / data for the frontend.
 *
 * @since 0.4.0
 * @package IT_Exchange
*/


class IT_Exchange_Product_Feature_Downloads {

	/**
	 * Constructor. Registers hooks
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function IT_Exchange_Product_Feature_Downloads() {
		if ( is_admin() ) {
			add_action( 'load-post-new.php', array( $this, 'init_feature_metaboxes' ) );
			add_action( 'load-post.php', array( $this, 'init_feature_metaboxes' ) );
			add_action( 'it_exchange_save_product', array( $this, 'save_feature_on_product_save' ) );
		}
		add_action( 'init', array( $this, 'register_downloads_post_type' ) );
		add_action( 'it_exchange_enabled_addons_loaded', array( $this, 'register_feature_support' ) );
		add_action( 'it_exchange_enabled_addons_loaded', array( $this, 'add_feature_support_to_digital_downloads' ) );
		add_action( 'it_exchange_update_product_feature_downloads', array( $this, 'save_feature' ), 9, 3 );
		add_filter( 'it_exchange_get_product_feature_downloads', array( $this, 'get_feature' ), 9, 3 );
		add_filter( 'it_exchange_product_has_feature_downloads', array( $this, 'product_has_feature') , 9, 2 );
		add_filter( 'it_exchange_product_supports_feature_downloads', array( $this, 'product_supports_feature') , 9, 2 );
		add_filter( 'wp', array( $this, 'handle_download_pickup_request' ), 9 );

		//We want to do this sooner than 10
		add_action( 'it_exchange_add_transaction_success', array( $this, 'add_transaction_hash_to_product' ), 5 );
	}

	/**
	 * Adds transaction hashes to the products in a transaction.
	 *
	 * @since 0.4.0
	 *
	 * @param object the cart data
	 * @param integer the transaction id
	 * @return updated cart data with the download hashes
	*/
	function add_transaction_hash_to_product( $transaction_id ) {
		// Grab all products purchased with this transaction
		$products = it_exchange_get_transaction_products( $transaction_id );
		foreach( $products as $key => $transaction_product ) {
			// If this is a downloadable product, generate a hash for each download unique to this transaction
			if ( $this->product_has_feature( 'false', $transaction_product['product_id'] ) ) {

				// Quantity
				$count = $transaction_product['count'];

				// Grab existing downloads for each product in transaction
				$existing_downloads = it_exchange_get_product_feature( $transaction_product['product_id'], 'downloads' );
				// Loop through downloads and create hash for each ( multiplied by quantity )
				foreach( $existing_downloads as $download_id => $download_data ) {
					// One for each count
					for( $i=0;$i<$count;$i++ ) {
						$expire_time = false;

						if ( it_exchange_get_product_feature( $transaction_product['product_id'], 'downloads', array( 'setting' => 'expires' ) ) ) {
							$int = it_exchange_get_product_feature( $transaction_product['product_id'], 'downloads', array( 'setting' => 'expire-int' ) );
							$units = it_exchange_get_product_feature( $transaction_product['product_id'], 'downloads', array( 'setting' => 'expire-units' ) );
							$expire_time = strtotime( '+' . $int . ' ' . $units );
						}

						$hash = it_exchange_create_unique_hash();

						// Create initial hash data package
						$hash_data = array(
							'hash'           => $hash,
							'transaction_id' => $transaction_id,
							'product_id'     => $transaction_product['product_id'],
							'file_id'        => $download_id,
							'customer_id'    => it_exchange_get_transaction_customer_id( $transaction_id ),
							'expires'        => it_exchange_get_product_feature( $transaction_product['product_id'], 'downloads', array( 'setting' => 'expires' ) ),
							'expire_int'     => it_exchange_get_product_feature( $transaction_product['product_id'], 'downloads', array( 'setting' => 'expire-int' ) ),
							'expire_units'   => it_exchange_get_product_feature( $transaction_product['product_id'], 'downloads', array( 'setting' => 'expire-units' ) ),
							'expire_time'    => $expire_time,
							'download_limit' => it_exchange_get_product_feature( $transaction_product['product_id'], 'downloads', array( 'setting' => 'limit' ) ),
							'downloads'      => '0',
						);

						// Add hash and data to DB as file post_meta
						$pm_id = it_exchange_add_download_hash_data( $download_id, $hash, $hash_data );
					}
				}
			}
		}
	}

	/**
	 * Register the product feature and add it to enabled product-type addons
	 *
	 * @since 0.4.0
	*/
	function register_feature_support() {
		// Register the product feature
		$slug        = 'downloads';
		$description = 'Downloadable files associated with a product';
		it_exchange_register_product_feature( $slug, $description );
	}

	/**
	 * Register downloads to the Digital downloads add-on by default
	 *
	 * @since 0.4.15
	 *
	 * @return void
	*/
	function add_feature_support_to_digital_downloads() {
		if ( it_exchange_is_addon_enabled( 'digital-downloads-product-type' ) )
			it_exchange_add_feature_support_to_product_type( 'downloads', 'digital-downloads-product-type' );
	}

	/**
	 * Register's the metabox for any product type that supports the feature
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function init_feature_metaboxes() {

		global $post;

		if ( isset( $_REQUEST['post_type'] ) ) {
			$post_type = $_REQUEST['post_type'];
		} else {
			if ( isset( $_REQUEST['post'] ) )
				$post_id = (int) $_REQUEST['post'];
			elseif ( isset( $_REQUEST['post_ID'] ) )
				$post_id = (int) $_REQUEST['post_ID'];
			else
				$post_id = 0;

			if ( $post_id )
				$post = get_post( $post_id );

			if ( isset( $post ) && !empty( $post ) )
				$post_type = $post->post_type;
		}

		if ( !empty( $_REQUEST['it-exchange-product-type'] ) )
			$product_type = $_REQUEST['it-exchange-product-type'];
		else
			$product_type = it_exchange_get_product_type( $post );

		if ( !empty( $post_type ) && 'it_exchange_prod' === $post_type ) {
			if ( !empty( $product_type ) &&  it_exchange_product_type_supports_feature( $product_type, 'downloads' ) )
				add_action( 'it_exchange_product_metabox_callback_' . $product_type, array( $this, 'register_metabox' ) );
		}

	}

	/**
	 * Registers the feature metabox for a specific product type
	 *
	 * Hooked to it_exchange_product_metabox_callback_[product-type] where product type supports this feature
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function register_metabox() {
		add_meta_box( 'it-exchange-product-downloads', __( 'Downloads', 'it-l10n-ithemes-exchange' ), array( $this, 'print_metabox' ), 'it_exchange_prod', 'it_exchange_normal', 'low' );
		add_meta_box( 'it-exchange-product-downloads-expiration', __( 'Downloads Expiration', 'it-l10n-ithemes-exchange' ), array( $this, 'print_expirations_metabox' ), 'it_exchange_prod', 'it_exchange_advanced' );
	}

	/**
	 * This echos the feature metabox.
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function print_metabox( $post ) {
		// Grab the iThemes Exchange Product object from the WP $post object
		$product = it_exchange_get_product( $post );

		// Set the value of the feature for this product
		$existing_downloads = it_exchange_get_product_feature( $product->ID, 'downloads' );
		?>
			<div class="downloads-label-add">
				<label><?php _e( 'Product Files', 'it-l10n-ithemes-exchange' ); ?> <span class="tip" title="<?php _e( 'If you are selling digital products (like music or ebooks), upload those here.', 'it-l10n-ithemes-exchange' ); ?>">i</span></label>
				<div class="download-add-new right">
					<a href class="button"><?php _e( 'Add New Download', 'it-l10n-ithemes-exchange' ); ?></a>
				</div>
			</div>
			<div class="downloads-list-wrapper">
				<div class="downloads-list-titles">
					<div class="download-item columns-wrapper">
						<div class="download-name column">
							<span><?php _e( 'Name', 'it-l10n-ithemes-exchange' ); ?></span>
						</div>
						<div class="download-source column">
							<span><?php _e( 'Source', 'it-l10n-ithemes-exchange' ); ?></span>
							<span class="tip" title="<?php _e( 'Use the WordPress media library or paste in any accessible URL (from Dropbox, Amazon, etc.) and we\'ll hide the source for you.', 'it-l10n-ithemes-exchange' ); ?>">i</span>
						</div>
					</div>
				</div>
				<div class="downloads-list">
					<!-- New download items start. -->
					<div class="download-item download-item-clone columns-wrapper hidden">
						<div class="download-name column">
							<input type="text" name="" autocomplete="off" class="" placeholder="<?php esc_attr_e( __( 'Name', 'it-l10n-ithemes-exchange' ) ); ?>" value="" />
						</div>
						<div class="download-source column">
							<input type="url" name="" autocomplete="off" class="" placeholder="<?php esc_attr_e( __( 'http://', 'it-l10n-ithemes-exchange' ) ); ?>" value="" />
							<a href class="it-exchange-upload-digital-download"><?php _e( 'Upload', 'it-l10n-ithemes-exchange' ); ?></a>
						</div>
						<div class="download-remove column">
							<a href="#" class="it-exchange-delete-digital-download">&times;</a>
						</div>
					</div>
					<!-- New download items end. -->
					<?php if ( empty( $existing_downloads ) ) : ?>
						<script type="text/javascript" charset="utf-8">
							var it_exchange_new_download_interation = 1;
						</script>
						<div class="download-item columns-wrapper" id="download-item-0">
							<div class="download-name column">
								<input type="text" name="it-exchange-digital-downloads[0][name]" autocomplete="off" class="" placeholder="<?php esc_attr_e( __( 'Name', 'it-l10n-ithemes-exchange' ) ); ?>" value="" tabindex="4" />
							</div>
							<div class="download-source column">
								<input type="url" name="it-exchange-digital-downloads[0][source]" autocomplete="off" class="" placeholder="<?php esc_attr_e( __( 'http://', 'it-l10n-ithemes-exchange' ) ); ?>" value="" tabindex="5" />
								<a href class="it-exchange-upload-digital-download"><?php _e( 'Upload', 'it-l10n-ithemes-exchange' ); ?></a>
							</div>
							<div class="download-remove column">
								<a href="#" class="it-exchange-delete-digital-download" tabindex="6" >&times;</a>
							</div>
						</div>
					<?php else : ?>
						<script type="text/javascript" charset="utf-8">
							var it_exchange_new_download_interation = 0;
						</script>
						<?php foreach( $existing_downloads as $id => $data ) : ?>
							<div id="download-item-<?php esc_attr_e( $id ); ?>" class="download-item download-exists columns-wrapper">
								<input type="hidden" name="it-exchange-digital-downloads[<?php esc_attr_e( $id ); ?>][id]" value="<?php esc_attr_e( $data['id'] ); ?>" />
								<div class="download-name column">
									<input type="text" name="it-exchange-digital-downloads[<?php esc_attr_e( $id ); ?>][name]" class="not-empty" value="<?php esc_attr_e( $data['name'] ); ?>" />
								</div>
								<div class="download-source column">
									<input type="text" name="it-exchange-digital-downloads[<?php esc_attr_e( $id ); ?>][source]" class="not-empty" value="<?php esc_attr_e( $data['source'] ); ?>" />
									<a href class="it-exchange-upload-digital-download"><?php _e( 'Upload', 'it-l10n-ithemes-exchange' ); ?></a>
								</div>
								<div class="download-remove column">
									<input id="it-exchange-digital-downloads-delete-<?php esc_attr_e( $id ); ?>" class="hide-if-js" type="checkbox" name="it-exchange-digital-downloads[<?php esc_attr_e( $id ); ?>][delete]" value="true" />
									<a href="#" class="it-exchange-delete-digital-download" data-checkbox-id="it-exchange-digital-downloads-delete-<?php esc_attr_e( $id ); ?>">&times;</a>
								</div>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
			</div>
		<?php
	}

	/**
	 * This echos the downloads expiration metabox.
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function print_expirations_metabox( $post ) {
		// Grab the iThemes Exchange Product object from the WP $post object
		$product = it_exchange_get_product( $post );

		// Download expires
		$download_expires = it_exchange_get_product_feature( $product->ID, 'downloads', array( 'setting' => 'expires' ) );
		// Download exipre-int
		$download_expire_int = it_exchange_get_product_feature( $product->ID, 'downloads', array( 'setting' => 'expire-int' ) );
		// Download expire-units
		$download_expire_units = it_exchange_get_product_feature( $product->ID, 'downloads', array( 'setting' => 'expire-units' ) );
		// Download limit
		$download_limit = it_exchange_get_product_feature( $product->ID, 'downloads', array( 'setting' => 'limit' ) );

		$expire_units = array(
			'hours'     => __( 'Hours', 'it-l10n-ithemes-exchange' ),
			'days'      => __( 'Days', 'it-l10n-ithemes-exchange' ),
			'weeks'     => __( 'Weeks', 'it-l10n-ithemes-exchange' ),
			'months'    => __( 'Months', 'it-l10n-ithemes-exchange' ),
			'years'     => __( 'Years', 'it-l10n-ithemes-exchange' ),
		);

		$expire_hidden = ( $download_expires == 0 ) ? ' hidden' : '';

		?>
		<div class="download-expiration">
			<label class="download-limit-label" for="it-exchange-digital-downloads-expire">
				<?php _e( 'Download Expiration', 'it-l10n-ithemes-exchange' ); ?>
				<span class="tip" title="<?php _e( 'Changing this value only applies to future purchases', 'it-l10n-ithemes-exchange' ); ?>">i</span>
			</label>
			<span><?php _e( 'Download links', 'it-l10n-ithemes-exchange' ); ?></span>
			<select name="it-exchange-digital-downloads-expires" id="it-exchange-digital-downloads-expires">
				<option value="0" <?php selected( 0, $download_expires ); ?>><?php _e( 'do not expire', 'it-l10n-ithemes-exchange' ); ?></option>
				<option value="1" <?php selected( 1, $download_expires ); ?>><?php _e( 'expire', 'it-l10n-ithemes-exchange' ); ?></option>
			</select>
			<span class="hide-if-no-expire<?php echo $expire_hidden ?>"><?php _e( 'after', 'it-l10n-ithemes-exchange' ); ?></span>
			<input type="number" class="hide-if-no-expire<?php echo $expire_hidden ?>" name="it-exchange-digital-downloads-expire-int" value="<?php esc_attr_e( $download_expire_int ); ?>" />
			<select class="hide-if-no-expire<?php echo $expire_hidden ?>" name="it-exchange-digital-downloads-expire-units">
				<?php foreach( $expire_units as $unit => $label ) : ?>
					<option value="<?php esc_attr_e( $unit ); ?>" <?php selected( $unit, $download_expire_units ); ?>><?php esc_attr_e( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<br/>
		<div class="download-limit">
			<label class="download-limit-label" for="it-exchange-digital-downloads-download-limit">
				<?php _e( 'Download Limit', 'it-l10n-ithemes-exchange' ); ?>
				<span class="tip" title="<?php _e( 'How many times customers can download the file(s).', 'it-l10n-ithemes-exchange' ); ?>">i</span>
			</label>
			<div class="download-limit-select">
				<select name="it-exchange-digital-downloads-download-limit" id="it-exchange-digital-downloads-download-limit">
					<?php
					$options = array( 0 => __( 'Unlimited', 'it-l10n-ithemes-exchange' ) );
					for ( $i=1;$i<=20;$i++ ) {
						$options[$i] = $i;
					}
					$options = apply_filters( 'it_exchange_download_limit_options', $options, $product );
					?>
					<?php foreach( $options as $limit_value => $limit_label ) : ?>
						<option value="<?php esc_attr_e( $limit_value ); ?>" <?php selected( $limit_value, $download_limit ); ?>><?php esc_attr_e( $limit_label ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
		<?php
	}

	/**
	 * This saves the downloads value
	 *
	 * @since 0.3.8
	 * @param object $post wp post object
	 * @return void
	*/
	function save_feature_on_product_save() {
		// Abort if we can't determine a product type
		if ( ! $product_type = it_exchange_get_product_type() )
			return;

		// Abort if we don't have a product ID
		$product_id = empty( $_POST['ID'] ) ? false : $_POST['ID'];
		if ( ! $product_id )
			return;

		// Abort if this product type doesn't support downloads
		if ( ! it_exchange_product_type_supports_feature( $product_type, 'downloads' ) )
			return;

		// Update Expires Meta
		$expires= isset( $_POST['it-exchange-digital-downloads-expires'] ) ? $_POST['it-exchange-digital-downloads-expires'] : 0;
		it_exchange_update_product_feature( $product_id, 'downloads', $expires, array( 'setting' => 'expires' ) );

		// Update Expire Int Meta
		$expire_int = isset( $_POST['it-exchange-digital-downloads-expire-int'] ) ? $_POST['it-exchange-digital-downloads-expire-int'] : 30;
		it_exchange_update_product_feature( $product_id, 'downloads', $expire_int, array( 'setting' => 'expire-int' ) );

		// Update Expire Units Meta
		$expire_units = isset( $_POST['it-exchange-digital-downloads-expire-units'] ) ? $_POST['it-exchange-digital-downloads-expire-units'] : 'days';
		it_exchange_update_product_feature( $product_id, 'downloads', $expire_units, array( 'setting' => 'expire-units' ) );

		// Update Download limit Meta
		$download_limit = isset( $_POST['it-exchange-digital-downloads-download-limit'] ) ? $_POST['it-exchange-digital-downloads-download-limit'] : 0;
		it_exchange_update_product_feature( $product_id, 'downloads', $download_limit, array( 'setting' => 'limit' ) );

		// Grab previously saved downloads
		$previous_downloads = it_exchange_get_product_feature( $product_id, 'downloads' );

		//Delete Non-Existant Downloads
		if ( !empty( $previous_downloads ) && is_array( $previous_downloads ) ) {
			foreach( $previous_downloads as $download_id => $data ) {
				if ( empty( $_POST['it-exchange-digital-downloads'] ) || !array_key_exists( $download_id, $_POST['it-exchange-digital-downloads'] ) )
					wp_delete_post( $download_id, true );
			}
		}

		//Add/Update Existant Downloads
		if ( ! empty( $_POST['it-exchange-digital-downloads'] ) && is_array( $_POST['it-exchange-digital-downloads'] ) ) {
			foreach ( (array) $_POST['it-exchange-digital-downloads'] as $download ) {

				$data = array(
					'product_id'  => $product_id,
					'download_id' => empty( $download['id'] ) ? false : trim( $download['id'] ),
					'source'      => empty( $download['source'] ) ? false : trim( $download['source'] ),
					'name'        => empty( $download['name'] ) ? false : trim( $download['name'] ),
				);

				if ( ! empty( $product_id ) && ! empty( $data['source'] ) && ! empty( $data['name'] ) )
					it_exchange_update_product_feature( $product_id, 'downloads', $data );

			}
		}

	}

	/**
	 * This updates the feature for a product
	 *
	 * @since 0.4.0
	 *
	 * @param integer $product_id the product id
	 * @param mixed $new_value the new value
	 * @return bolean
	*/
	function save_feature( $product_id, $new_value, $options=array() ) {

		if ( ! it_exchange_get_product( $product_id ) )
			return false;

        // Using options to determine if we're setting the download limit or adding/updating files
        $defaults = array(
            'setting' => 'files',
        );
        $options = ITUtility::merge_defaults( $options, $defaults );

		if ( 'files' == $options['setting'] ) {
			// Format data coming from $new_value
			$data = array(
				'post_type'   => 'it_exchange_download',
				'post_status' => 'publish',
				'post_title'  => $new_value['name'],
				'post_parent' => $new_value['product_id'],
			);

			// Add download id if we're updating an existing one.
			if ( ! empty( $new_value['download_id'] ) )
				$data['ID'] = $new_value['download_id'];

			// Remove our save_post action so we don't hit and endless loop
			remove_action( 'it_exchange_save_product', array( $this, 'save_feature_on_product_save' ) );
			if ( $download_id = wp_insert_post( $data ) ) {
				// Save the download
				update_post_meta( $download_id, '_it-exchange-download-info', $new_value );
			}
			// Add our action back
			add_action( 'it_exchange_save_product', array( $this, 'save_feature_on_product_save' ) );
		} else {
			$meta = get_post_meta( $product_id, '_it-exchange-download-meta', true );
			if ( 'limit' == $options['setting'] ) {
				$meta['download-limit'] = $new_value;
			} else if ( 'expires' == $options['setting'] ) {
				$meta['expires'] = (boolean) $new_value;
			} else if ( 'expire-int' == $options['setting'] ) {
				$meta['expire-int'] = $new_value;
			} else if ( 'expire-units' == $options['setting'] ) {
				$meta['expire-units'] = $new_value;
			}
			update_post_meta( $product_id, '_it-exchange-download-meta', $meta );
		}
	}

	/**
	 * Return the product's features
	 *
	 * @since 0.4.0
	 * @param mixed $existing the values passed in by the WP Filter API. Ignored here.
	 * @param integer product_id the WordPress post ID
	 * @return string product feature
	*/
	function get_feature( $existing, $product_id, $options=array() ) {

        // Using options to determine if we're getting the download limit or adding/updating files
        $defaults = array(
            'setting' => 'files',
        );
        $options = ITUtility::merge_defaults( $options, $defaults );

		if ( 'files' == $options['setting'] ) {
			$args = array(
				'post_parent' => $product_id,
				'post_type'   => 'it_exchange_download',
				'post_status' => 'publish',
			);

			if ( $download_posts = get_posts( $args ) ) {
				$downloads = array();
				foreach( $download_posts as $post ) {
					$post_meta      = get_post_meta( $post->ID, '_it-exchange-download-info', true );
					$source         = empty( $post_meta['source'] ) ? false : $post_meta['source'];
					$expires        = it_exchange_get_product_feature( $product_id, 'downloads', array( 'setting' => 'expires' ) );
					$expire_int     = it_exchange_get_product_feature( $product_id, 'downloads', array( 'setting' => 'expire-int' ) );
					$expire_units   = it_exchange_get_product_feature( $product_id, 'downloads', array( 'setting' => 'expire-units' ) );
					$download_limit = it_exchange_get_product_feature( $product_id, 'downloads', array( 'setting' => 'limit' ) );

					$downloads[$post->ID] = array(
						'id'             => $post->ID,
						'name'           => $post->post_title,
						'source'         => $source,
						'expires'        => $expires,
						'expire_int'     => $expire_int,
						'expire_units'   => $expire_units,
						'download_limit' => $download_limit,
					);
				}
				return $downloads;
			}
		} else if ( 'limit' == $options['setting'] ) {
			$meta = get_post_meta( $product_id, '_it-exchange-download-meta', true );
			return empty( $meta['download-limit'] ) ? 0 : absint( $meta['download-limit'] );
		} else if ( 'expires' == $options['setting'] ) {
			$meta = get_post_meta( $product_id, '_it-exchange-download-meta', true );
			return ! empty( $meta['expires'] );
		} else if ( 'expire-int' == $options['setting'] ) {
			$meta = get_post_meta( $product_id, '_it-exchange-download-meta', true );
			return empty( $meta['expire-int'] ) ? 30 : absint( $meta['expire-int'] );
		} else if ( 'expire-units' == $options['setting'] ) {
			$meta = get_post_meta( $product_id, '_it-exchange-download-meta', true );
			return empty( $meta['expire-units'] ) ? 'days' : $meta['expire-units'];
		} else if ( 'limit' == $options['setting'] ) {
			$meta = get_post_meta( $product_id, '_it-exchange-download-meta', true );
			return empty( $meta['download-limit'] ) ? 'days' : $meta['download-limit'];
		}
		return false;
	}

	/**
	 * Does the product have the feature?
	 *
	 * @since 0.4.0
	 * @param mixed $result Not used by core
	 * @param integer $product_id
	 * @return boolean
	*/
	function product_has_feature( $result, $product_id, $options=array() ) {
		// Does this product type support this feature?
		if ( false === $this->product_supports_feature( false, $product_id, $options ) )
			return false;
		return (boolean) $this->get_feature( false, $product_id, $options );
	}

	/**
	 * Does the product support this feature?
	 *
	 * This is different than if it has the feature, a product can
	 * support a feature but might not have the feature set.
	 *
	 * @since 0.4.0
	 * @param mixed $result Not used by core
	 * @param integer $product_id
	 * @return boolean
	*/
	function product_supports_feature( $result, $product_id ) {
		// Does this product type support this feature?
		$product_type = it_exchange_get_product_type( $product_id );
		return it_exchange_product_type_supports_feature( $product_type, 'downloads' );
	}

	/**
	 * Registers the downloads post type
	 *
	 * @since 0.4.0
	 * @since return void
	*/
	function register_downloads_post_type() {
		$post_type = 'it_exchange_download';
		$labels    = array(
			'name'          => __( 'Exchange Downloads', 'it-l10n-ithemes-exchange' ),
			'singular_name' => __( 'Download', 'it-l10n-ithemes-exchange' ),
		);
		$options = array(
			'labels' => $labels,
			'description' => __( 'An iThemes Exchange Post Type for storing all Downloads in the system', 'it-l10n-ithemes-exchange' ),
			'public'      => false,
			'show_ui'     => false,
			'show_in_nav_menus' => false,
			'show_in_menu'      => false,
			'show_in_admin_bar' => false,
			'hierarchical'      => false,
			'supports'          => array( // Support everything but page-attributes for add-on flexibility
				'title', 'editor', 'author', 'custom-fields',
			),
			'register_meta_box_cb' => array( $this, 'meta_box_callback' ),
		);
		register_post_type( $post_type, $options );
    }

	/**
	 * If a pickup request is made for a download, do our thing
	 *
	 * 1) Confirm the download hash is legit
	 * 2) Confirm the download hash belongs to the current user
	 * 3) Confirm the download limit isn't up
	 * 4) Deliver the file
	 * 5) Update meta data like download count and download limit
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function handle_download_pickup_request() {
		// Abort if not looking for a download
		if ( empty( $_GET['it-exchange-download'] ) )
			return;

		// Abort with message if hash isn't found
		if ( ! $hash_data = it_exchange_get_download_data_from_hash( $_GET['it-exchange-download'] ) ) {
			it_exchange_add_message( 'error', __( 'Download not found', 'it-l10n-ithemes-exchange' ) );
			$url = apply_filters( 'it_exchange_download_error_url', it_exchange_get_page_url( 'store' ) );
			it_exchange_redirect( $url, 'download-pickup-hash-not-found-to-store' );
			die();
		}

		// Get addon product type addon settings @todo move this setting to product-feature for downloads
		$settings = it_exchange_get_option( 'addon_digital_downloads' );
		$require_user_login = ! empty( $settings['require-user-login'] );
		$require_user_login = apply_filters( 'it_exchange_require_user_login_for_download', $require_user_login, $hash_data );

		// In the event that the admin never visited the settings page to register defaults
		if ( empty( $settings ) ) {
			add_filter( 'it_storage_get_defaults_exchange_addon_digital_downloads', array( 'IT_Exchange_Digital_Downloads_Add_On', 'set_default_settings' ) );
			$settings = it_exchange_get_option( 'addon_digital_downloads', true );
		}

		// If user isn't logged in, redirect them to login and bring them back when complete
		if ( ! empty( $require_user_login ) && ! is_user_logged_in() ) {
			$redirect_url = site_url() . '?it-exchange-download=' . $hash_data['hash'];
			it_exchange_add_session_data( 'login_redirect', $redirect_url );
			$url = it_exchange_get_page_url( 'login' );

			$redirect_options = array( 'hash_data' => $hash_data );
			it_exchange_redirect( $url, 'download-pickup-user-not-logged-in', $hash_data );
			die();
		}

		// If transaction isn't cleared for delivery of product, don't give them the download
		if ( ! it_exchange_transaction_is_cleared_for_delivery( $hash_data['transaction_id'] ) ) {
			it_exchange_add_message( 'error', __( 'The transaction this download is attached to is not valid for download', 'it-l10n-ithemes-exchange' ) );
			$redirect_url = apply_filters( 'it_exchange_redirect_transaction_not_cleared_to_pickup_file', it_exchange_get_page_url( 'downloads' ) );

			$redirect_options = array( 'hash_data' => $hash_data );
			it_exchange_redirect( $redirect_url, 'download-pickup-not-cleared-for-delivery', $redirect_options );
			die();
		}

		if ( $require_user_login ) {
			// If user doesn't belong to the download, and isn't an admin, send them to their downloads page.
			$customer = it_exchange_get_current_customer();
			if ( empty( $customer->id ) || ( $customer->id != $hash_data['customer_id'] && ! current_user_can( 'administrator' ) ) ) {
				it_exchange_add_message( 'error', __( 'You are not allowed to download this file.', 'it-l10n-ithemes-exchange' ) );
				$redirect_url = apply_filters( 'it_exchange_redirect_no_permission_to_pickup_file', it_exchange_get_page_url( 'downloads' ) );

				it_exchange_redirect( $redirect_url, 'download-pickup-not-authorized-customer' );
				die();
			}
		}

		// If download limit has been met, redirect to their downloads page
		if ( ! empty( $hash_data['download_limit'] ) && $hash_data['downloads'] >= $hash_data['download_limit'] ) {
			it_exchange_add_message( 'error', __( 'Download limit reached. Unable to download this file.', 'it-l10n-ithemes-exchange' ) );
			$redirect_url = apply_filters( 'it_exchange_redirect_no_permission_to_pickup_file', it_exchange_get_page_url( 'downloads' ) );

			it_exchange_redirect( $redirect_url, 'download-pickup-download-limit-met' );
			die();
		}

		// If download expiration has passed, redirect to their downloads page
		if ( ! empty( $hash_data['expires'] ) && $hash_data['expire_time'] < ( time() ) ) {
			it_exchange_add_message( 'error', __( 'Download expiration reached. Unable to download this file.', 'it-l10n-ithemes-exchange' ) );
			$redirect_url = apply_filters( 'it_exchange_redirect_no_permission_to_pickup_file', it_exchange_get_page_url( 'downloads' ) );
			it_exchange_redirect( $redirect_url, 'download-pickup-download-expiration-passed' );
			die();
		}

		// Attempt to serve the file
		it_exchange_serve_product_download( $hash_data );
		die();
	}
}
$IT_Exchange_Product_Feature_Downloads = new IT_Exchange_Product_Feature_Downloads();
