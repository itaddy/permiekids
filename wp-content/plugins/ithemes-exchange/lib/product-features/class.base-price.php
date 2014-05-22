<?php
/**
 * This will associate a monetary price with all product types.
 * By default, it registers a metabox on the product's add/edit screen and provides HTML / data for the frontend.
 *
 * @since 0.3.8
 * @package IT_Exchange
*/


class IT_Exchange_Base_Price {

	/**
	 * Constructor. Registers hooks
	 *
	 * @since 0.3.8
	 *
	 * @return void
	*/
	function IT_Exchange_Base_Price() {
		if ( is_admin() ) {
			add_action( 'load-post-new.php', array( $this, 'init_feature_metaboxes' ) );
			add_action( 'load-post.php', array( $this, 'init_feature_metaboxes' ) );
			add_action( 'it_exchange_save_product', array( $this, 'save_feature_on_product_save' ) );
		}
		add_action( 'it_exchange_update_product_feature_base-price', array( $this, 'save_feature' ), 9, 3 );
		add_filter( 'it_exchange_get_product_feature_base-price', array( $this, 'get_feature' ), 9, 3 );
		add_action( 'it_exchange_enabled_addons_loaded', array( $this, 'add_feature_support_to_product_types' ) );
		add_filter( 'it_exchange_product_has_feature_base-price', array( $this, 'product_has_feature') , 9, 3 );
		add_filter( 'it_exchange_product_supports_feature_base-price', array( $this, 'product_supports_feature') , 9, 2 );
	}

	/**
	 * Register the product and add it to enabled product-type addons
	 *
	 * @since 0.3.8
	*/
	function add_feature_support_to_product_types() {
		// Register the base-price_addon
		$slug        = 'base-price';
		$description = 'The base price for a product';
		it_exchange_register_product_feature( $slug, $description );

		// Add it to all enabled product-type addons
		$products = it_exchange_get_enabled_addons( array( 'category' => 'product-type' ) );
		foreach( $products as $key => $params ) {
			it_exchange_add_feature_support_to_product_type( 'base-price', $params['slug'] );
		}
	}

	/**
	 * Register's the metabox for any product type that supports the base-price feature
	 *
	 * @since 0.3.8
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
			if ( !empty( $product_type ) &&  it_exchange_product_type_supports_feature( $product_type, 'base-price' ) )
				add_action( 'it_exchange_product_metabox_callback_' . $product_type, array( $this, 'register_metabox' ) );
		}

	}

	/**
	 * Registers the price metabox for a specific product type
	 *
	 * Hooked to it_exchange_product_metabox_callback_[product-type] where product type supports base-price
	 *
	 * @since 0.3.8
	 * @return void
	*/
	function register_metabox() {
		add_meta_box( 'it-exchange-base-price', __( 'Base Price', 'it-l10n-ithemes-exchange' ), array( $this, 'print_metabox' ), 'it_exchange_prod', 'it_exchange_normal', 'high' );
	}

	/**
	 * This echos the base price metabox.
	 *
	 * @since 0.3.8
	 * @return void
	*/
	function print_metabox( $post ) {
		// Grab the iThemes Exchange Product object from the WP $post object
		$product = it_exchange_get_product( $post );

		// Set the value of the base-price for this product
		$product_base_price = ( '' === it_exchange_get_product_feature( $product->ID, 'base-price' ) ) ? '' : it_exchange_format_price( it_exchange_get_product_feature( $product->ID, 'base-price' ) );

		// Set description
		$description = __( 'Price', 'it-l10n-ithemes-exchange' );
		$description = apply_filters( 'it_exchange_base-price_addon_metabox_description', $description, $post );

		$settings = it_exchange_get_option( 'settings_general' );
		$currency = it_exchange_get_currency_symbol( $settings['default-currency'] );

		// Echo the form field
		do_action( 'it_exchange_before_print_metabox_base_price', $product );
		?>
			<label for="base-price"><?php esc_html_e( $description ); ?></label>
			<input type="text" placeholder="<?php esc_attr_e( it_exchange_format_price( 0 ) ); ?>" id="base-price" name="it-exchange-base-price" value="<?php esc_attr_e( $product_base_price ); ?>" tabindex="2" data-symbol="<?php esc_attr_e( $currency ); ?>" data-symbol-position="<?php esc_attr_e( $settings['currency-symbol-position'] ); ?>" data-thousands-separator="<?php esc_attr_e( $settings['currency-thousands-separator'] ); ?>" data-decimals-separator="<?php esc_attr_e( $settings['currency-decimals-separator'] ); ?>" />
		<?php
		do_action( 'it_exchange_after_print_metabox_base_price', $product );
	}

	/**
	 * This saves the base price value
	 *
	 * @since 0.3.8
	 *
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

		// Abort if this product type doesn't support base-price
		if ( ! it_exchange_product_type_supports_feature( $product_type, 'base-price' ) )
			return;

		// Abort if key for base-price option isn't set in POST data
		if ( ! isset( $_POST['it-exchange-base-price'] ) )
			return;

		if ( !empty( $_POST['it-exchange-base-price'] ) )
			$new_price = $_POST['it-exchange-base-price'];
		else
			$new_price = 0;

		// Save new value
		it_exchange_update_product_feature( $product_id, 'base-price', $new_price );

		/** VARIANT PRICING **/

        // Abort if the lock is engaged because versions are mismatched
        if ( ! empty( $_POST['it-exchange-lock-pricing-variants'] ) ) 
            return;

        // Save variants version for pricing if it is set
        if ( isset( $_POST['it-exchange-product-pricing-variants-version'] ) ) 
            it_exchange_update_product_feature( $product_id, 'base-price', $_POST['it-exchange-product-pricing-variants-version'], array( 'setting' => 'variants-version' ) );

        // Save variants if variants is activated
        if ( it_exchange_product_has_feature( $product_id, 'variants' ) ) { 
            if ( ! empty( $_POST['it-exchange-product-variant-pricing'] ) ) { 
                $controller = it_exchange_variants_addon_get_product_feature_controller( $product_id, 'base-price', array( 'setting' => 'variants' ) );
                $controller->clear_post_meta();
                $controller->set_all_variant_combos_for_product();
                foreach( $_POST['it-exchange-product-variant-pricing'] as $hash => $value ) { 
                    $controller->load_new_from_hash( $hash );
                    $controller->set_value( $value );
                    $controller->update_meta_value_for_current_combo();
                }   
                $controller->save_post_meta();
            } else {
                it_exchange_update_product_feature( $product_id, 'base-price', array(), array( 'setting' => 'variants' ) );
            }   
        }

	}

	/**
	 * This updates the base price for a product
	 *
	 * @since 0.3.8
	 *
	 * @param integer $product_id the product id
	 * @param mixed $new_price the new price
	 * @return bolean
	*/
	function save_feature( $product_id, $new_price, $options=array() ) {

        $defaults = array(
            'setting' => 'primary',
        );
        $options = wp_parse_args( $options, $defaults );

		if ( ! it_exchange_get_product( $product_id ) )
			return false;

		if ( 'primary' == $options['setting'] ) {
			$new_price = it_exchange_convert_to_database_number( $new_price );
			update_post_meta( $product_id, '_it-exchange-base-price', $new_price );
		} else if ( 'variants' == $options['setting'] ) {
			foreach( (array) $new_price as $key => $data ) {
				$new_price[$key]['value'] = it_exchange_convert_to_database_number( $data['value'] );
			}
			update_post_meta( $product_id, '_it-exchange-product-pricing-variants', $new_price );
		} else if ( 'variants-version' == $options['setting'] ) {
			update_post_meta( $product_id, '_it-exchange-product-pricing-variants-version', $new_price );
		}
	}

	/**
	 * Return the product's base price
	 *
	 * @since 0.3.8
	 * @param mixed $base_price the values passed in by the WP Filter API. Ignored here.
	 * @param integer product_id the WordPress post ID
	 * @return string base-price
	*/
	function get_feature( $base_price, $product_id, $options=array() ) {
        $defaults = array(
            'setting' => 'primary',
        );
        $options = wp_parse_args( $options, $defaults );

		if ( 'primary' == $options['setting'] ) {
			if ( '' !== $base_price = get_post_meta( $product_id, '_it-exchange-base-price', true ) )
				$base_price = it_exchange_convert_from_database_number( $base_price ); //create a decimal object (float)
			return $base_price;
		} else if ( 'variants' == $options['setting'] ) {
			if ( '' !== $variant_pricing = get_post_meta( $product_id, '_it-exchange-product-pricing-variants', true ) ) {
				foreach( (array) $variant_pricing as $key => $data ) {
					$variant_pricing[$key]['value'] = it_exchange_convert_from_database_number( $data['value'] );
				}
				return $variant_pricing;
			} else {
				return false;
			}
		} else if ( 'variants-version' == $options['setting'] ) {
			return get_post_meta( $product_id, '_it-exchange-product-pricing-variants-version', true );
		}
	}

	/**
	 * Does the product have a base price?
	 *
	 * @since 0.4.0
	 * @param mixed $result Not used by core
	 * @param integer $product_id
	 * @return boolean
	*/
	function product_has_feature( $result, $product_id, $options=array() ) {
		// Does this product type support this feature?
		if ( false === $this->product_supports_feature( false, $product_id ) )
			return false;
		$value = $this->get_feature( false, $product_id, $options );
		return empty( $value ) ? false : true;
	}

	/**
	 * Does the product support a base price?
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
		return it_exchange_product_type_supports_feature( $product_type, 'base-price' );
	}
}
$IT_Exchange_Base_Price = new IT_Exchange_Base_Price();
