<?php
/**
 * This file contains the class in charge of rewrites and template fetching
 *
 * @since 0.4.0
 * @package IT_Exchange
*/

/**
 * Pages Class. Registers rewrite rules and associated logic
 *
 * @since 0.4.0
*/
class IT_Exchange_Pages {

	/**
	 * @var $_account the WP username for the current user
	 * @since 0.4.0
	*/
	public $_account = false;

	/**
	 * @var string $_current_view the current Exchange frontend view
	 * @since 0.4.0
	*/
	public $_current_view = false;

	/**
	 * @var boolean $_pretty_permalinks are pretty permalinks set in WP Settings?
	 * @since 0.4.0
	*/
	public $_pretty_permalinks = false;

	/**
	 * Constructor
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function IT_Exchange_Pages() {
		add_action( 'init', array( $this, 'set_slugs_and_names' ) );
		add_action( 'init', array( $this, 'set_pretty_permalinks_boolean' ) );
		if ( is_admin() ) {
			add_filter( 'rewrite_rules_array', array( $this, 'register_rewrite_rules' ) );
		} else {
			add_action( 'template_redirect', array( $this, 'set_environment' ), 1 );
			add_action( 'template_redirect', array( $this, 'set_account' ), 2 );
			add_action( 'template_redirect', array( $this, 'registration_redirect' ), 9 );
			add_action( 'template_redirect', array( $this, 'login_out_page_redirect' ), 9 );
			add_action( 'template_redirect', array( $this, 'protect_pages' ), 11 );
			add_action( 'template_redirect', array( $this, 'prevent_empty_checkouts' ), 11 );
			add_action( 'template_redirect', array( $this, 'process_transaction' ), 12 );
			add_action( 'template_redirect', array( $this, 'set_wp_query_vars' ) );

			add_filter( 'query_vars', array( $this, 'register_query_vars' ) );
			add_filter( 'template_include', array( $this, 'fetch_template' ) );
			add_filter( 'template_include', array( $this, 'load_casper' ), 11 );
		}
	}

	/**
	 * Loads the slug properties from settings
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function set_slugs_and_names() {
		// registered pages
		$registered_pages = it_exchange_get_pages( false );
		foreach( (array) $registered_pages as $page => $data ) {
			$slug = '_' . $page . '_slug';
			$name = '_' . $page . '_name';
			$this->$slug = it_exchange_get_page_slug( $page );
			$this->$name = it_exchange_get_page_name( $page );
		}
	}

	/**
	 * Sets the pretty permalinks boolean
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function set_pretty_permalinks_boolean() {
		$permalinks = get_option( 'permalink_structure' );
		$this->_pretty_permalinks = ! empty( $permalinks );
	}

	/**
	 * Sets the environment based properties
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function set_environment() {
		$pages      = it_exchange_get_pages( false );
		foreach( (array) $pages as $page => $data ) {
			if ( 'product' == $page || 'disabled' == it_exchange_get_page_type( $page ) )
				continue;
			$property = '_is_' . $page;
			$this->$property = it_exchange_is_page( $page );
		}

		$post_type = get_query_var( 'post_type' );
		if ( (boolean) get_query_var( $this->_product_slug )
			|| ( !empty( $post_type ) && 'it_exchange_prod' === $post_type ) )
			$this->_is_product = true;
		else
			$this->_is_product = false;

		// Set current view property
		krsort( $pages );
		foreach( $pages as $page => $data ) {
			if ( 'disabled' == it_exchange_get_page_type( $page ) )
				continue;
			$property = '_is_' . $page;
			if ( $this->$property ) {
				$this->_current_view = $page;
				break;
			}
		}

		// Add hook for things that need to be done when on an exchange page
		if ( $this->_current_view )
			do_action( 'it_exchange_template_redirect', $this->_current_view );
	}

	/**
	 * Sets the account property based on current query_var or current user
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function set_account() {
		// Return if not viewing an account based page: account, profile, downloads, purchases, login
		$account_based_pages = array( 'account', 'profile', 'downloads', 'purchases' );
		$account_based_pages = apply_filters( 'it_exchange_account_based_pages', $account_based_pages );
		if ( ! in_array( $this->_current_view, $account_based_pages ) )
			return;

		$account = get_query_var( $this->_account_slug );

		if ( empty( $account ) || 1 == $account ) {

			$customer_id = get_current_user_id();

		} else if ( $account == (int)$account ) {

			$customer_id = $account;

		} else {

			if ( $customer = get_user_by( 'login', $account ) )
				$customer_id = $customer->ID;
			else
				$customer_id = false;

		}

		$this->_account = $customer_id;
		set_query_var( 'account', $customer_id );

	}

	/**
	 * Adds some custom query vars to WP_Query
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function set_wp_query_vars() {
		set_query_var( 'it_exchange_view', $this->_current_view );
	}

	/**
	 * Redirects users away from login page if they're already logged in
	 * or Redirects to /store/ if they log out.
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function login_out_page_redirect() {
		if ( is_user_logged_in() && 'login' == $this->_current_view ) {
			wp_redirect( it_exchange_get_page_url( 'profile' ) );
			die();
		} else if ( is_user_logged_in() && 'logout' == $this->_current_view ) {
			$default = 'disabled' == it_exchange_get_page_type( 'login' ) ? get_home_url() : str_replace( '&amp;', '&', wp_logout_url( it_exchange_get_page_url( 'login', false, true ) ) );
			$url = apply_filters( 'it_exchange_redirect_on_logout', $default );
			wp_redirect( $url );
			die();
		} else if ( ! is_user_logged_in() && 'logout' == $this->_current_view ) {
			wp_redirect( it_exchange_get_page_url( 'login' ) );
			die();
		}
	}

	/**
	 * Redirects users away from registration page if they're already logged in
	 * except for Administrators, because they might want to see the registration page.
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function registration_redirect() {
		if ( is_user_logged_in() && 'registration' == $this->_current_view
			&& ! current_user_can( 'administrator' ) ) {
			wp_redirect( it_exchange_get_page_url( 'profile' ) );
			die();
		}
	}

	/**
	 * Redirects users away from pages they don't have permission to view
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function protect_pages() {

		// Don't give access to single product views if product is disabled
		if ( 'product' == $this->_current_view ) {
			$enabled_product_types = it_exchange_get_enabled_addons( array( 'category' => 'product-type' ) );
			$product_type = it_exchange_get_product_type();
			if ( ! in_array( $product_type, array_keys( $enabled_product_types ) ) ) {
				wp_redirect( it_exchange_get_page_url( 'store' ) );
				die();
			}
		}

		// If user is an admin, abandon this. They can see it all
		if ( current_user_can( 'administrator' ) )
			return;

		// Set pages that we want to protect in one way or another
		$pages_to_protect = array(
			'account', 'profile', 'downloads', 'purchases', 'confirmation',
		);
		$pages_to_protect = apply_filters( 'it_exchange_pages_to_protect', $pages_to_protect );

		// Abandon if not a proteced page
		if ( ! in_array( $this->_current_view, $pages_to_protect ) )
			return;

		// If user isn't logged in, redirect
		if ( !is_user_logged_in() ) {
			if ( $this->_current_view != 'login' && $this->_current_view != 'registration' )
				it_exchange_add_session_data( 'login_redirect', it_exchange_get_page_url( $this->_current_view ) );
			$redirect_url = apply_filters( 'it_exchange_pages_to_protect_redirect_if_not_logged_in', it_exchange_get_page_url( 'registration' ) );
			wp_redirect( $redirect_url );
			die();
		} else if ( 'checkout' === $this->_current_view ) {
			return; //We just want to make sure users are logged in to see the checkout page
		}

		// Get current user
		$user_id = get_current_user_id();

		if ( 'confirmation' === $this->_current_view  ) {

			$transaction_id = false;
			$page_slug = it_exchange_get_page_slug( 'confirmation', true );

			if ( $transaction_hash = get_query_var( $page_slug ) )
				$transaction_id = it_exchange_get_transaction_id_from_hash( $transaction_hash );

			if ( !it_exchange_customer_has_transaction( $transaction_id, $user_id ) ) {
				$redirect_url = apply_filters( 'it_exchange_pages_to_protect_redirect_if_non_admin_requests_confirmation', it_exchange_get_page_url( 'purchases' ) );
				wp_redirect( $redirect_url );
				die();
			}

			return;

		}

		// If trying to view account and not an admin, and not the owner, redirect
		if ( in_array( $this->_current_view, $pages_to_protect )
				&& $this->_account != $user_id && ! current_user_can( 'administrator' ) ) {
			$redirect_url = apply_filters( 'it_exchange_pages_to_protect_redirect_if_non_admin_requests_account' , it_exchange_get_page_url( 'store' ) );
			wp_redirect( $redirect_url );
			die();
		}
		
		do_action( 'it_exchange_protect_pages' );
	}

	/**
	 * Redirect away from checkout if cart is empty
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function prevent_empty_checkouts() {
		if ( 'checkout' != $this->_current_view )
			return;

		if ( ! it_exchange_get_cart_products() ) {
			wp_redirect( it_exchange_get_page_url( 'cart' ) );
			die();
		}

	}

	/**
	 * Redirects users to confirmation page if the transaction was successful
	 * or to the checkout page if there was a failure.
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function process_transaction() {

		if ( 'transaction' == $this->_current_view ) {

			if ( is_user_logged_in() ) {
				$transaction_id = apply_filters( 'it_exchange_process_transaction', false );

				// If we made a transaction
				if ( $transaction_id ) {

					// Clear the cart
					it_exchange_empty_shopping_cart();

					// Grab the transaction confirmation URL. fall back to store if confirmation url fails
					$confirmation_url = it_exchange_get_transaction_confirmation_url( $transaction_id );
					if ( empty( $confirmation_url ) )
						$confirmation_url = it_exchange_get_page_url( 'store' );

					// Redirect
					wp_redirect( $confirmation_url );
					die();
				}
			}

			if ( it_exchange_is_multi_item_cart_allowed() ) {
				wp_redirect( it_exchange_get_page_url( 'checkout' ) );
			} else {
				$transaction_object = it_exchange_generate_transaction_object();
				if ( !empty( $transaction_object ) ) {
					foreach ( $transaction_object->products as $product ) {
						wp_redirect( get_permalink( $product['product_id'] ) );
					}
				} else {
					wp_redirect( it_exchange_get_page_url( 'store' ) );
				}
			}
			die();

		}

	}

	/**
	 * Determines which template file should be used for the current frontend view.
	 *
	 * If this is an Exchange view, look for the appropriate Exchange template in the users current theme.
	 * If an Exchange template is found in the theme, use the theme's page template and swap out our the_content for our template_parts
	 *
	 * @since 0.4.0
	 *
	 * @param the default template as determined by WordPress
	 * @return string a template file
	*/
	function fetch_template( $existing ) {

		// Return existing if this isn't an Exchange frontend view
		if ( ! $this->_current_view || 'exchange' != it_exchange_get_page_type( $this->_current_view ) )
			return $existing;

		// Set pages that we want to protect in one way or another
		$profile_pages = array(
			'account', 'profile', 'downloads', 'purchases',
		);
		$profile_pages = apply_filters( 'it_exchange_profile_pages', $profile_pages );

		if ( in_array( $this->_current_view, $profile_pages ) ) {
			if ( ! $this->_account )
				return get_404_template();
		}

		/**
		 * 1) If we found an iThemes Exchange Page Template in the theme's /exchange/ folder, return it.
		 * 2) If the found iThemes Exchange Theme Template has been filtered, return the filtered one instead and add the callback the_content filter
		 * -- In the event of option 2, this is working much like the 'product' == $this_current_view clase below would act with page.php
		*/
		if ( $template = it_exchange_locate_template( $this->_current_view . '.php' ) ) {
			remove_filter( 'the_content', 'wpautop' );
			$filtered_template = apply_filters( 'it_exchange_fetch_template_override_located_template', $template, $this );
			if ( $filtered_template != $template && 'product' == $this->_current_view ) {
				add_filter( 'the_content', array( $this, 'fallback_filter_for_page_template' ) );
				$template = $filtered_template;
			}

			return $template;
		}

		// If no iThemes Exchange template was found by it_exchange_location_template and we've viewing a product
		// then were'e going to need to set a filter
		if ( 'product' == $this->_current_view )
			add_filter( 'the_content', array( $this, 'fallback_filter_for_page_template' ) );

		// If no iThemes Exchange Template was found, use the theme's page template
		if ( $template = get_page_template() ) {
			$template = apply_filters( 'it_exchange_fetch_template_override_default_page_template', $template, $this );
			remove_filter( 'the_content', 'wpautop' );
			return $template;
		}

		// If nothing was found here, the theme has issues. Just return whatever template WP was going to use
		return $existing;
	}

	/**
	 * This loads our ghost post data and vars into the wp_query global when needed
	 *
	 * @since 0.4.0
	 *
	 * @param string $template We are hooking into a filter for an action. Always return value unchanged
	 * @return string
	*/
	function load_casper( $template ) {
		if ( $this->_current_view ) {
			if ( 'product' != $this->_current_view && 'exchange' == it_exchange_get_page_type( $this->_current_view ) ) {
				require( dirname( __FILE__ ) . '/class.casper.php' );
				new IT_Exchange_Casper( $this->_current_view );
			}
		}
		return $template;
	}

	/**
	 * This substitutes the themes content for our content-[$this->_current_view] template part.
	 *
	 * This only gets fired off if we couldn't find an exchange specific template file for the current view.
	 * If that happens, we use the theme's page.php template and filter the_content with our template part for that view.
	 *
	 * @since 0.4.0
	 *
	 * @param string $content exising default content
	 * @param string content generated from template part
	*/
	function fallback_filter_for_page_template( $content ) {
		$global_post = empty( $GLOBALS['post']->ID ) ? 0 : $GLOBALS['post']->ID;
		if ( ! it_exchange_get_product( $global_post ) )
			return $content;

		ob_start();
		add_filter( 'the_content', 'wpautop' );
		it_exchange_get_template_part( 'content', $this->_current_view );
		remove_filter( 'the_content', 'wpautop' );
		return ob_get_clean();
	}

	/**
	 * Registers our custom query vars with WordPress
	 *
	 * @since 0.4.0
	 *
	 * @param array $existing existing query vars
	 * @return array modified query vars
	*/
	function register_query_vars( $existing ) {
		$pages = it_exchange_get_pages( false );
		$vars  = array();

		foreach( $pages as $page => $data ) {
			if ( 'product' == $page || 'disabled' == it_exchange_get_page_type( $page ) )
				continue;
			if ( $var = it_exchange_get_page_slug( $page ) )
				$vars[] = $var;
		}
		$new_vars = array_merge( $vars, $existing );
		return $new_vars;
	}

	/**
	 * Registers our custom rewrite rules based on slug settings
	 *
	 * Loop through all the pages, grabbing their rewrite rules, grouped by order
	 * Then add to existing array of rewrites
	 *
	 * @since 0.4.0
	 *
	 * @param array $exisiting existing rewrite rules
	 * @return array modified rewrite rules
	*/
	function register_rewrite_rules( $existing ) {
		$this->set_slugs_and_names();

		// We only want pages that are exchange types for rewrites
		$pages = it_exchange_get_pages( true, array( 'type' => 'exchange' ) );
		$prioritized_rewrites = array();

		// Loop through and group rewrite callbacks by priority
		foreach( $pages as $page => $data ) {
			// Grab priority of rewrites and store in prioritized_rewrites array
			if ( ! empty( $data['rewrite-rules'] ) && is_array( $data['rewrite-rules'] ) ) {
				$priority = absint( $data['rewrite-rules'][0] );
				// Make sure priority key already exists
				if ( ! isset( $prioritized_rewrites[$priority] ) || ! is_array( $prioritized_rewrites[$priority] ) ) {
					$prioritized_rewrites[$priority] = array();
				}
				// Add rules for page to prioritized array
				if ( ! empty( $data['rewrite-rules'][1] ) && is_callable( $data['rewrite-rules'][1] ) ) {
					$rules = call_user_func( $data['rewrite-rules'][1], $page );
					if ( ! empty( $rules ) && is_array( $rules ) )
						$prioritized_rewrites[$priority][] = $rules;
				}
			}
		}

		// Reverse sort prioritized by keys
		krsort( $prioritized_rewrites );

		// Loop through priority array and apply rules
		foreach( $prioritized_rewrites as $priority => $rewrites ) {
			foreach( $rewrites as $rewrite ) {
				$existing = array_merge( $rewrite, $existing );
			}
		}

		// This is an exception for the confirmation page.
		if ( 'wordpress' == it_exchange_get_page_type( 'confirmation', true ) ) {
			$wpid = it_exchange_get_page_wpid( 'confirmation' );
	        if ( $wp_page = get_page( $wpid ) )
	            $page_slug = $wp_page->post_name;
	        else
	        	$page_slug = 'confirmation';
			
			$rewrite = array( $page_slug . '/([^/]+)/?$' => 'index.php?pagename=' . $page_slug . '&' . $page_slug . '=$matches[1]' );
			$existing = array_merge( $rewrite, $existing );
		}
		do_action( 'it_exchange_rewrite_rules_registered' );
		
		return $existing;
	}
}
global $IT_Exchange_Pages; // We need it inside casper
$IT_Exchange_Pages = new IT_Exchange_Pages();
