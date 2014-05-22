<?php

/*
Written by Chris Jean for iThemes.com
Version 1.5.3

Version History
	1.2.0 - 2011-06-27 - Chris Jean
		Added tooltip support
	1.2.1 - 2011-08-23 - Chris Jean
		Added dequeue for media-upload to defensively protect against poorly-coded plugins
	1.3.0 - 2011-10-20 - Chris Jean
		Added support for ITCoreClass subclasses that don't use storage
		Updated contextual_help to support the new WP 3.3 help system while keeping back compat
	1.3.1 - 2011-10-26 - Chris Jean
		Added compatibility check for the wp_dequeue_script function
	1.4.0 - 2011-12-09 - Chris Jean
		Added check and call for function set_help_sidebar()
	1.4.1 - 2012-02-13 - Chris Jean
		Improved relative path code to work with servers with odd ABSPATH configurations
	1.5.0 - 2012-09-24 - Chris Jean
		Updated code to handle ITDialog requests.
	1.5.1 - 2012-09-26 - Chris Jean
		Updated _plugin_url and _class_url generation code.
	1.5.2 - 2013-05-21 - Chris Jean
		Removed assign by reference.
	1.5.3 - 2013-06-25 - Chris Jean
		Rewrote $this->_self_link calculation in order to avoid strict standards warning in PHP 5.5.0.
*/


if ( ! class_exists( 'ITCoreClass' ) ) {
	it_classes_load( 'it-storage.php' );
	it_classes_load( 'it-form.php' );
	
	
	class ITCoreClass {
		var $_var = 'class_var_name';
		var $_page_title = 'Page Title';
		var $_page_var = 'class-page-var';
		var $_menu_title = 'Menu Title';
		var $_default_menu_function = 'add_theme_page';
		var $_access_level = 'switch_themes';
		var $_menu_priority = '10';
		var $_storage_version = '0';
		var $_it_storage_version = '';
		
		var $_page_ref = '';
		
		var $_storage;
		var $_global_storage = false;
		var $_suppress_errors = false;
		var $_options = array();
		
		var $_file;
		var $_class;
		var $_self_link;
		
		
		function ITCoreClass() {
			if ( false === $this->_it_storage_version )
				$this->_storage = false;
			else if ( '2' != $this->_it_storage_version )
				$this->_storage = new ITStorage( $this->_var, $this->_global_storage, $this->_storage_version, $this->_suppress_errors );
			else
				$this->_storage = new ITStorage2( $this->_var, $this->_storage_version );
			
			add_action( 'init', array( &$this, 'init' ), 0 );
			add_action( 'admin_menu', array( &$this, 'add_admin_pages' ), $this->_menu_priority );
			add_action( 'widgets_init', array( &$this, 'register_widgets' ) );
			add_action( "it_storage_do_upgrade_{$this->_var}", array( &$this, 'add_storage_upgrade_handler' ) );
		}
		
		function init() {
			$this->_load();
			$this->_set_vars();
			
			if ( isset( $_REQUEST['page'] ) && ( $_REQUEST['page'] === $this->_page_var ) ) {
				global $wp_version;
				
				if ( version_compare( $wp_version, '3.2.5', '>' ) )
					add_action( 'admin_head', array( &$this, '__add_screen_meta' ) );
				else
					add_filter( 'contextual_help', array( &$this, 'contextual_help' ), 10, 2 );
				
				add_filter( 'screen_settings', array( &$this, 'screen_settings' ), 10, 2 );
				
				if ( ! empty( $_REQUEST['it_ajax_handler'] ) )
					$this->_ajax_handler();
				else if ( ! empty( $_REQUEST['render_clean'] ) )
					add_action( 'admin_init', array( &$this, 'render_clean' ) );
				
				$this->_active_init();
			}
		}
		
		function _active_init() {
			// This function runs when the page is active
			
			it_classes_load( 'it-dialog.php' );
			
			if ( method_exists( $this, 'active_init' ) )
				$this->active_init();
		}
		
		function add_admin_pages() {
			$theme_menu_var = apply_filters( 'it_filter_theme_menu_var', '' );
			$default_menu_function = $this->_default_menu_function;
			
			if ( ! empty( $this->_parent_page_var ) )
				$this->_page_ref = add_submenu_page( $this->_parent_page_var, $this->_page_title, $this->_menu_title, $this->_access_level, $this->_page_var, array( &$this, 'index' ) );
			else if ( ! empty( $theme_menu_var ) )
				$this->_page_ref = add_submenu_page( $theme_menu_var, $this->_page_title, $this->_menu_title, $this->_access_level, $this->_page_var, array( &$this, 'index' ) );
			else
				$this->_page_ref = $default_menu_function( $this->_page_title, $this->_menu_title, $this->_access_level, $this->_page_var, array( &$this, 'index' ) );
			
			add_action( "admin_print_scripts-{$this->_page_ref}", array( $this, 'add_admin_scripts' ) );
			add_action( "admin_print_styles-{$this->_page_ref}", array( $this, 'add_admin_styles' ) );
		}
		
		function add_admin_scripts() {
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'thickbox' );
			
			wp_enqueue_script( 'it-core-class-script', "{$this->_class_url}/js/it-core-class.js" );
			
			ITUtility::add_tooltip_scripts();
		}
		
		function add_admin_styles() {
			wp_enqueue_style( 'thickbox' );
			
			wp_enqueue_style( 'it-classes-style', "{$this->_class_url}/css/classes.css" );
			
			if ( class_exists( 'ITDialog' ) )
				ITDialog::add_enqueues();
			
			ITUtility::add_tooltip_styles();
		}
		
		function add_storage_upgrade_handler() {
			if ( file_exists( dirname( $this->_file ) . '/upgrade-storage.php' ) )
				ITUtility::require_file_once( dirname( $this->_file ) . '/upgrade-storage.php' );
		}
		
		function _set_vars() {
			$this->_class = get_class( $this );
			
			if ( isset( $_REQUEST['page'] ) ) {
				$uri_parts = explode( '?', $_SERVER['REQUEST_URI'] );
				
				$this->_self_link = $uri_parts[0] . '?page=' . $_REQUEST['page'];
			}
			
			if ( empty( $this->_file ) || ! is_file( $this->_file ) )
				ITError::fatal( "empty_var:class_var:{$this->_class}->_file", "The $this->_class class did not fill in the \$this->_file variable. This should be done in the class constructor with a value of __FILE__." );
			
			$this->_plugin_path = dirname( $this->_file );
			$this->_plugin_url = ITUtility::get_url_from_file( $this->_plugin_path );
			
			$this->_class_path = dirname( __FILE__ );
			$this->_class_url = ITUtility::get_url_from_file( $this->_class_path );
		}
		
		function register_widgets() {
/*			ITUtility::require_file_once( dirname( __FILE__ ) . '/widget.php' );
			
			register_widget( 'PluginWidget' );*/
		}
		
		
		// Options Storage ////////////////////////////
		
		function _save() {
			if ( false === $this->_storage )
				return;
			
			$this->_storage->save( $this->_options );
		}
		
		function _load() {
			if ( false === $this->_storage )
				return;
			
			if ( ! isset( $this->_storage ) || ! is_callable( array( $this->_storage, 'load' ) ) )
				ITError::fatal( "empty_var:class_var:{$this->_class}->_storage", "The $this->_class class did not set the \$this->_storage variable. This should be set by the ITCoreClass class, ensure that the ITCoreClass::ITCoreClass() method is called." );
			
			$this->_options = $this->_storage->load();
		}
		
		
		// Rendering //////////////////////////////////
		
		function __add_screen_meta() {
			global $current_screen;
			
			$this->_add_screen_meta( $current_screen );
			
			if ( method_exists( $this, 'set_help_sidebar' ) )
				$this->set_help_sidebar();
		}
		
		function _add_screen_meta( $screen ) {
			if ( ! is_object( $screen ) || ! method_exists( $screen, 'add_help_tab' ) )
				return;
			
			$help = $this->contextual_help( '', $screen );
			
			if ( ! empty( $help ) && is_string( $help ) ) {
				$tab = array(
					'id'      => 'screen-info',
					'title'   => __( 'Screen Info' ),
					'content' => $help,
				);
				
				$screen->add_help_tab( $tab );
			}
		}
		
		function contextual_help( $text, $screen ) {
			return $text;
		}
		
		function screen_settings( $settings, $screen ) {
			return $settings;
		}
		
		function render_clean() {
			$this->add_admin_scripts();
			$this->add_admin_styles();
			
			if ( 'dialog' == $_REQUEST['render_clean'] ) {
				ITDialog::render( array( &$this, 'index' ) );
			}
			else {
				require_once( dirname( __FILE__ ) . '/it-thickbox.php' );
				
				ITThickbox::render_thickbox( array( &$this, 'index' ) );
			}
			
			exit;
		}
		
		function index() {
			if ( ! current_user_can( $this->_access_level ) )
				die( __( 'Cheatin&#8217; uh?' ) );
			
			// This needs to be modified to not allow an attacker to bypass it.
			// Possibly do a check to see if $_POST is not empty.
			if ( ! empty( $_REQUEST['_wpnonce'] ) )
				ITForm::check_nonce( ( ! empty( $this->_nonce ) ) ? $this->_nonce : null );
			
			ITUtility::cleanup_request_vars();
		}
		
		
		// Ajax Handlers //////////////////////////////
		
		function _ajax_handler() {
			if ( is_admin() )
				$this->_ajax_index();
			
			exit;
		}
		
		function _ajax_index() {
			// This function should be overridden to provide AJAX functionality.
		}
	}
}
