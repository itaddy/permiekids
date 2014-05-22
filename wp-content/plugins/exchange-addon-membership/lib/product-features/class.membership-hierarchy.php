<?php
/**
 * This will control membership welcome messages on the frontend membership dashboard
 *
 * @since 1.1.0
 * @package IT_Exchange_Addon_Membership
*/


class IT_Exchange_Addon_Membership_Product_Feature_Membership_Hierarchy {

	/**
	 * Constructor. Registers hooks
	 *
	 * @since 1.1.0
	 * @return void
	*/
	function IT_Exchange_Addon_Membership_Product_Feature_Membership_Hierarchy() {
		if ( is_admin() ) {
			add_action( 'load-post-new.php', array( $this, 'init_feature_metaboxes' ) );
			add_action( 'load-post.php', array( $this, 'init_feature_metaboxes' ) );
			add_action( 'it_exchange_save_product', array( $this, 'save_feature_on_product_save' ) );
		}
		add_action( 'it_exchange_enabled_addons_loaded', array( $this, 'add_feature_support_to_product_types' ) );
		add_action( 'it_exchange_update_product_feature_membership-hierarchy', array( $this, 'save_feature' ), 9, 3 );
		add_filter( 'it_exchange_get_product_feature_membership-hierarchy', array( $this, 'get_feature' ), 9, 3 );
		add_filter( 'it_exchange_product_has_feature_membership-hierarchy', array( $this, 'product_has_feature') , 9, 3 );
		add_filter( 'it_exchange_product_supports_feature_membership-hierarchy', array( $this, 'product_supports_feature') , 9, 2 );
	}

	/**
	 * Register the product feature and add it to enabled product-type addons
	 *
	 * @since 1.1.0
	*/
	function add_feature_support_to_product_types() {
		// Register the product feature
		$slug        = 'membership-hierarchy';
		$description = __( 'Allows you to set a Parent for a given membership, to enable membership hierarchy permissions.', 'it-l10n-exchange-addon-membership' );
		it_exchange_register_product_feature( $slug, $description );

		it_exchange_add_feature_support_to_product_type( 'membership-hierarchy', 'membership-product-type' );
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
			if ( !empty( $product_type ) &&  it_exchange_product_type_supports_feature( $product_type, 'membership-hierarchy' ) )
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
		add_meta_box( 'it-exchange-product-membership-hierarchy', __( 'Membership Hierarchy', 'it-l10n-exchange-addon-membership' ), array( $this, 'print_metabox' ), 'it_exchange_prod', 'it_exchange_advanced' );
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
		$membership_products = it_exchange_get_products( array( 'product_type' => 'membership-product-type', 'numberposts' => -1, 'show_hidden' => true ) );
		
		echo '<p>' . __( 'View and edit membership relationships below. You can add child memberships to include the content and files from another membership with this membership. You can also add and remove parent memberships that include this membership.', 'it-l10n-exchange-addon-membership' ) . '</p>';
		
		$child_ids = it_exchange_get_product_feature( $product->ID, 'membership-hierarchy', array( 'setting' => 'children' ) );
		$parent_ids = it_exchange_get_product_feature( $product->ID, 'membership-hierarchy', array( 'setting' => 'parents' ) );
		
		echo '<p><label for="it-exchange-membership-child-id" class="it-exchange-membership-label it-exchange-membership-child-label">' . __( 'Child Memberships', 'it-l10n-exchange-addon-membership' ) . ' <span class="tip" title="' . __( "A Parent gets all of its own access, plus all of it's Child(ren)'s access.", 'it-l10n-exchange-addon-membership' ) . '">i</span></label></p>';
		echo '<p>' . __( 'Additional membership available to owners of this membership level.', 'it-l10n-exchange-addon-membership' ) . '</p>';
	
  		echo '<div class="it-exchange-membership-child-ids-list-div">';
		it_exchange_membership_addon_display_membership_hierarchy( $child_ids );
		echo '</div>';
		        
        echo '<div class="it-exchange-membership-hierarchy-add it-exchange-membership-hierarchy-add-child">';
        echo '<select class="it-exchange-membership-child-id" name="it-exchange-membership-child-id">';
		echo '<option value="">' . __( 'Select a Membership', 'it-l10n-exchange-addon-membership' ) . '</option>';
		foreach ( $membership_products as $membership ) {
			if ( $membership->ID != $post->ID ) //needs to be fixed
				echo '<option value="' . $membership->ID . '">' . get_the_title( $membership->ID ) . '</option>';
		}
		echo '</select>';
        echo '<a href class="button">' . __( 'Add Child Membership', 'it-l10n-exchange-addon-membership' ) . '</a>';
        echo '</div>';
				
		echo '<p><label for="it-exchange-membership-parent-id" class="it-exchange-membership-label it-exchange-membership-parent-label">' . __( 'Parent Memberships', 'it-l10n-exchange-addon-membership' ) . ' <span class="tip" title="' . __( "A Parent gets all of its own access, plus all of it's Child(ren)'s access.", 'it-l10n-exchange-addon-membership' ) . '">i</span></label></p>';
		echo '<p>' . __( 'Memberships that include content from this membership and all children of it.', 'it-l10n-exchange-addon-membership' ) . '</p>';
  		
  		echo '<div class="it-exchange-membership-parent-ids-list-div">';
		echo '<ul>';
		foreach ( $parent_ids as $parent_id ) {
			if ( false !== get_post_status( $parent_id ) ) {
				echo '<li data-parent-id="' . $parent_id . '">';
				echo '<div class="inner-wrapper">' . get_the_title( $parent_id ) . ' <a href data-membership-id="' . $parent_id . '" class="it-exchange-membership-addon-delete-membership-parent it-exchange-remove-item">&times;</a>';
				echo '<input type="hidden" name="it-exchange-membership-parent-ids[]" value="' . $parent_id . '" /></div>';
				echo '</li>';
			}
		}
		echo '</ul>';
		echo '</div>';
		
        echo '<div class="it-exchange-membership-hierarchy-add it-exchange-membership-hierarchy-add-parent">';
        echo '<select class="it-exchange-membership-parent-id" name="it-exchange-membership-parent-id">';
		echo '<option value="">' . __( 'Select a Membership', 'it-l10n-exchange-addon-membership' ) . '</option>';
		foreach ( $membership_products as $membership ) {
			if ( $membership->ID != $post->ID ) //needs to be fixed
				echo '<option value="' . $membership->ID . '">' . get_the_title( $membership->ID ) . '</option>';
		}
		echo '</select>';
        echo '<a href class="button">' . __( 'Add Parent Membership', 'it-l10n-exchange-addon-membership' ) . '</a>';
        echo '</div>';
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
		if ( ! it_exchange_product_type_supports_feature( $product_type, 'membership-hierarchy' ) )
			return;
			
		$child_ids = empty( $_POST['it-exchange-membership-child-ids'] ) ? array() : $_POST['it-exchange-membership-child-ids'];
		$parent_ids = empty( $_POST['it-exchange-membership-parent-ids'] ) ? array() : $_POST['it-exchange-membership-parent-ids'];
			
		it_exchange_update_product_feature( $product_id, 'membership-hierarchy', $child_ids, array( 'setting' => 'children' ) );
		it_exchange_update_product_feature( $product_id, 'membership-hierarchy', $parent_ids, array( 'setting' => 'parents' ) );
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
			
			case 'children':
				$child_ids = get_post_meta( $product_id, '_it-exchange-membership-child-id' );
				if ( empty( $new_value ) ) {
					delete_post_meta( $product_id, '_it-exchange-membership-child-id' );
					foreach( $child_ids as $child_id ) {
						delete_post_meta( $child_id, '_it-exchange-membership-parent-id', $product_id );
					}
				} else {
					foreach( $child_ids as $child_id ) {
						if ( !in_array( $child_id, $new_value ) ) {
							delete_post_meta( $product_id, '_it-exchange-membership-child-id', $child_id );
							delete_post_meta( $child_id, '_it-exchange-membership-parent-id', $product_id );
						}
					}
					
					foreach ( $new_value as $child_id ) {
						if ( !in_array( $child_id, (array)$child_ids ) )
							add_post_meta( $product_id, '_it-exchange-membership-child-id', $child_id );
							
						$parent_ids = get_post_meta( $child_id, '_it-exchange-membership-parent-id' );
						if ( !in_array( $product_id, (array)$parent_ids ) )
							add_post_meta( $child_id, '_it-exchange-membership-parent-id', $product_id );
					}
				}
				break;
				
			case 'parents':
				$parent_ids = get_post_meta( $product_id, '_it-exchange-membership-parent-id' );
				if ( empty( $new_value ) ) {
					delete_post_meta( $product_id, '_it-exchange-membership-parent-id' );
					foreach( $parent_ids as $parent_id ) {
						delete_post_meta( $parent_id, '_it-exchange-membership-child-id', $product_id );
					}
				} else {
					foreach( $parent_ids as $parent_id ) {
						if ( !in_array( $parent_id, $new_value ) ) {
							delete_post_meta( $product_id, '_it-exchange-membership-parent-id', $parent_id );
							delete_post_meta( $parent_id, '_it-exchange-membership-child-id', $product_id );
						}
					}
					
					foreach ( $new_value as $parent_id ) {
						if ( !in_array( $parent_id, (array)$parent_ids ) )
							add_post_meta( $product_id, '_it-exchange-membership-parent-id', $parent_id );
								
						$child_ids = get_post_meta( $parent_id, '_it-exchange-membership-child-id' );
						if ( !in_array( $product_id, (array)$child_ids ) )
							add_post_meta( $parent_id, '_it-exchange-membership-child-id', $product_id );
					}
				}
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

			case 'children':
				$test = get_post_meta( $product_id, '_it-exchange-membership-child-id' );
				return $test;
			case 'parents':
				return get_post_meta( $product_id, '_it-exchange-membership-parent-id' );
				
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
	function product_supports_feature( $result, $product_id, $options=array() ) {
		// Does this product type support this feature?
		$product_type = it_exchange_get_product_type( $product_id );
		if ( ! it_exchange_product_type_supports_feature( $product_type, 'membership-hierarchy' ) )
			return false;

		return true;
	}
}
$IT_Exchange_Addon_Membership_Product_Feature_Membership_Hierarchy = new IT_Exchange_Addon_Membership_Product_Feature_Membership_Hierarchy();