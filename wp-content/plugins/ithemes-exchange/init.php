<?php
/*
 * Plugin Name: iThemes Exchange
 * Version: 1.8.2
 * Text Domain: LION
 * Description: Easily sell your digital goods with iThemes Exchange, simple ecommerce for WordPress
 * Plugin URI: http://ithemes.com/exchange/
 * Author: iThemes
 * Author URI: http://ithemes.com

 * Installation:
 * 1. Download and unzip the latest release zip file.
 * 2. If you use the WordPress plugin uploader to install this plugin skip to step 4.
 * 3. Upload the entire plugin directory to your `/wp-content/plugins/` directory.
 * 4. Activate the plugin through the 'Plugins' menu in WordPress Administration.
 *
*/

/**
 * Exchange main class.
 *
 * @package IT_Exchange
 * @since 0.1.0
*/
class IT_Exchange {

	var $_version         = '1.8.2';
	var $_wp_minimum      = '3.5';
	var $_slug            = 'it-exchange';
	var $_name            = 'iThemes Exchange';
	var $_series          = '';

	var $_plugin_path     = '';
	var $_plugin_url      = '';
	var $_plugin_base     = '';

	/**
	 * Setup the plugin
	 *
	 * Class Constructor. Sets up the environment and then loads admin or enqueues active bar.
	 *
	 * @uses IT_Exchange::set_plugin_locations()
	 * @uses IT_Exchange::set_textdomain()
	 * @uses IT_Exchange::init_exchange()
	 * @since 0.1.0
	 * @return void
	*/
	function IT_Exchange() {
		// Setup Plugin
		$this->set_plugin_locations();
		$this->set_textdomain();

		// Load supporting libraries
		require( $this->_plugin_path . 'lib/load.php' );
		require( $this->_plugin_path . 'api/load.php' );
		require( $this->_plugin_path . 'core-addons/load.php' );

		// Set version
		$GLOBALS['it_exchange']['version'] = $this->_version;
		if ( is_admin() ) {
			$versions         = get_option( 'it-exchange-versions', false );
			$current_version  = empty( $versions['current'] ) ? false: $versions['current'];
			$previous_version = empty( $versions['previous'] ) ? false: $versions['previous'];
			if ( $this->_version !== $current_version ) {
				$versions = array(
					'current'  => $this->_version,
					'previous' => $current_version,
				);
				update_option( 'it-exchange-versions', $versions );
				do_action( 'it_exchange_version_updated', $versions );
			}
		}

		do_action( 'it_exchange_loaded' );
		add_action( 'it_libraries_loaded', array( $this, 'addons_init' ) );
	}

	/**
	 * Defines where the plugin lives on the server
	 *
	 * @uses WP_PLUGIN_DIR
	 * @uses ABSPATH
	 * @uses site_url()
	 * @since 0.1.0
	 * @return void
	*/
	function set_plugin_locations() {
		$this->_plugin_path = plugin_dir_path( __FILE__ );
		$this->_plugin_url  = plugins_url( '', __FILE__ );
		$this->_plugin_base = plugin_basename( __FILE__  );
	}

	/**
	 * Returns IT Exchange Plugin Path
	 *
	 * @since 1.1.5
	 * @return void
	*/
	public function get_plugin_path() {
		return $this->_plugin_path;
	}

	/**
	 * Loads the translation data for WordPress
	 *
	 * @uses load_plugin_textdomain()
	 * @since 0.1.0
	 * @return void
	*/
	function set_textdomain() {
		load_plugin_textdomain( 'it-l10n-ithemes-exchange', false, dirname( $this->_plugin_base ) . '/lang/' );
	}

	/**
	 * Includes files for enabled add-ons
	 *
	 * @since 0.3.1
	 * @return void
	*/
	function addons_init() {
		// Add action for third party addons to register addons with.
		do_action( 'it_exchange_register_addons' );

		$enabled_addons = array();

		// Init all previously enabled addons
		if ( $enabled_addons = it_exchange_get_enabled_addons() ) {
			foreach( (array) $enabled_addons as $slug => $params ) {
				if ( ! empty( $params['file'] ) && is_file( $params['file'] ) ) {
					include( $params['file'] );
				} else {
					it_exchange_disable_addon( $slug );
					if ( is_admin() ) {
						wp_safe_redirect('admin.php?page=it-exchange-addons&message=addon-auto-disabled-' . $addon );
						die();
					}
				}
			}
		}

		// Get addons
		$registered = it_exchange_get_addons();

		// Auto enable all 3rd party addons
		foreach( $registered as $slug => $params ) {
			if ( ! it_exchange_is_core_addon( $slug ) && ! isset( $enabled_addons[$slug] ) )
				it_exchange_enable_addon( $slug );
		}
		do_action( 'it_exchange_enabled_addons_loaded' );
	}
}

/**
 * Loads Exchange after plugins have been enabled
 *
 * @since 0.4.0
 *
 * @return void
*/
function load_it_exchange() {
	// Init plugin
	global $IT_Exchange;
	$IT_Exchange = new IT_Exchange();
}
add_action( 'plugins_loaded', 'load_it_exchange' );

/**
 * Sets up options to perform after activation
 *
 * @since 0.4.0
 *
 * @return void
*/
function it_exchange_activation_hook() {
    add_option('_it-exchange-register-activation-hook', true);
    add_option('_it-exchange-flush-rewrites', true );
}
register_activation_hook( __FILE__, 'it_exchange_activation_hook' );

/**
 * Redirect users to the IT Exchange Setup page upon activation.
 *
 * @since 0.4.0
 *
 * @return void
*/
function it_exchange_register_activation_hook() {
	if ( ! is_network_admin() ) {
		if ( false !== get_option( '_it-exchange-register-activation-hook', false ) ) {
		    delete_option('_it-exchange-register-activation-hook');
			wp_safe_redirect('admin.php?page=it-exchange-setup' );
		}
	}
}
add_action( 'admin_init', 'it_exchange_register_activation_hook' );

/**
 * This flushes the rewrite rules for us on activation
 *
 * @since 0.4.0
 *
 * @return void
*/
function it_exchange_flush_rewrite_rules() {
	if ( false !== get_option( '_it-exchange-flush-rewrites', false ) ) {
		delete_option( '_it-exchange-flush-rewrites' );
		it_exchange_get_pages( true );
		flush_rewrite_rules();
	}
}
add_action( 'admin_init', 'it_exchange_flush_rewrite_rules', 99 );

// Init DB sessions
require( plugin_dir_path( __FILE__ ) . 'lib/sessions/class.session.php' );
