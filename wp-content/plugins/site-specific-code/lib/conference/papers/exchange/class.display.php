<?php

/**
 *
 * @package Conferences
 * @subpackage Papers/Exchange
 * @since 5/29
 */
class LDMW_Conference_Papers_Exchange_Display {

	/**
	 * @var string
	 */
	public static $page_slug = 'papers';

	/**
	 *
	 */
	public function __construct() {
		$this->register_paper_page();

		add_filter( 'it_exchange_is_page', array( $this, 'determine_if_paper_page' ), 10, 2 );

		add_filter( 'it_exchange_pages_to_protect', array( $this, 'add_papers_page_to_account' ) );
		add_filter( 'it_exchange_profile_pages', array( $this, 'add_papers_page_to_account' ) );
		add_filter( 'it_exchange_account_based_pages', array( $this, 'add_papers_page_to_account' ) );
		add_filter( 'it_exchange_customer_menu_pages', array( $this, 'add_papers_page_to_account_dashboard' ) );
	}

	/**
	 * Register our paper page as a page in iThemes Exchange
	 */
	function register_paper_page() {
		$options = array(
		  'slug'          => self::$page_slug,
		  'name'          => 'Papers',
		  'rewrite-rules' => array( 130, array( $this, 'get_paper_page_rewrites' ) ),
		  'url'           => array( $this, 'get_paper_page_urls' ),
		  'settings-name' => 'papers',
		  'tip'           => 'List of all purchased papers',
		  'type'          => 'exchange',
		  'menu'          => true,
		  'optional'      => true,
		);
		it_exchange_register_page( self::$page_slug, $options );
	}

	/**
	 * Returns rewrites for papers page in account dashboard
	 *
	 * @param $page string
	 *
	 * @return array|boolean
	 */
	public function get_paper_page_rewrites( $page ) {
		$slug = it_exchange_get_page_slug( $page );

		if ( $page == self::$page_slug ) {
			$account_slug = it_exchange_get_page_slug( 'account' );

			$rewrites = array(
			  $account_slug . '/([^/]+)/' . $slug => 'index.php?' . $account_slug . '=$matches[1]&' . $slug . '=1',
			  $account_slug . '/' . $slug . '$'   => 'index.php?' . $account_slug . '=1&' . $slug . '=1',
			);

			return $rewrites;
		}

		return false;
	}

	/**
	 * Returns URL for papers page
	 *
	 * @param $page string
	 *
	 * @return string
	 */
	public function get_paper_page_urls( $page ) {
		$slug = it_exchange_get_page_slug( $page );
		$base = trailingslashit( get_home_url() );

		if ( $page == self::$page_slug ) {
			$account_slug = it_exchange_get_page_slug( 'account' );
			$base = trailingslashit( $base . $account_slug );

			$account_name = get_query_var( 'account' );
			if ( $account_name && '1' != $account_name && ( 'login' != $page && 'logout' != $page ) ) {
				$base = trailingslashit( $base . $account_name );
			}

			return trailingslashit( $base . $slug );
		}

		return false;
	}

	/**
	 * Determine if we are on the paper page
	 *
	 * @param $is_page null|bool
	 * @param $page string
	 *
	 * @return null|bool
	 */
	public function determine_if_paper_page( $is_page, $page ) {
		if ( $page != self::$page_slug )
			return $is_page;

		global $wp_query;

		if ( $wp_query->get( self::$page_slug ) ) {
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * Add our papers account page to the addon pages
	 *
	 * @param $pages array
	 *
	 * @return array
	 */
	public function add_papers_page_to_account( $pages ) {
		$pages[] = self::$page_slug;

		return $pages;
	}

	/**
	 * Add the papers page to the account dashboard
	 * if they have purchased a paper product
	 *
	 * @param $pages array
	 *
	 * @return array
	 */
	public function add_papers_page_to_account_dashboard( $pages ) {
		$paper_products = get_user_meta( get_current_user_id(), '_ldmw_paper_products_purchased', true );

		if ( !empty( $paper_products ) )
			$pages[] = self::$page_slug;

		return $pages;
	}
}