<?php
/**
 * iThemes Exchange Membership Add-on
 * @package IT_Exchange_Addon_Membership
 * @since 1.0.0
*/

/**
 * Shows the nag when needed.
 *
 * @since 1.0.0
 *
 * @return void
*/
function it_exchange_membership_addon_show_version_nag() {
	if ( $GLOBALS['it_exchange']['version'] < '1.4.0' ) {
		?>
		<div id="it-exchange-add-on-min-version-nag" class="it-exchange-nag">
			<?php printf( __( 'The Membership add-on requires iThemes Exchange version 1.4.0 or greater. %sPlease upgrade Exchange%s.', 'it-l10n-exchange-addon-membership' ), '<a href="' . admin_url( 'update-core.php' ) . '">', '</a>' ); ?>
		</div>
		<script type="text/javascript">
			jQuery( document ).ready( function() {
				if ( jQuery( '.wrap > h2' ).length == '1' ) {
					jQuery("#it-exchange-add-on-min-version-nag").insertAfter('.wrap > h2').addClass( 'after-h2' );
				}
			});
		</script>
		<?php
	}
}
add_action( 'admin_notices', 'it_exchange_membership_addon_show_version_nag' );

/**
 * Adds actions to the plugins page for the iThemes Exchange Membership plugin
 *
 * @since 1.0.0
 *
 * @param array $meta Existing meta
 * @param string $plugin_file the wp plugin slug (path)
 * @param array $plugin_data the data WP harvested from the plugin header
 * @param string $context 
 * @return array
*/
function it_exchange_membership_plugin_row_actions( $actions, $plugin_file, $plugin_data, $context ) {
    
    $actions['setup_addon'] = '<a href="' . get_admin_url( NULL, 'admin.php?page=it-exchange-addons&add-on-settings=membership-product-type' ) . '">' . __( 'Setup Add-on', 'it-l10n-exchange-addon-membership' ) . '</a>';
    
    return $actions;
    
}
add_filter( 'plugin_action_links_exchange-addon-membership/exchange-addon-membership.php', 'it_exchange_membership_plugin_row_actions', 10, 4 );

/**
 * Adds digital downloads to the iThemes Exchange Membership plugin
 *
 * @since 1.0.0
 *
 * @return void
*/
function ithemes_exchange_membership_addon_add_feature_digital_downloads() {
	it_exchange_add_feature_support_to_product_type( 'downloads', 'membership-product-type' );
}
add_action( 'it_exchange_enabled_addons_loaded', 'ithemes_exchange_membership_addon_add_feature_digital_downloads' );

/**
 * Enqueues Membership scripts to WordPress Dashboard
 *
 * @since 1.0.0
 *
 * @param string $hook_suffix WordPress passed variable
 * @return void
*/
function it_exchange_membership_addon_admin_wp_enqueue_scripts( $hook_suffix ) {
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
	
	if ( isset( $post_type ) && 'it_exchange_prod' === $post_type ) {
		$deps = array( 'post', 'jquery-ui-sortable', 'jquery-ui-droppable', 'jquery-ui-tabs', 'jquery-ui-tooltip', 'jquery-ui-datepicker', 'autosave' );
		wp_enqueue_script( 'it-exchange-membership-addon-add-edit-product', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/js/add-edit-product.js', $deps );
	} else if ( isset( $post_type ) && 'it_exchange_prod' !== $post_type ) {
		wp_enqueue_script( 'it-exchange-membership-addon-add-edit-post', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/js/add-edit-post.js' );
	}
}
add_action( 'admin_enqueue_scripts', 'it_exchange_membership_addon_admin_wp_enqueue_scripts' );

/**
 * Enqueues Membership styles to WordPress Dashboard
 *
 * @since 1.0.0
 *
 * @return void
*/
function it_exchange_membership_addon_admin_wp_enqueue_styles() {
	global $post, $hook_suffix;

	if ( isset( $_REQUEST['post_type'] ) ) {
		$post_type = $_REQUEST['post_type'];
	} else {
		if ( isset( $_REQUEST['post'] ) ) {
			$post_id = (int) $_REQUEST['post'];
		} else if ( isset( $_REQUEST['post_ID'] ) ) {
			$post_id = (int) $_REQUEST['post_ID'];
		} else {
			$post_id = 0;
		}

		if ( $post_id )
			$post = get_post( $post_id );

		if ( isset( $post ) && !empty( $post ) )
			$post_type = $post->post_type;
	}

	// Exchange Product pages
	if ( isset( $post_type ) && 'it_exchange_prod' === $post_type ) {
		wp_enqueue_style( 'it-exchange-membership-addon-add-edit-product', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/styles/add-edit-product.css' );
	} else if ( isset( $post_type ) && 'it_exchange_prod' !== $post_type ) {
		wp_enqueue_style( 'it-exchange-membership-addon-add-edit-post', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/styles/add-edit-post.css' );
	}
}
add_action( 'admin_print_styles', 'it_exchange_membership_addon_admin_wp_enqueue_styles' );

/**
 * Enqueues Membership scripts to WordPress frontend
 *
 * @since 1.0.0
 *
 * @param string $current_view WordPress passed variable
 * @return void
*/
function it_exchange_membership_addon_load_public_scripts( $current_view ) {
	// Frontend Membership Dashboard CSS & JS
	wp_enqueue_script( 'it-exchange-membership-addon-public-js', ITUtility::get_url_from_file( dirname( __FILE__ ) . '/assets/js/membership-dashboard.js' ), array( 'jquery-zoom' ), false, true );
	wp_enqueue_style( 'it-exchange-membership-addon-public-css', ITUtility::get_url_from_file( dirname( __FILE__ ) . '/assets/styles/membership-dashboard.css' ) );
}
add_action( 'wp_enqueue_scripts', 'it_exchange_membership_addon_load_public_scripts' );

/**
 * Outputs the Membership media form button
 *
 * @since 1.0.18 
 * @return void
*/
function it_exchange_membership_addon_media_form_button() {
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
	
	add_thickbox();
   
	if ( isset( $post_type ) && 'it_exchange_prod' !== $post_type ) {
        // display button matching new UI
        echo '<style>.it_exchange_membership_media_icon{
					background:url(' . ITUtility::get_url_from_file( dirname( __FILE__ ) . '/images/membership16px.png' ) . ') no-repeat top left;
					display: inline-block;
					height: 16px;
					margin: 0 2px 0 0;
					vertical-align: text-top;
					width: 16px;
				}
				.wp-core-ui a.it_exchange_membership_media_link{
					 padding-left: 0.4em;
				}
			</style>
			<a href="#TB_inline?width=380&inlineId=select-membership-product" class="thickbox button it_exchange_membership_media_link" id="add_membership_content" title="' . __( 'Restrict content to a specific Membership product in this post with the Member Content shortcode', 'it-l10n-exchange-addon-membership' ) . '"><span class="it_exchange_membership_media_icon "></span> ' . __( 'Member Content', 'it-l10n-exchange-addon-membership' ) . '</a>';
    }
}
add_action( 'media_buttons', 'it_exchange_membership_addon_media_form_button', 15 );

/**
 * Outputs the Membershpi MCE Popup
 *
 * @since 1.0.18 
 * @return void
*/
function it_exchange_membership_addon_mce_popup_footer() {
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
	
	if ( isset( $post_type ) && 'it_exchange_prod' !== $post_type ) {
		?>
		<script>
            function InsertMemberContentShortcode() {
            	var membership_ids = new Array;

                jQuery( '#add-membership-id option:selected' ).each( function() {
					membership_ids.push( jQuery( this ).val() );
				});
				
				if ( membership_ids.length == 0 ){
                    alert("<?php _e( 'You must select at least one membership product', 'it-l10n-exchange-addon-membership' ); ?>");
                    return;
                }
                
                var content = tinyMCE.activeEditor.selection.getContent();

                window.send_to_editor( '[it-exchange-member-content membership_ids="' + membership_ids.join() + '"]' + content + '[/it-exchange-member-content]' );
            }
        </script>

        <div id="select-membership-product" style="display:none;">
            <div class="wrap">
                <div>
                	<p class="description" style="padding:0 15px;"><?php _e( 'To restrict content within this post or page to users with a specific membership level, highlight the section you’d like to restrict in your content, choose the membership levels you’d like to give access to the content and click the Insert Shortcode button below.', 'it-l10n-exchange-addon-membership' ); ?></p>
                    <div style="padding:15px 15px 0 15px;">
                        <h3><?php _e( 'Select Membership Product(s)', 'it-l10n-exchange-addon-membership' ); ?></h3>
                        <span>
                            <?php _e( 'Choose the Memberships allowed to see selected content.', 'it-l10n-exchange-addon-membership' ); ?>
                        </span>
                    </div>
                    <div style="padding:15px 15px 0 15px;">
                        <select id="add-membership-id" multiple="multiple" size="5">
                            <?php
                            $membership_products = it_exchange_get_products( array( 'product_type' => 'membership-product-type', 'show_hidden' => true ) );
							foreach ( $membership_products as $membership ) {
								echo '<option value="' . $membership->ID . '">' . get_the_title( $membership->ID ) . '</option>';
							}
                            ?>
                        </select>
                    </div>
                    <div style="padding:15px;">
                    <input type="button" class="button-primary" value="<?php _e( 'Insert Shortcode', 'it-l10n-exchange-addon-membership' ); ?>" onclick="InsertMemberContentShortcode();"/>&nbsp;&nbsp;&nbsp;
                    <a class="button" style="color:#bbb;" href="#" onclick="tb_remove(); return false;"><?php _e( 'Cancel', 'it-l10n-exchange-addon-membership' ); ?></a>
                    </div>
                </div>
            </div>
        </div>
        <?php
	}
	
}
add_action( 'admin_footer', 'it_exchange_membership_addon_mce_popup_footer' );

/**
 * Adds shortcode information below extended description box
 *
 * @since 1.0.0
 * @param object $post WordPress post object 
 * @return void
*/
function it_exchange_membership_addon_after_print_extended_description_metabox( $post ) {
	
	$product_type = it_exchange_get_product_type( $post->ID );
			
	if ( 'membership-product-type' === $product_type ) {
		echo '<p class="description">[it-exchange-membership-included-content] - ' . __( 'Displays content available with this membership. <a href="http://ithemes.com/codex/page/Exchange_Product_Types:_Memberships#Shortcodes">Click here to read about the available shortcode options.</a>', 'it-l10n-exchange-addon-membership' ) . '</p>';
	}
	
	//http://ithemes.com/codex/page/Exchange_Product_Types:_Memberships#Shortcodes
	
	return;
	
}
add_action( 'it_exchange_after_print_extended_description_metabox', 'it_exchange_membership_addon_after_print_extended_description_metabox' );

/**
 * Adds necessary details to Exchange upon successfully completed transaction
 *
 * @since 1.0.0
 * @param int $transaction_id iThemes Exchange Transaction ID 
 * @return void
*/
function it_exchange_membership_addon_add_transaction( $transaction_id ) {
	$cart_object = get_post_meta( $transaction_id, '_it_exchange_cart_object', true );
	$customer_id = get_post_meta( $transaction_id, '_it_exchange_customer_id', true );
	$customer = new IT_Exchange_Customer( $customer_id );
	$transaction = it_exchange_get_transaction( $transaction_id );
	$member_access = $customer->get_customer_meta( 'member_access' );
	$cancel_subscription = it_exchange_get_session_data( 'cancel_subscription' );
	foreach ( $cart_object->products as $product ) {
		$product_id = $product['product_id'];
		if ( it_exchange_product_supports_feature( $product_id, 'membership-content-access-rules' ) ) {
			//This is a membership product!
			if ( !in_array( $product_id, (array)$member_access ) ) {
				//If this user isn't already a member of this product, add it to their access list
				$member_access[$transaction_id] = $product_id;
			}
		}
		
		if ( !empty ( $cancel_subscription[$product_id] ) ) {
			$transaction->update_transaction_meta( 'free_days', $cancel_subscription[$product_id]['free_days'] );
			$transaction->update_transaction_meta( 'credit', $cancel_subscription[$product_id]['credit'] );
		}
	}
	if ( !empty( $cancel_subscription ) ) {
		foreach( $cancel_subscription as $cancel_subscription_item ) {
			$old_transaction_id = $cancel_subscription_item['old_transaction_id'];
			$old_transaction = it_exchange_get_transaction( $old_transaction_id );
			$old_transaction->update_status( 'cancelled' );
			if ( !empty( $member_access[$old_transaction_id] ) )
				unset( $member_access[$old_transaction_id] );
		}
	}
	$customer->update_customer_meta( 'member_access', $member_access );
}
add_action( 'it_exchange_add_transaction_success', 'it_exchange_membership_addon_add_transaction' );

/**
 * Adds necessary details to Exchange upon successfully completed child transaction
 *
 * @since 1.0.0
 * @param int $transaction_id iThemes Exchange Child Transaction ID 
 * @return void
*/
function it_exchange_membership_addon_add_child_transaction( $transaction_id ) {
	$parent_txn_id =  get_post_meta( $transaction_id, '_it_exchange_parent_tx_id', true );
	it_exchange_membership_addon_add_transaction( $parent_txn_id );
}
add_action( 'it_exchange_add_child_transaction_success', 'it_exchange_membership_addon_add_child_transaction' );

/**
 * Creates sessions data with logged in customer's membership access rules
 *
 * @since 1.0.0
 * @return void
*/
function it_exchange_membership_addon_setup_customer_session() {
	if ( is_user_logged_in() ) {
		$user_id = get_current_user_id();
		$parent_access = array();
		$customer = new IT_Exchange_Customer( $user_id );
		$member_access = $customer->get_customer_meta( 'member_access' );
		$member_access_session = it_exchange_get_session_data( 'member_access' );
		if ( !empty( $member_access )  ) {
			//If the transient doesn't exist, verify the membership access subscriber status and reset transient
			if ( false === get_transient( 'member_access_check_' . $customer->id ) ) {
				foreach( $member_access as $txn_id => $product_id ) {
					$transaction = it_exchange_get_transaction( $txn_id );
					if ( 'cancelled' === $transaction->get_status() ) {
						unset( $member_access[$txn_id] );
					} else {
						$subscription_status = $transaction->get_transaction_meta( 'subscriber_status' );
						//empty means it was never set... which should mean that recurring payments isn't setup
						if ( !empty( $subscription_status ) && 'active' !== $subscription_status )
							unset( $member_access[$txn_id] );
					}
				}
				set_transient( 'member_access_check_' . $customer->id, $member_access, 60 * 60 * 4 ); //only do it every four hours
				$customer->update_customer_meta( 'member_access', $member_access );
			}
			$parent_access = it_exchange_membership_addon_setup_most_parent_member_access_array( $member_access );
			$member_access = array_flip( $member_access ); // we want the transaction ID to be the value to help us determine child access relations to transaction IDs
			$member_access = it_exchange_membership_addon_setup_recursive_member_access_array( $member_access );
		}	
		$member_diff = array_diff_assoc( (array)$member_access, (array)$member_access_session );
		if ( !empty( $member_diff ) ) {
			it_exchange_update_session_data( 'member_access', $member_access );
			it_exchange_update_session_data( 'parent_access', $parent_access );
		}
	} else {
		it_exchange_clear_session_data( 'member_access' );
		it_exchange_clear_session_data( 'parent_access' );
	}
}
add_action( 'wp', 'it_exchange_membership_addon_setup_customer_session' );

function it_exchange_membership_addon_update_transaction_subscription_status( $transaction, $subscriber_id, $status ) {
	if ( !empty( $transaction->customer_id) ) {
		$customer = it_exchange_get_customer( $transaction->customer_id );
		if ( !empty( $customer ) ) {
			$member_access = $customer->get_customer_meta( 'member_access' );
			if ( 'active' !== $status ) { //we're canceled, suspended, deactivated or something in between...
				if ( !empty( $member_access[$transaction->ID] ) )
					unset( $member_access[$transaction->ID] ); //so we remove it
			}
			$customer->update_customer_meta( 'member_access', $member_access );
		}
	}
}
add_action( 'it_exchange_update_transaction_subscription_status', 'it_exchange_membership_addon_update_transaction_subscription_status', 10, 3 );

/**
 * Creates sessions data with logged in customer's membership access rules
 *
 * @since 1.0.0
 * @param int $post_id WordPress Post ID
 * @return void
*/
function it_exchange_before_delete_membership_product( $post_id ){
	$existing_access_rules = it_exchange_get_product_feature( $post_id, 'membership-content-access-rules' );
	
	if ( !empty( $existing_access_rules ) ) {
		foreach( $existing_access_rules as $rule ) {
			switch( $rule['selected'] ) {
				case 'posts':
					if ( !( $rules = get_post_meta( $rule['term'], '_item-content-rule', true ) ) )
						$rules = array();
					
					delete_post_meta( $rule['term'], '_item-content-rule-drip-interval-' . $post_id );
					delete_post_meta( $rule['term'], '_item-content-rule-drip-duration-' . $post_id );
					
					$restriction_exemptions = get_post_meta( $rule['term'], '_item-content-rule-exemptions', true );
					if ( !empty( $restriction_exemptions ) ) {
						if ( array_key_exists( $post_id, $restriction_exemptions ) ) {
							unset( $restriction_exemptions[$post_id] );
							if ( !empty( $restriction_exemptions ) )
								update_post_meta( $rule['term'], '_item-content-rule-exemptions', $restriction_exemptions );
							else
								delete_post_meta( $rule['term'], '_item-content-rule-exemptions' );
						}
					}
						
					if( false !== $key = array_search( $post_id, $rules ) ) {
						unset( $rules[$key] );
						if ( empty( $rules ) )
							delete_post_meta( $rule['term'], '_item-content-rule' );
						else
							update_post_meta( $rule['term'], '_item-content-rule', $rules );
					}
					break;
					
				case 'post_types':
					if ( !( $rules = get_option( '_item-content-rule-post-type-' . $rule['term'] ) ) )
						$rules = array();
						
					if( false !== $key = array_search( $post_id, $rules ) ) {
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
						
					if( false !==  $key = array_search( $post_id, $rules ) ) {
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
add_action( 'before_delete_post', 'it_exchange_before_delete_membership_product' );

/**
 * Checks if $post is restriction rules apply, if so, return Membership restricted templates
 * If not, check if $post drip rules apply, if so, return Membership dripped templates
 * Otherwise, return $post's $content
 *
 * @since 1.0.0
 * @param string $content
 * @return string
*/
function it_exchange_membership_addon_content_filter( $content ) {
	if ( it_exchange_membership_addon_is_content_restricted() )
		return it_exchange_membership_addon_content_restricted_template();
	if ( it_exchange_membership_addon_is_content_dripped() )
		return it_exchange_membership_addon_content_dripped_template();
	return $content;	
}
add_filter( 'the_content', 'it_exchange_membership_addon_content_filter' );

/**
 * Checks if $post is restriction rules apply, if so, return Membership restricted templates
 * If not, check if $post drip rules apply, if so, return Membership dripped templates
 * Otherwise, return $post's $content
 *
 * @since 1.0.0
 * @param string $content
 * @return string
*/
function it_exchange_membership_addon_excerpt_filter( $excerpt ) {
	if ( it_exchange_membership_addon_is_content_restricted() )
		return it_exchange_membership_addon_excerpt_restricted_template();
	if ( it_exchange_membership_addon_is_content_dripped() )
		return it_exchange_membership_addon_excerpt_dripped_template();
	return $excerpt;
}
add_filter( 'the_excerpt', 'it_exchange_membership_addon_excerpt_filter' );

/**
 * Returns Membership content restricted template part
 *
 * @since 1.0.0
 * @return string
*/
function it_exchange_membership_addon_content_restricted_template() {
	$GLOBALS['wp_query']->is_single = false; //false -- so comments_template() doesn't add comments
	$GLOBALS['wp_query']->is_page = false;   //false -- so comments_template() doesn't add comments
	ob_start();
	it_exchange_get_template_part( 'content', 'restricted' );
	return ob_get_clean();	
}

/**
 * Returns Membership excerpt restricted template part
 *
 * @since 1.0.0
 * @return string
*/
function it_exchange_membership_addon_excerpt_restricted_template() {
	$GLOBALS['wp_query']->is_single = false; //false -- so comments_template() doesn't add comments
	$GLOBALS['wp_query']->is_page = false;   //false -- so comments_template() doesn't add comments
	ob_start();
	it_exchange_get_template_part( 'excerpt', 'restricted' );
	return ob_get_clean();	
}

/**
 * Returns Membership content dripped template part
 *
 * @since 1.0.0
 * @return string
*/
function it_exchange_membership_addon_content_dripped_template() {
	$GLOBALS['wp_query']->is_single = false; //false -- so comments_template() doesn't add comments
	$GLOBALS['wp_query']->is_page = false;   //false -- so comments_template() doesn't add comments
	ob_start();
	it_exchange_get_template_part( 'content', 'dripped' );
	return ob_get_clean();	
}

/**
 * Returns Membership excerpt dripped template part
 *
 * @since 1.0.0
 * @return string
*/
function it_exchange_membership_addon_excerpt_dripped_template() {
	$GLOBALS['wp_query']->is_single = false; //false -- so comments_template() doesn't add comments
	$GLOBALS['wp_query']->is_page = false;   //false -- so comments_template() doesn't add comments
	ob_start();
	it_exchange_get_template_part( 'excerpt', 'dripped' );
	return ob_get_clean();	
}

/**
 * Function to modify the default transaction confirmation elements
 *
 * @since 1.0.0
 * @param array $elements Elements being loaded by Theme API
 * @return array $elements Modified elements array
*/
function it_exchange_membership_addon_content_confirmation_after_product_attrubutes( $elements ) {
	it_exchange_get_template_part( 'content-confirmation/elements/membership-confirmation' );
}
add_filter( 'it_exchange_content_confirmation_after_product_attibutes', 'it_exchange_membership_addon_content_confirmation_after_product_attrubutes' );

/**
 * Adds Membership Template Path to iThemes Exchange Template paths
 *
 * @since 1.0.0
 * @param array $possible_template_paths iThemes Exchange existing Template paths array
 * @param array $template_names
 * @return array
*/
function it_exchange_membership_addon_template_path( $possible_template_paths, $template_names ) {
	$possible_template_paths[] = dirname( __FILE__ ) . '/templates/';
	return $possible_template_paths;
}
add_filter( 'it_exchange_possible_template_paths', 'it_exchange_membership_addon_template_path', 10, 2 );

/**
 * Replaces base-price content product element with customer-pricing element, if found
 *
 * @since 1.0.7 
 *
 * @param array $parts Element array for temmplate parts
 * @return array Modified array with new customer-pricing element (if base-price was found).
*/
function it_exchange_memnbership_addon_get_content_product_product_advanced_loop_elements( $parts ) {
	$parts[] = 'intended-audience';	
	$parts[] = 'objectives';	
	$parts[] = 'prerequisites';	
	return $parts;
}
add_filter( 'it_exchange_get_content_product_product_advanced_loop_elements', 'it_exchange_memnbership_addon_get_content_product_product_advanced_loop_elements' );

/**
 * Adds upgrade price after base-price content product element, if found
 *
 * @since CHANGEME
 *
 * @param array $parts Element array for temmplate parts
 * @return array Modified array with new customer-pricing element (if base-price was found).
*/
function it_exchange_memnbership_addon_get_content_product_product_info_loop_elements( $parts ) {
	array_unshift( $parts, 'upgrade-details' );
	array_unshift( $parts, 'downgrade-details' );
	return $parts;
}
add_filter( 'it_exchange_get_content_product_product_info_loop_elements', 'it_exchange_memnbership_addon_get_content_product_product_info_loop_elements' );

/*
 * Registers the membership frontend dashboard page in iThemes Exchange
 *
 * @since 1.0.0
 *
 * @return void
*/
function it_exchange_membership_addon_account_page() {
    $options = array(
		'slug'          => 'memberships',
		'name'          => __( 'Memberships', 'it-l10n-exchange-addon-membership' ),
		'rewrite-rules' => array( 115, 'it_exchange_get_memberships_page_rewrites' ),
		'url'           => 'it_exchange_get_membership_page_urls',
		'settings-name' => __( 'Membership Page', 'it-l10n-exchange-addon-membership' ),
		'tip'           => __( 'Membership pages appear in the customers account profile.', 'it-l10n-exchange-addon-membership' ),
		'type'          => 'exchange',
		'menu'          => true,
		'optional'      => true,
    );  
    it_exchange_register_page( 'memberships', $options );
}
add_action( 'it_libraries_loaded', 'it_exchange_membership_addon_account_page', 11 );

/**
 * Returns rewrites for membership frontend dashboard page
 * callback from rewrite-rules $options in it_exchange_membership_addon_account_page()
 *
 * @since 1.0.0
 *
 * @param string page
 * @return mixed array|false
*/
function it_exchange_get_memberships_page_rewrites( $page ) {
	$slug = it_exchange_get_page_slug( $page );
	switch( $page ) {
		case 'memberships' :
			$account_slug = it_exchange_get_page_slug( 'account' );

			// If we're using WP as acount page type, add the WP slug to rewrites and return.
			if ( 'wordpress' == it_exchange_get_page_type( 'account' ) ) {
				$account = get_page( it_exchange_get_page_wpid( 'account' ) );
				$account_slug = $account->post_name;
			}

			$rewrites = array(
				$account_slug  . '/([^/]+)/' . $slug  . '/([^/]+)/?$'  => 'index.php?' . $account_slug . '=$matches[1]&' . $slug . '=$matches[2]',
				$account_slug . '/' . $slug  . '/([^/]+)/?$' => 'index.php?' . $account_slug . '=1&' . $slug . '=$matches[1]',
			);
			return $rewrites;
			break;
	}
	return false;
}

/**
 * Modifies rewrite rules when setting the Memberships page to a WordPress page
 *
 * @since 1.0.19 
 *
 * @param array $existing rewrite rules
 * @return array modified rewrite rules
*/
function it_exchange_membership_addon_register_rewrite_rules( $existing ) {
	if ( 'wordpress' == it_exchange_get_page_type( 'memberships', true ) ) {
		$wpid = it_exchange_get_page_wpid( 'memberships' );
		if ( $wp_page = get_page( $wpid ) )
			$page_slug = $wp_page->post_name;
		else
			$page_slug = 'memberships';

		$rewrite = array( $page_slug . '/([^/]+)/?$' => 'index.php?pagename=' . $page_slug . '&' . $page_slug . '=$matches[1]' );
		$existing = array_merge( $rewrite, $existing );
	}
	return $existing;
}
add_filter( 'rewrite_rules_array', 'it_exchange_membership_addon_register_rewrite_rules' );

/**
 * Returns URL for membership frontend dashboard page
 *
 * @since 1.0.0
 *
 * @param string page
 * @return string URL
*/
function it_exchange_get_membership_page_urls( $page ) {
	// Get slugs
    $slug       = it_exchange_get_page_slug( $page );
    $permalinks = (boolean) get_option( 'permalink_structure' );
    $base       = trailingslashit( get_home_url() );

	// Account Slug
	if ( 'wordpress' == it_exchange_get_page_type( 'account' ) ) {
		$account_page = get_page( it_exchange_get_page_wpid( 'account' ) );
		$account_slug = $account_page->post_name;
	} else {
		$account_slug = it_exchange_get_page_slug( 'account' );
	}
	
	// Replace account value with name if user is logged in
	if ( $permalinks )
		$base = trailingslashit( $base . $account_slug );
	else
		$base = add_query_arg( array( $account_slug => 1 ), $base );

	$account_name = get_query_var( 'account' );
	if ( $account_name && '1' != $account_name && ( 'login' != $page && 'logout' != $page ) ) {
		if ( $permalinks ) {
			$base = trailingslashit( $base . $account_name );
		} else {
			$base = remove_query_arg( $account_slug, $base );
			$base = add_query_arg( array( $account_slug => $account_name ), $base );
		}
	}

	if ( $permalinks )
		return trailingslashit( $base . $slug );
	else
		return add_query_arg( $slug, '', $base );
}

/**
 * Adds memberships to iThemes Exchange's pages API for:
 *  protected pages
 *  profile pages
 *  account based pages
 *
 * @since 1.0.0
 *
 * @param string page
 * @return array
*/
function it_exchange_membership_addon_pages( $pages ) {
	$pages[] = 'memberships';
	return $pages;	
}
add_filter( 'it_exchange_pages_to_protect', 'it_exchange_membership_addon_pages' );
add_filter( 'it_exchange_profile_pages', 'it_exchange_membership_addon_pages' );
add_filter( 'it_exchange_account_based_pages', 'it_exchange_membership_addon_pages' );

/**
 * Adds memberships URLs to customer's menus in the iThemes Exchange account pages
 *
 * @since 1.0.0
 *
 * @param string $nav Current nav HTML
 * @param object $customer current Customer
 * @return array
*/
function it_exchange_membership_addon_append_to_customer_menu_loop( $nav, $customer ) {
	$memberships = it_exchange_get_session_data( 'parent_access' );
	$page_slug = 'memberships';
	$permalinks = (bool)get_option( 'permalink_structure' );
		
	if ( !empty( $memberships ) ) {
		foreach ( $memberships as $membership_id ) {
			if ( !empty( $membership_id ) ) {				
				$membership_post = get_post( $membership_id );
				if ( !empty( $membership_post ) ) {
					$membership_slug = $membership_post->post_name;
					
					$query_var = get_query_var( 'memberships' );
					
					$class = !empty( $query_var ) && $query_var == $membership_slug ? ' class="current"' : '';
					
					if ( $permalinks )
						$url = it_exchange_get_page_url( $page_slug ) . $membership_slug;
					else
						$url = it_exchange_get_page_url( $page_slug ) . '=' . $membership_slug;
						
					$nav .= '<li' . $class . '><a href="' . $url . '">' . get_the_title( $membership_id ) . '</a></li>';
				}
			}
		}
	}
	
	return $nav;
}
add_filter( 'it_exchange_after_customer_menu_loop', 'it_exchange_membership_addon_append_to_customer_menu_loop', 10, 2 );

/**
 * Adds memberships URLs to customer's menus in the iThemes Exchange account pages
 *
 * @since 1.0.0
 *
 * @param string $product_name Product Name
 * @param object $product_obj iThemes Exchange Product Object
 * @return string $product_name Product Name
*/
function it_exchange_membership_addon_email_notification_order_table_product_name( $product_name, $product_obj ) {
	if ( it_exchange_product_has_feature( $product_obj['product_id'], 'membership-content-access-rules' ) ) {
	
		$page_slug = 'memberships';
		$permalinks = (bool)get_option( 'permalink_structure' );
		
		$membership_post = get_post( $product_obj['product_id'] );
		if ( !empty( $membership_post ) ) {
			$membership_slug = $membership_post->post_name;
			
			if ( $permalinks )
				$url = it_exchange_get_page_url( $page_slug ) . $membership_slug;
			else
				$url = it_exchange_get_page_url( $page_slug ) . '=' . $membership_slug;
				
			$product_name .= '<p><small>&nbsp;&nbsp;<a href="' . $url . '">' . __( 'View available content', 'it-l10n-exchange-addon-membership' ) . '</a><p></small>';
		}
	}
	return $product_name;
}
add_filter( 'it_exchange_email_notification_order_table_product_name', 'it_exchange_membership_addon_email_notification_order_table_product_name', 10, 2 );

/**
 * Replaces base-price with upgrade price
 *
 * @since 1.0.0
 *
 * @param string $db_base_price default Base Price
 * @param array $product iThemes Exchange Product
 * @param bool $format Whether or not the price should be formatted 
 * @return string $db_base_price modified, if upgrade price has been set for product
*/
function it_exchange_get_credit_pricing_cart_product_base_price( $db_base_price, $product, $format ) {
	$updown_details = it_exchange_get_session_data( 'updowngrade_details' );
	
	if ( !empty( $updown_details[$product['product_id']] ) && 'credit' == $updown_details[$product['product_id']]['upgrade_type'] ) {
		$db_base_price = it_exchange_convert_from_database_number( it_exchange_convert_to_database_number( $db_base_price ) );
		$db_base_price = $db_base_price - $updown_details[$product['product_id']]['credit'];

		if ( $format ) {
			$db_base_price = it_exchange_format_price( $db_base_price );
		}
	}
		
	return $db_base_price;
}
add_filter( 'it_exchange_get_cart_product_base_price', 'it_exchange_get_credit_pricing_cart_product_base_price', 10, 3 );
