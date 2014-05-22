<?php
/**
 * This will control membership welcome messages on the frontend membership dashboard
 *
 * @since 1.1.0
 * @package IT_Exchange_Addon_Membership
*/


class IT_Exchange_Addon_Membership_Product_Feature_Membership_Information {

	/**
	 * Constructor. Registers hooks
	 *
	 * @since 1.1.0
	 * @return void
	*/
	function IT_Exchange_Addon_Membership_Product_Feature_Membership_Information() {
		if ( is_admin() ) {
			add_action( 'load-post-new.php', array( $this, 'init_feature_metaboxes' ) );
			add_action( 'load-post.php', array( $this, 'init_feature_metaboxes' ) );
			add_action( 'it_exchange_save_product', array( $this, 'save_feature_on_product_save' ) );
		}
		add_action( 'it_exchange_enabled_addons_loaded', array( $this, 'add_feature_support_to_product_types' ) );
		add_action( 'it_exchange_update_product_feature_membership-information', array( $this, 'save_feature' ), 9, 3 );
		add_filter( 'it_exchange_get_product_feature_membership-information', array( $this, 'get_feature' ), 9, 3 );
		add_filter( 'it_exchange_product_has_feature_membership-information', array( $this, 'product_has_feature') , 9, 3 );
		add_filter( 'it_exchange_product_supports_feature_membership-information', array( $this, 'product_supports_feature') , 9, 2 );
	}

	/**
	 * Register the product feature and add it to enabled product-type addons
	 *
	 * @since 1.1.0
	*/
	function add_feature_support_to_product_types() {
		// Register the product feature
		$slug        = 'membership-information';
		$description = __( "This displays the intended audience message for each Membership type on the member's product page", 'it-l10n-exchange-addon-membership' );
		it_exchange_register_product_feature( $slug, $description );

		it_exchange_add_feature_support_to_product_type( 'membership-information', 'membership-product-type' );
	}

	/**
	 * Register's the metabox for any product type that supports the feature
	 *
	 * @since 1.1.0
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
			if ( !empty( $product_type ) &&  it_exchange_product_type_supports_feature( $product_type, 'membership-information' ) )
				add_action( 'it_exchange_product_metabox_callback_' . $product_type, array( $this, 'register_metabox' ), 1 ); //we want this to appear first in Membership product types
		}
		
	}

	/**
	 * Registers the feature metabox for a specific product type
	 *
	 * Hooked to it_exchange_product_metabox_callback_[product-type] where product type supports the feature 
	 *
	 * @since 1.1.0
	 * @return void
	*/
	function register_metabox() {
		add_meta_box( 'it-exchange-product-membership-information', __( 'Membership Information', 'it-l10n-exchange-addon-membership' ), array( $this, 'print_metabox' ), 'it_exchange_prod', 'it_exchange_advanced' );
	}

	/**
	 * This echos the feature metabox.
	 *
	 * @since 1.1.0
	 * @return void
	*/
	function print_metabox( $post ) {
		// Grab the iThemes Exchange Product object from the WP $post object
		$product = it_exchange_get_product( $post );
        $defaults = it_exchange_get_option( 'addon_membership' );
		
		$intended_audience = it_exchange_get_product_feature( $product->ID, 'membership-information', array( 'setting' => 'intended-audience' ) );
		$objectives = it_exchange_get_product_feature( $product->ID, 'membership-information', array( 'setting' => 'objectives' ) );
		$prerequisites = it_exchange_get_product_feature( $product->ID, 'membership-information', array( 'setting' => 'prerequisites' ) );
		
		echo '<p><label for="it-exchange-membership-information-intended-audience-template" class="customer-information-label">' . $defaults['membership-intended-audience-label'] . ' <span class="tip" title="' . __('This label will appear when displaying the intended audience information on a membership.', 'it-l10n-exchange-addon-membership' ) . '">i</span></label></p>';
        
        echo wp_editor( $intended_audience, 'membership-information-intended-audience-template', array( 'textarea_name' => 'it-exchange-membership-information-intended-audience-template', 'textarea_rows' => 10, 'textarea_cols' => 30, 'editor_class' => 'large-text', 'teeny' => true ) );
		
		echo '<p><label for="it-exchange-membership-information-objectives-template" class="customer-information-label">' . $defaults['membership-objectives-label'] . ' <span class="tip" title="' . __('This label will appear when displaying the objective information on a membership.', 'it-l10n-exchange-addon-membership' ) . '">i</span></label></p>';
		
        echo wp_editor( $objectives, 'membership-information-objectives-template', array( 'textarea_name' => 'it-exchange-membership-information-objectives-template', 'textarea_rows' => 10, 'textarea_cols' => 30, 'editor_class' => 'large-text', 'teeny' => true ) );
		
		echo '<p><label for="it-exchange-membership-information-prerequisites-template" class="customer-information-label">' . $defaults['membership-prerequisites-label'] . ' <span class="tip" title="' . __('This label will appear when displaying the prerequisite information on a membership.', 'it-l10n-exchange-addon-membership' ) . '">i</span></label></p>';
		
        echo wp_editor( $prerequisites, 'membership-information-prerequisites-template', array( 'textarea_name' => 'it-exchange-membership-information-prerequisites-template', 'textarea_rows' => 10, 'textarea_cols' => 30, 'editor_class' => 'large-text', 'teeny' => true ) );
	}

	/**
	 * This saves the value
	 *
	 * @since 1.1.0
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
		if ( ! it_exchange_product_type_supports_feature( $product_type, 'membership-information' ) )
			return;

		if ( empty( $_POST['it-exchange-membership-information-intended-audience-template'] ) )
			delete_post_meta( $product_id, '_it-exchange-product-membership-intended-audience' );
		else
			it_exchange_update_product_feature( $product_id, 'membership-information', $_POST['it-exchange-membership-information-intended-audience-template'], array( 'setting' => 'intended-audience' ) );
			
		if ( empty( $_POST['it-exchange-membership-information-objectives-template'] ) )
			delete_post_meta( $product_id, '_it-exchange-product-membership-objectives' );
		else
			it_exchange_update_product_feature( $product_id, 'membership-information', $_POST['it-exchange-membership-information-objectives-template'], array( 'setting' => 'objectives' ) );
			
		if ( empty( $_POST['it-exchange-membership-information-prerequisites-template'] ) )
			delete_post_meta( $product_id, '_it-exchange-product-membership-prerequisites' );
		else
			it_exchange_update_product_feature( $product_id, 'membership-information', $_POST['it-exchange-membership-information-prerequisites-template'], array( 'setting' => 'prerequisites' ) );
	}

	/**
	 * Return the product's features
	 *
	 * @since 1.1.0
	 * @param mixed $existing the values passed in by the WP Filter API. Ignored here.
	 * @param integer product_id the WordPress post ID
	 * @return string product feature
	*/
	function save_feature( $product_id, $new_value, $options=array() ) {
		switch ( $options['setting'] ) {
			
			case 'intended-audience':
				update_post_meta( $product_id, '_it-exchange-product-membership-intended-audience', $new_value );
				break;
			case 'objectives':
				update_post_meta( $product_id, '_it-exchange-product-membership-objectives', $new_value );
				break;
			case 'prerequisites':
				update_post_meta( $product_id, '_it-exchange-product-membership-prerequisites', $new_value );
				break;
			
		}
		return true;
	}

	/**
	 * Return the product's features
	 *
	 * @since 1.1.0
	 *
	 * @param mixed $existing the values passed in by the WP Filter API. Ignored here.
	 * @param integer product_id the WordPress post ID
	 * @return string product feature
	*/
	function get_feature( $existing, $product_id, $options=array() ) {
		switch ( $options['setting'] ) {
			
			case 'intended-audience':
				return get_post_meta( $product_id, '_it-exchange-product-membership-intended-audience', true );
			case 'objectives':
				return get_post_meta( $product_id, '_it-exchange-product-membership-objectives', true );
			case 'prerequisites':
				return get_post_meta( $product_id, '_it-exchange-product-membership-prerequisites', true );
		}
		
		return false;
	}

	/**
	 * Does the product have the feature?
	 *
	 * @since 1.1.0
	 * @param mixed $result Not used by core
	 * @param integer $product_id
	 * @return boolean
	*/
	function product_has_feature( $result, $product_id, $options=array() ) {
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
	 * @since 1.1.0
	 * @param mixed $result Not used by core
	 * @param integer $product_id
	 * @return boolean
	*/
	function product_supports_feature( $result, $product_id ) {
		// Does this product type support this feature?
		$product_type = it_exchange_get_product_type( $product_id );
		if ( ! it_exchange_product_type_supports_feature( $product_type, 'membership-information' ) )
			return false;

		return true;
	}
}
$IT_Exchange_Addon_Membership_Product_Feature_Membership_Information = new IT_Exchange_Addon_Membership_Product_Feature_Membership_Information();