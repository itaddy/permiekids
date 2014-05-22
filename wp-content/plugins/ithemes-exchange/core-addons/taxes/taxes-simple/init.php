<?php
/**
 * This file inits our Simple Taxes add-on.
 * It is only included when the add-on is enabled.
 * @package IT_Exchange
 * @since 1.0.0
*/

include( dirname( __FILE__ ) . '/functions.php' );

/**
 * Prints the Settings page for Simple Taxes
 *
 *
 * @since 1.0.0
 *
 * @return void
*/
function it_exchange_taxes_simple_settings_callback() {
	$settings = it_exchange_get_option( 'addon_taxes_simple', true );
	$form_values  = ! it_exchange_has_messages( 'error' ) ? $settings : ITForm::get_post_data();
	$form_options = array(
		'id'      => 'it-exchange-add-on-taxes-simple-settings',
		'enctype' => false,
		'action'  => 'admin.php?page=it-exchange-addons&add-on-settings=taxes-simple',
	);
	$form = new ITForm( $form_values, array( 'prefix' => 'it-exchange-add-on-taxes_simple' ) );

	if ( it_exchange_has_messages( 'notice' ) ) {
		foreach( (array) it_exchange_get_messages( 'notice' ) as $message ) {
			ITUtility::show_status_message( $message );
		}
	}
	if ( it_exchange_has_messages( 'error' ) ) {
		foreach( (array) it_exchange_get_messages( 'error' ) as $message ) {
			ITUtility::show_error_message( $message );
		}
	}
	?>
	<div class="wrap">
		<?php ITUtility::screen_icon( 'it-exchange' ); ?>
		<h2><?php _e( 'Simple Taxes', 'it-l10n-ithemes-exchange' ); ?></h2>

		<?php do_action( 'it_exchange_addon_settings_page_top' ); ?>
		<?php $form->start_form( $form_options, 'it-exchange-taxes-simple-settings' ); ?>
			<label for="default-tax-rate"><?php _e( 'Default Tax Rate', 'it-l10n-ithemes-exchange' ); ?></label>
			<?php $form->add_text_box( 'default-tax-rate' ); ?> %</br />
			<?php $form->add_check_box( 'calculate-after-discounts' ); ?>
			<label for="calculate-after-discounts"><?php _e( 'Calculate taxes after discounts are applied?', 'it-l10n-ithemes-exchange' ); ?></label>
			<h3><?php _e( 'Labels', 'it-l10n-ithemes-exchange' ); ?></h3>
			<label for="tax-label-singular"><?php _e( 'Tax:', 'it-l10n-ithemes-exchange' ); ?><br />
			<?php $form->add_text_box( 'tax-label-singular', array( 'class' => 'normal' ) ); ?></label>
			<label for="tax-label-plural"><?php _e( 'Taxes:', 'it-l10n-ithemes-exchange' ); ?><br />
			<?php $form->add_text_box( 'tax-label-plural', array( 'class' => 'normal' ) ); ?></label>
			<p class="submit">
				<?php $form->add_submit( 'submit', array( 'value' => __( 'Save Changes', 'it-l10n-ithemes-exchange' ), 'class' => 'button button-primary button-large' ) ); ?>
			</p>
		<?php $form->end_form(); ?>
	</div>
	<?php
}

/**
 * Set default settings
 *
 * @since 1.2.1
 *
 * @param array $defaults incoming from filter
 * @return array
*/
function it_exchange_addon_taxes_simple_default_settings ( $defaults ) {
	$defaults['tax-label-singular'] = __( 'Tax', 'it-l10n-ithemes-exchange' );
	$defaults['tax-label-plural']   = __( 'Taxes', 'it-l10n-ithemes-exchange' );
	return $defaults;
}
add_filter( 'it_storage_get_defaults_exchange_addon_taxes_simple', 'it_exchange_addon_taxes_simple_default_settings' );

/**
 * Save settings
 *
 * @since 1.0.0
 *
 * @return void
*/
function it_exchange_addon_save_taxes_simple_settings() {
	$defaults = it_exchange_get_option( 'addon_taxes_simple' );
	$new_values = wp_parse_args( ITForm::get_post_data(), $defaults );

	// Return if not on our page or POST isn't set.
	if ( empty( $_POST ) || empty( $_GET['add-on-settings'] ) || 'taxes-simple' != $_GET['add-on-settings'] )
		return;

	// Check nonce
	if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'it-exchange-taxes-simple-settings' ) ) {
		it_exchange_add_message( 'error', __( 'Error. Please try again', 'it-l10n-ithemes-exchange' ) );
		return;
	}

	// Validate data
	if ( ! is_numeric( $new_values['default-tax-rate'] ) ) {
		it_exchange_add_message( 'error', __( 'Default tax rate must be numeric', 'it-l10n-ithemes-exchange' ) );
	} else if ( empty( $new_values['tax-label-plural'] ) || empty( $new_values['tax-label-singular'] ) ) {
		it_exchange_add_message( 'error', __( 'Tax labels cannot be left empty', 'it-l10n-ithemes-exchange' ) );
	} else {
		it_exchange_save_option( 'addon_taxes_simple', $new_values );
		it_exchange_add_message( 'notice', __( 'Settings Saved', 'it-l10n-ithemes-exchange' ) );
	}
}
add_action( 'admin_init', 'it_exchange_addon_save_taxes_simple_settings' );

/**
 * Add Simple Taxes to the content-cart totals and content-checkout loop
 *
 * @since 1.0.0
 *
 * @param array $elements list of existing elements
 * @return array
*/
function it_exchange_addon_add_taxes_simple_to_template_totals_loops( $elements ) {
	$tax_options           = it_exchange_get_option( 'addon_taxes_simple' );
	$process_after_savings = ! empty( $tax_options['calculate-after-discounts'] );

	// Locate the discounts key in elements array (if it exists)
	$index = array_search( 'totals-savings', $elements );
	if ( false === $index )
		$index = -1;

	// Bump index by 1 if calculating tax after discounts
	if ( -1 != $index && $process_after_savings )
		$index++;

	array_splice( $elements, $index, 0, 'totals-taxes-simple' );
	return $elements;
}
add_filter( 'it_exchange_get_content_cart_totals_elements', 'it_exchange_addon_add_taxes_simple_to_template_totals_loops' );
add_filter( 'it_exchange_get_content_checkout_totals_elements', 'it_exchange_addon_add_taxes_simple_to_template_totals_loops' );

/**
 * Add Simple Taxes to the super-widget-checkout totals loop
 *
 * @since 1.0.0
 *
 * @param array $loops list of existing elements
 * @return array
*/
function it_exchange_addon_add_taxes_simple_to_sw_template_totals_loops( $loops ) {
	$tax_options           = it_exchange_get_option( 'addon_taxes_simple' );
	$process_after_savings = ! empty( $tax_options['calculate-after-discounts'] );

	// Locate the discounts key in elements array (if it exists)
	$index = array_search( 'discounts', $loops );
	if ( false === $index )
		$index = -1;

	// Bump index by 1 if calculating tax after discounts
	if ( -1 != $index && $process_after_savings )
		$index++;

	array_splice( $loops, $index, 0, 'taxes-simple' );
	return $loops;
}
add_filter( 'it_exchange_get_super-widget-checkout_after-cart-items_loops', 'it_exchange_addon_add_taxes_simple_to_sw_template_totals_loops' );

/**
 * Adds our templates directory to the list of directories
 * searched by Exchange
 *
 * @since 1.0.0
 *
 * @param array $template_path existing array of paths Exchange will look in for templates
 * @param array $template_names existing array of file names Exchange is looking for in $template_paths directories
 * @return array
*/
function it_exchange_addon_taxes_simple_register_templates( $template_paths, $template_names ) {
	// Bail if not looking for one of our templates
	$add_path = false;
	$templates = array(
		'content-cart/elements/totals-taxes-simple.php',
		'content-checkout/elements/totals-taxes-simple.php',
		'super-widget-checkout/loops/taxes-simple.php',
	);
	foreach( $templates as $template ) {
		if ( in_array( $template, (array) $template_names ) )
			$add_path = true;
	}
	if ( ! $add_path )
		return $template_paths;

	$template_paths[] = dirname( __FILE__ ) . '/templates';
	return $template_paths;
}
add_filter( 'it_exchange_possible_template_paths', 'it_exchange_addon_taxes_simple_register_templates', 10, 2 );

/**
 * Adjusts the cart total
 *
 * @since 1.0.0
 *
 * @param $total the total passed to us by Exchange.
 * @return
*/
function it_exchange_addon_taxes_simple_modify_total( $total ) {
	$taxes = it_exchange_addon_get_simple_taxes_for_cart( false );
	return $total + $taxes;
}
add_filter( 'it_exchange_get_cart_total', 'it_exchange_addon_taxes_simple_modify_total' );

/**
 * Enqueue css for settings page
 *
 * @since 1.1.0
 *
 * @return void
*/
function it_exchange_addon_taxes_simple_enqueue_admin_css() {
	$current_screen = get_current_screen();
	if ( ! empty( $current_screen->base ) && 'exchange_page_it-exchange-addons' == $current_screen->base && ! empty( $_GET['add-on-settings'] ) && 'taxes-simple' == $_GET['add-on-settings'] )
		wp_enqueue_style( 'it-exchange-addon-taxes-simple-settings', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/css/settings.css' );
}
add_action( 'admin_print_styles', 'it_exchange_addon_taxes_simple_enqueue_admin_css' );
