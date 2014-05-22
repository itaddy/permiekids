<?php
/**
 * This will associate shipping with any product types who register shipping support.
 * By default, it registers a metabox on the product's add/edit screen and provides HTML / data for the frontend.
 *
 * @since 1.4.0
 * @package IT_Exchange
*/


class IT_Exchange_Product_Feature_Shipping {

	/**
	 * Constructor. Registers hooks
	 *
	 * @since 1.4.0
	 * @return void
	*/
	function IT_Exchange_Product_Feature_Shipping() {
		if ( is_admin() ) {
			add_action( 'load-post-new.php', array( $this, 'init_feature_metaboxes' ) );
			add_action( 'load-post.php', array( $this, 'init_feature_metaboxes' ) );
			add_action( 'it_exchange_save_product', array( $this, 'save_feature_on_product_save' ) );
		}
		add_action( 'it_exchange_enabled_addons_loaded', array( $this, 'register_feature_support' ) );
		add_action( 'it_exchange_enabled_addons_loaded', array( $this, 'add_feature_support_to_physical_products' ) );
		add_action( 'it_exchange_update_product_feature_shipping', array( $this, 'save_feature' ), 9, 3 );
		add_filter( 'it_exchange_get_product_feature_shipping', array( $this, 'get_feature' ), 9, 3 );
		add_filter( 'it_exchange_product_has_feature_shipping', array( $this, 'product_has_feature') , 9, 2 );
		add_filter( 'it_exchange_product_supports_feature_shipping', array( $this, 'product_supports_feature') , 9, 2 );
	}

	/**
	 * Register the product feature and add it to enabled product-type addons
	 *
	 * @since 1.4.0
	*/
	function register_feature_support() {
		// Abort if we don't have a shipping add-on enabled
		$addons = it_exchange_get_enabled_addons( array( 'category' => 'shipping' ) );
		if ( empty( $addons ) )
			return;

		// Register the product feature
		$slug        = 'shipping';
		$description = 'Adds shipping fields to a product';
		it_exchange_register_product_feature( $slug, $description );
	}

	/**
	 * Register shipping to the Physical Products product type add-on by default
	 *
	 * @since 1.4.0
	 *
	 * @return void
	*/
	function add_feature_support_to_physical_products() {
		if ( it_exchange_is_addon_enabled( 'physical-product-type' ) )
			it_exchange_add_feature_support_to_product_type( 'shipping', 'physical-product-type' );
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
			if ( !empty( $product_type ) &&  it_exchange_product_type_supports_feature( $product_type, 'shipping' ) )
				add_action( 'it_exchange_product_metabox_callback_' . $product_type, array( $this, 'register_metabox' ) );
		}

	}

	/**
	 * Registers the feature metabox for a specific product type
	 *
	 * Hooked to it_exchange_product_metabox_callback_[product-type] where product type supports this feature
	 *
	 * Does not register the box if no shipping features have been registered.
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function register_metabox() {
		$shipping_features = it_exchange_get_registered_shipping_features();
		if ( ! empty( $shipping_features ) )
			add_meta_box( 'it-exchange-product-shipping', __( 'Shipping', 'it-l10n-ithemes-exchange' ), array( $this, 'print_metabox' ), 'it_exchange_prod', 'it_exchange_normal', 'low' );
	}

	/**
	 * This echos the feature metabox.
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function print_metabox( $post ) {
		// Grab the iThemes Exchange Product object from the WP $post object
		$product          = it_exchange_get_product( $post );
		$screen           = get_current_screen();
		$shipping_enabled = ( ! empty( $screen->action ) && 'add' == $screen->action ) ? true : it_exchange_product_has_feature( $post->ID, 'shipping' );
		?>
		<div class="shipping-header">
			<div class="shipping-label">
				<label for="it-exchange-flat-rate-shipping-cost"><?php _e( 'Shipping', 'it-l10n-ithemes-exchange' ); ?> <span class="tip" title="<?php _e( 'Set shipping details for the product here.', 'it-l10n-ithemes-exchange' ); ?>">i</span></label>
			</div>

			<div class="shipping-toggle">
				<label id="it-exchange-shipping-disabled-label" for="it-exchange-shipping-disabled">
					<input type="checkbox" id="it-exchange-shipping-disabled" name="it-exchange-shipping-disabled" <?php checked( ! $shipping_enabled ); ?>/>
					<?php _e( 'Disable shipping for this product', 'it-l10n-ithemes-exchange' ); ?>
					<span class="tip" title="<?php _e( 'Check this box to indicate that shipping is not needed for this product.', 'it-l10n-ithemes-exchange' ); ?>">i</span>
				</label>
			</div>
		</div>
		<div class="shipping-wrapper <?php echo $shipping_enabled ? '' : 'hidden'; ?>">
			<?php
			it_exchange_do_shipping_feature_boxes( $product );
			?>
		</div>
		<div class="shipping-wrapper shipping-wrapper-disabled <?php echo $shipping_enabled ? 'hidden' : ''; ?>">
			<div class="shipping-feature">
				<p><?php _e( 'Shipping has been disabled for this product.', 'it-l10n-ithemes-exchange' ); ?></p>
			</div>
		</div>
		<?php
	}

	/**
	 * This saves the shipping disabled value
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

		// Update shipping preferences on save
		$shipping_enabled = empty( $_POST['it-exchange-shipping-disabled'] );
		it_exchange_update_product_feature( $product_id, 'shipping', $shipping_enabled );

		// Update shipping features
		if ( $shipping_enabled ) {
			it_exchange_get_shipping_features_for_product( it_exchange_get_product( $product_id ) );
			do_action( 'it_exchange_update_shipping_features_on_product_save' );
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

		update_post_meta( $product_id, '_it-exchange-shipping-enabled', $new_value );
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

		if ( ! it_exchange_get_product( $product_id ) )
			return false;

		return get_post_meta( $product_id, '_it-exchange-shipping-enabled', true );
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
		return it_exchange_product_type_supports_feature( $product_type, 'shipping' );
	}
}
$IT_Exchange_Product_Feature_Shipping= new IT_Exchange_Product_Feature_Shipping();
