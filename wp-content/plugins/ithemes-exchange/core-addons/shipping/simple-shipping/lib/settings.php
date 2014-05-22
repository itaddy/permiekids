<?php
/**
 * Callback function for add-on settings
 *
 * We are using this differently than most add-ons. We want the gear
 * to appear on the add-ons screen so we are registering the callback.
 * It will be intercepted though if the user clicks on it and redirected to
 * The Exchange settings --> shipping tab.
 *
 * @since 1.4.0
 *
 * @return void
*/
function it_exchange_simple_shipping_settings_callback() {
	// Store Owners should never arrive here. Add a link just in case the do somehow
	?>
	<div class="wrap">
		<?php ITUtility::screen_icon( 'it-exchange' ); ?>
		<h2><?php _e( 'Shipping', 'it-l10n-ithemes-exchange' ); ?></h2>
		<?php
		$url = add_query_arg( array( 'page' => 'it-exchange-settings', 'tab' => 'shipping' ), esc_url( admin_url( 'admin.php' ) ) );
		?><p><?php printf( __( 'Settings are located in the %sShipping tab%s on the Exchange Settings page.', 'it-l10n-ithemes-exchange' ), '<a href="' . $url . '">', '</a>' ); ?></p>
	</div>
	<?php
}

/**
 * Redirects to General Settings -> Shipping -> Simple Shipping from add-on settings page.
 *
 * @since 1.4.0
 *
 * return void
*/
function it_exchange_simple_shipping_settings_redirect() {
	$page  = ! empty( $_GET['page'] ) && 'it-exchange-addons' == $_GET['page'];
	$addon = ! empty( $_GET['add-on-settings'] ) && 'simple-shipping' == $_GET['add-on-settings'];

	if ( $page && $addon ) {
		wp_redirect( add_query_arg( array( 'page' => 'it-exchange-settings', 'tab' => 'shipping', 'provider' => 'simple-shipping' ), admin_url( 'admin.php' ) ) );
		die();
	}
}
add_action( 'admin_init', 'it_exchange_simple_shipping_settings_redirect' );
