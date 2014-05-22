<?php
/**
 * Functions / hooks only needed in the admin
 * @package IT_Exchange
 * @since 0.4.0
*/

/**
 * Enqueues CSS / JS on add / edit page
 *
 * @since 0.4.0
 *
 * @return void
*/
function it_exchange_basic_coupons_enqueue_js_css() {
	$screen         = get_current_screen();
	$current_filter = current_filter();

	// Abort if screen wasn't found
	if ( empty( $screen ) )
		return;

	// Abort if not adding, editing or on the coupons list screen.
	if ( 'exchange_page_it-exchange-edit-basic-coupon' == $screen->base || 'exchange_page_it-exchange-add-basic-coupon' == $screen->base || 'edit-it_exchange_coupon' == $screen->id ) {
		// Enqueue JS / CSS based on current filter
		if ( 'admin_print_scripts' == $current_filter ) {
			// JS
			$deps = array( 'jquery', 'jquery-ui-tooltip', 'jquery-ui-datepicker' );
			wp_enqueue_script( 'it-exchange-add-edit-coupon', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/js/add-edit-coupon.js', $deps );
		} else if ( 'admin_print_styles' == $current_filter ) {
			// CSS
			$deps = array( 'jquery-ui-tooltip', 'jquery-ui-datepicker' );
			wp_enqueue_style( 'it-exchange-add-edit-coupon', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/css/add-edit-coupon.css' );
		}
	}
}
add_action( 'admin_print_styles', 'it_exchange_basic_coupons_enqueue_js_css' );
add_action( 'admin_print_scripts', 'it_exchange_basic_coupons_enqueue_js_css' );

/**
 * Adds Basic Coupons post type to list of post type to remove the quick edit
 *
 * @since 0.4.5
 *
 * @return array list of post types
*/
function it_exchange_remove_quick_edit_from_basic_coupons( $post_types ) {
	$post_types[] = 'it_exchange_coupon';
	return $post_types;
}
add_filter( 'it_exchange_remove_quick_edit_from_post_types', 'it_exchange_remove_quick_edit_from_basic_coupons', 10 );

/**
 * Saves a coupon
 *
 * @since 0.4.0
 *
 * @return void
*/
function it_exchange_basic_coupons_save_coupon() {
	if ( empty( $_POST['it-exchange-basic-coupons-add-edit-coupon'] ) )
		return;

	// Redirect to All coupons if cancel button was submited
	if ( ! empty( $_POST['cancel'] ) ) {
		wp_safe_redirect( add_query_arg( array( 'post_type' => 'it_exchange_coupon' ), 'edit.php' ) );
		die();
	}

	$nonce = empty( $_POST['_wpnonce'] ) ? false : $_POST['_wpnonce'];
	if ( ! wp_verify_nonce( $nonce, 'it-exchange-basic-coupons-add-edit-coupon' ) )
		return;

	$data = ITForm::get_post_data();

	if ( ! it_exchange_basic_coupons_data_is_valid() )
		return;

	// Remove hidden field
	unset( $data['add-edit-coupon'] );

	// Convert name to post_title
	$data['post_title'] = $data['name'];
	unset( $data['name'] );

	// Update message or added message
	$msg = empty( $data['ID'] ) ? 'added' : 'updated';

	// Convert code, amount-number, amount-type, start-date, end-date to meta
	$data['post_meta']['_it-basic-code']           = $data['code'];
	$data['post_meta']['_it-basic-amount-number']  = it_exchange_convert_to_database_number( $data['amount-number'] );
	$data['post_meta']['_it-basic-amount-type']    = $data['amount-type'];
	$data['post_meta']['_it-basic-start-date']     = $data['start-date'];
	$data['post_meta']['_it-basic-end-date']       = $data['end-date'];
	$data['post_meta']['_it-basic-limit-quantity'] = $data['limit-quantity'];
	$data['post_meta']['_it-basic-quantity']       = $data['quantity'];
	$data['post_meta']['_it-basic-limit-product']  = $data['limit-product'];
	$data['post_meta']['_it-basic-product-id']     = $data['product-id'];
	unset( $data['code'] );
	unset( $data['amount-number'] );
	unset( $data['amount-type'] );
	unset( $data['start-date'] );
	unset( $data['end-date'] );
	unset( $data['limit-quantity'] );
	unset( $data['quantity'] );
	unset( $data['limit-product'] );
	unset( $data['product-id'] );

	if ( $post_id = it_exchange_add_coupon( $data ) ) {
		wp_safe_redirect( add_query_arg( array( 'post_type' => 'it_exchange_coupon' ), get_admin_url() . 'edit.php' ) );
	}
}
add_action( 'admin_init', 'it_exchange_basic_coupons_save_coupon' );

/**
 * Vaidates coupon data
 *
 * @since 0.4.0
 *
 * @return boolean
*/
function it_exchange_basic_coupons_data_is_valid() {
	$data = ITForm::get_post_data();
	if ( empty( $data['name'] ) )
		it_exchange_add_message( 'error', __( 'Coupon Name cannot be left empty', 'it-l10n-ithemes-exchange' ) );
	if ( empty( $data['code'] ) )
		it_exchange_add_message( 'error', __( 'Coupon Code cannot be left empty', 'it-l10n-ithemes-exchange' ) );
	if ( empty( $data['amount-number'] ) )
		it_exchange_add_message( 'error', __( 'Coupon Discount cannot be left empty', 'it-l10n-ithemes-exchange' ) );
	if ( ! is_numeric( $data['amount-number'] ) || trim( $data['amount-number'] ) < 1 )
		it_exchange_add_message( 'error', __( 'Coupon Discount must be a postive number', 'it-l10n-ithemes-exchange' ) );
	if ( ! empty( $data['limit-quantity'] ) && ! is_numeric( $data['quantity'] ) )
		it_exchange_add_message( 'error', __( 'Available Coupons must be a number', 'it-l10n-ithemes-exchange' ) );
	if ( ! empty( $data['limit-product'] ) && ! it_exchange_get_product( $data['product-id'] ) )
		it_exchange_add_message( 'error', __( 'Please select a product.', 'it-l10n-ithemes-exchange' ) );

	return ! it_exchange_has_messages( 'error' );
}

/**
 * This adds a menu item to the Exchange menu pointing to the WP All [post_type] table
 *
 * @since 0.4.0
 *
 * @return void
*/
function it_exchange_basic_coupons_add_menu_item() {
	if ( ! empty( $_GET['page'] ) && 'it-exchange-add-basic-coupon' == $_GET['page'] ) {
		$slug = 'it-exchange-add-basic-coupon';
		$func = 'it_exchange_basic_coupons_print_add_edit_coupon_screen';
		add_submenu_page( 'it-exchange', __( 'Add Coupon', 'it-l10n-ithemes-exchange' ), __( 'Add Coupon', 'it-l10n-ithemes-exchange' ), 'update_plugins', $slug, $func );
	} else if ( ! empty( $_GET['page'] ) && 'it-exchange-edit-basic-coupon' == $_GET['page'] ) {
		$slug = 'it-exchange-edit-basic-coupon';
		$func = 'it_exchange_basic_coupons_print_add_edit_coupon_screen';
		add_submenu_page( 'it-exchange', __( 'Edit Coupon', 'it-l10n-ithemes-exchange' ), __( 'Edit Coupon', 'it-l10n-ithemes-exchange' ), 'update_plugins', $slug, $func );
	}
	$url = add_query_arg( array( 'post_type' => 'it_exchange_coupon' ), 'edit.php' );
	add_submenu_page( 'it-exchange', __( 'Coupons', 'it-l10n-ithemes-exchange' ), __( 'Coupons', 'it-l10n-ithemes-exchange' ), 'update_plugins', $url );
}
add_action( 'admin_menu', 'it_exchange_basic_coupons_add_menu_item' );

/**
 * Redirects admin users away from core add / edit post type screens for coupons to our custom ones.
 *
 * @since 0.4.0
 *
 * @return void
*/
function it_exchange_basic_coupons_redirect_core_add_edit_screens() {
	$pagenow   = empty( $GLOBALS['pagenow'] ) ? false : $GLOBALS['pagenow'];
	$post_type = empty( $_GET['post_type'] ) ? false : $_GET['post_type'];
	$post_id   = empty( $_GET['post'] ) ? false : $_GET['post'];
	$action    = empty( $_GET['action'] ) ? false : $_GET['action'];

	if ( ! $pagenow || ( 'post-new.php' != $pagenow && 'post.php' != $pagenow ) )
		return;

	// Redirect for add new screen
	if ( 'post-new.php' == $pagenow && 'it_exchange_coupon' == $post_type ) {
		wp_safe_redirect( add_query_arg( array( 'page' => 'it-exchange-add-basic-coupon' ), get_admin_url() . 'admin.php' ) );
		die();
	}

	// Redirect for edit screen
	if ( in_array( $action, array( 'delete', 'trash', 'untrash' ) ) )
		return;

	$coupon = new IT_Exchange_Coupon( $post_id );
	if ( 'post.php' == $pagenow ) {
		if ( $post_id && 'it_exchange_coupon' == $coupon->post_type ) {
			wp_safe_redirect( add_query_arg( array( 'page' => 'it-exchange-edit-basic-coupon', 'post' => $post_id ), get_admin_url() . 'admin.php' ) );
			die();
		}
	}
}
add_action( 'admin_init', 'it_exchange_basic_coupons_redirect_core_add_edit_screens' );

/**
 * Prints the add coupon screen
 *
 * @since 0.4.0
 *
 * @return void;
*/
function it_exchange_basic_coupons_print_add_edit_coupon_screen() {
	// Setup add / edit variables
	$post_id = empty( $_GET['post'] ) ? false : $_GET['post'];
	$heading = $post_id ? __( 'Edit Coupon', 'it-l10n-ithemes-exchange' ) : __( 'Add Coupon', 'it-l10n-ithemes-exchange' );
	$form_action = $post_id ? add_query_arg( array( 'page' => 'it-exchange-edit-basic-coupon', 'post' => $post_id ), get_admin_url() . 'admin.php' ) : add_query_arg( array( 'page' => 'it-exchange-add-basic-coupon' ), get_admin_url() . 'admin.php' );

	// Set form values
	if ( $post_id ) {
		$coupon = new IT_Exchange_Coupon( $post_id );

		$amount = it_exchange_convert_from_database_number( $coupon->amount_number );

		if ( 'amount' == $coupon->amount_type )
			$amount = it_exchange_format_price( $amount, false );

		$values['name']           = $coupon->post_title;
		$values['code']           = $coupon->code;
		$values['amount-number']  = $amount;
		$values['amount-type']    = $coupon->amount_type;
		$values['start-date']     = $coupon->start_date;
		$values['end-date']       = $coupon->end_date;
		$values['limit-quantity'] = $coupon->limit_quantity;
		$values['quantity']       = $coupon->quantity;
		$values['limit-product']  = $coupon->limit_product;
		$values['product-id']     = $coupon->product_id;
	}

	$errors = it_exchange_get_messages( 'error' );
	if ( ! empty( $errors ) ) {
		foreach( $errors as $error ) {
			ITUtility::show_error_message( $error );
		}
	} else if ( ! empty( $_GET['added'] ) ) {
		ITUtility::show_status_message( __( 'Coupon Added', 'it-l10n-ithemes-exchange' ) );
	} else if ( ! empty( $_GET['updated'] ) ) {
		ITUtility::show_status_message( __( 'Coupon Updated', 'it-l10n-ithemes-exchange' ) );
	}

	$form_values  = empty( $values ) ? ITForm::get_post_data() : $values;
	$form_values  = ! empty( $errors ) ? ITForm::get_post_data() : $form_values;
	$form         = new ITForm( $form_values, array( 'prefix' => 'it-exchange-basic-coupons' ) );
	$form_options = array(
		'id'      => apply_filters( 'it-exchange-basic-coupons_form_id', 'it-exchange-basic-coupons' ),
		'enctype' => apply_filters( 'it-exchange-basic-coupons_enctype', false ),
		'action'  => $form_action,
	);
	?>
	<div class="wrap">
		<?php
		ITUtility::screen_icon( 'it-exchange-coupons' );
		echo '<h2>' . $heading . '</h2>';
		$form->start_form( $form_options, 'it-exchange-basic-coupons-add-edit-coupon' );

		if ( $post_id )
			$form->add_hidden( 'ID', $post_id );
		$form->add_hidden( 'add-edit-coupon', true );
		?>
		<div class="it-exchange-add-basic-coupon">
			<div class="fields">
				<div class="field">
					<label for="name"><?php _e( 'Name', 'it-l10n-ithemes-exchange' ); ?> <span class="tip" title="<?php _e( 'What do you want to call this coupon? This is just for your reference.', 'it-l10n-ithemes-exchange' ); ?>">i</span></label>
					<?php $form->add_text_box( 'name' ); ?>
				</div>
				<div class="field coupon-code">
					<label for="code"><?php _e( 'Code', 'it-l10n-ithemes-exchange' ); ?> <span class="tip" title="<?php _e( 'Try something cool like EXCHANGERULEZ5000! Or click the dice to generate a random code.', 'it-l10n-ithemes-exchange' ); ?>">i</span></label>
					<?php $form->add_text_box( 'code', array( 'class' => 'emptycode' ) ); ?>
					<a href class="dice" title="Generate a random code."><img src="<?php echo esc_attr( ITUtility::get_url_from_file( dirname( __FILE__ ) ) ); ?>/images/dice-t.png" /></a>
				</div>

				<div class="field amount">
					<label for="amount-number"><?php _e( 'Amount', 'it-l10n-ithemes-exchange' ); ?></label>
					<?php $form->add_text_box( 'amount-number', array( 'type' => 'number' ) ); ?>
					<?php
					$settings = it_exchange_get_option( 'settings_general' );
					$currency = $settings['default-currency'];
					$symbol   = it_exchange_get_currency_symbol( $currency );
					?>
					<?php $form->add_drop_down( 'amount-type', array( '%' => __( '% Percent', 'it-l10n-ithemes-exchange' ), 'amount' => $symbol . ' ' . $currency ) ); ?>
				</div>

				<div class="field date" data-alert="<?php _e( 'Please select an end date that is after the start date.', 'it-l10n-ithemes-exchange' ); ?>">
					<div class="start-date">
						<label for="start-date"><?php _e( 'Start Date', 'it-l10n-ithemes-exchange' ); ?></label>
						<?php $form->add_text_box( 'start-date', array( 'class' => 'datepicker', 'data-append' => 'end-date' ) ); ?>
					</div>
					<div class="end-date">
						<label for="end-date"><?php _e( 'End Date', 'it-l10n-ithemes-exchange' ); ?></label>
						<?php $form->add_text_box( 'end-date', array( 'class' => 'datepicker', 'data-append' => 'start-date' ) ); ?>
					</div>
				</div>

				<div class="field limit-quantity">
					<?php $form->add_check_box( 'limit-quantity' ); ?>
					<label for="limit-quantity">
						<?php _e( 'Limit number of coupons', 'it-l10n-ithemes-exchange' ); ?>
						<span class="tip" title="<?php esc_attr_e( __( 'Check to limit the number of times this coupon can be used', 'it-l10n-ithemes-exchange' ) ); ?>">i</span>
					</label>
				</div>

				<div class="field quantity">
					<?php $form->add_text_box( 'quantity', array( 'type' => 'number' ) ); ?>
					<span class="tip" title="<?php _e( 'How many times can this coupon be used before it is disabled?', 'it-l10n-ithemes-exchange' ); ?>">i</span>
				</div>

				<div class="field limit-product">
					<?php $form->add_check_box( 'limit-product' ); ?>
					<label for="limit-product">
						<?php _e( 'Limit to a specific product', 'it-l10n-ithemes-exchange' ); ?>
						<span class="tip" title="<?php esc_attr_e( __( 'Check to limit the coupon discount to a specific product price, not the cart total', 'it-l10n-ithemes-exchange' ) ); ?>">i</span>
					</label>
				</div>

				<div class="field product-id">
					<?php 
					$product_options = array( 0 => __( 'Select a product', 'it-l10n-ithemes-exchange' ) );
					$products        = it_exchange_get_products( array( 'show_hidden' => true, 'posts_per_page' => -1 ) );
					foreach( (array) $products as $id => $product ) {
						$product_options[$product->ID] = $product->post_title;
					}
					?>
					<?php $form->add_drop_down( 'product-id', $product_options ); ?>
					<span class="tip" title="<?php _e( 'Select a product to use with this coupon.', 'it-l10n-ithemes-exchange' ); ?>">i</span>
				</div>

				<div class="field">
					<?php $form->add_submit( 'cancel', array( 'class' => 'button-large button', 'value' => __( 'Cancel', 'it-l10n-ithemes-exchange' ) ) ); ?>
					<?php $form->add_submit( 'submit', array( 'class' => 'button-large button-primary button', 'value' => __( 'Save', 'it-l10n-ithemes-exchange' ) ) ); ?>
				</div>
			</div>
		</div>
		<?php
		$form->end_form();
		?>
	</div>
	<?php
}

/**
 * Remove Custom add coupon and edit coupon links from submenu
 *
 * @since 0.4.0
 *
 * @return void
*/
function it_exchange_basic_coupons_remove_submenu_links() {
	if ( ! empty( $GLOBALS['submenu']['it-exchange'] ) ) {
		foreach( $GLOBALS['submenu']['it-exchange'] as $key => $sub ) {
			if ( 'it-exchange-add-basic-coupon' == $sub[2] || 'it-exchange-edit-basic-coupon' == $sub[2] ) {
				// Remove the extra coupons submenu item
				unset( $GLOBALS['submenu']['it-exchange'][$key] );
				// Mark the primary coupons submenu item as current
				$GLOBALS['submenu_file'] = 'edit.php?post_type=it_exchange_coupon';
			}
		}
	}
}
add_action( 'admin_head', 'it_exchange_basic_coupons_remove_submenu_links' );

/**
 * Adds the coupon specific columns to the View All Coupons table
 *
 * @since 0.4.0
 * @param array $existing  exisiting columns array
 * @return array  modified columns array
*/
function it_exchange_basic_coupons_product_columns( $existing ) {
	$columns['cb']    = '<input type="checkbox" />';
	$columns['title'] = __( 'Title', 'it-l10n-ithemes-exchange' );
	$columns['it_exchange_coupon_code']       = __( 'Coupon Code', 'it-l10n-ithemes-exchange' );
	$columns['it_exchange_coupon_discount']   = __( 'Discount', 'it-l10n-ithemes-exchange' );
	$columns['it_exchange_coupon_start_date'] = __( 'Start Date', 'it-l10n-ithemes-exchange' );
	$columns['it_exchange_coupon_end_date']   = __( 'End Date', 'it-l10n-ithemes-exchange' );
	$columns['it_exchange_coupon_quantity']   = __( 'Available Coupons', 'it-l10n-ithemes-exchange' );
	$columns['it_exchange_coupon_product_id'] = __( 'Product', 'it-l10n-ithemes-exchange' );

	return $columns;
}
add_filter( 'manage_edit-it_exchange_coupon_columns', 'it_exchange_basic_coupons_product_columns', 999 );

/**
 * Makes the custom columns added above sortable
 *
 * @since 0.4.0
 * @param array $sortables  existing sortable columns
 * @return array  modified sortable columnns
*/
function it_exchange_basic_coupons_sortable_columns( $sortables ) {
	$sortables['it_exchange_coupon_code']       = 'it-exchange-coupon-code';
	$sortables['it_exchange_coupon_discount']   = 'it-exchange-coupon-discount';
	$sortables['it_exchange_coupon_start_date'] = 'it-exchange-coupon-start-date';
	$sortables['it_exchange_coupon_end_date']   = 'it-exchange-coupon-end-date';
	$sortables['it_exchange_coupon_quantity']   = 'it-exchange-coupon-quantity';
	$sortables['it_exchange_coupon_product_id'] = 'it-exchange-coupon-product-id';

	return $sortables;
}
add_filter( 'manage_edit-it_exchange_coupon_sortable_columns', 'it_exchange_basic_coupons_sortable_columns' );

/**
 * Adds the data to the custom columns
 *
 * @since 0.4.0
 * @param string $column  column title
 * @return void
*/
function it_exchange_basic_coupons_custom_column_info( $column ) {
	global $post;

	$coupon = it_exchange_get_coupon( $post );

	switch( $column ) {
		case 'it_exchange_coupon_code':
			esc_attr_e( $coupon->code );
			break;
		case 'it_exchange_coupon_discount':
			echo esc_attr( it_exchange_get_coupon_discount_label( $coupon ) );
			break;
		case 'it_exchange_coupon_start_date':
			esc_attr_e( $coupon->start_date );
			break;
		case 'it_exchange_coupon_end_date':
			esc_attr_e( $coupon->end_date );
			break;
		case 'it_exchange_coupon_quantity':
			$quantity_label = ( empty( $coupon->limit_quantity ) ) ? __( 'Unlimited', 'it-l10n-ithemes-exchange' ) : $coupon->quantity;
			esc_attr_e( $quantity_label );
			break;
		case 'it_exchange_coupon_product_id':
			$product_name = ( empty( $coupon->limit_product ) ) ? __( 'All Products', 'it-l10n-ithemes-exchange' ) : it_exchange_get_product_feature( $coupon->product_id, 'title' );
			esc_attr_e( $product_name );
			break;
	}
}
add_filter( 'manage_it_exchange_coupon_posts_custom_column', 'it_exchange_basic_coupons_custom_column_info' );

/**
 * Modify sort of coupons in edit.php for custom columns
 *
 * @since 0.4.0F
 *
 * @param string $request original request
 */
function it_exchange_basic_coupons_modify_wp_query_request_on_edit_php( $request ) {
	global $hook_suffix;

	if ( 'edit.php' === $hook_suffix ) {
		if ( 'it_exchange_coupon' === $request['post_type'] && isset( $request['orderby'] ) ) {
			switch( $request['orderby'] ) {
				case 'it-exchange-coupon-code' :
					$request['orderby']  = 'meta_value';
					$request['meta_key'] = '_it-basic-code';
					break;
				case 'it-exchange-coupon-discount':
					$request['orderby']  = 'meta_value_num';
					$request['meta_key'] = '_it-basic-amount-number';
					break;
				case 'it-exchange-coupon-start-date':
					$request['orderby']  = 'meta_value_date';
					$request['meta_key'] = '_it-basic-start-date';
					break;
				case 'it-exchange-coupon-end-date':
					$request['orderby']  = 'meta_value_date';
					$request['meta_key'] = '_it-basic-end-date';
					break;
				case 'it-exchange-coupon-quantity':
					$request['orderby']  = 'meta_value_num';
					$request['meta_key'] = '_it-basic-quantity';
					break;
				case 'it-exchange-coupon-product-id':
					$request['orderby']  = 'meta_value_num';
					$request['meta_key'] = '_it-basic-product-id';
					break;
			}
		}
	}

	return $request;
}
add_filter( 'request', 'it_exchange_basic_coupons_modify_wp_query_request_on_edit_php' );

/**
 * Register our pages as an exchange pages so that exchange CSS class is applied to admin body
 *
 * @since 0.4.17
 *
 * @param array $pages existing pages
 * @return array
*/
function it_exchange_basic_coupons_register_exchange_admin_page( $pages ) {
	$pages[] = 'it-exchange-add-basic-coupon';
	return $pages;
}
add_filter( 'it_exchange_admin_pages', 'it_exchange_basic_coupons_register_exchange_admin_page' );
