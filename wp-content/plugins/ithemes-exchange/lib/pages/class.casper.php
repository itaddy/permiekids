<?php
/**
 * This file contains the class in charge of our ghost posts
 *
 * @since 0.4.0
 * @package IT_Exchange
*/

/**
 * Casper is our friendly ghost post.
 *
 * It is used when viewing a frontend iThemes Exchange view other than a single product.
 * It modifies some of the wp_query properties to fit our needs
 *
 * @since 0.4.0
*/
class IT_Exchange_Casper {

	/**
	 * @var string $_current_view the current iThemes Exchange frontend view. Should not ever be product.
	 * @since 0.4.0
	*/
	private $_current_view;

	/**
	 * Constructor. Sets $_current_view and $_wp_query properties.
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function IT_Exchange_Casper( $current_view ) {
		if ( 'product' == $current_view )
			throw new Exception( 'IT_Exchange_Casper should not be constructed when $current_view is "product".' );
		if ( 'exchange' != it_exchange_get_page_type( $current_view ) )
			throw new Exception( 'IT_Exchange_Casper should not be constructed when $current_view is not an "exchange" page type.' );

		$this->_current_view = $current_view;
		$this->_wp_query = $GLOBALS['wp_query'];
		$this->modify_wp_query();
	}

	/**
	 * Modifies the WP Query to our liking
	 *
	 * @since 0.4.0
	 *
	 * return void
	*/
	function modify_wp_query() {
		$this->_wp_query->posts_per_page = 1;
		$this->_wp_query->nopaging = true;
		$this->_wp_query->post_count = 1;

		// If we don't have a post, load an empty one
		if ( empty( $this->_wp_query->post ) )
			$this->_wp_query->post = new WP_Post( new stdClass() );

		$this->_wp_query->post->ID = 0;
		$this->_wp_query->post->post_date = current_time( 'mysql' );
		$this->_wp_query->post->post_date_gmt = current_time( 'mysql', 1 );
		$this->_wp_query->post->post_content = $this->get_content();
		$this->_wp_query->post->post_title = $this->get_title();
		$this->_wp_query->post->post_excerpt = '';
		$this->_wp_query->post->post_status = 'publish';
		$this->_wp_query->post->comment_status = false;
		$this->_wp_query->post->ping_status = false;
		$this->_wp_query->post->post_password = '';
		$this->_wp_query->post->post_name = 'it-exchange-ghost-' . $this->_current_view;
		$this->_wp_query->post->to_ping = '';
		$this->_wp_query->post->pinged = '';
		$this->_wp_query->post->post_modified = $this->_wp_query->post->post_date;
		$this->_wp_query->post->post_modified_gmt = $this->_wp_query->post->post_date_gmt;
		$this->_wp_query->post->post_content_filtered = '';
		$this->_wp_query->post->post_parent = 0;
		$this->_wp_query->post->guid = get_home_url() . '/' . $this->get_guid();
		$this->_wp_query->post->menu_order = 0;
		$this->_wp_query->post->post_type = 'page';
		$this->_wp_query->post->post_mime_type = '';
		$this->_wp_query->post->comment_count = 0;
		$this->_wp_query->post->filter = 'raw';

		$this->_wp_query->posts = array( $this->_wp_query->post );
		$this->_wp_query->found_posts = 1;
		$this->_wp_query->is_single = false; //false -- so comments_template() doesn't add comments
		$this->_wp_query->is_preview = false;
		$this->_wp_query->is_page = false; //false -- so comments_template() doesn't add comments
		$this->_wp_query->is_archive = false;
		$this->_wp_query->is_date = false;
		$this->_wp_query->is_year = false;
		$this->_wp_query->is_month = false;
		$this->_wp_query->is_day = false;
		$this->_wp_query->is_time = false;
		$this->_wp_query->is_author = false;
		$this->_wp_query->is_category = false;
		$this->_wp_query->is_tag = false;
		$this->_wp_query->is_tax = false;
		$this->_wp_query->is_search = false;
		$this->_wp_query->is_feed = false;
		$this->_wp_query->is_comment_feed = false;
		$this->_wp_query->is_trackback = false;
		$this->_wp_query->is_home = false;
		$this->_wp_query->is_404 = false;
		$this->_wp_query->is_comments_popup = false;
		$this->_wp_query->is_paged = false;
		$this->_wp_query->is_admin = false;
		$this->_wp_query->is_attachment = false;
		$this->_wp_query->is_singular = false;
		$this->_wp_query->is_posts_page = false;
		$this->_wp_query->is_post_type_archive = false;

		$GLOBALS['wp_query'] = $this->_wp_query;

	}

	/**
	 * Generates content for the current view
	 *
	 * @since 0.4.0
	 *
	 * @return string HTML
	*/
	function get_content() {
		ob_start();
		it_exchange_get_template_part( 'content', $this->_current_view );
		$content = ob_get_clean();
		return $content;
	}

	/**
	 * Generates a title for the ghost page
	 *
	 * @since 0.4.0
	 *
	 * @return string
	*/
	function get_title() {
		if ( ! $name = it_exchange_get_page_name( $this->_current_view ) )
			return '';
		return $name;
	}

	/**
	 * Creates a guid based on the current view
	 *
	 * @since 0.4.0
	 *
	 * @return string
	*/
	function get_guid() {
		return it_exchange_get_page_url( str_replace( '_', '-', $this->_current_view ) );
	}
}
