<?php

/**
 *
 * @package Conferences
 * @subpackage Papers
 * @since 5/29
 */
class LDMW_Conference_Papers_Admin_Base {
	/**
	 *
	 */
	public function __construct() {
		$this->register_rewrites();
		add_action( 'parse_query', array( $this, 'fix_connected_items_slug_to_id' ), 5 );
		add_filter( 'post_type_link', array( $this, 'fix_paper_url' ), 10, 2 );
		add_action( 'pre_get_posts', array( $this, 'force_archive' ) );
		add_action( 'pre_get_posts', array( $this, 'fix_search' ) );
		add_action( 'pre_get_posts', array( $this, 'allow_search_by_tax' ) );
		add_filter( 'posts_search', array( $this, 'allow_search_by_excerpt' ), 10, 2 );
	}

	/**
	 * Register rewrite rules
	 *
	 * /conferences/{conference-name}/papers/page/{page}
	 * /conferences/{conference-name}/papers/{paper-name}
	 */
	public function register_rewrites() {
		add_rewrite_rule( "^conferences/([^/]+)/papers/page/?([0-9]{1,})/?", 'index.php?connected_direction=to&connected_type=' . LDMW_Conference_Papers_Admin_CPT::$connected_type . '&connected_items=$matches[1]&post_type=' . LDMW_Conference_Papers_Admin_CPT::$slug . '&paged=$matches[2]', 'top' );
		add_rewrite_rule( "^conferences/([^/]+)/papers/([^/]+)/?", 'index.php?connected_direction=to&connected_type=' . LDMW_Conference_Papers_Admin_CPT::$connected_type . '&connected_items=$matches[1]&post_type=' . LDMW_Conference_Papers_Admin_CPT::$slug . '&' . LDMW_Conference_Papers_Admin_CPT::$slug . '=$matches[2]', 'top' );
		add_rewrite_rule( "^conferences/([^/]+)/papers/?", 'index.php?connected_direction=to&connected_type=' . LDMW_Conference_Papers_Admin_CPT::$connected_type . '&connected_items=$matches[1]&post_type=' . LDMW_Conference_Papers_Admin_CPT::$slug, 'top' );
	}

	/**
	 * Turn the connected_items into post slug into a ID
	 *
	 * @param $query WP_Query
	 */
	public function fix_connected_items_slug_to_id( $query ) {
		if ( !$query->is_main_query() )
			return;

		if ( is_admin() )
			return;

		if ( $query->get( 'connected_type' ) != LDMW_Conference_Papers_Admin_CPT::$connected_type )
			return;

		if ( is_numeric( $query->get( 'connected_items' ) ) || is_object( $query->get( 'connected_items' ) ) )
			return;

		$papers = get_posts( array( 'post_type' => LDMW_Conference_Papers_Admin_CPT::$slug, 'name' => $query->get( LDMW_Conference_Papers_Admin_CPT::$slug ) ) );

		if ( empty( $papers[0] ) )
			$query->set_404();

		$posts = get_posts( array(
			'name'            => $query->get( 'connected_items' ),
			'post_type'       => TribeEvents::POSTTYPE,
			'connected_type'  => LDMW_Conference_Papers_Admin_CPT::$connected_type,
			'connected_items' => $papers[0]->ID
		  )
		);

		if ( isset( $posts[0] ) )
			$query->set( 'connected_items', $posts[0]->ID );
		else
			$query->set_404();
	}

	/**
	 * @param $post_link string
	 * @param int $id
	 *
	 * @return string
	 */
	public function fix_paper_url( $post_link, $id = 0 ) {

		$post = get_post( $id );

		if ( is_wp_error( $post ) || LDMW_Conference_Papers_Admin_CPT::$slug != $post->post_type || empty( $post->post_name ) )
			return $post_link;

		$posts = get_posts( array( 'connected_type' => LDMW_Conference_Papers_Admin_CPT::$connected_type, 'connected_items' => $id ) );

		if ( empty( $posts[0] ) )
			return $post_link;

		$conference_name = $posts[0]->post_name;

		return home_url( user_trailingslashit( "conferences/$conference_name/papers/$post->post_name" ) );
	}

	/**
	 * Force the archive page
	 *
	 * @param $query WP_Query
	 */
	public function force_archive( $query ) {
		if ( !$query->is_main_query() )
			return;

		if ( $query->get( 'post_type' ) != LDMW_Conference_Papers_Admin_CPT::$slug )
			return;

		if ( $query->get( LDMW_Conference_Papers_Admin_CPT::$slug ) != '' )
			return;

		$query->is_archive = true;
		$query->is_post_type_archive = true;
		$query->is_404 = false;
	}

	/**
	 * Fix the search page
	 *
	 * @param $query WP_Query
	 */
	public function fix_search( $query ) {
		if ( !$query->is_main_query() || !$query->is_search() )
			return;

		if ( $query->query['post_type'] != LDMW_Conference_Papers_Admin_CPT::$slug )
			return;

		$query->set( 'post_type', LDMW_Conference_Papers_Admin_CPT::$slug );
	}

	/**
	 * Allow for searching by paper_theme taxonomy
	 *
	 * @param $query WP_Query
	 */
	public function allow_search_by_tax( $query ) {
		if ( !$query->is_main_query() )
			return;

		if ( $query->get( 'post_type' ) != LDMW_Conference_Papers_Admin_CPT::$slug )
			return;

		if ( $query->get( LDMW_Conference_Papers_Admin_CPT::$slug ) != '' )
			return;

		if ( empty( $_GET['paper_theme'] ) )
			return;

		$query->query['tag'] = ''; // init this variable for WP Query so it doesn't throw a isset error

		$query->query['tax_query'] = array(
		  array(
			'taxonomy' => LDMW_Conference_Papers_Admin_Taxonomy::$slug,
			'terms'    => (array) $_GET['paper_theme']
		  )
		);
		$query->query_vars['tax_query'] = array(
		  array(
			'taxonomy' => LDMW_Conference_Papers_Admin_Taxonomy::$slug,
			'terms'    => (array) $_GET['paper_theme']
		  )
		);
		$query->parse_tax_query( $query->query );
	}

	/**
	 * Allow for a search by an excerpt
	 *
	 * @param $search string
	 * @param $wp_query WP_Query
	 *
	 * @return string
	 */
	public function allow_search_by_excerpt( $search, $wp_query ) {
		if ( !$wp_query->is_main_query() )
			return $search;

		if ( !$wp_query->get( 'post_type' ) == LDMW_Conference_Papers_Admin_CPT::$slug )
			return $search;

		if ( $wp_query->get( LDMW_Conference_Papers_Admin_CPT::$slug ) != '' )
			return $search;

		global $wpdb;

		$searchand = ' AND ';
		$n = !empty( $wp_query->query_vars['exact'] ) ? '' : '%';
		$search = '';

		foreach ( $wp_query->query_vars['search_terms'] as $term ) {
			$term = like_escape( esc_sql( $term ) );

			$search .= "{$searchand}(($wpdb->posts.post_title LIKE '{$n}{$term}{$n}') OR ($wpdb->posts.post_content LIKE '{$n}{$term}{$n}') OR ($wpdb->posts.post_excerpt LIKE '{$n}{$term}{$n}'))";
		}

		return $search;
	}
}