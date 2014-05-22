<?php

/*
Written by Chris Jean for iThemes.com
Version 2.0.2

Version History
	1.0.0 - 2010-07-27
		Initial release version
	1.0.1 - 2010-10-05
		Added check for function get_post_type_object to force 3.0-only use
	1.0.2 - 2010-10-06 - Chris Jean
		Removed "public" from function definition for PHP 4 compatibility
	1.0.3 - 2011-05-03 - Chris Jean
		Added the ability for plugins to supply templates
	1.0.4 - 2011-05-16 - Chris Jean
		Added $post_id arg to validate_meta_box_options call
	1.1.0 - 2011-10-06 - Chris Jean
		Added it_custom_post_type_{$this->_var}_filter_settings filter
	2.0.0 - 2011-12-09 - Chris Jean
		Big structural rewrite to update code to work better with the updated
			WordPress post type API
	2.0.1 - 2011-12-12 - Chris Jean
		Changed wp_print_scripts hook to wp_enqueue_scripts
		Changed wp_print_styles hook to wp_enqueue_scripts
	2.0.2 - 2013-05-21 - Chris Jean
		Removed assign by reference.
*/


if ( ! class_exists( 'ITPostType' ) ) {
	it_classes_load( 'it-filterable-templates.php' );
	
	class ITPostType {
		var $_file = '';
		var $_template_path = null;
		
		var $_var = '';
		var $_slug = null;
		var $_name = '';
		var $_name_plural = '';
		var $_use_storage = false;
		var $_storage_version = '0';
		
		var $_settings = array();
		var $_meta_boxes = array();
		var $_menu_pages = array();
		
		var $_editor_load_jquery = false;
		var $_editor_load_thickbox = false;
		var $_public_load_jquery = false;
		var $_public_load_thickbox = false;
		var $_has_custom_screen_icon = false;
		
		var $_page_refs = array();
		var $_template = '';
		var $_storage = false;
		var $_options = false;
		var $_class;
		
		var $_is_singular = false;
		var $_is_archive = false;
		var $_is_editor = false;
		var $_is_admin_post_listing = false;
		
		
		function ITPostType() {
			$this->_class = get_class( $this );
			
			if ( ! $this->__validate_config() )
				return;
			
			
			if ( true == $this->_use_storage ) {
				it_classes_load( 'it-storage.php' );
				
				$this->_storage = new ITStorage2( $this->_var, $this->_storage_version );
				
				add_action( "it_storage_do_upgrade_{$this->_var}", array( &$this, '__load_storage_upgrade_handler' ) );
				
				if ( is_callable( array( &$this, 'set_defaults' ) ) )
					add_filter( "it_storage_get_defaults_{$this->_var}", array( &$this, 'set_defaults' ) );
			}
			
			
			add_action( 'deactivated_plugin', array( &$this, '__deactivate_plugin' ) );
			
			add_action( 'init', array( &$this, '__init' ) );
			add_action( 'admin_init', array( &$this, '__admin_init' ) );
			add_action( 'admin_menu', array( &$this, '__admin_menu' ) );
			
			add_action( 'pre_get_posts', array( &$this, '__identify_post_type' ) );
			add_action( 'load-post-new.php', array( &$this, '__identify_editor' ) );
			add_action( 'load-post.php', array( &$this, '__identify_editor' ) );
			add_action( 'load-edit.php', array( &$this, '__identify_editor' ) );
			
			add_action( 'admin_print_styles', array( &$this, '__admin_print_styles' ) );
		}
		
		// Initialize the Post Type ////////////////////////////
		
		function __init() {
			$this->_load();
			$this->__setup_default_settings();
			
			$this->_registered_args = register_post_type( $this->_var, $this->_settings );
			
			if ( empty( $this->_template_path ) ) {
				if ( is_callable( array( &$this, 'set_template_path' ) ) )
					$this->_template_path = $this->set_template_path();
				else
					$this->_template_path = dirname( $this->_file ) . '/templates';
			}
			
			if ( ! empty( $this->_template_path ) )
				add_filter( 'it_filter_possible_template_paths', array( &$this, '__filter_possible_template_paths' ) );
			
			if ( method_exists( $this, 'init' ) )
				$this->init();
			
			if ( ! is_admin() )
				$this->__public_init();
		}
		
		function __filter_possible_template_paths( $paths ) {
			$paths[] = $this->_template_path;
			
			return $paths;
		}
		
		function __admin_init() {
			if ( false === get_option( $this->_var . '_activated' ) )
				$this->__activate();
		}
		
		function __identify_editor() {
			$typenow = $GLOBALS['typenow'];
			
			if ( version_compare( $GLOBALS['wp_version'], '3.2.10', '<' ) && ( 'post' == $typenow ) ) {
				if ( isset( $_REQUEST['post'] ) )
					$typenow = get_post_type( $_REQUEST['post'] );
				else if ( isset( $_REQUEST['post_ID'] ) )
					$typenow = get_post_type( $_REQUEST['post_ID'] );
			}
			
			if ( $this->_var == $typenow )
				$this->__prepare_editor();
		}
		
		function __public_init() {
			if ( method_exists( $this, 'public_init' ) )
				$this->public_init();
		}
		
		// Add Custom Menu Pages ////////////////////////////
		
		function __admin_menu() {
			if ( empty( $this->_menu_pages ) )
				return;
			
			foreach ( (array) $this->_menu_pages as $var => $args ) {
				$this->_page_refs[$var] = add_submenu_page( "edit.php?post_type={$this->_var}", $args['page_title'], $args['menu_title'], $args['capability'], $var, array( &$this, $args['callback'] ) );
				
				add_action( "load-{$this->_page_refs[$var]}", array( &$this, '__prepare_editor' ) );
			}
		}
		
		// Setup/Run Editor-specific Handlers ////////////////////////////
		
		function __prepare_editor() {
			$this->_is_editor = true;
			
			add_filter( "views_edit-{$this->_var}", array( &$this, '__add_list_table_views' ) );
			
			add_action( 'save_post', array( &$this, 'save_meta_box_options' ) );
			add_action( 'admin_print_scripts', array( &$this, '__admin_print_editor_scripts' ) );
			add_action( 'admin_print_styles', array( &$this, '__admin_print_editor_styles' ) );
			
			if ( true === $this->_has_custom_screen_icon )
				add_action( 'admin_notices', array( &$this, '__modify_current_screen' ) );
			
			$this->__add_contextual_help();
		}
		
		function __add_list_table_views( $views ) {
			return $views;
		}
		
		// Load Custom Templates if in This Custom Post Type's views ////////////////////////////
		
		function __identify_post_type( $wp_query ) {
			if ( $wp_query->get( 'post_type' ) != $this->_var )
				return;
			
			remove_action( 'pre_get_posts', array( &$this, '__identify_post_type' ) );
			
			
			if ( is_archive() ) {
				if ( is_admin() ) {
					if ( ! isset( $_REQUEST['orderby'] ) )
						add_filter( 'posts_orderby', array( &$this, '__filter_posts_orderby' ), 10, 2 );
					
					$this->_is_admin_post_listing = true;
				}
				else {
					add_filter( 'posts_orderby', array( &$this, '__filter_posts_orderby' ), 10, 2 );
					add_filter( 'pre_option_posts_per_page', array( &$this, '__filter_posts_per_page' ) );
					
					$this->_template = 'archive';
					$this->_is_archive = true;
				}
			}
			else if ( is_singular() ) {
				$this->_template = 'single';
				$this->_is_singular = true;
			}
			else {
				return;
			}
			
			add_action( 'wp_enqueue_scripts', array( &$this, '__print_scripts' ) );
			add_action( 'wp_enqueue_scripts', array( &$this, '__print_styles' ) );
			
			add_action( 'template_redirect', array( &$this, '__template_redirect' ), 100 );
		}
		
		function __template_redirect() {
			$paths = array( get_stylesheet_directory(), get_template_directory() );
			
			if ( ! empty( $this->_template_path ) )
				$paths[] = $this->_template_path;
			
			$paths = apply_filters( 'it_post_type_filter_template_paths', array_unique( $paths ) );
			
			foreach ( (array) $paths as $path )
				$this->__load_template( $path );
		}
		
		function __load_template( $path ) {
			if ( ! is_dir( $path ) )
				return;
			
			
			$var_directory = str_replace( '_', '-', $this->_var );
			$file = '';
			
			if ( is_dir( "$path/$var_directory" ) && is_file( "$path/$var_directory/{$this->_template}.php" ) )
				$file = "$path/$var_directory/{$this->_template}.php";
			
			if ( is_file( "$path/{$this->_template}-{$this->_var}.php" ) )
				$file = "$path/{$this->_template}-{$this->_var}.php";
			
			
			if ( empty( $file ) )
				return;
			
			include( $file );
			
			exit;
		}
		
		// Help Handlers ////////////////////////////
		
		function __add_contextual_help() {
			if ( empty( $this->_menu_pages ) )
				return;
			
			foreach ( (array) $this->_menu_pages as $var => $args ) {
				if ( method_exists( $this, "{$var}_get_contextual_help" ) )
					add_contextual_help( $this->_page_refs[$var], call_user_func( array( $this, "{$var}_get_contextual_help" ) ) );
			}
		}
		
		// Ensure Proper Flushing of Rewrite Rules ////////////////////////////
		
		function __activate() {
			$this->__flush_rewrite_rules();
		}
		
		function __flush_rewrite_rules() {
			global $wp_rewrite;
			$wp_rewrite->flush_rules();
			
			update_option( $this->_var . '_activated', true );
		}
		
		function __deactivate_plugin() {
			delete_option( $this->_var . '_activated' );
		}
		
		// Options Storage ////////////////////////////
		
		function _save() {
			if ( false == $this->_use_storage )
				return;
			
			$this->_storage->save( $this->_options );
		}
		
		function _load() {
			if ( false == $this->_use_storage )
				return;
			
			if ( ! isset( $this->_storage ) || ! is_callable( array( $this->_storage, 'load' ) ) )
				ITError::fatal( "empty_var:class_var:{$this->_class}->_storage", "The $this->_class class did not set the \$this->_storage variable. This should be set by the ITPostType class, ensure that the ITPostType::ITPostType() method is called." );
			
			$this->_options = $this->_storage->load();
		}
		
		function __load_storage_upgrade_handler() {
			if ( ! empty( $this->_upgrade_handler_file ) && file_exists( $this->_upgrade_handler_file ) )
				require_once( $this->_upgrade_handler_file );
			else if ( file_exists( dirname( $this->_file ) . '/upgrade-storage.php' ) )
				require_once( dirname( $this->_file ) . '/upgrade-storage.php' );
		}
		
		// Style and Script Handlers ////////////////////////////
		
		function __load_style( $file, $name = null ) {
			if ( empty( $name ) )
				$name = "$file-style";
			
			if ( file_exists( dirname( $this->_file ) . "/css/$file-{$this->_var}.css" ) ) {
				it_classes_load( 'it-file-utility.php' );
				
				$css_url = ITFileUtility::get_url_from_file( dirname( $this->_file ) . "/css/$file-{$this->_var}.css" );
				wp_enqueue_style( "{$this->_var}-$name", $css_url );
			}
		}
		
		function __load_script( $file, $name = null ) {
			if ( empty( $name ) )
				$name = "$file-script";
			
			
			$dependencies = array();
			
			if ( is_admin() ) {
				if ( true === $this->_editor_load_jquery )
					$dependencies[] = 'jquery';
				if ( true === $this->_editor_load_thickbox )
					$dependencies[] = 'thickbox';
			}
			else {
				if ( true === $this->_public_load_jquery )
					$dependencies[] = 'jquery';
				if ( true === $this->_public_load_thickbox )
					$dependencies[] = 'thickbox';
			}
			
			
			if ( file_exists( dirname( $this->_file ) . "/js/$file-{$this->_var}.js" ) ) {
				it_classes_load( 'it-file-utility.php' );
				
				$js_url = ITFileUtility::get_url_from_file( dirname( $this->_file ) . "/js/$file-{$this->_var}.js" );
				wp_enqueue_script( "{$this->_var}-$name-script", $js_url, $dependencies );
			}
		}
		
		function __admin_print_styles() {
			$this->__load_style( 'admin' );
			
			if ( method_exists( $this, 'admin_print_styles' ) )
				$this->admin_print_styles();
		}
		
		function __admin_print_editor_scripts() {
			$this->__load_script( 'editor' );
			
			if ( method_exists( $this, 'admin_print_editor_scripts' ) )
				$this->admin_print_editor_scripts();
			else if ( method_exists( $this, 'admin_print_scripts' ) )
				$this->admin_print_scripts();
		}
		
		function __admin_print_editor_styles() {
			$this->__load_style( 'editor' );
			
			if ( method_exists( $this, 'admin_print_editor_styles' ) )
				$this->admin_print_editor_styles();
			else if ( method_exists( $this, 'admin_print_styles' ) )
				$this->admin_print_styles();
		}
		
		function __print_scripts() {
			$this->__load_script( 'public', 'script' );
			
			if ( method_exists( $this, 'print_scripts' ) )
				$this->print_scripts();
		}
		
		function __print_styles() {
			$this->__load_style( 'public', 'style' );
			
			if ( method_exists( $this, 'print_styles' ) )
				$this->print_styles();
		}
		
		// Ensure That the Custom Post Type's Icon is Used ////////////////////////////
		
		function __modify_current_screen() {
			global $current_screen;
			
			if ( empty( $current_screen->parent_file ) || ( "edit.php?post_type={$this->_var}" !== $current_screen->parent_file ) )
				return $current_screen;
			
			$current_screen->parent_base = $this->_var;
		}
		
		// Modify Post Query ////////////////////////////
		
		function __filter_posts_orderby( $orderby, $wp_query ) {
			if ( is_callable( array( &$this, 'filter_posts_orderby' ) ) ) {
				global $wpdb;
				
				$orderby = $this->filter_posts_orderby( $orderby, $wp_query );
				
				while ( preg_match( '/%WPDB-([^%]+)%/', $orderby, $match ) )
					$orderby = str_replace( "%WPDB-{$match[1]}%", $wpdb->{$match[1]}, $orderby );
			}
			
			return $orderby;
		}
		
		function __filter_posts_per_page( $posts_per_page ) {
			if ( is_callable( array( &$this, 'filter_posts_per_page' ) ) )
				return $this->filter_posts_per_page( $posts_per_page );
			
			return $posts_per_page;
		}
		
		// Adjust Custom Post Type Settings Based on Configuration ////////////////////////////
		
		function __setup_default_settings() {
			// For full list of available settings, see the register_post_type() function in wp-includes/post.php
			// Common settings: can_export, description, exclude_from_search, has_archive, hierarchical, menu_icon,
			//   public, register_meta_box_cb, rewrite, show_in_nav_menus, show_ui, supports
			$default_settings = array(
				'public'               => true,
				'has_archive'          => true,
				'supports'             => array(  // Shorthand for calling add_post_type_support()
					'title',
					'editor',
				),
				'register_meta_box_cb' => array( &$this, '__register_meta_boxes' ),
			);
			
			$default_settings['labels'] = $this->__get_default_labels();
			
			if ( ! empty( $this->_slug ) )
				$default_settings['rewrite'] = array( 'slug' => $this->_slug );
			
			$this->_settings = array_merge( $default_settings, $this->_settings );
			
			if ( ! empty( $this->_settings['menu_icon'] ) && ! preg_match( '/^http/', $this->_settings['menu_icon'] ) ) {
				if ( ! isset( $this->_url_base ) ) {
					it_classes_load( 'it-file-utility.php' );
					$this->_url_base = ITFileUtility::get_url_from_file( dirname( $this->_file ) );
				}
				
				$this->_settings['menu_icon'] = $this->_url_base . '/' . $this->_settings['menu_icon'];
			}
			
			$slug = apply_filters( "it_custom_post_type_{$this->_var}_filter_slug", '' );
			
			if ( ! empty( $slug ) )
				$this->_settings['rewrite'] = array( 'slug' => $slug );
			
			$this->_settings = apply_filters( "it_custom_post_type_{$this->_var}_filter_settings", $this->_settings );
		}
		
		function __get_default_labels() {
			$labels = array(
				'name'               => $this->_name_plural,
				'singular_name'      => $this->_name,
				'add_new'            => _x( 'Add New', 'post', 'it-l10n-ithemes-exchange' ),
				'add_new_item'       => sprintf( _x( 'Add New %s', 'post', 'it-l10n-ithemes-exchange' ), $this->_name ),
				'edit_item'          => sprintf( _x( 'Edit %s', 'post', 'it-l10n-ithemes-exchange' ), $this->_name ),
				'new_item'           => sprintf( _x( 'New %s', 'post', 'it-l10n-ithemes-exchange' ), $this->_name ),
				'view_item'          => sprintf( _x( 'View %s', 'post', 'it-l10n-ithemes-exchange' ), $this->_name ),
				'search_items'       => sprintf( _x( 'Search %s', 'post', 'it-l10n-ithemes-exchange' ), $this->_name_plural ),
				'not_found'          => sprintf( _x( 'No %s found', 'post', 'it-l10n-ithemes-exchange' ), strtolower( $this->_name ) ),
				'not_found_in_trash' => sprintf( _x( 'No %s found in trash', 'post', 'it-l10n-ithemes-exchange' ), strtolower( $this->_name_plural ) ),
				'parent_item_colon'  => sprintf( _x( 'Parent %s:', 'post', 'it-l10n-ithemes-exchange' ), $this->_name ),
				'all_items'          => sprintf( _x( 'All %s', 'post', 'it-l10n-ithemes-exchange' ), $this->_name_plural ),
			);
			
			return $labels;
		}
		
		// Verify Class Configuration ////////////////////////////
		
		function __validate_config() {
			if ( ! function_exists( 'get_post_type_object' ) )
				return false;
			
			if ( empty( $this->_var ) ) {
				it_classes_load( 'it-error.php' );
				
				ITError::admin_warn( 'it-post-type-missing-var', "Unable to load {$this->_class} due to missing _var.", 'edit_plugins' );
				return false;
			}
			
			if ( empty( $this->_file ) ) {
				it_classes_load( 'it-error.php' );
				
				ITError::admin_warn( 'it-post-type-missing-file', "Unable to load {$this->_class} due to missing _file.", 'edit_plugins' );
				return false;
			}
			
			return true;
		}
		
		// Meta Box Handlers ////////////////////////////
		
		function __register_meta_boxes() {
			do_action( "register_post_type_meta_boxes-{$this->_var}" );
			
			foreach ( (array) $this->_meta_boxes as $var => $args )
				add_meta_box( $var, $args['title'], array( &$this, 'render_meta_box' ), $this->_var, $args['context'], $args['priority'], $args );
		}
		
		function render_meta_box( $post, $object ) {
			if ( ! isset( $this->_meta_box_options ) )
				$this->_meta_box_options = get_post_meta( $post->ID, '_it_options', true );
			
			if ( ! isset( $this->_meta_box_form ) )
				$this->_meta_box_form = new ITForm( $this->_meta_box_options, array( 'prefix' => $this->_var ) );
			
			call_user_func( array( &$this, $object['args']['callback'] ), $post, $this->_meta_box_form, $this->_meta_box_options, $object );
			
			if ( ! isset( $this->_meta_box_nonce_added ) ) {
				$this->_meta_box_form->add_hidden( 'nonce', wp_create_nonce( $this->_var ) );
				
				$this->_meta_box_nonce_added = true;
			}
		}
		
		function save_meta_box_options( $post_id ) {
			// Skip if the nonce check fails
			if ( ! isset( $_POST["{$this->_var}-nonce"] ) || ! wp_verify_nonce( $_POST["{$this->_var}-nonce"], $this->_var ) )
				return;
			
			// Don't save or update on autosave
			if ( defined( 'DOING_AUTOSAVE' ) && ( true === DOING_AUTOSAVE ) )
				return;
			
			// Only allow those with permissions to modify the type to save/update a layout
			if ( ! current_user_can( 'edit_post', $post_id ) )
				return;
			
			
			// Save/update options
			$options = ITForm::parse_values( $_POST, array( 'prefix' => $this->_var ) );
			unset( $options['nonce'] );
			
			if ( method_exists( $this, 'validate_meta_box_options' ) )
				$options = $this->validate_meta_box_options( $options, $post_id );
			
			update_post_meta( $post_id, '_it_options', $options );
		}
	}
}
