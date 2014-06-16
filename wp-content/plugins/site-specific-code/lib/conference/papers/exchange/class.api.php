<?php

/**
 *
 * @package Conference
 * @subpackage papers/Exchange
 * @since 5/29
 */
class IT_Theme_API_Papers implements IT_Theme_API {
	/**
	 * @var string
	 */
	private $_context = 'papers';

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
		$this->_article = $GLOBALS['it_exchange_paper_purchase'];
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
			$paper_products = get_user_meta( get_current_user_id(), '_ldmw_paper_products_purchased', true );
			$purchased = array();

			if ( is_array( $paper_products ) ) {
				foreach ( $paper_products as $jp ) {
					$articles = $this->get_articles_from_row( key( $jp ), $jp[key( $jp )] );
					$purchased = array_merge( $purchased, $articles );
				}
			}

			return count( $purchased ) > 0;
		}

		// If we made it here, we're doing a loop of transaction_products for the current query.
		// This will init/reset the transaction_products global and loop through them.
		if ( empty( $GLOBALS['it_exchange']['paper_purchases'] ) ) {
			$paper_products = get_user_meta( get_current_user_id(), '_ldmw_paper_products_purchased', true );
			$purchased = array();

			if ( is_array( $paper_products ) ) {
				foreach ( $paper_products as $jp ) {
					$articles = $this->get_articles_from_row( key( $jp ), $jp[key( $jp )] );
					$purchased = array_merge( $purchased, $articles );
				}
			}

			$purchased = $this->array_unique( $purchased );

			$GLOBALS['it_exchange']['paper_purchases'] = $purchased;
			$GLOBALS['it_exchange_paper_purchase'] = reset( $GLOBALS['it_exchange']['paper_purchases'] );

			return true;
		}
		else {
			if ( next( $GLOBALS['it_exchange']['paper_purchases'] ) ) {
				$GLOBALS['it_exchange_paper_purchase'] = current( $GLOBALS['it_exchange']['paper_purchases'] );

				return true;
			}
			else {
				$GLOBALS['it_exchange']['paper_purchases'] = array();
				end( $GLOBALS['it_exchange']['paper_purchases'] );
				$GLOBALS['it_exchange_paper_purchase'] = false;

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
			return array( get_post( $id ) );
		}
		elseif ( $type == 'conference' ) {
			remove_action( 'parse_query', array( 'TribeEventsQuery', 'parse_query' ), 50 );
			remove_action( 'pre_get_posts', array( 'TribeEventsQuery', 'pre_get_posts' ), 50 );
			$query = new WP_Query( array(
				'post_type'           => LDMW_Conference_Papers_Admin_CPT::$slug,
				'connected_type'      => LDMW_Conference_Papers_Admin_CPT::$connected_type,
				'connected_items'     => $id,
				'connected_direction' => 'to',
				'nopaging'            => true
			  )
			);

			$posts = $query->posts;

			add_action( 'parse_query', array( 'TribeEventsQuery', 'parse_query' ), 50 );
			add_action( 'pre_get_posts', array( 'TribeEventsQuery', 'pre_get_posts' ), 50 );

			return $posts;
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
		return LDMW_Conference_Papers_Display_Base::get_pdf_url_for_paper( $this->_article->ID );
	}

	/**
	 * Unique the array
	 *
	 * @param $array WP_Post[]
	 * @param bool $keep_key_assoc
	 *
	 * @return array
	 */
	protected function array_unique( $array, $keep_key_assoc = false ) {
		$duplicate_keys = array();
		$tmp = array();

		foreach ( $array as $key => $val ) {
			// convert objects to arrays, in_array() does not support objects
			if ( is_object( $val ) )
				$val = $val->ID;

			if ( !in_array( $val, $tmp ) )
				$tmp[] = $val;
			else
				$duplicate_keys[] = $key;
		}

		foreach ( $duplicate_keys as $key )
			unset( $array[$key] );

		return $keep_key_assoc ? $array : array_values( $array );
	}

}