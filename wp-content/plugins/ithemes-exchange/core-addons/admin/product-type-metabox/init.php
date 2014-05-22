<?php
/**
 * This is a core add-on. It adds the Product Type metabox to the New / Edit Product view
 *
 * @since 0.3.1
 * @package IT_Exchange
*/
class IT_Exchange_Core_Addon_Product_Type_Meta_Box {

	/**
	 * Class constructor. Registers hooks
	 *
	 * @since 0.3.1
	 * @return void
	*/
	function IT_Exchange_Core_Addon_Product_Type_Meta_Box() {
		if ( is_admin() ) {
			add_action( 'it_exchange_product_metabox_callback', array( $this, 'register_product_type_meta_box' ) );
			add_action( 'it_exchange_save_product', array( $this, 'update_post_product_type' ) );
		}
	}

	/**
	 * Register's the Product Type Metabox
	 *
	 * @since 0.3.1
	 * @return void
	*/
	function register_product_type_meta_box( $post ) {
		global $pagenow;
		$product_types = it_exchange_get_enabled_addons( array( 'category' => 'product-type' ) );
		if ( is_array( $product_types ) && count( $product_types ) > 1  && $pagenow != 'post-new.php' )
			add_meta_box( 'it_exchange_product_type', __( 'Product Type', 'it-l10n-ithemes-exchange' ), array( $this, 'print_meta_box' ), $post->post_type, 'side' );
	}

	/**
	 * This method prints the contents of the metabox
	 *
	 * @since 0.3.0
	 * @void
	*/
	function print_meta_box( $post ) {
		$product = it_exchange_get_product( $post );

		$enabled              = it_exchange_get_enabled_addons( array( 'category' => array( 'product-type' ) ) );
		$current_product_type = $product->product_type;

		echo '<p>' . __( 'You must save the product after changing this to access new product options.', 'it-l10n-ithemes-exchange' ) . '</p>';
		if ( empty( $enabled ) ) {
			echo '<p>' . __( 'You currently have not Product Type add-ons enabled.', 'it-l10n-ithemes-exchange' ) . '</p>';
		} else if ( count( $enabled ) === 1 ) {
			$product_type = reset( $enabled );
			echo '<p>' . esc_attr( $product_type['name'] ) . '</p>';
			echo '<input type="hidden" name="it-exchange-product-type" value="' . esc_attr( $product_type['slug'] ) . '" />';
			echo '<p class="description">' . __( 'You must have more than 1 product type enabled to make changes here.', 'it-l10n-ithemes-exchange' ) . '</p>';
		} else {
			?><div id="it-exchange-product-type-select"><?php
			foreach( $enabled as $slug => $params ) {
				?>
				<label for="it-exchange-product-type-<?php esc_attr_e( $slug ); ?>">
					<input type="radio" id="it-exchange-product-type-<?php esc_attr_e( $slug ); ?>" name="it-exchange-product-type" <?php checked( $slug, $current_product_type ); ?> value="<?php esc_attr_e( $slug ); ?>" /> <?php esc_attr_e( $params['name'] ); ?><br />
				</label>
				<?php
			}
			?></div><?php
		}
	}

	/**
	 * Updates the post_meta that holds the product type
	 *
	 * @since 0.3.0
	 * @return void
	*/
	function update_post_product_type( $post ) {
		$product_type = false;

		// Ensure we have a WP post object or return
		if ( ! is_object( $post ) )
			$post = get_post( $post );
		if ( empty( $post->ID ) )
			return;

		if ( ! $product = it_exchange_get_product( $post ) )
			return;

		if ( ! $product->ID )
			return;

		// If there is an updated product type in the POST array, use that. Otherwise, use the url param if not empty.
		$product_type = empty ( $_POST['it-exchange-product-type'] ) && ! empty( $_GET['it-exchange-product-type'] ) ? $_GET['it-exchange-product-type'] : false;
		if ( ! empty( $_POST['it-exchange-product-type'] ) )
			$product_type = $_POST['it-exchange-product-type'];

		// If we have a product_type, update
		if ( $product_type )
			update_post_meta( $post->ID, '_it_exchange_product_type', $product_type );
	}
}
new IT_Exchange_Core_Addon_Product_Type_Meta_Box();
