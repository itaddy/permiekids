<?php

/**
 *
 * @package Journals
 * @subpackage Exchange
 * @since 5/15
 */
class IT_Theme_API_Journals implements IT_Theme_API {
	/**
	 * @var string
	 */
	private $_context = 'journals';

	/**
	 * @var array
	 */
	public $_tag_map = array(
	  'articles'    => 'articles',
	  'name'        => 'name',
	  'author'      => 'author',
	  'publishdate' => 'publish_date',
	  'view'        => 'view',
	  'download'    => 'download'
	);

	/**
	 * @var WP_Post
	 */
	private $_article;

	/**
	 *
	 */
	function __construct() {
		$this->_article = $GLOBALS['it_exchange_journal_purchase'];
	}

	/**
	 * Return the exchange context
	 *
	 * @return string
	 */
	function get_api_context() {
		return $this->_context;
	}

	/**
	 * Loop through articles purchased
	 *
	 * @param array $options
	 *
	 * @return bool
	 */
	public function articles( $options = array() ) {
		// Return boolean if has flag was set
		if ( $options['has'] ) {
			$journal_products = get_user_meta( get_current_user_id(), '_ldmw_journal_products_purchased', true );
			$purchased = array();

			if ( is_array( $journal_products ) ) {
				foreach ( $journal_products as $jp ) {
					$articles = $this->get_articles_from_row( key( $jp ), $jp[key( $jp )] );
					$purchased = array_merge( $purchased, $articles );
				}
			}

			return count( $purchased ) > 0;
		}

		// If we made it here, we're doing a loop of transaction_products for the current query.
		// This will init/reset the transaction_products global and loop through them.
		if ( empty( $GLOBALS['it_exchange']['journal_purchases'] ) ) {
			$journal_products = get_user_meta( get_current_user_id(), '_ldmw_journal_products_purchased', true );
			$purchased = array();

			if ( is_array( $journal_products ) ) {
				foreach ( $journal_products as $jp ) {
					$articles = $this->get_articles_from_row( key( $jp ), $jp[key( $jp )] );
					$purchased = array_merge( $purchased, $articles );
				}
			}

			$GLOBALS['it_exchange']['journal_purchases'] = $purchased;
			$GLOBALS['it_exchange_journal_purchase'] = reset( $GLOBALS['it_exchange']['journal_purchases'] );

			return true;
		}
		else {
			if ( next( $GLOBALS['it_exchange']['journal_purchases'] ) ) {
				$GLOBALS['it_exchange_journal_purchase'] = current( $GLOBALS['it_exchange']['journal_purchases'] );

				return true;
			}
			else {
				$GLOBALS['it_exchange']['journal_purchases'] = array();
				end( $GLOBALS['it_exchange']['journal_purchases'] );
				$GLOBALS['it_exchange_journal_purchase'] = false;

				return false;
			}
		}
	}

	/**
	 * @param $type string
	 * @param $id int
	 *
	 * @return WP_Post[] | null
	 */
	protected function get_articles_from_row( $type, $id ) {

		if ( $type == 'post' ) {
			return get_post( $id );
		}
		elseif ( $type == 'tax' ) {
			$query = new WP_Query( array(
				'post_type' => LDMW_Journal_Admin_CPT::$slug,
				'tax_query' => array(
				  'taxonomy' => LDMW_Journal_Admin_Taxonomy::$slug,
				  'field'    => 'term_id',
				  'terms'    => $id
				)
			  )
			);

			return $query->get_posts();
		}

		return null;
	}

	/**
	 * Return the title of the article
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function name( $options = array() ) {
		return $this->_article->post_title;
	}

	/**
	 * Return the author of an article
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function author( $options = array() ) {
		$author = $this->_article->post_author;
		$author = get_user_by( 'id', $author );

		return $author->display_name;
	}

	/**
	 * Return the publish date of the author
	 *
	 * By default m/d/y
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function publish_date( $options = array() ) {
		$defaults = array(
		  'date_format' => 'm/d/y'
		);

		$options = wp_parse_args( $options, $defaults );

		return ( new DateTime( $this->_article->post_date_gmt ) )->format( $options['date_format'] );
	}

	/**
	 * Get the URL to the article
	 *
	 * @param array $options
	 *
	 * @return bool|string
	 */
	public function view( $options = array() ) {
		return get_permalink( $this->_article );
	}

	/**
	 * Get the download URL
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function download( $options = array() ) {
		return LDMW_Journal_Display_Base::get_pdf_url_for_article( $this->_article->ID );
	}

}