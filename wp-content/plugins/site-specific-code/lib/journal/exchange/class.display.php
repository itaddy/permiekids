<?php

/**
 *
 * @package Journal
 * @subpackage Exchange
 * @since 5/15
 */
class LDMW_Journal_Exchange_Display {

	/**
	 * @var string
	 */
	public static $page_slug = 'journals';

	/**
	 *
	 */
	public function __construct() {
		remove_filter( 'it_exchange_after_customer_menu_loop', 'it_exchange_membership_addon_append_to_customer_menu_loop' );
		add_filter( 'it_exchange_after_customer_menu_loop', array( $this, 'add_memberships_to_customer_menu_loop' ), 10, 2 );

		$this->register_journal_page();

		add_filter( 'it_exchange_is_page', array( $this, 'determine_if_journal_page' ), 10, 2 );

		add_filter( 'it_exchange_pages_to_protect', array( $this, 'add_journals_page_to_account' ) );
		add_filter( 'it_exchange_profile_pages', array( $this, 'add_journals_page_to_account' ) );
		add_filter( 'it_exchange_account_based_pages', array( $this, 'add_journals_page_to_account' ) );
		add_filter( 'it_exchange_customer_menu_pages', array( $this, 'add_journals_page_to_account_dashboard' ) );
	}

	/**
	 * Don't show journal related products in the exchange membership menu
	 *
	 * @param $nav string
	 * @param $customer IT_Exchange_Customer
	 *
	 * @return string
	 */
	public function add_memberships_to_customer_menu_loop( $nav, $customer ) {
		$memberships = it_exchange_get_session_data( 'parent_access' );
		$page_slug = 'memberships';
		$permalinks = (bool) get_option( 'permalink_structure' );

		if ( !empty( $memberships ) ) {
			foreach ( $memberships as $membership_id ) {
				if ( !empty( $membership_id ) ) {
					$membership_post = get_post( $membership_id );
					if ( !empty( $membership_post ) && "" == get_post_meta( $membership_id, '_ldmw_volume_id', true )
					  && "" == get_post_meta( $membership_id, '_ldmw_article_id', true )
					  && "" == get_post_meta( $membership_id, '_ldmw_paper_id', true )
					  && "" == get_post_meta( $membership_id, '_ldmw_conference_id', true ) ) {
						$membership_slug = $membership_post->post_name;

						$query_var = get_query_var( 'memberships' );

						$class = !empty( $query_var ) && $query_var == $membership_slug ? ' class="current"' : '';

						if ( $permalinks )
							$url = it_exchange_get_page_url( $page_slug ) . $membership_slug;
						else
							$url = it_exchange_get_page_url( $page_slug ) . '=' . $membership_slug;

						$nav .= '<li' . $class . '><a href="' . $url . '">' . get_the_title( $membership_id ) . '</a></li>';
					}
				}
			}
		}

		return $nav;
	}

	/**
	 * Register our journal page as a page in iThemes Exchange
	 */
	function register_journal_page() {
		$options = array(
		  'slug'          => self::$page_slug,
		  'name'          => 'Journals',
		  'rewrite-rules' => array( 130, array( $this, 'get_journal_page_rewrites' ) ),
		  'url'           => array( $this, 'get_journal_page_urls' ),
		  'settings-name' => 'Journals',
		  'tip'           => 'List of all purchased journals',
		  'type'          => 'exchange',
		  'menu'          => true,
		  'optional'      => true,
		);
		it_exchange_register_page( self::$page_slug, $options );
	}

	/**
	 * Returns rewrites for journals page in account dashboard
	 *
	 * @param $page string
	 *
	 * @return array|boolean
	 */
	public function get_journal_page_rewrites( $page ) {
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
	 * Returns URL for journals page
	 *
	 * @param $page string
	 *
	 * @return string
	 */
	public function get_journal_page_urls( $page ) {
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
	 * Determine if we are on the journal page
	 *
	 * @param $is_page null|bool
	 * @param $page string
	 *
	 * @return null|bool
	 */
	public function determine_if_journal_page( $is_page, $page ) {
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
	 * Add our journals account page to the addon pages
	 *
	 * @param $pages array
	 *
	 * @return array
	 */
	public function add_journals_page_to_account( $pages ) {
		$pages[] = self::$page_slug;

		return $pages;
	}

	/**
	 * Add the journals page to the account dashboard
	 * if they have purchased a journal product
	 *
	 * @param $pages array
	 *
	 * @return array
	 */
	public function add_journals_page_to_account_dashboard( $pages ) {
		$journal_products = get_user_meta( get_current_user_id(), '_ldmw_journal_products_purchased', true );

		if ( !empty( $journal_products ) )
			$pages[] = self::$page_slug;

		return $pages;
	}

}