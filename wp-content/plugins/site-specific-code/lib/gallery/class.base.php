<?php

/**
 *
 * @package LDMW
 * @subpackage Gallery
 * @since 5/20
 */
class LDMW_Gallery_Base {

	/**
	 * @var string
	 */
	public static $cpt_slug = 'gallery';

	/**
	 * @var string
	 */
	public static $tax_slug = 'events';

	/**
	 *
	 */
	public function __construct() {
		$this->register_post_type();
		$this->register_taxonomy();
		add_filter( 'posts_clauses', array( $this, 'allow_orderby_tax' ), 10, 2 );
		add_action( 'parse_query', array( $this, 'show_me_certain_tags' ) );
		add_action( 'pre_get_posts', array( $this, 'add_search' ) );
		add_filter( 'posts_search', array( $this, 'do_search' ), 10, 2 );
	}

	/**
	 * Register the FC base post type
	 */
	public function register_post_type() {
		$labels = array(
		  'name'          => 'Galleries',
		  'singular_name' => 'Gallery',
		  'add_new_item'  => 'Add New Event',
		  'menu_name'     => 'Event Photos'
		);

		$args = array(
		  'labels'      => $labels,
		  'public'      => true,
		  'has_archive' => true,
		  'rewrite'     => array(
			'with_front' => false,
			'slug'       => 'gallery',
			'feeds'      => false,
		  ),
		  'supports'    => array( 'title', 'editor', 'thumbnail', 'excerpt' )
		);

		register_post_type( self::$cpt_slug, $args );
	}

	/**
	 * Register our custom taxonomy
	 */
	public function register_taxonomy() {
		$labels = array(
		  'name'                       => 'Tags',
		  'menu_name'                  => 'Tags',
		  'singular_name'              => 'Tag',
		  'search_items'               => 'Search Tags',
		  'add_new_item'               => 'Add New Tag',
		  'all_items'                  => 'All Tags',
		  'parent_item'                => 'Tag',
		  'parent_item_colon'          => 'Tag:',
		  'edit_item'                  => 'Edit Tag',
		  'view_item'                  => 'View Tag',
		  'new_item_name'              => 'New Tag',
		  'separate_items_with_commas' => 'Separate tags with commas',
		  'add_or_remove_items'        => 'Add or remove tags',
		  'choose_from_most_used'      => 'Choose from most used tags',
		  'not_found'                  => 'No tags found.'
		);

		$args = array(
		  'labels'       => $labels,
		  'slug'         => 'tags',
		  'hierarchical' => false
		);

		register_taxonomy( self::$tax_slug, self::$cpt_slug, $args );
	}

	/**
	 * Allow for notices to be ordered by taxonomy
	 *
	 * @param $clauses array
	 * @param $wp_query WP_Query
	 *
	 * @return array
	 */
	public function allow_orderby_tax( $clauses, $wp_query ) {
		global $wpdb;

		if ( isset( $wp_query->query['orderby'] ) && self::$tax_slug == $wp_query->query['orderby'] ) {

			$clauses['join'] .= <<<SQL
LEFT OUTER JOIN {$wpdb->term_relationships} ON {$wpdb->posts}.ID={$wpdb->term_relationships}.object_id
LEFT OUTER JOIN {$wpdb->term_taxonomy} USING (term_taxonomy_id)
LEFT OUTER JOIN {$wpdb->terms} USING (term_id)
SQL;

			$clauses['where'] .= " AND (taxonomy = '" . self::$tax_slug . "' OR taxonomy IS NULL)";
			$clauses['groupby'] = "object_id";
			$clauses['orderby'] = "GROUP_CONCAT({$wpdb->terms}.name ORDER BY name ASC) ";
			$clauses['orderby'] .= ( 'ASC' == strtoupper( $wp_query->get( 'order' ) ) ) ? 'ASC' : 'DESC';
		}

		return $clauses;
	}

	/**
	 * Filter by tags
	 *
	 * @param $wp_query WP_Query
	 */
	public function show_me_certain_tags( $wp_query ) {
		if ( ! $wp_query->is_main_query() )
			return;

		if ( ! $wp_query->get( 'post_type' ) == self::$cpt_slug )
			return;

		if ( empty( $_GET['show_me'] ) )
			return;

		$wp_query->query['tag'] = ''; // init this variable for WP Query so it doesn't throw a isset error

		$wp_query->query['tax_query'] = array(
		  array(
			'taxonomy' => self::$tax_slug,
			'terms'    => (array) $_GET['show_me']
		  )
		);
		$wp_query->query_vars['tax_query'] = array(
		  array(
			'taxonomy' => self::$tax_slug,
			'terms'    => (array) $_GET['show_me']
		  )
		);
		$wp_query->parse_tax_query( $wp_query->query );
	}

	/**
	 * Add our search variable to the search in the WP Query
	 *
	 * @param $wp_query WP_Query
	 */
	public function add_search( $wp_query ) {

		if ( ! $wp_query->is_main_query() )
			return;

		if ( ! $wp_query->get( 'post_type' ) == self::$cpt_slug )
			return;

		if ( empty( $_GET['g_search'] ) )
			return;

		$wp_query->set( 's', esc_attr( $_GET['g_search'] ) );
	}

	/**
	 * Do search
	 *
	 * @param $search string
	 * @param $wp_query WP_Query
	 *
	 * @return string
	 */
	public function do_search( $search, $wp_query ) {
		if ( ! $wp_query->is_main_query() )
			return $search;

		if ( ! $wp_query->get( 'post_type' ) == self::$cpt_slug )
			return $search;

		if ( empty( $_GET['g_search'] ) )
			return $search;

		global $wpdb;

		$searchand = ' AND ';
		$n = ! empty( $wp_query->query_vars['exact'] ) ? '' : '%';
		$search = '';

		foreach ( $wp_query->query_vars['search_terms'] as $term ) {
			$term = like_escape( esc_sql( $term ) );

			$search .= "{$searchand}(($wpdb->posts.post_title LIKE '{$n}{$term}{$n}') OR ($wpdb->posts.post_content LIKE '{$n}{$term}{$n}') OR ($wpdb->posts.post_excerpt LIKE '{$n}{$term}{$n}'))";
		}

		return $search;
	}
}