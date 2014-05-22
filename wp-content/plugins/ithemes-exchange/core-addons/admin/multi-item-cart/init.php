<?php
/**
 * This is basically a fancy setting masquerading as an addon.
 * @package IT_Exchange
 * @since 0.4.0
*/
// No settings. This is either enabled or disabled.

function it_exchange_register_multi_item_cart_pages() {

    // Cart
    $options = array(
        'slug'          => 'cart',
        'name'          => __( 'Shopping Cart', 'it-l10n-ithemes-exchange' ),
        'rewrite-rules' => array( 215, 'it_exchange_multi_item_cart_get_page_rewrites' ),
        'url'           => 'it_exchange_multi_item_cart_get_page_urls',
        'settings-name' => __( 'Customer Shopping Cart', 'it-l10n-ithemes-exchange' ),
        'type'          => 'exchange',
        'menu'          => true,
        'optional'      => true,
    );
    it_exchange_register_page( 'cart', $options );

    // Checkout
    $options = array(
        'slug'          => 'checkout',
        'name'          => __( 'Checkout', 'it-l10n-ithemes-exchange' ),
        'rewrite-rules' => array( 216, 'it_exchange_multi_item_cart_get_page_rewrites' ),
        'url'           => 'it_exchange_multi_item_cart_get_page_urls',
        'settings-name' => __( 'Customer Checkout', 'it-l10n-ithemes-exchange' ),
        'type'          => 'exchange',
        'menu'          => true,
        'optional'      => true,
    );
    it_exchange_register_page( 'checkout', $options );
}
add_action( 'it_libraries_loaded', 'it_exchange_register_multi_item_cart_pages', 11 );

/**
 * Returns rewrites for cart and checkout pages
 *
 * @since 0.4.4
 *
 * @param string page
 * @return array
*/
function it_exchange_multi_item_cart_get_page_rewrites( $page ) {
    $slug           = it_exchange_get_page_slug( $page );
    $store_slug     = it_exchange_get_page_slug( 'store' );
	$store_disabled = ( 'disabled' == it_exchange_get_page_type( 'store' ) );

	// Don't use store if its disabled
	$store_segment  = $store_disabled ? '' : $store_slug . '/';

	// If we're using WP, add the WP slug to rewrites and return.
	if ( 'wordpress' == it_exchange_get_page_type( 'store' ) ) {
		$store = get_page( it_exchange_get_page_wpid( 'store' ) );
		$page_slug = $store->post_name;
		return array( $page_slug . '/' . $slug => 'index.php?' . $slug . '=1', );
	}


	return array( $store_segment . $slug => 'index.php?' . $slug . '=1', );
}

/**
 * Returns URL for cart and checkout pages
 *
 * @since 0.4.4
 *
 * @param string page
 * @return array
*/
function it_exchange_multi_item_cart_get_page_urls( $page ) {
	// Get slugs
	$slug           = it_exchange_get_page_slug( $page );
	$store_slug     = it_exchange_get_page_slug( 'store' );
	$store_disabled = ( 'disabled' == it_exchange_get_page_type( 'store' ) );

	// Don't use store if its disabled
	$store_segment  = $store_disabled ? '/' : '/' . $store_slug . '/';

	// Set cart and page urls
	if ( (boolean) get_option( 'permalink_structure' ) ) {
		$cart_url     = trailingslashit( get_home_url() . $store_segment . $slug );
		$checkout_url = trailingslashit( get_home_url() . $store_segment . $slug );
	} else {
		$cart_url     = add_query_arg( $slug, 1, get_home_url() );
		$checkout_url = add_query_arg( $slug, 1, get_home_url() );
	}

	if ( 'cart' == $page )
		return $cart_url;
	else if ( 'checkout' == $page )
		return $checkout_url;
}

/**
 * Enables multi item carts
 * @since 0.4.0
*/
add_filter( 'it_exchange_multi_item_cart_allowed', '__return_true' );

/**
 * Settings page
 *
 * @since 1.8.1
 *
 * @return void
*/
function it_exchange_multi_item_cart_settings_callback() {
	$form_values  = it_exchange_get_option( 'addon_multi_item_cart', true );
	if ( ! isset( $form_values['show-continue-shopping-button'] ) )
		$form_values['show-continue-shopping-button'] = true;
	$form_options = array(
		'id'      => 'it-exchange-add-on-multi-item-cart-settings',
		'action'  => 'admin.php?page=it-exchange-addons&add-on-settings=multi-item-cart-option',
	);
	$form         = new ITForm( $form_values, array( 'prefix' => 'it-exchange-add-on-multi-item-cart' ) );

	?>
	<div class="wrap">
		<?php ITUtility::screen_icon( 'it-exchange' ); ?>
		<h2><?php _e( 'Multi-Item Cart Settings', 'it-l10n-ithemes-exchange' ); ?></h2>

		<?php do_action( 'it_exchange_multi_item_cart_settings_page_top' ); ?>
		<?php do_action( 'it_exchange_addon_settings_page_top' ); ?>
		<?php $form->start_form( $form_options, 'it-exchange-multi-item-cart-settings' ); ?>

		<?php
		do_action( 'it_exchange_digital_downloads_settings_form_top' );
		if ( ! empty( $form_values ) )
			foreach ( $form_values as $key => $var )
				$form->set_option( $key, $var );

		if ( ! empty( $_POST['__it-form-prefix'] ) && 'it-exchange-add-on-multi-item-cart' == $_POST['__it-form-prefix'] )
			ITUtility::show_status_message( __( 'Options Saved', 'it-l10n-ithemes-exchange' ) );
		?>
		<div class="it-exchange-addon-settings it-exchange-multi-item-cart-addon-settings">
			<p>
				<label for="show-continue-shopping-button">
				<?php $form->add_check_box( 'show-continue-shopping-button' ); ?>
				<?php _e( 'Show the Continue Shopping button on the cart page?', 'it-l10n-ithemes-exchange' ); ?>
				</label>
			</p>
		</div>
		<?php

		do_action( 'it_exchange_multi_item_cart_settings_form_bottom' );
		?>
		<p class="submit">
			<?php $form->add_submit( 'submit', array( 'value' => __( 'Save Changes', 'it-l10n-ithemes-exchange' ), 'class' => 'button button-primary button-large' ) ); ?>
		</p>
	<?php $form->end_form(); ?>
	<?php do_action( 'it_exchange_multi_item_cart_settings_page_bottom' ); ?>
	<?php do_action( 'it_exchange_addon_settings_page_bottom' ); ?>
</div>
<?php
}

/**
 * Saves the settings page values
 *
 * @since 1.8.1
 *
 * @return void
*/
function it_exchange_multi_item_cart_save_settings() {
	if ( empty( $_POST['__it-form-prefix'] ) || 'it-exchange-add-on-multi-item-cart' != $_POST['__it-form-prefix'] )
		return;

	$form_values = it_exchange_get_option( 'addon_multi_item_cart', true );
	$form_values['show-continue-shopping-button'] = ! empty( $_POST['it-exchange-add-on-multi-item-cart-show-continue-shopping-button'] );
	it_exchange_save_option( 'addon_multi_item_cart', $form_values );
}
add_action( 'admin_init', 'it_exchange_multi_item_cart_save_settings' );
