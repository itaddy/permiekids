<?php
/**
 * This will control membership content access
 *
 * @since 1.0.0
 * @package IT_Exchange_Addon_Membership
*/


class IT_Exchange_Addon_Membership_Product_Feature_Content_Access {

	/**
	 * Constructor. Registers hooks
	 *
	 * @since 1.0.0
	 * @return void
	*/
	function IT_Exchange_Addon_Membership_Product_Feature_Content_Access() {
		if ( is_admin() ) {
			add_action( 'load-post-new.php', array( $this, 'init_feature_metaboxes' ) );
			add_action( 'load-post.php', array( $this, 'init_feature_metaboxes' ) );
			add_action( 'it_exchange_save_product', array( $this, 'save_feature_on_product_save' ) );
		}
		add_action( 'it_exchange_enabled_addons_loaded', array( $this, 'add_feature_support_to_product_types' ) );
		add_action( 'it_exchange_update_product_feature_membership-content-access-rules', array( $this, 'save_feature' ), 9, 3 );
		add_filter( 'it_exchange_get_product_feature_membership-content-access-rules', array( $this, 'get_feature' ), 9, 3 );
		add_filter( 'it_exchange_product_has_feature_membership-content-access-rules', array( $this, 'product_has_feature') , 9, 2 );
		add_filter( 'it_exchange_product_supports_feature_membership-content-access-rules', array( $this, 'product_supports_feature') , 9, 2 );
	}

	/**
	 * Register the product feature and add it to enabled product-type addons
	 *
	 * @since 1.0.0
	*/
	function add_feature_support_to_product_types() {
		// Register the product feature
		$slug        = 'membership-content-access-rules';
		$description = __( 'How long a membership should last.', 'it-l10n-exchange-addon-membership' );
		it_exchange_register_product_feature( $slug, $description );

		it_exchange_add_feature_support_to_product_type( 'membership-content-access-rules', 'membership-product-type' );
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
			if ( !empty( $product_type ) &&  it_exchange_product_type_supports_feature( $product_type, 'membership-content-access-rules' ) )
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
		add_meta_box( 'it-exchange-product-membership-content-access-rules', __( 'Membership Content Access', 'it-l10n-exchange-addon-membership' ), array( $this, 'print_metabox' ), 'it_exchange_prod', 'it_exchange_normal', 'low'  );
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
		$access_rules = it_exchange_get_product_feature( $product->ID, 'membership-content-access-rules' );
		
		?>
		<div class="it-exchange-content-access-header">
	        <div class="it-exchange-content-access-label-add">
	            <label><?php _e( 'Content Access', 'it-l10n-exchange-addon-membership' ); ?> <span class="tip" title="<?php _e( 'Content Access settings restrict access to content for this membership.  Note: Delay Access settings can only be applied to individual posts or pages.', 'it-l10n-exchange-addon-membership' ); ?>">i</span></label>
	        </div>
		</div>
        <div class="it-exchange-content-access-list-wrapper">
			<?php
            if ( !empty( $access_rules ) ) {
                $hidden_access_list_class = '';
                $hidden_no_rules_class = 'hidden';
            } else {
                $hidden_access_list_class = 'hidden';
                $hidden_no_rules_class = '';
            }
            ?>
        	<div class="it-exchange-content-access-list <?php echo $hidden_access_list_class; ?>">
                <div class="it-exchange-content-access-list-titles">
                    <div class="it-exchange-content-access-item columns-wrapper">
                        <div class="column"></div>
                        <div class="it-exchange-content-access-type column">
                            <span><?php _e( 'Type', 'it-l10n-exchange-addon-membership' ); ?></span>
                        </div>
                        <div class="it-exchange-content-access-content column">
                            <span><?php _e( 'Content', 'it-l10n-exchange-addon-membership' ); ?></span>
                        </div>
                        <div class="it-exchange-content-access-delay column">
                            <span><?php _e( 'Delay Access', 'it-l10n-exchange-addon-membership' ); ?> <span class="tip" title="<?php _e( 'This setting can only be applied to individual posts or pages.', 'it-l10n-exchange-addon-membership' ); ?>">i</span></span>
                        </div>
                    </div>
                </div>
                <?php $count = 0; ?>
                <?php $group_count = 0; ?>
                <?php $groupings = array(); ?>
                <div class="it-exchange-membership-addon-content-access-rules content-access-sortable">
                <?php
				if ( !empty( $access_rules ) ) {
					foreach( $access_rules as $rule ) {
						$current_grouped_id = isset( $rule['grouped_id'] ) ? $rule['grouped_id'] : false;
												
						if ( !empty( $groupings ) && $current_grouped_id !== end( $groupings ) ) {
						
							echo '</div></div>'; //this is ending the divs from the group opening in it_exchange_membership_addon_build_content_rule()
							array_pop( $groupings );
													
						} else if ( false === $current_grouped_id && !empty( $groupings ) ) {
												
							foreach( $groupings as $group ) {
								echo '</div></div>'; //this is ending the divs from the group opening in it_exchange_membership_addon_build_content_rule()
							}
							$groupings = array();
							
						}
												
						echo it_exchange_membership_addon_build_content_rule( $rule, $count++, $product->ID );
						
						$current_group_id = isset( $rule['group_id'] ) ? $rule['group_id'] : false;
						
						if ( false !== $current_group_id && !in_array( $current_group_id, $groupings ) )
							$groupings[] = $current_group_id;
						
						if ( false !== $current_group_id && $group_count >= $current_group_id )
							$group_count = $rule['group_id'] + 1;

					}
					
					if ( !empty( $groupings ) ) {
						foreach( $groupings as $group ) {
							echo '</div></div>'; //this is ending the divs from the group opening in it_exchange_membership_addon_build_content_rule()
						}
						$groupings = array();
					}
				}
                ?>
                </div>
            </div>
            <div class="it-exchange-content-no-rules it-exchange-membership-content-access-add-new-rule <?php echo $hidden_no_rules_class; ?>"><?php _e( 'No content access rules added to this membership yet. <a href="">Add New Rule</a>', 'it-l10n-exchange-addon-membership' ); ?></div>
        </div>
		<div class="it-exchange-content-access-footer">
			<div class="it-exchange-membership-content-access-add-new-rule left">
	            <a href class="button"><?php _e( 'Add New Rule', 'it-l10n-exchange-addon-membership' ); ?></a>
	        </div>
            <div class="it-exchange-membership-content-access-add-new-group left">
                <a href class="button"><?php _e( 'Add New Group', 'it-l10n-exchange-addon-membership' ); ?></a>
            </div>
        </div>
		<script type="text/javascript" charset="utf-8">
            var it_exchange_membership_addon_content_access_iteration = <?php echo $count; ?>;
            var it_exchange_membership_addon_content_access_group_iteration = <?php echo $group_count; ?>;
        </script>
		<?php
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
		if ( ! it_exchange_product_type_supports_feature( $product_type, 'membership-content-access-rules' ) )
			return;
		
		$existing_access_rules = it_exchange_get_product_feature( $product_id, 'membership-content-access-rules' );
		
		//ITDebug::print_r( $_REQUEST );
		
		if ( ! empty( $_REQUEST['it_exchange_content_access_rules'] ) ) {
			
			foreach( $_REQUEST['it_exchange_content_access_rules'] as $key => $rule ) {
			
				if ( !empty( $rule['selected'] ) && !empty( $rule['selection'] ) && !empty( $rule['term'] ) ) {
				
					switch( $rule['selected'] ) {
					
						case 'posts':
							if ( !( $rules = get_post_meta( $rule['term'], '_item-content-rule', true ) ) )
								$rules = array();
								
							if ( !empty( $rule['drip-interval'] ) && !empty( $rule['drip-duration'] ) ) {
								update_post_meta( $rule['term'], '_item-content-rule-drip-interval-' . $product_id, absint( $rule['drip-interval'] ) );
								update_post_meta( $rule['term'], '_item-content-rule-drip-duration-' . $product_id, $rule['drip-duration'] );
								unset( $rule['drip-interval'] );
								unset( $rule['drip-duration'] );
								unset( $_REQUEST['it_exchange_content_access_rules'][$key]['drip-interval'] );
								unset( $_REQUEST['it_exchange_content_access_rules'][$key]['drip-duration'] );
							}
								
							if ( !in_array( $product_id, $rules ) ) {
								$rules[] = $product_id;
								update_post_meta( $rule['term'], '_item-content-rule', $rules );
							}
							break;
							
						case 'post_types':
							if ( !( $rules = get_option( '_item-content-rule-post-type-' . $rule['term'] ) ) )
								$rules = array();
	
							if ( !in_array( $product_id, $rules ) ) {
								$rules[] = $product_id;
								update_option( '_item-content-rule-post-type-' . $rule['term'],  $rules );
							}
							break;
							
						case 'taxonomy':
							if ( !( $rules = get_option( '_item-content-rule-tax-' . $rule['selection'] . '-' . $rule['term'] ) ) )
								$rules = array();
								
							if ( !in_array( $product_id, $rules ) ) {
								$rules[] = $product_id;
								update_option( '_item-content-rule-tax-' . $rule['selection'] . '-' . $rule['term'],  $rules );
							}
							break;
						
					} 
					
				} else if ( isset( $rule['group'] ) && isset( $rule['group_id'] ) ) {
				
					//nothing really to do here, just want to make sure this case isn't unset by the else
				
				} else {
				
					//This should only happen if the user adds a new rule but doesn't make a selection
					unset( $_REQUEST['it_exchange_content_access_rules'][$key] );
					
				}
				
			}
				
			it_exchange_update_product_feature( $product_id, 'membership-content-access-rules', $_REQUEST['it_exchange_content_access_rules'] );
			
		} else {
			
			it_exchange_update_product_feature( $product_id, 'membership-content-access-rules', array() );
			
		}
			
		if ( !empty( $existing_access_rules ) ) {
			
			$updated_access_rules = it_exchange_get_product_feature( $product_id, 'membership-content-access-rules' );
			$diff_access_rules = array();
			
			foreach ( $existing_access_rules as $existing_access_rule ) {
			
				$defaults = array(
					'selection' => '',
					'selected'  => '',
					'term'      => '',
				);
				$existing_access_rule = wp_parse_args( $existing_access_rule, $defaults );
				
				$found = false;
			
				foreach ( $updated_access_rules as $updated_access_rule ) {
				
					$updated_access_rule = wp_parse_args( $updated_access_rule, $defaults );
													
					if (   $existing_access_rule['selection'] === $updated_access_rule['selection']
						&& $existing_access_rule['selected']  === $updated_access_rule['selected']
						&& $existing_access_rule['term']      === $updated_access_rule['term'] ) {
						$found = true;
						continue;
					}
				
				}
				
				if ( !$found )
					$diff_access_rules[] = $existing_access_rule;
				
			}
						
			if ( ! empty( $diff_access_rules ) ) {
				
				foreach( $diff_access_rules as $rule ) {
				
					switch( $rule['selected'] ) {
					
						case 'posts':
							if ( !( $rules = get_post_meta( $rule['term'], '_item-content-rule', true ) ) )
								$rules = array();
								
							if( false !== $key = array_search( $product_id, $rules ) ) {
								unset( $rules[$key] );
								if ( empty( $rules ) )
									delete_post_meta(  $rule['term'], '_item-content-rule' );
								else
									update_post_meta( $rule['term'], '_item-content-rule', $rules );
							}
							break;
							
						case 'post_types':
							if ( !( $rules = get_option( '_item-content-rule-post-type-' . $rule['term'] ) ) )
								$rules = array();
								
							if( false !== $key = array_search( $product_id, $rules ) ) {
								unset( $rules[$key] );
								if ( empty( $rules ) )
									delete_option( '_item-content-rule-post-type-' . $rule['term'] );
								else
									update_option( '_item-content-rule-post-type-' . $rule['term'],  $rules );
							}
							break;
							
						case 'taxonomy':
							if ( !( $rules = get_option( '_item-content-rule-tax-' . $rule['selection'] . '-' . $rule['term'] ) ) )
								$rules = array();
								
							if( false !==  $key = array_search( $product_id, $rules ) ) {
								unset( $rules[$key] );
								if ( empty( $rules ) )
									delete_option( '_item-content-rule-tax-' . $rule['selection'] . '-' . $rule['term'] );
								else
									update_option( '_item-content-rule-tax-' . $rule['selection'] . '-' . $rule['term'],  $rules );
							}
							break;
						
					}
					
				}
				
			}

		}
		
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
		if ( ! it_exchange_get_product( $product_id ) )
			return false;
			
		update_post_meta( $product_id, '_it-exchange-membership-addon-content-access-meta', $new_value );
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
		return get_post_meta( $product_id, '_it-exchange-membership-addon-content-access-meta', true );
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

		// If it does support, does it have it?
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
		if ( ! it_exchange_product_type_supports_feature( $product_type, 'membership-content-access-rules' ) )
			return false;

		return true;
	}
}
$IT_Exchange_Addon_Membership_Product_Feature_Content_Access = new IT_Exchange_Addon_Membership_Product_Feature_Content_Access();
