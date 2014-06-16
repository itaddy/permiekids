<?php
/**
 * Plugin Name: Site Specific Code
 * Plugin URI: http://ironbounddesigns.com
 * Description: Site specific code for Membership Website
 * Version: 1.0
 * Author: Iron Bound Designs
 * Author URI: http://ironbounddesigns.com
 * License: GPL2
 */
ini_set( 'xdebug.var_display_max_depth', '10' );

/**
 * Class LDMW_Plugin
 */
class LDMW_Plugin {
	/**
	 * @var string
	 */
	public static $url;
	/**
	 * @var string
	 */
	public static $dir;

	/**
	 * Add required actions.
	 */
	public function __construct() {
		self::$url = plugin_dir_url( __FILE__ );
		self::$dir = plugin_dir_path( __FILE__ );

		add_action( 'init', array( $this, 'load' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'scripts_and_styles' ) );
		spl_autoload_register( array( "LDMW_Plugin", "autoload" ) );
	}

	/**
	 * Kick things off
	 */
	public function load() {
		if ( ! class_exists( 'IT_Exchange' ) || ! function_exists( 'it_exchange_register_prorated_subscription_addon' )
		  || ! function_exists( 'it_exchange_register_invoices_addon' ) || ! function_exists( 'it_exchange_register_membership_addon' )
		  || ! class_exists( 'GFForms' ) || ! function_exists( 'it_exchange_register_manual_purchases_addon' )
		)
			add_action( 'admin_notices', array( $this, 'nag' ) );
		else
			require( self::$dir . "init.php" );
	}

	/**
	 * Display a nag if requirements are not met.
	 */
	public function nag() {
		?>
		<div class="error"><p>This plugin requires iThemes Exchange and the following add-ons: Prorated Subscriptions, Membership, Invoices</p></div><?php
	}

	/**
	 * Register necessary scripts.
	 *
	 * @param $hook string
	 */
	public function admin_scripts( $hook ) {
		if ( strpos( $hook, 'ldmw' ) == false )
			return;

		wp_enqueue_style( 'ldmw-admin', self::$url . "assets/css/admin.css", array(), "1.0" );
		wp_enqueue_script( 'ldmw-admin', self::$url . "assets/js/admin.js", array( 'jquery-ui-datepicker', 'jquery' ), 1.0 );
	}

	/**
	 * Register necessary scripts and styles.
	 */
	public function scripts_and_styles() {
		wp_register_style( 'ldmw-display', LDMW_Plugin::$url . "assets/css/front-end.css" );
	}

	/**
	 * Autoloader
	 *
	 * @param $class_name string
	 */
	public static function autoload( $class_name ) {
		if ( substr( $class_name, 0, 4 ) != "LDMW" )
			return;

		$path = self::$dir . "lib";

		$class = substr( $class_name, 4 );
		$class = strtolower( $class );

		$parts = explode( "_", $class );
		$name = array_pop( $parts );

		$path .= implode( "/", $parts );
		$path .= "/class.$name.php";

		if ( file_exists( $path ) ) {
			require( $path );

			return;
		}

		if ( file_exists( str_replace( "class.", "abstract.", $path ) ) ) {
			require( str_replace( "class.", "abstract.", $path ) );

			return;
		}

		if ( file_exists( str_replace( "class.", "interface.", $path ) ) ) {
			require( str_replace( "class.", "interface.", $path ) );

			return;
		}
	}

	/**
	 * Activation hook
	 */
	public static function activation() {
		if ( ! wp_next_scheduled( 'ldmw_daily_cron' ) )
			wp_schedule_event( time(), 'daily', 'ldmw_daily_cron' );
	}

	/**
	 * Add a deactivation hook.
	 */
	public static function deactivation() {
		wp_clear_scheduled_hook( 'ldmw_daily_cron' );
	}
}

register_activation_hook( __FILE__, array( 'LDMW_Plugin', 'activation' ) );
register_deactivation_hook( __FILE__, array( 'LDMW_Plugin', 'deactivation' ) );
new LDMW_Plugin();