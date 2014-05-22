<?php
/*
 * Plugin Name: iThemes Exchange - Recurring Payments Add-on
 * Version: 1.0.17
 * Description: Adds the recurring payments abilities to iThemes Exchange
 * Plugin URI: http://ithemes.com/exchange/recurring-payments/
 * Author: iThemes
 * Author URI: http://ithemes.com
 * iThemes Package: exchange-addon-recurring-payments
 
 * Installation:
 * 1. Download and unzip the latest release zip file.
 * 2. If you use the WordPress plugin uploader to install this plugin skip to step 4.
 * 3. Upload the entire plugin directory to your `/wp-content/plugins/` directory.
 * 4. Activate the plugin through the 'Plugins' menu in WordPress Administration.
 *
*/

define( 'ITE_RECURRING_PAYMENTS_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

/**
 * This registers our plugin as a recurring payments addon
 *
 * @since 1.0.0
 *
 * @return void
*/
function it_exchange_register_recurring_payments_addon() {
	$options = array(
		'name'              => __( 'Recurring Payments', 'it-l10n-exchange-addon-membership' ),
		'description'       => __( 'This add-on turns on recurring payments for supporting payment gateways.', 'it-l10n-exchange-addon-membership' ),
		'author'            => 'iThemes',
		'author_url'        => 'http://ithemes.com/exchange/recurring-payments/',
		'icon'              => ITUtility::get_url_from_file( dirname( __FILE__ ) . '/lib/images/recurring50px.png' ),
		'file'              => dirname( __FILE__ ) . '/init.php',
		'category'          => 'other',
		'settings-callback' => 'it_exchange_recurring_payments_addon_settings_callback',	
	);
	it_exchange_register_addon( 'recurring-payments', $options );
}
add_action( 'it_exchange_register_addons', 'it_exchange_register_recurring_payments_addon' );

/**
 * Loads the translation data for WordPress
 *
 * @uses load_plugin_textdomain()
 * @since 1.0.0
 * @return void
*/
function it_exchange_recurring_payments_set_textdomain() {
	load_plugin_textdomain( 'it-l10n-exchange-addon-membership', false, dirname( plugin_basename( __FILE__  ) ) . '/lang/' );
}
add_action( 'plugins_loaded', 'it_exchange_recurring_payments_set_textdomain' );

/**
 * Registers Plugin with iThemes updater class
 *
 * @since 1.0.0
 *
 * @param object $updater ithemes updater object
 * @return void
*/
function ithemes_exchange_addon_recurring_payments_updater_register( $updater ) { 
	    $updater->register( 'exchange-addon-recurring-payments', __FILE__ );
}
add_action( 'ithemes_updater_register', 'ithemes_exchange_addon_recurring_payments_updater_register' );
require( dirname( __FILE__ ) . '/lib/updater/load.php' );

/**
 * On activation, set a time, frequency and name of an action hook to be scheduled.
 *
 * @since 1.0.0
 */
function it_exchange_recurring_payments_activation() {
	wp_schedule_event( strtotime( 'Tomorrow 4AM' ), 'daily', 'it_exchange_recurring_payments_daily_schedule' );
}
register_activation_hook( __FILE__, 'it_exchange_recurring_payments_activation' );

/**
 * On deactivation, remove all functions from the scheduled action hook.
 *
 * @since 1.0.0
 */
function it_exchange_recurring_payments_deactivation() {
	wp_clear_scheduled_hook( 'it_exchange_recurring_payments_daily_schedule' );
}
register_deactivation_hook( __FILE__, 'it_exchange_recurring_payments_deactivation' );
