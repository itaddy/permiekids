<?php
/**
 * This will control membership welcome messages on the frontend membership dashboard
 *
 * @since 1.0.0
 * @package IT_Exchange_Addon_Membership
*/


class IT_Exchange_Addon_Membership_Product_Feature_Membership_Welcome {

	/**
	 * Constructor. Registers hooks
	 *
	 * @since 1.0.0
	 * @return void
	*/
	function IT_Exchange_Addon_Membership_Product_Feature_Membership_Welcome() {
		if ( is_admin() ) {
			add_action( 'load-post-new.php', array( $this, 'init_feature_metaboxes' ) );
			add_action( 'load-post.php', array( $this, 'init_feature_metaboxes' ) );
			add_action( 'it_exchange_save_product', array( $this, 'save_feature_on_product_save' ) );
		}
		add_action( 'it_exchange_enabled_addons_loaded', array( $this, 'add_feature_support_to_product_types' ) );
		add_action( 'it_exchange_update_product_feature_membership-welcome-message', array( $this, 'save_feature' ), 9, 3 );
		add_filter( 'it_exchange_get_product_feature_membership-welcome-message', array( $this, 'get_feature' ), 9, 3 );
		add_filter( 'it_exchange_product_has_feature_membership-welcome-message', array( $this, 'product_has_feature') , 9, 2 );
		add_filter( 'it_exchange_product_supports_feature_membership-welcome-message', array( $this, 'product_supports_feature') , 9, 2 );
	}

	/**
	 * Register the product feature and add it to enabled product-type addons
	 *
	 * @since 1.0.0
	*/
	function add_feature_support_to_product_types() {
		// Register the product feature
		$slug        = 'membership-welcome-message';
		$description = __( "This displays a custom welcome message for each Membership type on the member's dashboard", 'it-l10n-exchange-addon-membership' );
		it_exchange_register_product_feature( $slug, $description );

		it_exchange_add_feature_support_to_product_type( 'membership-welcome-message', 'membership-product-type' );
	}

	/**
	 * Register's the metabox for any product type that supports the feature
	 *
	 * @since 1.0.0
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
			if ( !empty( $product_type ) &&  it_exchange_product_type_supports_feature( $product_type, 'membership-welcome-message' ) )
				add_action( 'it_exchange_product_metabox_callback_' . $product_type, array( $this, 'register_metabox' ), 1 ); //we want this to appear first in Membership product types
		}
		
	}

	/**
	 * Registers the feature metabox for a specific product type
	 *
	 * Hooked to it_exchange_product_metabox_callback_[product-type] where product type supports the feature 
	 *
	 * @since 1.0.0
	 * @return void
	*/
	function register_metabox() {
		add_meta_box( 'it-exchange-product-membership-welcome-message', __( 'Welcome Message', 'it-l10n-exchange-addon-membership' ), array( $this, 'print_metabox' ), 'it_exchange_prod', 'it_exchange_advanced', 'high' ); //needs to be set to 'high' too, to appear first in Membership product types
	}

	/**
	 * This echos the feature metabox.
	 *
	 * @since 1.0.0
	 * @return void
	*/
	function print_metabox( $post ) {
		// Grab the iThemes Exchange Product object from the WP $post object
		$product = it_exchange_get_product( $post );

		// Set the value of the feature for this product
		$product_feature_value = it_exchange_get_product_feature( $product->ID, 'membership-welcome-message' );
		
		$description = __( "This message will appear above the list of protected membership content in your user's account. Use it to describe this membership, let them know what to read first or do next.", 'it-l10n-exchange-addon-membership' );
		$description = apply_filters( 'it_exchange_membership_addon_product_welcome-message_metabox_description', $description );

		if ( $description ) {
			echo '<p class="intro-description">' . $description . '</p>';
		}
	
        echo wp_editor( $product_feature_value, 'welcome-message-template', array( 'textarea_name' => 'it-exchange-product-membership-welcome-message', 'textarea_rows' => 10, 'textarea_cols' => 30, 'editor_class' => 'large-text' ) );
	}

	/**
	 * This saves the value
	 *
	 * @since 1.0.0
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
		if ( ! it_exchange_product_type_supports_feature( $product_type, 'membership-welcome-message' ) || empty( $_POST['it-exchange-product-membership-welcome-message']  ))
			return;

		// If the value is empty (0), delete the key, otherwise save
		if ( empty( $_POST['it-exchange-product-membership-welcome-message'] ) )
			delete_post_meta( $product_id, '_it-exchange-product-membership-welcome-message' );
		else
			it_exchange_update_product_feature( $product_id, 'membership-welcome-message', $_POST['it-exchange-product-membership-welcome-message'] );
	}
/**
	 * This updates the feature for a product
	 *
	 * @since 1.0.0
	 *
	 * @param integer $product_id the product id
	 * @param mixed $new_value the new value 
	 * @return bolean
	*/
	function save_feature( $product_id, $new_value ) {
		update_post_meta( $product_id, '_it-exchange-product-membership-welcome-message', $new_value );
		return true;
	}

	/**
	 * Return the product's features
	 *
	 * @since 1.0.0
	 * @param mixed $existing the values passed in by the WP Filter API. Ignored here.
	 * @param integer product_id the WordPress post ID
	 * @return string product feature
	*/
	function get_feature( $existing, $product_id ) {
		// Is the the add / edit product page?
		$current_screen = is_admin() ? get_current_screen(): false;
		$editing_product = ( ! empty( $current_screen->id ) && 'it_exchange_prod' == $current_screen->id );

		// Return the value if supported or on add/edit screen
		if ( it_exchange_product_supports_feature( $product_id, 'membership-welcome-message' ) || $editing_product )
			return get_post_meta( $product_id, '_it-exchange-product-membership-welcome-message', true );

		return false;
	}

	/**
	 * Does the product have the feature?
	 *
	 * @since 1.0.0
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
	 * @since 1.0.0
	 * @param mixed $result Not used by core
	 * @param integer $product_id
	 * @return boolean
	*/
	function product_supports_feature( $result, $product_id ) {
		// Does this product type support this feature?
		$product_type = it_exchange_get_product_type( $product_id );
		if ( ! it_exchange_product_type_supports_feature( $product_type, 'membership-welcome-message' ) )
			return false;

		return true;
	}
}
$IT_Exchange_Addon_Membership_Product_Feature_Membership_Welcome = new IT_Exchange_Addon_Membership_Product_Feature_Membership_Welcome();