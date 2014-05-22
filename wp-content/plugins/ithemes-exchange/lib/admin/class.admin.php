<?php
/**
 * iThemes Exchange admin class.
 *
 * This class manages the admin side of the plugin
 *
 * @package IT_Exchange
 * @since 0.1.0
*/
class IT_Exchange_Admin {

	/**
	 * @var object $_parent parent class
	 * @since 0.1.0
	*/
	var $_parent;

	/**
	 * @var string $_current_page current page based on $_GET['page']
	 * @since 0.3.4
	*/
	var $_current_page;

	/**
	 * @var string $_current_tab
	 * @since 0.3.4
	*/
	var $_current_tab;

	/**
	 * @var string $status_message informative message for current settings tab
	 * @since 0.3.6
	*/
	var $status_message;

	/**
	 * @var string $error_message error message for current settings tab
	 * @since 0.3.6
	*/
	var $error_message;

	/**
	 * Class constructor
	 *
	 * @uses add_action()
	 * @since 0.1.0
	 * @return void
	*/
	function IT_Exchange_Admin( &$parent ) {

		// Set parent property
		$this->_parent = $parent;

		// Admin Menu Capability
		$this->admin_menu_capability = apply_filters( 'it_exchange_admin_menu_capability', 'activate_plugins' );

		// Set current properties
		$this->set_current_properties();

		// Open iThemes Exchange menu when on add/edit iThemes Exchange product post type
		add_action( 'parent_file', array( $this, 'open_exchange_menu_on_post_type_views' ) );

		// Add actions for iThemes registration
		add_action( 'admin_notices', array( $this, 'add_wizard_nag' ) );
		add_action( 'admin_menu', array( $this, 'add_exchange_admin_menu' ) );
		add_action( 'admin_menu', array( $this, 'add_lower_priority_items_to_exchange_menu' ), 90 );
		add_action( 'admin_init', array( $this, 'enable_disable_registered_add_on' ) );
		add_action( 'admin_init', array( $this, 'enable_required_add_ons' ) );
		add_filter( 'admin_body_class', array( $this, 'add_exchange_class_to_exchange_pages' ) );
		add_filter( 'upload_mimes', array( $this, 'uploads_mimes_for_products' ) );
		add_filter( 'wp_ajax_ite-country-state-update', array( $this, 'update_country_state_ui_in_general_settings' ) );

		// Admin Product Redirects
		add_action( 'admin_init', array( $this, 'redirect_post_new_to_product_type_selection_screen' ) );
		add_action( 'admin_init', array( $this, 'bounce_user_to_all_products_if_directly_accessing_disabled_product_type' ) );

		// Init our custom add/edit layout interface
		add_action( 'admin_enqueue_scripts', array( $this, 'it_exchange_admin_wp_enqueue_scripts' ) );
		add_action( 'admin_print_styles', array( $this, 'it_exchange_admin_wp_enqueue_styles' ) );
		add_action( 'admin_init', array( $this, 'remove_third_party_metaboxes' ) );
		add_action( 'admin_init', array( $this, 'setup_add_edit_product_screen_layout' ) );

		// Force 2 column view on add / edit products
		add_filter( 'screen_layout_columns', array( $this, 'modify_add_edit_page_layout' ) );
		add_filter( 'get_user_option_screen_layout_it_exchange_prod', array( $this, 'update_user_column_options' ) );

		// Save core settings
		add_action( 'admin_init', array( $this, 'save_core_wizard_settings' ), 9 );
		add_action( 'admin_init', array( $this, 'save_core_general_settings' ) );
		add_action( 'admin_init', array( $this, 'save_core_email_settings' ) );
		add_action( 'admin_init', array( $this, 'save_core_page_settings' ), 9 ); // Priority 9 to catch product rewrites

		// Email settings callback
		add_filter( 'it_exchange_general_settings_tab_callback_email', array( $this, 'register_email_settings_tab_callback' ) );
		add_action( 'it_exchange_print_general_settings_tab_links', array( $this, 'print_email_settings_tab_link' ) );

		// Page settings callback
		add_filter( 'it_exchange_general_settings_tab_callback_pages', array( $this, 'register_pages_settings_tab_callback' ) );
		add_action( 'it_exchange_print_general_settings_tab_links', array( $this, 'print_pages_settings_tab_link' ) );

		// General Settings Defaults
		add_filter( 'it_storage_get_defaults_exchange_settings_general', array( $this, 'set_general_settings_defaults' ) );

		// Email Settings Defaults
		add_filter( 'it_storage_get_defaults_exchange_settings_email', array( $this, 'set_email_settings_defaults' ) );

		// Page Settings Defaults
		add_filter( 'it_storage_get_defaults_exchange_settings_pages', array( $this, 'set_pages_settings_defaults' ) );

		// Add-on Page Filters
		add_action( 'it_exchange_print_add_ons_page_tab_links', array( $this, 'print_enabled_add_ons_tab_link' ) );
		add_action( 'it_exchange_print_add_ons_page_tab_links', array( $this, 'print_disabled_add_ons_tab_link' ) );
		add_filter( 'it_exchange_add_ons_tab_callback_get-more', array( $this, 'register_get_more_add_ons_tab_callback' ) );
		add_action( 'it_exchange_print_add_ons_page_tab_links', array( $this, 'print_get_more_add_ons_tab_link' ) );

		// Update existing nav menu post_type entries when permalink structure is changed
		add_action( 'update_option_permalink_structure', array( $this, 'maybe_update_ghost_pages_in_wp_nav_menus' ) );

		// Remove Quick Edit
		add_filter( 'post_row_actions', array( $this, 'it_exchange_remove_quick_edit' ), 10, 2 );
		add_filter( 'page_row_actions', array( $this, 'it_exchange_remove_quick_edit' ), 10, 2 );

		// User Edit
		add_filter( 'user_row_actions', array( $this, 'it_exchange_user_row_actions' ), 10, 2 );
		add_action( 'all_admin_notices', array( $this, 'it_exchange_user_edit_load' ) );
		add_action( 'show_user_profile', array( $this, 'it_exchange_user_profile' ) );
		add_action( 'edit_user_profile', array( $this, 'it_exchange_user_profile' ) );

		add_action( 'it_exchange_addon_settings_page_top', array( $this, 'return_to_addons' ) );

		add_filter( 'plugin_action_links_ithemes-exchange/init.php', array( $this, 'it_exchange_plugin_row_actions' ), 10, 4 );
		add_filter( 'plugin_row_meta', array( $this, 'it_exchange_plugin_row_meta' ), 10, 4 );
	}

	/**
	 * Adds actions to the plugins page for the iThemes Exchange plugin
	 *
	 * @since 0.4.15
	 *
	 * @param array $meta Existing meta
	 * @param string $plugin_file the wp plugin slug (path)
	 * @param array $plugin_data the data WP harvested from the plugin header
	 * @param string $context
	 * @return array
	*/
	function it_exchange_plugin_row_actions( $actions, $plugin_file, $plugin_data, $context ) {

		$actions['quick_setup'] = '<a href="' . get_admin_url( NULL, 'admin.php?page=it-exchange-setup' ) . '">' . __( 'Quick Setup', 'it-l10n-ithemes-exchange' ) . '</a>';

		return $actions;

	}

	/**
	 * Adds links to the plugin row meta
	 *
	 * @since 0.4.18
	 *
	 * @param array $meta Existing meta
	 * @param string $plugin_file the wp plugin slug (path)
	 * @param array $plugin_data the data WP harvested from the plugin header
	 * @param string $status the plugin activation status
	 * @return array
	*/
	function it_exchange_plugin_row_meta( $meta, $plugin_file, $plugin_data, $status ) {
		if ( 'ithemes-exchange/init.php' == $plugin_file ) {
			$meta[] = '<a href="http://ithemes.com/codex/page/Exchange" target="_blank">' . __( 'Documentation', 'it-l10n-ithemes-exchange' ) . '</a>';
			$meta[] = '<a href="http://ithemes.com/exchange/support" target="_blank">' . __( 'Support', 'it-l10n-ithemes-exchange' ) . '</a>';
			$meta[] = '<a href="http://ithemes.com/exchange/add-ons" target="_blank">' . __( 'Add-ons', 'it-l10n-ithemes-exchange' ) . '</a>';
		}
		return $meta;
	}


	/**
	 * Adds link to bottom of addons settings pages to return to the addons pages
	 *
	 * @since 0.4.5
	 * @return void
	*/
	function return_to_addons() {
		if ( ! empty( $GLOBALS['hook_suffix'] ) && 'exchange_page_it-exchange-addons' == $GLOBALS['hook_suffix'] ) { //only show on add-on-settings pages
			$url = add_query_arg( 'page', 'it-exchange-addons', admin_url( 'admin.php' ) );
			echo '<div class="it-exchange-return-to-addons"><p><a href="' . $url . '">&larr; ' . __( 'Back to Add-ons', 'it-l10n-ithemes-exchange' ) . '</a></p></div>';
		}
	}

	/**
	 * Adds iThemes Exchange User row action to users.php row actions
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function it_exchange_user_row_actions( $actions, $user_object ) {

		$actions['it_exchange'] = "<a class='it-exchange-cust-info' href='" . esc_url( add_query_arg( array( 'wp_http_referer' => urlencode( stripslashes_deep( $_SERVER['REQUEST_URI'] ) ), 'it_exchange_customer_data' => 1 ), get_edit_user_link( $user_object->ID ) ) ) . "'>" . __( 'Customer Data', 'it-l10n-ithemes-exchange' ) . "</a>";

		return $actions;
	}

	/**
	 * Adds iThemes Exchange User Meta page to user-edit.php
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function it_exchange_user_profile( $profileuser ) {

		if ( current_user_can('edit_users') )
			include( 'views/admin-user-profile.php' );

	}

	/**
	 * Adds iThemes Exchange User Meta page to user-edit.php
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function it_exchange_user_edit_load() {

		//A little hacky
		global $pagenow;

		if ( in_array( $pagenow, array( 'user-edit.php', 'profile.php' ) )
			&& !empty( $_REQUEST['it_exchange_customer_data'] )  && current_user_can('edit_users') ) {

			add_action( 'it_exchange_print_user_edit_page_tab_links', array( $this, 'print_products_user_edit_tab_link' ) );
			add_action( 'it_exchange_print_user_edit_page_tab_links', array( $this, 'print_transactions_user_edit_tab_link' ) );
			add_action( 'it_exchange_print_user_edit_page_tab_links', array( $this, 'print_info_user_edit_tab_link' ) );

			include( 'views/admin-user-edit.php' );
			include( ABSPATH . 'wp-admin/admin-footer.php');
			die();

		}

	}

	/**
	 * Save iThemes Exchange User Meta Options to user-edit.php
	 *
	 * @since 0.4.0
	 * @param int $user_id User ID of meta we're saving
	 * @return void
	*/
	function it_exchange_edit_user_profile_update( $user_id ) {
		if ( isset( $_REQUEST['it_exchange_customer_note'] ) )
			update_user_meta( $user_id, '_it_exchange_customer_note', $_REQUEST['it_exchange_customer_note'] );
	}

	/**
	 * Prints the tabs for the iThemes Exchange Add-ons Page
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function print_user_edit_page_tabs() {
		?>
		<h2 class="nav-tab-wrapper">
		<?php do_action( 'it_exchange_print_user_edit_page_tab_links', $this->_current_tab ); ?>
		</h2>
		<?php
	}

	/**
	 * Prints the products tab for the user-edit.php Page
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function print_products_user_edit_tab_link( $current_tab ) {
		$active = ( 'products' === $current_tab || false === $current_tab ) ? 'nav-tab-active' : '';
		?><a class="nav-tab <?php echo $active; ?>" href="<?php echo add_query_arg( 'tab', 'products' ); ?>#it-exchange-member-options"><?php _e( 'Products', 'it-l10n-ithemes-exchange' ); ?></a><?php
	}

	/**
	 * Prints the transactions tab for the user-edit.php Page
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function print_transactions_user_edit_tab_link( $current_tab ) {
		$active = 'transactions' == $current_tab ? 'nav-tab-active' : '';
		?><a class="nav-tab <?php echo $active; ?>" href="<?php echo add_query_arg( 'tab', 'transactions' ); ?>#it-exchange-member-options"><?php _e( 'Transactions', 'it-l10n-ithemes-exchange' ); ?></a><?php
	}

	/**
	 * Prints the info tab for the user-edit.php Page
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function print_info_user_edit_tab_link( $current_tab ) {
		$active = ( 'info' === $current_tab ) ? 'nav-tab-active' : '';
		?><a class="nav-tab <?php echo $active; ?>" href="<?php echo add_query_arg( 'tab', 'info' ); ?>#it-exchange-member-options"><?php _e( 'Info', 'it-l10n-ithemes-exchange' ); ?></a><?php
	}

	/**
	 * Sets the _current_page and _current_tab properties
	 *
	 * @since 0.3.4
	 * @return void
	*/
	function set_current_properties() {
		$this->_current_page = empty( $_GET['page'] ) ? false : $_GET['page'];
		$this->_current_tab = empty( $_GET['tab'] ) ? false : $_GET['tab'];
	}

	/**
	 * Adds the nag to the top of the admin screens if not complete
	 *
	 * @since 0.4.0
	*/
	function add_wizard_nag() {
		if ( ! empty( $_REQUEST['it_exchange_settings-dismiss-wizard-nag'] ) )
			update_option( 'it-exchange-hide-wizard-nag', true );

		if ( isset( $_GET['it-exchange-show-wizard-link'] ) )
			delete_option( 'it-exchange-hide-wizard-nag' );

		if ( true == (boolean) get_option( 'it-exchange-hide-wizard-nag' ) )
			return;

		if ( 'it-exchange-setup' != $this->_current_page )
			include( 'views/admin-wizard-notice.php' );
	}

	/**
	 * Adds the main iThemes Exchange menu item to the WP admin menu
	 *
	 * @since 0.2.0
	 * @return void
	*/
	function add_exchange_admin_menu() {
		// Add main iThemes Exchange menu item
		add_menu_page( 'iThemes Exchange', 'Exchange', $this->admin_menu_capability, 'it-exchange', array( $this, 'print_exchange_setup_page' ) );

		// Add setup wizard page without menu item unless we're viewing it.
		if ( 'it-exchange-setup' == $this->_current_page )
			add_submenu_page( 'it-exchange', 'iThemes Exchange Setup Wizard', 'Setup Wizard', $this->admin_menu_capability, 'it-exchange-setup', array( $this, 'print_exchange_setup_page' ) );

		// Add the product submenu pages depending on active product add-ons
		$this->add_product_submenus();

		// Add Transactions menu item
		add_submenu_page( 'it-exchange', 'iThemes Exchange ' . __( 'Payments', 'it-l10n-ithemes-exchange' ), __( 'Payments', 'it-l10n-ithemes-exchange' ), $this->admin_menu_capability, 'edit.php?post_type=it_exchange_tran' );

		// Remove default iThemes Exchange sub-menu item created with parent menu item
		remove_submenu_page( 'it-exchange', 'it-exchange' );
	}

	/**
	 * Adds the help menu at the bottom of the menu
	 *
	 * @since 0.4.17
	 *
	 * @return void
	*/
	function add_lower_priority_items_to_exchange_menu() {

		// Add Settings Menu Item
		$settings_callback = array( $this, 'print_exchange_settings_page' );
		if ( 'it-exchange-settings' == $this->_current_page && ! empty( $this->_current_tab ) )
			$settings_callback = apply_filters( 'it_exchange_general_settings_tab_callback_' . $this->_current_tab, $settings_callback );
		add_submenu_page( 'it-exchange', 'iThemes Exchange Settings', 'Settings', $this->admin_menu_capability, 'it-exchange-settings', $settings_callback );

		// Add Add-ons menu item
		$add_ons_callback = array( $this, 'print_exchange_add_ons_page' );
		if ( 'it-exchange-addons' == $this->_current_page && ! empty( $this->_current_tab ) ) {
			$add_ons_callback = apply_filters( 'it_exchange_add_ons_tab_callback_' . $this->_current_tab, $add_ons_callback );
		}
		if ( !empty( $_GET['add-on-settings'] ) && $addon = it_exchange_get_addon( $_GET['add-on-settings'] ) ) {
			if ( ! empty( $addon['options']['settings-callback'] ) && is_callable( $addon['options']['settings-callback'] ) )
				$add_ons_callback = $addon['options']['settings-callback'];
		}
		add_submenu_page( 'it-exchange', 'iThemes Exchange Add-ons', 'Add-ons', $this->admin_menu_capability, 'it-exchange-addons', $add_ons_callback );

		// Help menu
		add_submenu_page( 'it-exchange', __( 'Help', 'it-l10n-ithemes-exchange' ), __( 'Help', 'it-l10n-ithemes-exchange' ), $this->admin_menu_capability, 'it-exchange-help', array( $this, 'print_help_page' ) );
	}

	/**
	 * Adds the product submenus based on number of enabled product-type add-ons
	 *
	 * @since 0.3.0
	 * @return void
	*/
	function add_product_submenus() {
		// Check for enabled product add-ons. Don't need product pages if we don't have product add-ons enabled
		if ( $enabled_product_types = it_exchange_get_enabled_addons( array( 'category' => array( 'product-type' ) ) ) ) {
			$add_on_count = count( $enabled_product_types );
			add_submenu_page( 'it-exchange', 'All Products', 'All Products', $this->admin_menu_capability, 'edit.php?post_type=it_exchange_prod' );
			if ( 1 == $add_on_count ) {
				// If we only have one product-type enabled, add standard post_type pages
				$product = reset( $enabled_product_types );
				add_submenu_page( 'it-exchange', 'Add Product', 'Add Product', $this->admin_menu_capability, 'post-new.php?post_type=it_exchange_prod&it-exchange-product-type=' . $product['slug'] );
			} else if ( $add_on_count > 1 ) {
				// If we have more than one product type, add them each separately
				foreach( $enabled_product_types as $type => $params ) {
					$name = empty( $params['options']['labels']['singular_name'] ) ? 'Product' : esc_attr( $params['options']['labels']['singular_name'] );
					add_submenu_page( 'it-exchange', 'Add ' . $name, 'Add ' . $name, $this->admin_menu_capability, 'post-new.php?post_type=it_exchange_prod&it-exchange-product-type=' . esc_attr( $params['slug'] ) );
				}
			}
		}
	}

	/**
	 * Registers the callback for the email tab
	 *
	 * @param mixed default callback for general settings.
	 * @since 0.3.4
	 * @return mixed function or class method name
	*/
	function register_email_settings_tab_callback( $default ) {
		return array( $this, 'print_email_settings_page' );
	}

	/**
	 * Prints the email tab for general settings
	 *
	 * @since 0.3.4
	 * @param $current_tab the current tab
	 * @return void
	*/
	function print_email_settings_tab_link( $current_tab ) {
		$active = 'email' == $current_tab ? 'nav-tab-active' : '';
		?><a class="nav-tab <?php echo $active; ?>" href="<?php echo admin_url( 'admin.php?page=it-exchange-settings&tab=email' ); ?>"><?php _e( 'Email Settings', 'it-l10n-ithemes-exchange' ); ?></a><?php
	}

	/**
	 * Registers the callback for the pages tab
	 *
	 * @param mixed default callback for general settings.
	 * @since 0.3.7
	 * @return mixed function or class method name
	*/
	function register_pages_settings_tab_callback( $default ) {
		return array( $this, 'print_pages_settings_page' );
	}

	/**
	 * Prints the pages tab for general settings
	 *
	 * @since 0.3.7
	 * @param $current_tab the current tab
	 * @return void
	*/
	function print_pages_settings_tab_link( $current_tab ) {
		$active = 'pages' == $current_tab ? 'nav-tab-active' : '';
		?><a class="nav-tab <?php echo $active; ?>" href="<?php echo admin_url( 'admin.php?page=it-exchange-settings&tab=pages' ); ?>"><?php _e( 'Pages', 'it-l10n-ithemes-exchange' ); ?></a><?php
	}

	/**
	 * Prints the tabs for the iThemes Exchange General Settings
	 *
	 * @since 0.3.4
	 * @return void
	*/
	function print_general_settings_tabs() {
		$active = empty( $this->_current_tab ) ? 'nav-tab-active' : '';
		?>
		<h2 class="nav-tab-wrapper">
		<a class="nav-tab <?php echo $active; ?>" href="<?php echo admin_url( 'admin.php?page=it-exchange-settings' ); ?>"><?php _e( 'General', 'it-l10n-ithemes-exchange' ); ?></a>
		<?php do_action( 'it_exchange_print_general_settings_tab_links', $this->_current_tab ); ?>
		</h2>
		<?php
	}

	/**
	 * Prints the tabs for the iThemes Exchange Add-ons Page
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function print_add_ons_page_tabs() {
		$active = ( empty( $this->_current_tab ) || 'all' == $this->_current_tab ) ? 'nav-tab-active' : '';
		?>
		<h2 class="nav-tab-wrapper">
		<a class="nav-tab <?php echo $active; ?>" href="<?php echo admin_url( 'admin.php?page=it-exchange-addons' ); ?>"><?php _e( 'All', 'it-l10n-ithemes-exchange' ); ?></a>
		<?php do_action( 'it_exchange_print_add_ons_page_tab_links', $this->_current_tab ); ?>
		</h2>
		<?php
	}

	/**
	 * Prints the enabled tab for the Add-ons Page
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function print_enabled_add_ons_tab_link( $current_tab ) {
		$active = 'enabled' == $current_tab ? 'nav-tab-active' : '';
		?><a class="nav-tab <?php echo $active; ?>" href="<?php echo admin_url( 'admin.php?page=it-exchange-addons&tab=enabled' ); ?>"><?php _e( 'Enabled', 'it-l10n-ithemes-exchange' ); ?></a><?php
	}

	/**
	 * Prints the disabled tab for the Add-ons Page
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function print_disabled_add_ons_tab_link( $current_tab ) {
		$active = 'disabled' == $current_tab ? 'nav-tab-active' : '';
		?><a class="nav-tab <?php echo $active; ?>" href="<?php echo admin_url( 'admin.php?page=it-exchange-addons&tab=disabled' ); ?>"><?php _e( 'Disabled', 'it-l10n-ithemes-exchange' ); ?></a><?php
	}

	/**
	 * Registers the callback for the get more add-ons tab
	 *
	 * @param mixed default callback for add-ons page.
	 * @since 0.4.0
	 * @return mixed function or class method name
	*/
	function register_get_more_add_ons_tab_callback( $default ) {
		return array( $this, 'print_get_more_add_ons_page' );
	}

	/**
	 * Prints the enabled add ons page for iThemes Exchange
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function print_get_more_add_ons_page() {
		$add_on_cats = it_exchange_get_addon_categories();
		$message = empty( $_GET['message'] ) ? false : $_GET['message'];
		if ( 'installed' == $message )
			ITUtility::show_status_message( __( 'Add-on installed.', 'it-l10n-ithemes-exchange' ) );

		$error = empty( $_GET['error'] ) ? false : $_GET['error'];
		if ( 'installed' == $error )
			ITUtility::show_error_message( __( 'Error: Add-on not installed.', 'it-l10n-ithemes-exchange' ) );

		include( 'views/admin-get-more-addons.php' );
	}

	/**
	 * Prints the Get More tab for the Add-ons Page
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function print_get_more_add_ons_tab_link( $current_tab ) {
		$active = 'get-more' == $current_tab ? 'nav-tab-active' : '';
		?><a class="nav-tab <?php echo $active; ?>" href="<?php echo admin_url( 'admin.php?page=it-exchange-addons&tab=get-more' ); ?>"><?php _e( 'Get More', 'it-l10n-ithemes-exchange' ); ?></a><?php
	}

	/**
	 * Prints the help/support page for iThemes Exchange
	 *
	 * @since 0.4.14
	 * @return void
	*/
	function print_help_page() {
		include( 'views/admin-help.php' );
	}

	/**
	 * Prints the setup page for iThemes Exchange
	 *
	 * @since 0.2.0
	 * @return void
	*/
	function print_exchange_setup_page() {
		$flush_cache  = ! empty( $_POST );
		$settings     = it_exchange_get_option( 'settings_general', $flush_cache );
		$form_values  = empty( $this->error_message ) ? $settings : ITForm::get_post_data();
		$form         = new ITForm( $form_values, array( 'prefix' => 'it_exchange_settings' ) );
		$form_options = array(
			'id'      => apply_filters( 'it_exchange_settings_form_id', 'it-exchange-settings' ),
			'enctype' => apply_filters( 'it_exchange_settings_form_enctype', false ),
		);
		if ( $messages = it_exchange_get_messages( 'notice' ) ) {
			foreach( $messages as $notice ) {
				ITUtility::show_status_message( $notice );
			}
			it_exchange_clear_messages( 'notice' );
		}
		if ( $messages = it_exchange_get_messages( 'error' ) ) {
			foreach( $messages as $error ) {
				ITUtility::show_error_message( $error );
			}
			it_exchange_clear_messages( 'error' );
		}
		include( 'views/admin-wizard.php' );
	}

	/**
	 * Sets the general settings default values
	 *
	 * @since 0.3.7
	 * @return array
	*/
	function set_general_settings_defaults( $values ) {
		$defaults = array(
			'company-base-country'         => 'US',
			'company-base-state'           => 'OK',
			'store-product-order-by'       => 'date',
			'store-product-order'          => 'DESC',
			'default-currency'             => 'USD',
			'currency-symbol-position'     => 'before',
			'currency-thousands-separator' => ',',
			'currency-decimals-separator'  => '.',
			'site-registration'            => 'it',
			'enable-gallery-popup'         => '1',
			'enable-gallery-zoom'          => '1',
			'product-gallery-zoom-action'  => 'click',
			'checkout-reg-form'            => 'registration',
			'company-email'                => get_bloginfo( 'admin_email' ),
			'company-name'                 => wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
			'customer-account-page'        => 'Welcome to your account, [it_exchange_customer show="first-name"].<br />Using the links above, you can edit your profile and view your purchases and products.<br />Thanks for being a customer.'
		);
		$values = ITUtility::merge_defaults( $values, $defaults );
		return $values;
	}

	/**
	 * Sets the email settings default values
	 *
	 * static so we can access it from email_notifications w/o loading entire class
	 *
	 * @since 0.4.0
	 * @return array
	*/
	static function set_email_settings_defaults( $values ) {
		$defaults = array(
			'notification-email-address' => esc_attr( get_bloginfo( 'admin_email' ) ),
			'admin-email-address'      => esc_attr( get_bloginfo( 'admin_email' ) ),
			'admin-email-name'         => wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
			'admin-email-subject'      => sprintf( __( 'You made a sale! Yabba Dabba Doo! %s', 'it-l10n-ithemes-exchange' ), '[it_exchange_email show=receipt_id]' ),
			'admin-email-template'     => sprintf( __( "Your friend %s just bought all this awesomeness from your store!

Order: %s

%s", 'it-l10n-ithemes-exchange' ), '[it_exchange_email show=fullname]', '[it_exchange_email show=receipt_id]', '[it_exchange_email show=order_table]' ),
			'receipt-email-address'      => esc_attr( get_bloginfo( 'admin_email' ) ),
			'receipt-email-name'         => wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
			'receipt-email-subject'      => sprintf( __( 'Receipt for Purchase: %s', 'it-l10n-ithemes-exchange' ), '[it_exchange_email show=receipt_id]' ),
			'receipt-email-template'     => sprintf( __( "Hello %s,

Thank you for your order. Your order's details are below.
Order: %s

%s

%s", 'it-l10n-ithemes-exchange' ), '[it_exchange_email show=name]', '[it_exchange_email show=receipt_id]', '[it_exchange_email show=order_table options=purchase_message]', '[it_exchange_email show=download_list]' ),
		);
		$values = ITUtility::merge_defaults( $values, $defaults );
		return $values;
	}

	/**
	 * Prints the settings page for iThemes Exchange
	 *
	 * @since 0.3.4
	 * @return void
	*/
	function print_exchange_settings_page() {
		$flush_cache  = ! empty( $_POST );
		$settings     = it_exchange_get_option( 'settings_general', $flush_cache );
		$form_values  = empty( $this->error_message ) ? $settings : ITForm::get_post_data();
		$form         = new ITForm( $form_values, array( 'prefix' => 'it_exchange_settings' ) );
		$form_options = array(
			'id'      => apply_filters( 'it_exchange_settings_form_id', 'it-exchange-settings' ),
			'enctype' => apply_filters( 'it_exchange_settings_form_enctype', false ),
		);
		if ( ! empty ( $this->status_message ) )
			ITUtility::show_status_message( $this->status_message );
		if ( ! empty( $this->error_message ) )
			ITUtility::show_error_message( $this->error_message );
		include( 'views/admin-settings.php' );
	}

	/**
	 * Prints the email page for iThemes Exchange
	 *
	 * @since 0.3.4
	 * @return void
	*/
	function print_email_settings_page() {
		$flush_cache  = ! empty( $_POST );
		$settings     = it_exchange_get_option( 'settings_email', $flush_cache );
		$form_values  = empty( $this->error_message ) ? $settings : ITForm::get_post_data();
		$form         = new ITForm( $form_values, array( 'prefix' => 'it_exchange_email_settings' ) );
		$form_options = array(
			'id'      => apply_filters( 'it_exchange_email_settings_form_id', 'it-exchange-email-settings' ),
			'enctype' => apply_filters( 'it_exchange_email_settings_form_enctype', false ),
			'action'  => 'admin.php?page=it-exchange-settings&tab=email',
		);
		if ( ! empty ( $this->status_message ) )
			ITUtility::show_status_message( $this->status_message );
		if ( ! empty( $this->error_message ) )
			ITUtility::show_error_message( $this->error_message );
		include( 'views/admin-email-settings.php' );
	}

	/**
	 * Prints the pages page for iThemes Exchange
	 *
	 * @since 0.3.7
	 * @return void
	*/
	function print_pages_settings_page() {
		$flush_cache  = ! empty( $_POST );
		$pages    = it_exchange_get_pages( $flush_cache );
		$settings = array();

		// Setup form field values for each page
		foreach( $pages as $page => $data ) {
			$settings[$page . '-slug'] = it_exchange_get_page_slug( $page );
			$settings[$page . '-name'] = it_exchange_get_page_name( $page );
			$settings[$page . '-type'] = it_exchange_get_page_type( $page );
			$settings[$page . '-wpid'] = it_exchange_get_page_wpid( $page );
		}
		$form_values  = empty( $this->error_message ) ? $settings : ITForm::get_post_data();
		$form         = new ITForm( $form_values, array( 'prefix' => 'it_exchange_page_settings' ) );
		$form_options = array(
			'id'      => apply_filters( 'it_exchange_page_settings_form_id', 'it-exchange-page-settings' ),
			'enctype' => apply_filters( 'it_exchange_page_settings_form_enctype', false ),
			'action'  => 'admin.php?page=it-exchange-settings&tab=pages',
		);
		if ( ! empty ( $this->status_message ) )
			ITUtility::show_status_message( $this->status_message );
		if ( ! empty( $this->error_message ) )
			ITUtility::show_error_message( $this->error_message );
		include( 'views/admin-page-settings.php' );
	}

	/**
	 * Sets the Pages settings default values
	 *
	 * @since 0.4.0
	 * @return array
	*/
	function set_pages_settings_defaults( $values ) {
		$registered = it_exchange_get_registered_pages();
		$defaults = array();
		foreach( $registered as $page => $options ) {
			$defaults[$page . '-name'] = $options['name'];
			$defaults[$page . '-slug'] = $options['slug'];
			$defaults[$page . '-type'] = $options['type'];
			$defaults[$page . '-wpid'] = $options['wpid'];
		}
		$values = ITUtility::merge_defaults( $values, $defaults );
		return $values;
	}

	/**
	 * Prints the add-ons page in the admin area
	 *
	 * @since 0.2.0
	 * @return void
	*/
	function print_exchange_add_ons_page() {
		$add_on_cats = it_exchange_get_addon_categories();
		$message = empty( $_GET['message'] ) ? false : $_GET['message'];
		if ( 'enabled' == $message ) {
			ITUtility::show_status_message( __( 'Add-on enabled.', 'it-l10n-ithemes-exchange' ) );
		} else if ( 'disabled' == $message ) {
			ITUtility::show_status_message( __( 'Add-on disabled.', 'it-l10n-ithemes-exchange' ) );
		} else if ( 'addon-auto-disabled-' == substr( $message, 0, 20 ) ) {
			$addon_slug = substr( $message, 20 );
			$status_message = __( sprintf( 'iThemes Exchange has automatically disabled an add-on: %s. This is mostly likely due to it being uninstalled or improperlly registered.', $addon_slug ), 'it-l10n-ithemes-exchange' );
			ITUtility::show_status_message( $status_message );
		}

		$error= empty( $_GET['error'] ) ? false : $_GET['error'];
		if ( 'enabled' == $error )
			ITUtility::show_error_message( __( 'Error: Add-on not enabled.', 'it-l10n-ithemes-exchange' ) );
		else if ( 'disabled' == $error )
			ITUtility::show_error_message( __( 'Error: Add-on not disabled.', 'it-l10n-ithemes-exchange' ) );

		include( 'views/admin-add-ons.php' );
	}

	/**
	 * Enable all addons tagged as "required"
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function enable_required_add_ons() {
		$registered = it_exchange_get_addons();
		$enabled    = it_exchange_get_enabled_addons();

		foreach ( $registered as $slug => $params ) {
			if ( !empty( $params['options']['tag'] ) && 'required' === $params['options']['tag'] ) {
				if ( empty( $enabled[$slug] ) ) {
					$enabled_addon = it_exchange_enable_addon( $slug );
				}
			}
		}
	}

	/**
	 * Adds the it-exchange-admin CSS class to body on Exchange admin pages
	 *
	 * @since 0.4.17
	 *
	 * @param string $classes incoming classes from WP filter
	 * @return string
	*/
	function add_exchange_class_to_exchange_pages( $classes ) {
		if ( $this->is_exchange_admin_page() ) {
			$classes = explode( ' ', $classes );
			$classes[] = 'it-exchange-admin';
			$classes = implode( ' ', $classes );
		}
		return $classes;
	}

	/**
	 * Is this an exchange admin page?
	 *
	 * @since 0.4.17
	 *
	 * @return boolean
	*/
	function is_exchange_admin_page() {
		$is_exchange_admin = false;

		// Is this an exchange post type?
		$post_types = array(
			'it_exchange_prod',
			'it_exchange_tran',
			'it_exchange_coupon',
			'it_exchange_download',
		);
		if ( ! empty( $_GET['post_type'] ) && in_array( $_GET['post_type'], (array) apply_filters( 'it_exchange_post_types', $post_types ) ) )
			$is_exchange_admin = true;

		// Are we editing an exchange product type
		if ( ! empty( $_GET['action'] ) && 'edit' == $_GET['action'] && ! empty( $_GET['post'] ) && in_array( get_post_type( $_GET['post'] ), (array) apply_filters( 'it_exchange_post_types', $post_types ) ) )
			$is_exchange_admin = true;

		// Is this an Exchange page
		$pages = array(
			'it-exchange-settings',
			'it-exchange-addons',
			'it-exchange-help',
			'it-exchange-setup',
		);
		if ( ! empty( $this->_current_page ) && in_array( $this->_current_page, (array) apply_filters( 'it_exchange_admin_pages', $pages ) ) )
			$is_exchange_admin = true;

		return $is_exchange_admin;
	}

	/**
	 * Adds a registered Add-on to list of enabled add-ons
	 *
	 * @since 0.2.0
	*/
	function enable_disable_registered_add_on() {
		$enable_addon  = empty( $_GET['it-exchange-enable-addon'] ) ? false : $_GET['it-exchange-enable-addon'];
		$disable_addon = empty( $_GET['it-exchange-disable-addon'] ) ? false : $_GET['it-exchange-disable-addon'];
		$tab = empty( $_GET['tab'] ) ? false : $_GET['tab'];

		if ( ! $enable_addon && ! $disable_addon )
			return;

		$registered    = it_exchange_get_addons();

		// Enable or Disable addon requested by user
		if ( $enable_addon ) {
			if ( $nonce_valid = wp_verify_nonce( $_GET['_wpnonce'], 'exchange-enable-add-on' ) )
				$enabled = it_exchange_enable_addon( $enable_addon );
			$message = 'enabled';
		} else if ( $disable_addon ) {
			if ( $nonce_valid = wp_verify_nonce( $_GET['_wpnonce'], 'exchange-disable-add-on' ) )
				$enabled = it_exchange_disable_addon( $disable_addon );
			$message = 'disabled';
		}

		// Redirect if nonce not valid
		if ( ! $nonce_valid ) {
			wp_safe_redirect( admin_url( '/admin.php?page=it-exchange-addons&tab=' . $tab . '&error=' . $message ) );
			die();
		}

		// Disable any enabled add-ons that aren't registered any more while we're here.
		$enabled_addons = it_exchange_get_enabled_addons();
		foreach( (array) $enabled_addons as $slug => $params ) {
			if ( empty( $registered[$slug] ) )
				it_exchange_disable_addon( $slug );
		}

		$redirect_to = admin_url( '/admin.php?page=it-exchange-addons&tab=' . $tab . '&message=' . $message );

		// Redirect to settings page on activation if it exists
		if ( $enable_addon ) {
			if ( $enabled = it_exchange_get_addon( $enable_addon ) )  {
				if ( ! empty( $enabled['options']['settings-callback'] ) && is_callable( $enabled['options']['settings-callback'] ) )
					$redirect_to .= '&add-on-settings=' . $enable_addon;
			}
		}

		wp_safe_redirect( $redirect_to );
		die();
	}

	/**
	 * Opens the iThemes Exchange Admin Menu when viewing the Add New page
	 *
	 * @since 0.3.0
	 * @return string
	*/
	function open_exchange_menu_on_post_type_views( $parent_file, $revert=false ) {
		global $submenu_file, $pagenow, $post;

		if ( 'post-new.php' != $pagenow && 'post.php' != $pagenow )
			return $parent_file;

		if ( empty( $post->post_type ) || ( 'it_exchange_prod' != $post->post_type && 'it_exchange_tran' != $post->post_type ) )
			return $parent_file;

		// Set Add New as bold when on the post-new.php screen
		if ( 'post-new.php' == $pagenow ) {
			if ( ! empty( $_GET['it-exchange-product-type'] ) )
				$submenu_file = "post-new.php?post_type=it_exchange_prod&it-exchange-product-type={$_GET['it-exchange-product-type']}";
			else
				$submenu_file = 'it-exchange-choose-product-type';
		}

		// Return it-exchange as the parent (open) menu when on post-new.php and post.php for it_exchange_prod post_types
		return 'it-exchange';
	}

	/**
	 * Redirects post-new.php to it-exchange-choose-product-type when needed
	 *
	 * If we have landed on post-new.php?post_type=it_exchange_prod without the product_type param
	 * and with multiple product-type add-ons enabled.
	 *
	 * @since 0.3.1
	 * @return void
	*/
	function redirect_post_new_to_product_type_selection_screen() {
		global $pagenow;
		$product_type_add_ons = it_exchange_get_enabled_addons( array( 'category' => array( 'product-type' ) ) );
		$post_type            = empty( $_GET['post_type'] ) ? false : $_GET['post_type'];
		$product_type         = empty( $_GET['it-exchange-product-type'] ) ? false : $_GET['it-exchange-product-type'];

		if ( ! empty( $product_type ) )
			return;

		if ( count( $product_type_add_ons ) > 1 && 'post-new.php' == $pagenow && 'it_exchange_prod' == $post_type ) {
			$product_type_add_ons = reset( $product_type_add_ons );
			if ( ! empty( $product_type_add_ons['slug'] ) ) {
				wp_safe_redirect( add_query_arg( 'it-exchange-product-type', $product_type_add_ons['slug'] ) );
				die();
			}
		}
	}

	/**
	 * Prints select options for the currency type
	 *
	 * @since 0.3.6
	 * return array
	*/
	function get_default_currency_options() {
		$options = array();
		$currency_options = it_exchange_get_data_set( 'currencies' );
		foreach( (array) $currency_options as $cc => $currency ) {
			$options[$cc] = ucwords( $currency['name'] ) . ' (' . $currency['symbol'] . ')';
		}
		return $options;
	}

	/**
	 * AJAX callback for general settings country states UI
	 *
	 * @since 1.2.0
	 *
	 * @return void
	*/
	function update_country_state_ui_in_general_settings() {
		$base_country = empty( $_POST['ite_base_country_ajax'] ) ? 'US' : $_POST['ite_base_country_ajax'];
		$base_state   = empty( $_POST['ite_base_state_ajax'] ) ? 'OK' : $_POST['ite_base_state_ajax'];
		$states       = it_exchange_get_data_set( 'states', array( 'country' => $base_country ) );

		// Fire off one of the following two functions.
		if ( ! empty( $states ) )  {
		   ?>
			<select name="it_exchange_settings-company-base-state" id="company-base-state">
				<?php foreach( $states as $key => $value ) : ?>
					<option value="<?php esc_attr_e( $key ); ?>" <?php selected( $key, $base_state ); ?>><?php esc_attr_e( $value ); ?></option>
				<?php endforeach; ?>
			</select>
			<?php
			die();
		} else {
			?>
			<input class="small-text" max-length="3" type="text" name="it_exchange_settings-company-base-state" id="company-base-state" value="">
			<p class="description">
				<?php printf( __( 'Please use the 2-3 character %sISO abbreviation%s for country subdivisions', 'it-l10n-ithemes-exchange' ), '<a href="http://en.wikipedia.org/wiki/ISO_3166-2" target="_blank">', '</a>' ); ?>
			</p>
			<?php
			die();
		}
	}

	/**
	 * Save core general settings
	 *
	 * Validates data and saves to options.
	 *
	 * @since 0.3.4
	 *
	 * @return void
	*/
	function save_core_general_settings() {
		if ( empty( $_POST ) || 'it-exchange-settings' != $this->_current_page || ! empty( $this->_current_tab ) )
			return;

		$settings = wp_parse_args( ITForm::get_post_data(), it_exchange_get_option( 'settings_general' ) );

        // Check nonce
        if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'exchange-general-settings' ) ) {
            $this->error_message = __( 'Error. Please try again', 'it-l10n-ithemes-exchange' );
            return;
        }

		if ( ! empty( $this->error_message ) || $error_msg = $this->general_settings_are_invalid( $settings ) ) {
			if ( ! empty( $error_msg ) )
				$this->error_message = $error_msg;
		} else {
			it_exchange_save_option( 'settings_general', $settings );
			$this->status_message = __( 'Settings Saved.', 'it-l10n-ithemes-exchange' );
		}
	}

	/**
	 * Save core general settings from Wizard and performs action for other addons to handle saving
	 *
	 * Validates data and saves to options.
	 *
	 * @since 0.3.4
	 *
	 * @return void
	*/
	function save_core_wizard_settings() {

		// Abandon if not saving wizard
		if ( !( isset( $_REQUEST['it_exchange_settings-wizard-submitted'] ) && 'it-exchange-setup' === $this->_current_page ) )
			return;

		if ( empty( $_REQUEST['it-exchange-transaction-methods'] ) ) {
			it_exchange_add_message( 'error', __( 'You must select at least one Payment Method.', 'it-l10n-ithemes-exchange' ) );
			return;
		}

		// Grab general settings
		$general_settings = array();
		$default_wizard_general_settings = apply_filters( 'default_wizard_general_settings', array( 'company-email', 'default-currency' ) );

		foreach( $default_wizard_general_settings as $var ) {
			if ( isset( $_REQUEST['it_exchange_settings-' . $var] ) )
				$general_settings[$var] = $_REQUEST['it_exchange_settings-' . $var];
		}

		$settings = wp_parse_args( $general_settings, it_exchange_get_option( 'settings_general' ) );
		if ( it_exchange_has_messages( 'error' ) || $error_msg = $this->general_settings_are_invalid( $settings ) ) {
			if ( ! empty( $error_msg ) ) {
				it_exchange_add_message( 'error', $error_msg );
				return;
			}
		}

		// Save activated product types
		$product_addons = it_exchange_get_addons( array( 'category' => 'product-type' ) );

		// Filter out simple products
		if ( isset( $product_addons['simple-product-type'] ) )
			unset( $product_addons['simple-product-type'] );

		// Disable all of the product-type addons accept for simple
		foreach( $product_addons as $product_addon_slug => $options ) {
			it_exchange_disable_addon( $product_addon_slug );
		}

		// Loop through selected and renable them.
		if ( ! empty( $_REQUEST['it-exchange-product-types'] ) && is_array( $_REQUEST['it-exchange-product-types'] ) ) {
			foreach( $_REQUEST['it-exchange-product-types'] as $product_addon ) {
				it_exchange_enable_addon( $product_addon );
			}
		}

		// Handle core Simple Shipping addon settings here
		if ( empty( $_REQUEST['it-exchange-shipping-methods'] ) || ( ! in_array( 'simple-shipping-flat-rate', $_REQUEST['it-exchange-shipping-methods'] ) && ! in_array( 'simple-shipping-free', $_REQUEST['it-exchange-shipping-methods'] ) ) ) {
			it_exchange_disable_addon( 'simple-shipping' );
		} else {
			it_exchange_enable_addon( 'simple-shipping' );
			$simple_shipping_options = it_exchange_get_option( 'simple-shipping', true );
			$simple_shipping_options['enable-flat-rate-shipping'] = in_array( 'simple-shipping-flat-rate', $_REQUEST['it-exchange-shipping-methods'] );
			$simple_shipping_options['enable-free-shipping'] = in_array( 'simple-shipping-free', $_REQUEST['it-exchange-shipping-methods'] );

			if ( ! empty( $simple_shipping_options['enable-flat-rate-shipping'] ) ) {
				$flat_rate_default_cost = $_REQUEST['it_exchange_settings-simple-shipping-flat-rate-cost'];
				$simple_shipping_options['flat-rate-shipping-amount'] = $flat_rate_default_cost;
			}

			it_exchange_save_option( 'simple-shipping', $simple_shipping_options );
		}

		// Transaction Methods
		$tx_error_msgs = array();
		$addons = it_exchange_get_addons( array( 'category' => 'transaction-methods', 'show_required' => false ) );
		foreach( $_REQUEST['it-exchange-transaction-methods'] as $add_on ) {
			it_exchange_enable_addon( $add_on );
			unset( $addons[$add_on] );
			$tx_error_msgs = apply_filters( 'it_exchange_save_' . $add_on . '_wizard_settings', $tx_error_msgs );
		}
		foreach ( $addons as $addon ) {
			it_exchange_disable_addon( $addon['slug'] );
		}

		if ( ! empty( $tx_error_msgs ) ) {
			foreach( (array) $tx_error_msgs as $msg ) {
				it_exchange_add_message( 'error', $msg );
			}
			return;
		}

		// Signup for mailchimp if checkbox was checked
		if ( !empty( $_REQUEST['it_exchange_settings-exchange-notifications'] )
			&& !empty( $_REQUEST['it_exchange_settings-company-email'] ) ) {

			$this->mail_chimp_signup( $_REQUEST['it_exchange_settings-company-email'] );

		}

		// Auto enable any core add-ons we want enabled on setup.
		$auto_enabled_addons = array(
			'basic-reporting',
		);
		foreach( $auto_enabled_addons as $addon ) {
			it_exchange_enable_addon( $addon );
		}

		it_exchange_save_option( 'settings_general', $settings );

		do_action( 'it_exchange_enabled_addons_loaded' );
		do_action( 'it_exchange_save_wizard_settings' );


		$settings = it_exchange_get_option( 'settings_general', true );
		$sample_product = empty( $settings['sample-product-id'] ) ? false : it_exchange_get_product( $settings['sample-product-id'] );

		$settings_saved = __( 'Settings Saved. Congrats!', 'it-l10n-ithemes-exchange' );
		$add_product_link = sprintf( __( '%sAdd a Product%s', 'it-l10n-ithemes-exchange' ), '<a href="' . get_admin_url() . 'post-new.php?post_type=it_exchange_prod&it-exchange-product-type=digital-downloads-product-type">', '</a>' );
		$view_addons_link = sprintf( __( '%sEnable Add-ons%s', 'it-l10n-ithemes-exchange' ), '<a href="' . get_admin_url() . 'admin.php?page=it-exchange-addons">', '</a>' );

		$view_sample_link = empty( $sample_product->ID ) ? '' : ' | ' . sprintf( __( '%sView Sample Product%s', 'it-l10n-ithemes-exchange' ), '<a href="' . get_admin_url() . 'post.php?post=' . $sample_product->ID . '&action=edit">', '</a>' );

		it_exchange_add_message( 'notice', $settings_saved . ' ' . $add_product_link . ' | ' . $view_addons_link . $view_sample_link );

		wp_redirect( get_admin_url() . 'admin.php?page=it-exchange-setup&it_exchange_settings-dismiss-wizard-nag=1' );
		die();
	}

	public function mail_chimp_signup( $email ) {

		$email = trim( $email );

		if ( is_email( $email ) ) {

			$mailchimp = 'http://ithemes.us2.list-manage.com/subscribe/post?u=7acf83c7a47b32c740ad94a4e&amp;id=9da0741ac0';
			$query = array(
				'body' => array(
					'EMAIL' => $email,
				),
			);
			wp_remote_post( $mailchimp, $query );

		}

	}

	/**
	 * Validate general settings
	 *
	 * @since 0.3.6
	 * @param string $settings submitted settings
	 * @return false or error message
	*/
	function general_settings_are_invalid( $settings ) {
		$errors = array();
		if ( ! empty( $settings['company-email'] ) && ! is_email( $settings['company-email'] ) )
			$errors[] = __( 'Please provide a valid email address.', 'it-l10n-ithemes-exchange' );
		if ( empty( $settings['currency-thousands-separator'] ) )
			$errors[] = __( 'Thousands Separator cannot be empty', 'it-l10n-ithemes-exchange' );
		if ( empty( $settings['currency-decimals-separator'] ) )
			$errors[] = __( 'Decimals Separator cannot be empty', 'it-l10n-ithemes-exchange' );

		$errors = apply_filters( 'it_exchange_general_settings_validation_errors', $errors );
		if ( ! empty ( $errors ) )
			return implode( '<br />', $errors );
		else
			return false;
	}

	/**
	 * Save core email tab settings
	 *
	 * Validates data and saves to options.
	 *
	 * @since 0.3.4
	 * @return void
	*/
	function save_core_email_settings() {
		if ( empty( $_POST ) || 'it-exchange-settings' != $this->_current_page || 'email' != $this->_current_tab )
			return;

        // Check nonce
        if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'exchange-email-settings' ) ) {
            $this->error_message = __( 'Error. Please try again', 'it-l10n-ithemes-exchange' );
            return;
        }

		$settings = wp_parse_args( ITForm::get_post_data(), it_exchange_get_option( 'settings_email' ) );

		if ( ! empty( $this->error_message ) || $error_msg = $this->email_settings_are_invalid( $settings ) ) {
			if ( ! empty( $error_msg ) )
				$this->error_message = $error_msg;
		} else {
			it_exchange_save_option( 'settings_email', $settings );
			$this->status_message = __( 'Settings Saved.', 'it-l10n-ithemes-exchange' );
		}
	}

	/**
	 * Validate email settings
	 *
	 * @since 0.3.6
	 * @param string $settings submitted settings
	 * @return false or error message
	*/
	function email_settings_are_invalid( $settings ) {
		$errors = array();
		if ( empty( $settings['receipt-email-address'] )
			|| ( !empty( $settings['receipt-email-address'] ) && ! is_email( $settings['receipt-email-address'] ) ) )
			$errors[] = __( 'Please provide a valid email address.', 'it-l10n-ithemes-exchange' );
		if ( empty( $settings['receipt-email-name'] ) )
			$errors[] = __( 'Email Name cannot be empty', 'it-l10n-ithemes-exchange' );
		if ( empty( $settings['receipt-email-subject'] ) )
			$errors[] = __( 'Email Subject cannot be empty', 'it-l10n-ithemes-exchange' );
		if ( empty( $settings['receipt-email-template'] ) )
			$errors[] = __( 'Email Template cannot be empty', 'it-l10n-ithemes-exchange' );

		if ( !empty( $settings['notification-email-address'] ) ) {

			$emails = explode( ',', $settings['notification-email-address'] );

			foreach( $emails as $email ) {

				if ( !is_email( trim( $email ) ) ) {
					$errors[] = __( 'Invalid email address in Sales Notification Email Address', 'it-l10n-ithemes-exchange' );
					break;
				}

			}

		}

		$errors = apply_filters( 'it_exchange_email_settings_validation_errors', $errors );
		if ( ! empty ( $errors ) )
			return '<p>' . implode( '<br />', $errors ) . '</p>';
		else
			return false;
	}

	/**
	 * Save core pages tab settings
	 *
	 * Validates data and saves to options.
	 *
	 * @since 0.3.7
	 * @return void
	*/
	function save_core_page_settings() {
		if ( empty( $_POST ) || 'it-exchange-settings' != $this->_current_page || 'pages' != $this->_current_tab )
			return;

		// Grab page settings from DB
		$existing = it_exchange_get_pages( true );
		$settings = array();

		// Format for settings form
		foreach( $existing as $page => $data ) {
			$settings[$page . '-slug'] = it_exchange_get_page_slug( $page );
			$settings[$page . '-name'] = it_exchange_get_page_name( $page );
			$settings[$page . '-type'] = it_exchange_get_page_type( $page );
			$settings[$page . '-wpid'] = it_exchange_get_page_wpid( $page );
		}
		$settings = wp_parse_args( ITForm::get_post_data(), $settings );


		// If WordPress page is set to 0 somehow, use exchange page
		foreach( $existing as $page => $data ) {
			if ( 'wordpress' == $settings[$page . '-type'] && empty( $settings[$page . '-wpid'] ) )
				$settings[$page . '-type'] = 'exchange';
		}

        // Check nonce
        if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'exchange-page-settings' ) ) {
            $this->error_message = __( 'Error. Please try again', 'it-l10n-ithemes-exchange' );
            return;
        }

		// Trim all slug settings
		foreach( $settings as $key => $value ) {
			if ( 'slug' == substr( $key, -4 ) )
				$settings[$key] = sanitize_title( $value );
			else
				$settings[$key] = trim($value);
		}

		if ( ! empty( $this->error_message ) || $error_msg = $this->page_settings_are_invalid( $settings ) ) {
			if ( ! empty( $error_msg ) )
				$this->error_message = $error_msg;
		} else {
			it_exchange_save_option( 'settings_pages', $settings );
			$this->status_message = __( 'Settings Saved.', 'it-l10n-ithemes-exchange' );

			// Flag rewrites to be updated
			add_option( '_it-exchange-flush-rewrites', true );

			// Maybe update Ghost Page nav urls
			$this->maybe_update_ghost_pages_in_wp_nav_menus();
		}
	}

	/**
	 * Update URLs in nav menus
	 *
	 * If WP permalinks are updated or if Exchange page slugs are updated in settings, look for nav menu items, and update URLs
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function maybe_update_ghost_pages_in_wp_nav_menus() {
		// We can't depend on params passed by action because we call this from elsewhere as well
		$using_permalinks = (boolean) get_option( 'permalink_structure' );
		$pages = it_exchange_get_pages( true );
		$args = array(
			'post_type' => 'nav_menu_item',
			'posts_per_page' => -1,
			'meta_query' =>
				array(
					'key' => '_menu_item_xfn',
					'value' => 'it-exchange-',
					'compare' => 'LIKE',
				),
		);
		$nav_post_items = get_posts( $args );

		// Loop through found posts and see if URL has changed since it was created.
		$GLOBALS['it_exchange']['updating_nav'] = true;
		foreach( $nav_post_items as $key => $item ) {
			$page = get_post_meta( $item->ID, '_menu_item_xfn', true );

			// Meta Query is returning items where _menu_item_xfn isn't a post meta key
			if ( empty( $page ) )
				continue;

			$page = substr( $page, 12 );

			//if ( ! it_exchange_get_page_slug( $page ) )
			//	continue;

			$current_url = get_post_meta( $item->ID, '_menu_item_url', true );
			$page_url = it_exchange_get_page_url( $page, true );

			// If URL is different, update it.
			if ( $current_url != $page_url )
				update_post_meta( $item->ID, '_menu_item_url', $page_url );
		}
		$GLOBALS['it_exchange']['updating_nav'] = false;
	}

	/**
	 * Validate page settings
	 *
	 * @since 0.3.7
	 * @param string $settings submitted settings
	 * @return false or error message
	*/
	function page_settings_are_invalid( $settings ) {
		$errors = array();

		$used_slugs = array();
		foreach( $settings as $setting => $value ) {
			// Check for unique slugs
			if ( 'slug' == substr( $setting, -4 ) && in_array( $value, $used_slugs ) )
				$errors = array( __( 'Please make sure all your page slugs are unique.', 'it-l10n-ithemes-exchange' ) );
			else if ( 'exchange' == $settings[substr( $setting, 0, -4 ) . 'type'] )
				$used_slugs[] = $value;

			// *-wpid comes back as 0 when not set.
			if ( ! isset( $value ) || '' == $value ) {
				$errors = array( __( 'Page settings cannot be left blank.', 'it-l10n-ithemes-exchange' ) );
			}
		}

		$errors = apply_filters( 'it_exchange_page_settings_validation_errors', $errors );
		if ( ! empty ( $errors ) )
			return '<p>' . implode( '<br />', $errors ) . '</p>';
		else
			return false;
	}

	/**
	 * Set the max columns option for the add / edit product page.
	 *
	 * @since 0.4.0
	 *
	 * @param $columns Existing array of how many colunns to show for a post type
	 * @return array Filtered array
	*/
	function modify_add_edit_page_layout( $columns ) {
		$columns['it_exchange_prod'] = 2;
		return $columns;
	}

	/**
	 * Updates the user options for number of columns to use on add / edit product views
	 *
	 * @since 0.4.0
	 *
	 * @return 2
	*/
	function update_user_column_options( $existing ) {
		return 2;
	}

	/**
	 * Inits the scripts used by IT Exchange dashboard
	 *
	 * @since 0.4.0
	 * @param string $hook_suffix The current page hook we're on.
	 * @return void
	*/
	function it_exchange_admin_wp_enqueue_scripts( $hook_suffix ) {
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
			else
				$post_type = NULL;
		}

		wp_register_script( 'it-exchange-dialog', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/js/tips.js', array( 'jquery-ui-dialog' ) );
		
		if ( isset( $post_type ) && 'it_exchange_prod' === $post_type ) {
			$deps = array( 'post', 'jquery-ui-sortable', 'jquery-ui-droppable', 'jquery-ui-tabs', 'jquery-ui-tooltip', 'jquery-ui-datepicker', 'autosave', 'it-exchange-dialog' );
			wp_enqueue_script( 'it-exchange-add-edit-product', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/js/add-edit-product.js', $deps );
			wp_localize_script( 'it-exchange-add-edit-product', 'addEditProductL10n', array(
					'mediaManagerTitle'     => __( 'Select Images', 'it-l10n-ithemes-exchange' ),
					'largerThan150'         => __( 'You must upload an image larger than 150x150.', 'it-l10n-ithemes-exchange' ),
					'editMediaManagerTitle' => __( 'Edit Image', 'it-l10n-ithemes-exchange' ),
					'uploadSource'          => __( 'Upload Source', 'it-l10n-ithemes-exchange' ),
					'insert'                => __( 'Insert', 'it-l10n-ithemes-exchange' ),
					'advanced'              => __( 'Advanced', 'it-l10n-ithemes-exchange' ),
					'advanced_tooltip'      => __( 'We want to keep things simple for you, so we put the kitchen sink here. Click this button to add more info about your product.', 'it-l10n-ithemes-exchange' ),
				)
			);
		} else if ( isset( $post_type ) && 'it_exchange_tran' === $post_type && ! empty( $_GET['action'] ) && 'edit' == $_GET['action'] ) {
			$deps = array( 'jquery-ui-tooltip' );
			wp_enqueue_script( 'it-exchange-transaction-details', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/js/transaction-details.js', $deps );
		} else if ( 'exchange_page_it-exchange-addons' === $hook_suffix ) {
			$deps = array( 'jquery-ui-tooltip', 'jquery-ui-sortable' );
			wp_enqueue_script( 'it-exchange-add-ons', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/js/add-ons.js', $deps );
		} else if ( 'exchange_page_it-exchange-settings' === $hook_suffix ) {
			if ( empty( $_GET['tab'] ) ) {
				$deps = array( 'jquery-ui-tooltip', 'it-exchange-dialog' );
				wp_enqueue_script( 'it-exchange-settings-general', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/js/settings-general.js', $deps );
				wp_localize_script( 'it-exchange-settings-general', 'settingsGenearlL10n', array(
						'delteConfirmationText'  => __( 'You have checked the option to "Reset ALL data". Are you sure you want to delete all Exchange products, transactions, and settings?', 'it-l10n-ithemes-exchange' ),
					)
				);
			} else if ( $_GET['tab'] == 'pages') {
				$deps = array( 'jquery-ui-tooltip' );
				wp_enqueue_script( 'it-exchange-settings-pages', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/js/settings-pages.js', $deps );
				wp_localize_script( 'it-exchange-settings-pages', 'settingsPagesL10n', array(
						'emptyWPPage'  => __( 'Please make sure all WordPress page types have a WordPress page selected.', 'it-l10n-ithemes-exchange' ),
					)
				);
			} else if ( $_GET['tab'] == 'shipping') {
				$deps = array( 'jquery-ui-tooltip' );
				wp_enqueue_script( 'it-exchange-settings-pages', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/js/settings-pages.js', $deps );
			}
		} else if ( 'exchange_page_it-exchange-setup' === $hook_suffix ) {
			$deps = array( 'jquery-ui-tooltip' );
			wp_enqueue_script( 'it-exchange-wizard', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/js/wizard.js', $deps );
		} else if ( ( 'profile.php' === $hook_suffix || 'user-edit.php' === $hook_suffix ) && isset( $_REQUEST['it_exchange_customer_data'] ) ) {
			wp_enqueue_script( 'it-exchange-customer-info', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/js/customer-info.js' );
		} else if ( 'exchange_page_it-exchange-help' === $hook_suffix ) {
			$deps = array( 'jquery-ui-tooltip' );
			wp_enqueue_script( 'it-exchange-help', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/js/help.js', $deps );
		}
		do_action( 'it_exchange_admin_wp_enqueue_scripts', $hook_suffix, $post_type );
	}

	/**
	 * Inits the scripts used by IT Exchange dashboard
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function it_exchange_admin_wp_enqueue_styles() {
		global $hook_suffix, $wp_version;

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
			else
				$post_type = NULL;
		}

		// All WP Admin pages
		wp_enqueue_style( 'it-exchange-wp-admin', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/styles/wp-admin.css' );

		// All admin exchange pages
		if ( preg_match('|(it_exchange)|i', str_replace( '-', '_', $hook_suffix ) ) || ( isset( $post_type ) && preg_match('|(it_exchange)|i', str_replace( '-', '_', $post_type ) ) ) ) {
			wp_enqueue_style( 'it-exchange-exchange-only-admin', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/styles/exchange-admin.css' );
			
			if ( $wp_version <= 3.7 ) {
				wp_enqueue_style( 'it-exchange-exchange-only-admin-pre-3.8', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/styles/exchange-admin-pre-3.8.css' );
			}
		}

		// Specific Exchange pages
		if ( isset( $post_type ) && 'it_exchange_prod' === $post_type ) {
			wp_enqueue_style( 'it-exchange-add-edit-product', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/styles/add-edit-product.css' );
		} else if ( isset( $post_type ) && 'it_exchange_tran' === $post_type && ! empty( $_GET['action'] ) && 'edit' == $_GET['action'] ) {
			wp_enqueue_style( 'it-exchange-transaction-details', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/styles/transaction-details.css' );
		} else if ( 'exchange_page_it-exchange-addons' === $hook_suffix ) {
			wp_enqueue_style( 'it-exchange-add-ons', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/styles/add-ons.css' );
		} else if ( 'exchange_page_it-exchange-setup' === $hook_suffix ) {
			wp_enqueue_style( 'it-exchange-wizard', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/styles/wizard.css' );
		} else if ( 'exchange_page_it-exchange-settings' === $hook_suffix ) {
			wp_enqueue_style( 'it-exchange-settings', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/styles/settings.css' );
		} else if ( ( 'profile.php' === $hook_suffix || 'user-edit.php' === $hook_suffix ) && isset( $_REQUEST['it_exchange_customer_data'] ) ) {
			wp_enqueue_style( 'it-exchange-customer-info', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/styles/customer-info.css' );
		} else if ( 'index.php' === $hook_suffix ) {
			wp_enqueue_style( 'it-exchange-dashboard', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/styles/dashboard.css' );
		} else if ( 'exchange_page_it-exchange-help' === $hook_suffix ) {
			wp_enqueue_style( 'it-exchange-help', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/styles/help.css' );
		}
		do_action( 'it_exchange_admin_wp_enqueue_styles', $hook_suffix, $post_type );
	}

	/**
	 * Remvoe third party metaboxes if we absolutely have to blacklist them.
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function remove_third_party_metaboxes() {
		global $pagenow, $post;
		$post_type = empty( $_REQUEST['post_type'] ) ? false : $_REQUEST['post_type'];
		$post_type = empty( $post_type ) && ! empty( $_REQUEST['post'] ) ? $_REQUEST['post'] : $post_type;
		$post_type = is_numeric( $post_type ) ? get_post_type( $post_type ) : $post_type;

		// For Transaction Details Page
		if ( ( 'post-new.php' == $pagenow || 'post.php' == $pagenow ) && 'it_exchange_tran' == $post_type ) {
			// Remove builder meta box
			if ( 'builder' == strtolower( get_option( 'template' ) ) || ( isset( $GLOBALS['theme_index'] ) && 'it-builder' == $GLOBALS['theme_index'] ) )
				add_filter( 'builder_layout_filter_non_layout_post_types', array( $this, 'remove_builder_custom_layout_box' ) );
		}
	}

	/**
	 * Does not allow users to land on the add/edit page of a disabled product type
	 *
	 * @since 0.4.12
	 *
	 * @return void
	*/
	function bounce_user_to_all_products_if_directly_accessing_disabled_product_type() {
		global $pagenow, $post;
		$post_type = empty( $_REQUEST['post_type'] ) ? false : $_REQUEST['post_type'];
		$post_type = empty( $post_type ) && ! empty( $_REQUEST['post'] ) ? $_REQUEST['post'] : $post_type;
		$post_type = is_numeric( $post_type ) ? get_post_type( $post_type ) : $post_type;
		$redirect  = false;

		if ( ( 'post-new.php' != $pagenow && 'post.php' != $pagenow ) || 'it_exchange_prod' != $post_type )
			return;

		// Redirect if no product-type addons are enabled
		if ( ! $enabled_product_types = it_exchange_get_enabled_addons( array( 'category' => 'product-type' ) ) ) {
			$redirect = add_query_arg( 'page', 'it-exchange-settings', get_admin_url() . 'admin.php' );;
			wp_redirect( $redirect );
			die();
		}

		$enabled_product_types = array_keys( (array) $enabled_product_types );
		// Redirect if were creating a new product for a product type that's not enabled
		if ( 'post-new.php' == $pagenow && ! in_array( it_exchange_get_product_type(), $enabled_product_types ) && empty( $_POST ) && empty( $_GET['action'] ) )
			$redirect = add_query_arg( 'post_type', 'it_exchange_prod', get_admin_url() . 'edit.php' );

		// Redirect if on edit product that is part of a disabled product-type addon
		if ( 'post.php' == $pagenow && empty( $_POST ) && empty( $_GET['action'] ) || ( ! empty( $_GET['action'] ) && 'edit' == $_GET['action'] ) ) {
			$post_id = empty( $_GET['post'] ) ? false : $_GET['post'];
			if ( ! in_array( it_exchange_get_product_type( $post_id ), $enabled_product_types ) )
				$redirect = add_query_arg( 'post_type', 'it_exchange_prod', get_admin_url() . 'edit.php' );
		}

		if ( $redirect ) {
			wp_redirect( $redirect );
			die();
		}
	}

	/**
	 * Inits the add / edit product layout
	 *
	 * @since 0.4.0
	 * @param array $filter_var Don't modify this. Always return it.
	 * @return void
	*/
	function setup_add_edit_product_screen_layout() {
		global $pagenow, $post;
		$post_type = empty( $_REQUEST['post_type'] ) ? false : $_REQUEST['post_type'];
		$post_type = empty( $post_type ) && ! empty( $_REQUEST['post'] ) ? $_REQUEST['post'] : $post_type;
		$post_type = is_numeric( $post_type ) ? get_post_type( $post_type ) : $post_type;

		if ( ( 'post-new.php' != $pagenow && 'post.php' != $pagenow ) || 'it_exchange_prod' != $post_type )
			return;

		// Enqueue Media library scripts and styles
		wp_enqueue_media();

		// Remove screen options from products
		add_filter('screen_options_show_screen', '__return_false');

		// Adds class to wrap div
		add_action( 'admin_head', array( $this, 'add_edit_product_append_wrap_classes' ) );

		// Temporarially remove post support for post_formats and title
		add_filter( 'post_updated_messages', array( $this, 'temp_remove_theme_supports' ) );

		// Register layout metabox
		add_action( 'do_meta_boxes', array( $this, 'register_custom_layout_metabox' ), 999, 2 );

		// Setup custom add / edit product layout
		add_action( 'submitpost_box', array( $this, 'init_add_edit_product_screen_layout' ) );

	}

	/**
	 * Adds an additional class to the wrap div for add / edit products
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function add_edit_product_append_wrap_classes() {
		global $post_format_set_class;
		$classes = explode( ' ', $post_format_set_class );
		$classes[] = 'it-exchange-add-edit-product';
		$classes = array_filter( $classes );
		$post_format_set_class = implode( $classes );
	}

	/**
	 * Temporarily Remove support for post_formats and title
	 *
	 * @since 0.4.0
	 * @param array $messages We're hijacking a hook. Never modify. Always return
	 * @return void
	*/
	function temp_remove_theme_supports( $messages ) {
		$product_type = it_exchange_get_product_type();

		if ( it_exchange_product_type_supports_feature( $product_type, 'wp-post-formats' ) ) {
			it_exchange_remove_feature_support_for_product_type( 'wp-post-formats', $product_type );
			it_exchange_add_feature_support_to_product_type( 'temp_disabled_wp-post-formats', $product_type );
		}
		if ( it_exchange_product_type_supports_feature( $product_type, 'title' ) ) {
			it_exchange_remove_feature_support_for_product_type( 'title', $product_type );
			it_exchange_add_feature_support_to_product_type( 'temp_disabled_title', $product_type );
		}
		if ( it_exchange_product_type_supports_feature( $product_type, 'extended-description' ) ) {
			it_exchange_remove_feature_support_for_product_type( 'extended-description', $product_type );
			it_exchange_add_feature_support_to_product_type( 'temp_disabled_extended-description', $product_type );
		}
		return $messages;
	}

	/**
	 * Adds the custom layout metabox
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function register_custom_layout_metabox( $post_type, $context ) {
		if ( 'it_exchange_prod' != $post_type && 'side' != $context )
			return;

		$id       = 'it-exchange-add-edit-product-interface-main';
		$title    = __( 'Main Product Interface', 'it-l10n-ithemes-exchange' ); // Not used
		$callback = array( $this, 'do_add_edit_product_screen_layout_main' );
		add_meta_box( $id, $title, $callback, 'it_exchange_prod', 'normal', 'high' );

		$id       = 'it-exchange-add-edit-product-interface-side';
		$title    = __( 'Side Product Interface', 'it-l10n-ithemes-exchange' ); // Not used
		$callback = array( $this, 'do_add_edit_product_screen_layout_side' );
		add_meta_box( $id, $title, $callback, 'it_exchange_prod', 'side', 'high' );
	}

	/**
	 * Setup the custom screen by shifting meta boxes around in preparation for our meta box
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function init_add_edit_product_screen_layout() {
		global $wp_meta_boxes;
		$product_type = it_exchange_get_product_type();

		// Init it_exchange_advanced_low context
		$wp_meta_boxes['it_exchange_prod']['it_exchange_advanced_low'] = array();
		$custom_layout = array();

		// Remove our layout metaboxes
		if ( ! empty( $wp_meta_boxes['it_exchange_prod']['normal']['high']['it-exchange-add-edit-product-interface-main'] ) ) {
			$custom_layout_normal = $wp_meta_boxes['it_exchange_prod']['normal']['high']['it-exchange-add-edit-product-interface-main'];
			unset( $wp_meta_boxes['it_exchange_prod']['normal']['high']['it-exchange-add-edit-product-interface-main'] );
		}
		if ( ! empty( $wp_meta_boxes['it_exchange_prod']['side']['high']['it-exchange-add-edit-product-interface-side'] ) ) {
			$custom_layout_side = $wp_meta_boxes['it_exchange_prod']['side']['high']['it-exchange-add-edit-product-interface-side'];
			unset( $wp_meta_boxes['it_exchange_prod']['side']['high']['it-exchange-add-edit-product-interface-side'] );
		}

		// Loop through side, normal, and advanced contexts and move all metaboxes to it_exchange_advanced_low context
		foreach( array( 'side', 'normal', 'advanced' ) as $context ) {
			if ( ! empty ( $wp_meta_boxes['it_exchange_prod'][$context] ) ) {
				foreach( $wp_meta_boxes['it_exchange_prod'][$context] as $priority => $boxes ) {
					if ( ! isset( $wp_meta_boxes['it_exchange_prod']['it_exchange_advanced']['low'] ) )
						 $wp_meta_boxes['it_exchange_prod']['it_exchange_advanced']['low']= array();
					$wp_meta_boxes['it_exchange_prod']['it_exchange_advanced']['low'] = array_merge(
						$wp_meta_boxes['it_exchange_prod']['it_exchange_advanced']['low'],
						$wp_meta_boxes['it_exchange_prod'][$context][$priority]
					);
				}

				$wp_meta_boxes['it_exchange_prod'][$context] = array();
			}
		}

		// Add our custom layout back to normal/side high
		if ( ! empty( $custom_layout_normal ) )
			$wp_meta_boxes['it_exchange_prod']['normal']['high']['it-exchange-add-edit-product-interface-main'] = $custom_layout_normal;
		if ( ! empty( $custom_layout_side ) )
			$wp_meta_boxes['it_exchange_prod']['side']['high']['it-exchange-add-edit-product-interface-side'] = $custom_layout_side;

		update_user_option( get_current_user_id(), 'meta-box-order_it_exchange_prod', array() );


		// Add any temporarially disabled features back
		if ( it_exchange_product_type_supports_feature( $product_type, 'temp_disabled_wp-post-formats' ) ) {
			it_exchange_remove_feature_support_for_product_type( 'temp_disabled_wp-post-formats', $product_type );
			it_exchange_add_feature_support_to_product_type( 'wp-post-formats', $product_type );
		}
		if ( it_exchange_product_type_supports_feature( $product_type, 'temp_disabled_title' ) ) {
			it_exchange_remove_feature_support_for_product_type( 'temp_disabled_title', $product_type );
			it_exchange_add_feature_support_to_product_type( 'title', $product_type );
		}
		if ( it_exchange_product_type_supports_feature( $product_type, 'temp_disabled_extended-description' ) ) {
			it_exchange_remove_feature_support_for_product_type( 'temp_disabled_extended-description', $product_type );
			it_exchange_add_feature_support_to_product_type( 'extended-description', $product_type );
		}

		// Move Featured Image to top of side if supported
		if ( it_exchange_product_type_supports_feature( $product_type, 'featured-image' ) ) {
			remove_meta_box('postimagediv', 'it_exchange_prod', 'it_exchange_advanced' );
			add_meta_box('postimagediv', __('Featured Image'), 'post_thumbnail_meta_box', 'it_exchange_prod', 'it_exchange_side' );
		} else {
			remove_meta_box('postimagediv', 'it_exchange_prod', 'it_exchange_advanced' );
		}


		do_action( 'it_exchange_add_edit_product_screen_layout_setup', $product_type );
		do_action( 'it_exchange_add_edit_product_screen_layout_setup_for_' . $product_type, $product_type );
	}

	/**
	 * This prints the iThemes Exchange add / edit product custom layout interface (a fancy meta box)
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function do_add_edit_product_screen_layout_main( $post ) {
		do_meta_boxes( 'it_exchange_prod', 'it_exchange_normal', $post );
		do_meta_boxes( 'it_exchange_prod', 'it_exchange_advanced', $post );
	}

	/**
	 * This prints the iThemes Exchange a / edit product custom layout interface for the side column
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function do_add_edit_product_screen_layout_side( $post ) {
		do_meta_boxes( 'it_exchange_prod', 'it_exchange_side', $post );
	}

	/**
	 * Removed Quick Edit action from IT Exchange Post Types
	 *
	 * @since 0.4.0
	 *
	 * @return array
	*/
	function it_exchange_remove_quick_edit( $actions, $post ) {

		$it_exchange_post_types = apply_filters( 'it_exchange_remove_quick_edit_from_post_types',
			array(
				'it_exchange_download',
				'it_exchange_prod',
				'it_exchange_tran',
			)
		);

		if ( in_array( $post->post_type, $it_exchange_post_types ) )
			unset( $actions['inline hide-if-no-js'] ); //unset the Quick Edit action

		return $actions;
	}

	/**
	 * Add it_exchange_tran post type to Builder blacklist for Custom Layouts meta box
	 *
	 * @param array $post_types An array of post types that will not include the builder custom layout
	 * @since 0.4.0
	 * @return array
	*/
	function remove_builder_custom_layout_box( $post_types ) {
		$post_types[] = 'it_exchange_tran';
		return $post_types;
	}

	/**
	 * Expand the allowed mime types for Exchange products
	 *
	 * @since 1.0.1
	 *
	 * @param array $mime_types existing mime_types
	 * @reutrn array
	*/
	function uploads_mimes_for_products( $mime_types ) {
		if ( ! wp_get_referer() )
			return $mime_types;

		$url_parts = parse_url( wp_get_referer() );
		if ( empty( $url_parts['query'] ) )
			return $mime_types;

		$query_vars = array();
		parse_str( $url_parts['query'], $query_vars );

		if ( empty( $query_vars['post_type'] ) || 'it_exchange_prod' != $query_vars['post_type'] )
			return $mime_types;

		$additional_mime_types = array(
			'zip'  => 'application/zip',
			'epub' => 'application/epub+zip',
			'mobi' => 'application/x-mobipocket-ebook',
			'm4r'  => 'audio/aac',
			'psd'  => 'image/photoshop',
			'apk'  => 'application/vnd.android.package-archive',
			'msi'  => 'application/x-ole-storage',
		);
		$additional_mime_types = apply_filters( 'it_exchange_additional_mime_types', $additional_mime_types );

		$mime_types = array_merge( $mime_types, $additional_mime_types );
		return $mime_types;
	}
}
if ( is_admin() )
	$GLOBALS['IT_Exchange_Admin'] = new IT_Exchange_Admin( $this );
