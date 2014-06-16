<?php

/**
 *
 * @package Journal
 * @subpackage Admin
 * @since 5/3
 */
class LDMW_Journal_Admin_Base {
	/**
	 *
	 */
	public function __construct() {
		$this->include_taxonomy_in_article_url();
		add_filter( 'post_type_link', array( $this, 'fix_article_url' ), 10, 2 );
		add_action( 'pre_get_posts', array( $this, 'force_taxonomy_hierarchy' ) );
		add_action( 'pre_get_posts', array( $this, 'redirect_from_articles_to_journals' ) );
	}

	/**
	 * Include the entirety of the taxonomy hierarchy in the URL
	 *
	 * i.e. /journals/volume-name/issue-name/article-name/
	 */
	function include_taxonomy_in_article_url() {
		add_rewrite_rule( "^journals/uncategorized/([^/]+)/?", 'index.php?post_type=' . LDMW_Journal_Admin_CPT::$slug . '&' . LDMW_Journal_Admin_Taxonomy::$slug . '=uncategorized&' . LDMW_Journal_Admin_CPT::$slug . '=$matches[1]', 'top' );
		add_rewrite_rule( "^journals/([^/]+)/([^/]+)/page/?([0-9]{1,})/?", 'index.php?post_type=' . LDMW_Journal_Admin_CPT::$slug . '&' . LDMW_Journal_Admin_Taxonomy::$slug . '=$matches[2]&paged=$matches[3]', 'top' );
		add_rewrite_rule( "^journals/([^/]+)/([^/]+)/([^/]+)/?", 'index.php?post_type=' . LDMW_Journal_Admin_CPT::$slug . '&' . LDMW_Journal_Admin_Taxonomy::$slug . '=$matches[2]&' . LDMW_Journal_Admin_CPT::$slug . '=$matches[3]', 'top' );
	}

	/**
	 * Fix the post type link
	 *
	 * @param $post_link string
	 * @param int $id
	 *
	 * @return string|void
	 */
	function fix_article_url( $post_link, $id = 0 ) {

		$post = get_post( $id );

		if ( is_wp_error( $post ) || LDMW_Journal_Admin_CPT::$slug != $post->post_type || empty( $post->post_name ) )
			return $post_link;

		// Get the volume:
		$terms = get_the_terms( $post->ID, LDMW_Journal_Admin_Taxonomy::$slug );

		if ( is_wp_error( $terms ) || ! $terms ) {
			return home_url( user_trailingslashit( "journals/uncategorized/$post->post_name" ) );
		}
		else {
			$volume_object = array_pop( $terms );
			$volume = $volume_object->slug;

			$issue_object = array_pop( $terms );
			$issue = $issue_object->slug;
		}

		return home_url( user_trailingslashit( "journals/$volume/$issue/$post->post_name" ) );
	}

	/**
	 * Force the entirety of the taxonomy hierarchy in the URL
	 *
	 * @param $wp_query WP_Query
	 */
	public function force_taxonomy_hierarchy( $wp_query ) {
		if ( ! $wp_query->is_main_query() )
			return;

		if ( ! isset( $wp_query->query_vars['journal'] ) || ! isset( $wp_query->query['journal'] ) )
			return;

		if ( isset( $wp_query->query['article'] ) )
			return;

		if ( $wp_query->get( 'paged' ) ) {
			return;
		}
		// we are in a two step url /volume/issue
		if ( $wp_query->query_vars['journal'] != $wp_query->query['journal'] )
			return;

		$last_term = $wp_query->query_vars['journal'];
		$last_term = get_term_by( 'slug', $last_term, LDMW_Journal_Admin_Taxonomy::$slug );

		// if there is no parent, then this is a volume, so we can exit
		if ( $last_term->parent == 0 )
			return;

		$parent_term = get_term( $last_term->parent, LDMW_Journal_Admin_Taxonomy::$slug );

		if ( is_null( $parent_term ) || is_wp_error( $parent_term ) )
			return;

		$current_url = trailingslashit( $this->url_origin( $_SERVER ) );

		$parsed_url = @parse_url( $current_url );

		// separate all of the paths, and remove the empty elements on the beginning and end
		$path_parts = explode( "/", $parsed_url['path'] );
		array_pop( $path_parts );
		array_shift( $path_parts );

		// insert the parent term slug right after the /journals/ section
		$path_parts[- 1] = $path_parts[0];
		$path_parts[0] = $parent_term->slug;
		ksort( $path_parts );

		// rebuild the path
		$path = "/";
		$path .= implode( "/", $path_parts );
		$path = trailingslashit( $path );

		$parsed_url['path'] = $path;

		// rebuild the URL
		$new_url = http_build_url( $parsed_url );

		if ( $current_url == $new_url )
			return;

		wp_redirect( $new_url );
		exit;
	}

	/**
	 * Get the full current URL
	 *
	 * @param array $s $_SERVER
	 * @param bool $use_forwarded_host
	 *
	 * @return string
	 */
	protected function url_origin( $s, $use_forwarded_host = false ) {
		$ssl = ( ! empty( $s['HTTPS'] ) && $s['HTTPS'] == 'on' ) ? true : false;
		$sp = strtolower( $s['SERVER_PROTOCOL'] );
		$protocol = substr( $sp, 0, strpos( $sp, '/' ) ) . ( ( $ssl ) ? 's' : '' );
		$port = $s['SERVER_PORT'];
		$port = ( ( ! $ssl && $port == '80' ) || ( $ssl && $port == '443' ) ) ? '' : ':' . $port;
		$host = ( $use_forwarded_host && isset( $s['HTTP_X_FORWARDED_HOST'] ) ) ? $s['HTTP_X_FORWARDED_HOST'] : ( isset( $s['HTTP_HOST'] ) ? $s['HTTP_HOST'] : null );
		$host = isset( $host ) ? $host : $s['SERVER_NAME'] . $port;

		$url = $protocol . '://' . $host;

		return $url . $s['REQUEST_URI'];
	}

	/**
	 * Redirect away from the /articles/ page to the /journals/ page
	 *
	 * @param $wp_query WP_Query
	 */
	public function redirect_from_articles_to_journals( $wp_query ) {
		if ( $wp_query->is_main_query() === false )
			return;

		if ( isset( $wp_query->query['name'] ) && $wp_query->query['name'] == 'articles' && empty( $wp_query->query['page'] ) ) {
			wp_redirect( home_url( '/journals/' ) );
			exit();
		}
	}

}