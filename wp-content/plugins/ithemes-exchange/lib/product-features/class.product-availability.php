<?php
/**
 * This will control email messages with any product types that register email message support.
 * By default, it registers a metabox on the product's add/edit screen and provides HTML / data for the frontend.
 *
 * @since 0.4.0
 * @package IT_Exchange
*/


class IT_Exchange_Product_Feature_Product_Availability {

	/**
	 * Constructor. Registers hooks
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function IT_Exchange_Product_Feature_Product_Availability() {
		if ( is_admin() ) {
			add_action( 'load-post-new.php', array( $this, 'init_feature_metaboxes' ) );
			add_action( 'load-post.php', array( $this, 'init_feature_metaboxes' ) );
			add_action( 'it_exchange_save_product', array( $this, 'save_feature_on_product_save' ) );
		}
		add_action( 'it_exchange_enabled_addons_loaded', array( $this, 'add_feature_support_to_product_types' ) );
		add_action( 'it_exchange_update_product_feature_availability', array( $this, 'save_feature' ), 9, 3 );
		add_filter( 'it_exchange_get_product_feature_availability', array( $this, 'get_feature' ), 9, 3 );
		add_filter( 'it_exchange_product_has_feature_availability', array( $this, 'product_has_feature') , 9, 3 );
		add_filter( 'it_exchange_product_supports_feature_availability', array( $this, 'product_supports_feature') , 9, 3 );
	}

	/**
	 * Register the product feature and add it to enabled product-type addons
	 *
	 * @since 0.4.0
	*/
	function add_feature_support_to_product_types() {
		// Register the product feature
		$slug        = 'availability';
		$description = __( 'Availability to purchase the product.', 'it-l10n-ithemes-exchange' );
		it_exchange_register_product_feature( $slug, $description );

		// Add it to all enabled product-type addons
		$products = it_exchange_get_enabled_addons( array( 'category' => 'product-type' ) );
		foreach( $products as $key => $params ) {
			it_exchange_add_feature_support_to_product_type( 'availability', $params['slug'] );
		}
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
			if ( !empty( $product_type ) &&  it_exchange_product_type_supports_feature( $product_type, 'availability' ) )
				add_action( 'it_exchange_product_metabox_callback_' . $product_type, array( $this, 'register_metabox' ) );
		}

	}

	/**
	 * Registers the feature metabox for a specific product type
	 *
	 * Hooked to it_exchange_product_metabox_callback_[product-type] where product type supports the feature
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function register_metabox() {
		add_meta_box( 'it-exchange-product-availability', __( 'Availability', 'it-l10n-ithemes-exchange' ), array( $this, 'print_metabox' ), 'it_exchange_prod', 'normal' );
	}

	/**
	 * This echos the feature metabox.
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function print_metabox( $post ) {

		$date_format = get_option( 'date_format' );
		$jquery_date_format = it_exchange_php_date_format_to_jquery_datepicker_format( $date_format );

		// Grab the iThemes Exchange Product object from the WP $post object
		$product = it_exchange_get_product( $post );

		// Set the value of the feature for this product
		$product_feature_value = it_exchange_get_product_feature( $product->ID, 'availability' );
		$start_enabled		   = it_exchange_get_product_feature( $product->ID, 'availability', array( 'type' => 'start', 'setting' => 'enabled' ) );
		$end_enabled           = it_exchange_get_product_feature( $product->ID, 'availability', array( 'type' => 'end', 'setting' => 'enabled' ) );
		$start_date            = empty( $product_feature_value['start'] ) ? '' : $product_feature_value['start'];
		$end_date              = empty( $product_feature_value['end'] ) ? '' : $product_feature_value['end'];

		// Set description
		$description = __( 'Use these settings to determine when a product is available to purchase.', 'it-l10n-ithemes-exchange' );
		$description = apply_filters( 'it_exchange_product_availability_metabox_description', $description );

		?>
			<?php if ( $description ) : ?>
				<p class="intro-description"><?php echo $description; ?></p>
			<?php endif; ?>
			<p>
				<input type="checkbox" id="it-exchange-enable-product-availability-start"  class="it-exchange-checkbox-enable" name="it-exchange-enable-product-availability-start" value="yes" <?php checked( 'yes', $start_enabled ); ?> />&nbsp;<label for="it-exchange-enable-product-availability-start"><?php _e( 'Use a start date', 'it-l10n-ithemes-exchange' ); ?></label>
				&nbsp;
				<input type="checkbox" id="it-exchange-enable-product-availability-end" class="it-exchange-checkbox-enable" name="it-exchange-enable-product-availability-end" value="yes" <?php checked( 'yes', $end_enabled ); ?> />&nbsp;<label for="it-exchange-enable-product-availability-end"><?php _e( 'Use an end date', 'it-l10n-ithemes-exchange' ); ?></label>
			</p>
			<p>
				<p class="it-exchange-enable-product-availability-start<?php echo ( $start_enabled == 'no' ) ? ' hide-if-js' : '' ?>">
					<label for="it-exchange-product-availability-start"><?php _e( 'Start Date', 'it-l10n-ithemes-exchange' ); ?></label>
					<input type="text" class="datepicker" id="it-exchange-product-availability-start" name="it-exchange-product-availability-start" value="<?php esc_attr_e( $start_date ); ?>" />
				</p>
				<p class="it-exchange-enable-product-availability-end<?php echo ( $end_enabled == 'no' ) ? ' hide-if-js' : '' ?>">
					<label for="it-exchange-product-availability-end"><?php _e( 'End Date', 'it-l10n-ithemes-exchange' ); ?></label>
					<input type="text" class="datepicker" id="it-exchange-product-availability-end" name="it-exchange-product-availability-end" value="<?php esc_attr_e( $end_date ); ?>" />
				</p>
			</p>
            <input type="hidden" name="it_exchange_availability_date_picker_format" value="<?php echo $jquery_date_format; ?>" />
		<?php
	}

	/**
	 * This saves the value
	 *
	 * @since 0.3.8
	 *
	 * @param object $post wp post object
	 * @return void
	*/
	function save_feature_on_product_save() {

		$new_value = array();

		// Abort if we can't determine a product type
		if ( ! $product_type = it_exchange_get_product_type() )
			return;

		// Abort if we don't have a product ID
		$product_id = empty( $_POST['ID'] ) ? false : $_POST['ID'];
		if ( ! $product_id )
			return;

		// Set enabled
		$start_enabled = empty( $_POST['it-exchange-enable-product-availability-start'] ) ? 'no' : 'yes';
		it_exchange_update_product_feature( $product_id, 'availability', $start_enabled, array( 'type' => 'start', 'setting' => 'enabled' ) );
		$end_enabled = empty( $_POST['it-exchange-enable-product-availability-end'] ) ? 'no' : 'yes';
		it_exchange_update_product_feature( $product_id, 'availability', $end_enabled, array( 'type' => 'end', 'setting' => 'enabled' ) );

		// Abort if this product type doesn't support this feature
		if ( ! it_exchange_product_type_supports_feature( $product_type, 'availability', array( 'type' => 'either' ) ) )
			return;

		// Get new value from post
		$avail['start'] = empty( $_POST['it-exchange-product-availability-start'] ) ? '' : $_POST['it-exchange-product-availability-start'];
		$avail['end']   = empty( $_POST['it-exchange-product-availability-end'] ) ? '' : $_POST['it-exchange-product-availability-end'];


		// Loop through start and end dates
		foreach( $avail as $key => $val ) {

			// Get the user's option set in WP General Settings
			$wp_date_format = get_option( 'date_format', 'm/d/Y' );

			// strtotime requires formats starting with day to be separated by - and month separated by /
			if ( 'd' == substr( $wp_date_format, 0, 1 ) )
				$val = str_replace( '/', '-', $val );

			// Transfer to epoch
			if ( $epoch = strtotime( $val ) ) {

				 // Returns an array with values of each date segment
				 $date = date_parse( $val );

				 // Confirms we have a legitimate date
				 if ( checkdate( $date['month'], $date['day'], $date['year'] ) )
					 $new_value[$key] = $epoch;
			}
		}

		if ( ! empty( $new_value['start'] ) && ! empty( $new_value['end'] )
			&& $new_value['start'] >= $new_value['end'] )
			return;

		// Save new value
		it_exchange_update_product_feature( $product_id, 'availability', $new_value );
	}

	/**
	 * This updates the feature for a product
	 *
	 * @since 0.4.0
	 * @param integer $product_id the product id
	 * @param mixed $new_value the new value
	 * @return bolean
	*/
	function save_feature( $product_id, $new_value, $options=array() ) {
		$defaults['type']    = 'either';
		$defaults['setting'] = 'availability';
		$options = ITUtility::merge_defaults( $options, $defaults );

		// Save enabled options
		if ( 'enabled' == $options['setting'] ) {
			if ( ! in_array( $options['type'], array( 'start', 'end' ) ) )
				return false;

			update_post_meta( $product_id, '_it-exchange-enable-product-availability-' . $options['type'], $new_value );
			return true;
		}

		// If we made it here, we're saving availability dates
		update_post_meta( $product_id, '_it-exchange-product-availability', $new_value );
		return true;
	}

	/**
	 * Return the product's features
	 *
	 * @since 0.4.0
	 *
	 * @param mixed $existing the values passed in by the WP Filter API. Ignored here.
	 * @param integer product_id the WordPress post ID
	 * @return string product feature
	*/
	function get_feature( $existing, $product_id, $options=array() ) {
		$date_format = get_option('date_format');
		$defaults['type']    = 'either';
		$defaults['setting'] = 'availability';
		$options = ITUtility::merge_defaults( $options, $defaults );

		if ( 'enabled' == $options['setting'] ) {
			// Test if its enabled
			switch( $options['type'] ) {
				case 'start' :
				case 'end' :
					$enabled = get_post_meta( $product_id, '_it-exchange-enable-product-availability-' . $options['type'], true );
					if ( ! in_array( $enabled, array( 'yes', 'no' ) ) )
						$enabled = 'no';
					return $enabled;
					break;
				case 'both' :
				case 'either' :
					$start_enabled = get_post_meta( $product_id, '_it-exchange-enable-product-availability-start', true );
					$end_enabled   = get_post_meta( $product_id, '_it-exchange-enable-product-availability-end', true );
					if ( ! in_array( $start_enabled, array( 'yes', 'no' ) ) )
						$start_enabled = 'no';
					if ( ! in_array( $end_enabled, array( 'yes', 'no' ) ) )
						$end_enabled = 'no';

					// If both are set to 'yes', the result is true for 'both' and for 'either' case
					if ( 'yes' == $start_enabled && 'yes' == $end_enabled )
						return 'yes';

					// If both are set to 'no', the result is false for 'both' and for 'either' case
					if ( 'no' == $start_enabled && 'no' == $end_enabled )
						return 'no';

					// If we made it here, one is 'yes' and one is 'no'. If case is 'both', return 'no'. If case is 'either', return 'yes'.
					if ( 'both' == $options['type'] )
						return 'no';
					return 'yes';
					break;
			}
		} else if ( 'availability' == $options['setting'] ) {
			// Return availability dates
			// Don't use either here. Only both, start, or end
			if ( ! $value = get_post_meta( $product_id, '_it-exchange-product-availability', true ) )
				return false;

			foreach( (array) $value as $key => $val ) {
				$value[$key] = date_i18n( $date_format, $val );
			}

			switch ( $options['type'] ) {
				case 'start' :
					return $value['start'];
					break;
				case 'end' :
					return $value['end'];
					break;
				case 'both' :
				case 'either' :
				default:
					return $value;
					break;
			}
		}
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
		$defaults['type'] = 'either';
		$options = ITUtility::merge_defaults( $options, $defaults );

		// Does this product type support this feature?
		if ( false === $this->product_supports_feature( false, $product_id, $options ) )
			return false;

		// If it does support, does it have it?
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
	function product_supports_feature( $result, $product_id, $options=array() ) {
		$defaults['type'] = 'either';
		$options = ITUtility::merge_defaults( $options, $defaults );

		// Does this product type support this feature?
		$product_type = it_exchange_get_product_type( $product_id );
		if ( ! it_exchange_product_type_supports_feature( $product_type, 'availability' ) )
			return false;

		// Determine if this product has turned on product availability
		if ( 'no' == it_exchange_get_product_feature( $product_id, 'availability', array( 'type' => $options['type'], 'setting' => 'enabled' ) ) )
			return false;

		return true;
	}
}
$IT_Exchange_Product_Feature_Product_Availability = new IT_Exchange_Product_Feature_Product_Availability();
