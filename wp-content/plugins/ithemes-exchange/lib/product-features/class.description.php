<?php
/**
 * This will control email messages with any product types that register email message support.
 * By default, it registers a metabox on the product's add/edit screen and provides HTML / data for the frontend.
 *
 * @since 0.4.0
 * @package IT_Exchange
*/


class IT_Exchange_Product_Feature_Product_Description {

	/**
	 * Constructor. Registers hooks
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function IT_Exchange_Product_Feature_Product_Description() {
		if ( is_admin() ) {
			add_action( 'load-post-new.php', array( $this, 'init_feature_metaboxes' ) );
			add_action( 'load-post.php', array( $this, 'init_feature_metaboxes' ) );
			add_action( 'it_exchange_save_product', array( $this, 'save_feature_on_product_save' ) );
		}
		add_action( 'it_exchange_enabled_addons_loaded', array( $this, 'add_feature_support_to_product_types' ) );
		add_action( 'it_exchange_update_product_feature_description', array( $this, 'save_feature' ), 9, 2 );
		add_filter( 'it_exchange_get_product_feature_description', array( $this, 'get_feature' ), 9, 2 );
		add_filter( 'it_exchange_product_has_feature_description', array( $this, 'product_has_feature') , 9, 2 );
		add_filter( 'it_exchange_product_supports_feature_description', array( $this, 'product_supports_feature') , 9, 2 );
	}

	/**
	 * Register the product feature and add it to enabled product-type addons
	 *
	 * @since 0.4.0
	*/
	function add_feature_support_to_product_types() {
		// Register the product feature
		$slug        = 'description';
		$description = 'Description of the product';
		it_exchange_register_product_feature( $slug, $description );

		// Add it to all enabled product-type addons
		$products = it_exchange_get_enabled_addons( array( 'category' => 'product-type' ) );
		foreach( $products as $key => $params ) {
			it_exchange_add_feature_support_to_product_type( 'description', $params['slug'] );
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
			if ( !empty( $product_type ) &&  it_exchange_product_type_supports_feature( $product_type, 'description' ) )
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
		add_meta_box( 'it-exchange-product-description', __( 'Description', 'it-l10n-ithemes-exchange' ), array( $this, 'print_metabox' ), 'it_exchange_prod', 'it_exchange_normal' );
	}

	/**
	 * This echos the feature metabox.
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function print_metabox( $post ) {
		$settings     = it_exchange_get_option( 'settings_general' );
		$enable_description_wysiwyg = ! empty( $settings['wysiwyg-for-product-description'] );
		$label_text   = apply_filters( 'it_exchange_add_edit_product_description_label', __( 'Description', 'it-l10n-ithemes-exchange' ), $post );
		$tooltip_text = apply_filters( 'it_exchange_add_edit_product_description_tooltip', __( 'This is a quick, descriptive summary of what your product does and is usually 3-5 sentences long. To add additional info, use the Advanced button below to make an extended description.', 'it-l10n-ithemes-exchange' ), $post );
		?>
		<label for="it-exchange-product-description-field"><?php echo $label_text; ?> <span class="tip" title="<?php esc_attr_e( $tooltip_text ); ?>">i</span></label>
		<?php
		$textarea_id          = 'it-exchange-product-description-field';
		$textarea_name        = 'it-exchange-product-description';
		$textarea_content     = $this->get_feature ( false, $post->ID );
		$textarea_rows        = apply_filters( 'it_exchange_product_descripiton_textarea_rows', '10' );
		$textarea_placeholder = apply_filters( 'it_exchange_product_description_placeholder', __( 'Enter description...' ), $post );
		$textarea_tab_index   = apply_filters( 'it_exchange_product_description_admin_tab_index', 3 );

		if ( $GLOBALS['wp_version'] >= 3.3 && function_exists( 'wp_editor' ) && $enable_description_wysiwyg ) {
			echo wp_editor( $textarea_content, $textarea_id, array( 'textarea_name' => $textarea_name, 'textarea_rows' => $textarea_rows, 'tabindex' => $textarea_tab_index ) );
		} else {
			?>
			<textarea name="<?php esc_attr_e( $textarea_name ); ?>" id="<?php esc_attr_e( $textarea_id ); ?>" tabindex="<?php esc_attr_e( $textarea_tab_index ); ?>" rows="<?php esc_attr_e( $textarea_rows ); ?>" placeholder="<?php esc_attr_e( $textarea_placeholder ); ?>"><?php echo esc_html( htmlspecialchars( $textarea_content ) ); ?></textarea>
		<?php
		}
	}

	/**
	 * This saves the value
	 *
	 * @since 0.4.0
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

		// Abort if this product type doesn't support this feature
		if ( ! it_exchange_product_type_supports_feature( $product_type, 'description' ) )
			return;

		// Abort if key for feature option isn't set in POST data
		if ( ! isset( $_POST['it-exchange-product-description'] ) )
			return;

		// Get new value from post
		$new_value = $_POST['it-exchange-product-description'];

		// Save new value
		it_exchange_update_product_feature( $product_id, 'description', $new_value );
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
	function save_feature( $product_id, $new_value ) {
		if ( ! it_exchange_get_product( $product_id ) )
			return false;
		update_post_meta( $product_id, '_it-exchange-product-description', $new_value );
	}

	/**
	 * Return the product's features
	 *
	 * @since 0.4.0
	 * @param mixed $existing the values passed in by the WP Filter API. Ignored here.
	 * @param integer product_id the WordPress post ID
	 * @return string product feature
	*/
	function get_feature( $existing, $product_id ) {
		$value = get_post_meta( $product_id, '_it-exchange-product-description', true );
		return $value;
	}

	/**
	 * Does the product have this feature?
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
		return it_exchange_product_type_supports_feature( $product_type, 'description' );
	}
}
$IT_Exchange_Product_Feature_Product_Description = new IT_Exchange_Product_Feature_Product_Description();
