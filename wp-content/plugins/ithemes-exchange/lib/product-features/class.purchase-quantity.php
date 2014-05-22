<?php
/**
 * This will control email messages with any product types that register email message support.
 * By default, it registers a metabox on the product's add/edit screen and provides HTML / data for the frontend.
 *
 * @since 0.4.0
 * @package IT_Exchange
*/


class IT_Exchange_Product_Feature_Purchase_Quantity {

	/**
	 * Constructor. Registers hooks
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function IT_Exchange_Product_Feature_Purchase_Quantity() {
		if ( is_admin() ) {
			add_action( 'load-post-new.php', array( $this, 'init_feature_metaboxes' ) );
			add_action( 'load-post.php', array( $this, 'init_feature_metaboxes' ) );
			add_action( 'it_exchange_save_product', array( $this, 'save_feature_on_product_save' ) );
		}
		add_action( 'it_exchange_enabled_addons_loaded', array( $this, 'add_feature_support_to_product_types' ) );
		add_action( 'it_exchange_update_product_feature_purchase-quantity', array( $this, 'save_feature' ), 9, 3 );
		add_filter( 'it_exchange_get_product_feature_purchase-quantity', array( $this, 'get_feature' ), 9, 3 );
		add_filter( 'it_exchange_product_has_feature_purchase-quantity', array( $this, 'product_has_feature') , 9, 2 );
		add_filter( 'it_exchange_product_supports_feature_purchase-quantity', array( $this, 'product_supports_feature') , 9, 2 );
		add_filter( 'it_exchange_default_field_names', array( $this, 'set_purchase_quantity_vars' ) );
	}

	/**
	 * Register the product feature and add it to enabled product-type addons
	 *
	 * @since 0.4.0
	*/
	function add_feature_support_to_product_types() {
		// Register the product feature
		$slug        = 'purchase-quantity';
		$description = __( 'The max quantity available per purchase settings for a product.', 'it-l10n-ithemes-exchange' );
		it_exchange_register_product_feature( $slug, $description );

		// Add it to all enabled product-type addons
		$products = it_exchange_get_enabled_addons( array( 'category' => 'product-type' ) );
		foreach( $products as $key => $params ) {
				it_exchange_add_feature_support_to_product_type( 'purchase-quantity', $params['slug'] );
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
			if ( !empty( $product_type ) &&  it_exchange_product_type_supports_feature( $product_type, 'purchase-quantity' ) )
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
		add_meta_box( 'it-exchange-product-purchase-quantity', __( 'Purchase Quantity', 'it-l10n-ithemes-exchange' ), array( $this, 'print_metabox' ), 'it_exchange_prod', 'normal' );
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
		$product_feature_value = it_exchange_get_product_feature( $product->ID, 'purchase-quantity' );

		// Allow quantity?
		$product_feature_enable_value = it_exchange_get_product_feature( $product->ID, 'purchase-quantity', array( 'setting' => 'enabled' ) );

		?>
			<p>
				<input type="checkbox" id="it-exchange-enable-product-quantity" class="it-exchange-checkbox-enable" name="it-exchange-enable-product-quantity" <?php echo checked( 'yes', $product_feature_enable_value ); ?> value="yes" />
				&nbsp;<label for="it-exchange-enable-product-quantity"><?php _e( 'Check this to allow customers to modify the quantity they want to purchase.', 'it-l10n-ithemes-exchange' ); ?></label>
			</p>
			<div class="it-exchange-enable-product-quantity<?php echo ( $product_feature_enable_value == 'no' ) ? ' hide-if-js' : '' ?>">
				<p>
					<?php _e( 'What is the maximum quantity a customer can set when purchasing this product?', 'it-l10n-ithemes-exchange' ); ?>
				</p>
	            <input class="" type="text" name="it-exchange-product-quantity" value="<?php esc_attr_e( $product_feature_value ); ?>" />
                <p class="description"><?php _e( 'Leave blank for unlimited.', 'it-l10n-ithemes-exchange' ); ?></p>
			</div>
		<?php
	}

	/**
	 * Sets the purchase quantity query_var
	 *
	 * @since 0.4.0
	 *
	 * @param array $vars sent in through filter
	 * @return array
	*/
	function set_purchase_quantity_vars( $vars ) {
		$vars['product_purchase_quantity']     = 'it-exchange-product-purchase-quantity';
		$vars['product_max_purchase_quantity'] = 'it-exchange-product-max-purchase-quantity';
		return $vars;
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
		// Abort if we can't determine a product type
		if ( ! $product_type = it_exchange_get_product_type() )
			return;

		// Abort if we don't have a product ID
		$product_id = empty( $_POST['ID'] ) ? false : $_POST['ID'];
		if ( ! $product_id )
			return;

		// Save option for checkbox allowing quantity
		if ( empty( $_POST['it-exchange-enable-product-quantity'] ) )
			it_exchange_update_product_feature( $product_id, 'purchase-quantity', 'no', array( 'setting' => 'enabled' ) );
		else
			it_exchange_update_product_feature( $product_id, 'purchase-quantity', 'yes', array( 'setting' => 'enabled' ) );

		// Abort if this product type doesn't support this feature
		if ( ! it_exchange_product_type_supports_feature( $product_type, 'purchase-quantity' ) || empty( $_POST['it-exchange-enable-product-quantity']  ))
			return;

		// If the value is empty (0), delete the key, otherwise save
		if ( empty( $_POST['it-exchange-product-quantity'] ) )
			delete_post_meta( $product_id, '_it-exchange-product-quantity' );
		else
			it_exchange_update_product_feature( $product_id, 'purchase-quantity', absint( $_POST['it-exchange-product-quantity'] ) );
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

		// Using options to determine if we're setting the enabled setting or the actual max_number setting
		$defaults = array(
			'setting' => 'max_number',
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		// Only accept settings for max_number (default) or 'enabled' (checkbox)
		if ( 'max_number' == $options['setting'] ) {
			$new_value = absint( $new_value );
			update_post_meta( $product_id, '_it-exchange-product-quantity', $new_value );
			return true;
		} else if ( 'enabled' == $options['setting'] ) {
			// Enabled setting must be yes or no.
			if ( ! in_array( $new_value, array( 'yes', 'no' ) ) )
				$new_value = 'yes';
			update_post_meta( $product_id, '_it_exchange_product_allow_quantity', $new_value );
			return true;
		}
		return false;
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
		// Is the the add / edit product page?
		$current_screen = is_admin() ? get_current_screen(): false;
		$editing_product = ( ! empty( $current_screen->id ) && 'it_exchange_prod' == $current_screen->id );

		// Using options to determine if we're getting the enabled setting or the actual max_number setting
		$defaults = array(
			'setting' => 'max_number',
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		if ( 'enabled' == $options['setting'] ) {
			$enabled = get_post_meta( $product_id, '_it_exchange_product_allow_quantity', true );
			if ( ! in_array( $enabled, array( 'yes', 'no' ) ) )
				$enabled = 'yes';
			return $enabled;
		} else if ( 'max_number' == $options['setting'] ) {
			// Return the value if supported or on add/edit screen
			if ( it_exchange_product_supports_feature( $product_id, 'purchase-quantity' ) || $editing_product )
				return get_post_meta( $product_id, '_it-exchange-product-quantity', true );
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
	function product_has_feature( $result, $product_id ) {
		// Does this product type support this feature?
		if ( false === $this->product_supports_feature( false, $product_id ) )
			return false;
		return (boolean) $this->get_feature( false, $product_id );
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
		if ( ! it_exchange_product_type_supports_feature( $product_type, 'purchase-quantity' ) )
			return false;

		// Determine if this product has turned off product quantity
		if ( 'no' == it_exchange_get_product_feature( $product_id, 'purchase-quantity', array( 'setting' => 'enabled' ) ) )
			return false;

		return true;
	}
}
$IT_Exchange_Product_Feature_Purchase_Quantity = new IT_Exchange_Product_Feature_Purchase_Quantity();
