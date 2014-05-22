<?php
/**
 * This will control wordpress page templates with any product types that register page_template support
 * By default, it registers a metabox on the product's add/edit screen and provides HTML / data for the frontend.
 *
 * @since 1.7.10
 * @package IT_Exchange
*/


class IT_Exchange_Product_Feature_Product_Page_Template {

	/**
	 * Constructor. Registers hooks
	 *
	 * @since 1.7.10
	 * @return void
	*/
	function IT_Exchange_Product_Feature_Product_Page_Template() {
		if ( is_admin() ) {
			add_action( 'load-post-new.php', array( $this, 'init_feature_metaboxes' ) );
			add_action( 'load-post.php', array( $this, 'init_feature_metaboxes' ) );
			add_action( 'it_exchange_save_product', array( $this, 'save_feature_on_product_save' ) );
		} else {
			add_filter( 'it_exchange_fetch_template_override_default_page_template', array( $this, 'replace_template' ) );
			add_filter( 'it_exchange_fetch_template_override_located_template', array( $this, 'replace_template' ) );
		}
		add_action( 'it_exchange_enabled_addons_loaded', array( $this, 'add_feature_support_to_product_types' ) );
		add_filter( 'it_exchange_get_product_feature_page-template', array( $this, 'get_feature' ), 9, 2 );
		add_filter( 'it_exchange_product_has_feature_page-template', array( $this, 'product_has_feature') , 9, 2 );
		add_filter( 'it_exchange_product_supports_feature_page-template', array( $this, 'product_supports_feature') , 9, 2 );
		add_action( 'it_exchange_update_product_feature_page-template', array( $this, 'save_feature' ), 9, 3 );
	}

	/**
	 * Register the product feature and add it to enabled product-type addons
	 *
	 * @since 1.7.10
	*/
	function add_feature_support_to_product_types() {
		// Register the product feature
		$slug        = 'page-template';
		$description = 'WP Page Template for the product';
		it_exchange_register_product_feature( $slug, $description );

		// Add it to all enabled product-type addons
		$products = it_exchange_get_enabled_addons( array( 'category' => 'product-type' ) );
		foreach( $products as $key => $params ) {
			it_exchange_add_feature_support_to_product_type( 'page-template', $params['slug'] );
		}
	}

	/**
	 * Register's the metabox for any product type that supports the feature
	 *
	 * @since 1.7.10
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
			if ( !empty( $product_type ) &&  it_exchange_product_type_supports_feature( $product_type, 'page-template' ) )
				add_action( 'it_exchange_product_metabox_callback_' . $product_type, array( $this, 'register_metabox' ) );
		}

	}

	/**
	 * Registers the feature metabox for a specific product type
	 *
	 * Hooked to it_exchange_product_metabox_callback_[product-type] where product type supports the feature
	 *
	 * @since 1.7.10
	 * @return void
	*/
	function register_metabox() {
		add_meta_box( 'it-exchange-product-page-template', __( 'Page Template', 'it-l10n-ithemes-exchange' ), array( $this, 'print_metabox' ), 'it_exchange_prod', 'it_exchange_advanced' );
	}

	/**
	 * This echos the feature metabox.
	 *
	 * @since 1.7.10
	 * @return void
	*/
	function print_metabox( $post ) {
	if ( 0 != count( get_page_templates() ) ) {
		$template = get_post_meta( $post->ID, '_wp_page_template', true );
		?>
		<p><strong><?php _e( 'Template' ) ?></strong></p>
		<p><?php _e( 'This option will allow you to override the default WordPress template used for this product.', 'it-l10n-ithemes-exchange' ); ?> <span class="tip" title="<?php esc_attr_e( __( 'Exchange uses page.php as it\'s default when no custom Exchange Templates are available. Page templates for Exchange products need to include a call to the_content() or use Exchange Theme API functions to display correctly.', 'it-l10n-ithemes-exchange' ) ); ?>">i</span></p>
		<label class="screen-reader-text" for="page_template"><?php _e( 'Page Template' ) ?></label>
		<select name="it-exchange-page-template" id="it-exchange-page-template">
			<option value='default'><?php _e(' Default Template' ); ?></option>
			<?php page_template_dropdown( $template ); ?>
		</select> <span class="tip" title="<?php esc_attr_e( __( 'These options are generated from alternative page templates included with your current theme.', 'it-l10n-ithemes-exchange' ) ); ?>">i</span>
		<?php
		}
	}

	/**
	 * This saves the value
	 *
	 * @since 1.7.10
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
		if ( ! it_exchange_product_type_supports_feature( $product_type, 'page-template' ) )
			return;

		// Save option for checkbox allowing quantity
		if ( ! empty( $_POST['it-exchange-page-template'] ) )
			it_exchange_update_product_feature( $product_id, 'page-template', $_POST['it-exchange-page-template'] );
	}

	/**
	 * This updates the feature for a product
	 *
	 * @since 1.7.10
	 *
	 * @param integer $product_id the product id
	 * @param mixed $new_value the new value
	 * @return bolean
	*/
	function save_feature( $product_id, $new_value, $options=array() ) {
		if ( ! $post = get_post( $product_id ) )
			return;

		update_post_meta( $product_id, '_wp_page_template', $new_value );
	}

	/**
	 * Return the product's features
	 *
	 * @since 1.7.10
	 * @param mixed $existing the values passed in by the WP Filter API. Ignored here.
	 * @param integer product_id the WordPress post ID
	 * @return string product feature
	*/
	function get_feature( $existing, $product_id ) {
		$value = get_post_meta( $product_id, '_wp_page_template', true );
		return $value;
	}

	/**
	 * Does the product have the feature?
	 *
	 * @since 1.7.10
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
	 * @since 1.7.10
	 * @param mixed $result Not used by core
	 * @param integer $product_id
	 * @return boolean
	*/
	function product_supports_feature( $result, $product_id ) {
		// Does this product type support this feature?
		$product_type = it_exchange_get_product_type( $product_id );
		return it_exchange_product_type_supports_feature( $product_type, 'page-template' );
	}

	/**
	 * Filter page.php template for selected page_template if it exists
	 *
	 * @since 1.7.10
	 *
	 * @param string $template incoming page.php template location
	 * @return string
	*/
	function replace_template( $template ) {
		if ( it_exchange_is_page( 'product' ) ) {
			$product_id = empty( $GLOBALS['post']->ID ) ? false : $GLOBALS['post']->ID;

			if ( it_exchange_product_supports_feature( $product_id, 'page-template' ) && it_exchange_product_has_feature( $product_id, 'page-template' ) ) {
				$page_template = it_exchange_get_product_feature( $product_id, 'page-template' );
				$template = (boolean) locate_template( $page_template ) ? locate_template( $page_template ) : $template;
			}
		}
		return $template;
	}
}
$IT_Exchange_Product_Feature_Product_Page_Template = new IT_Exchange_Product_Feature_Product_Page_Template();
