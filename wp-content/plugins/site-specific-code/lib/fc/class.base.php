<?php

/**
 *
 * @package LDMW
 * @subpackage FC
 * @since 5/17
 */
class LDMW_FC_Base {

	/**
	 * @var string
	 */
	public static $cpt_slug = 'divisional-notices';

	/**
	 * @var string
	 */
	public static $tax_slug = 'divisions';

	/**
	 *
	 */
	public function __construct() {
		$this->register_post_type();
		$this->register_metabox();
		$this->register_taxonomy();
		add_filter( 'posts_clauses', array( $this, 'allow_orderby_tax' ), 10, 2 );
		add_action( 'pre_get_posts', array( $this, 'sort_notices_by_tax' ) );
	}

	/**
	 * Register the FC base post type
	 */
	public function register_post_type() {
		$labels = array(
		  'name'          => 'Divisional Notices',
		  'singular_name' => 'Divisional Notice',
		  'add_new_item'  => 'Add New Notice',
		  'menu_name'     => 'Division Notices'
		);

		$args = array(
		  'labels'      => $labels,
		  'public'      => true,
		  'has_archive' => true,
		  'rewrite'     => array(
			'with_front' => false,
			'slug'       => 'notices',
			'feeds'      => false,
		  ),
		  'supports'    => array( 'title', 'editor' )
		);

		register_post_type( self::$cpt_slug, $args );
	}

	/**
	 * Register our metabox for holding attachments
	 */
	public function register_metabox() {
		$fm = new Fieldmanager_Group( array(
			'name'           => 'ldmw_divisional_notices_data',
			'collapsible'    => true,
			'limit'          => 0,
			'starting_count' => 0,
			'add_more_label' => 'Add another',
			'sortable'       => true,
			'children'       => array(
			  'attachment' => new Fieldmanager_Media( array(
				  'name'               => 'attachment',
				  'label'              => 'Attachment',
				  'description'        => 'Upload any attachments to be displayed alongside this notice',
				  'button_label'       => 'Select Attachment',
				  'modal_title'        => 'Choose an Attachment',
				  'modal_button_label' => 'Choose Attachment',
				)
			  )
			)
		  )
		);

		$fm->add_meta_box( 'Notice Attachments', array( self::$cpt_slug ) );
	}

	/**
	 * Register our custom taxonomy
	 */
	public function register_taxonomy() {
		$labels = array(
		  'name'                       => 'Divisions',
		  'menu_name'                  => 'Divisions',
		  'singular_name'              => 'Division',
		  'search_items'               => 'Search Divisions',
		  'add_new_item'               => 'Add New Division',
		  'all_items'                  => 'All Divisions',
		  'parent_item'                => 'Division',
		  'parent_item_colon'          => 'Division:',
		  'edit_item'                  => 'Edit Division',
		  'view_item'                  => 'View Division',
		  'new_item_name'              => 'New Division',
		  'separate_items_with_commas' => 'Separate divisions with commas',
		  'add_or_remove_items'        => 'Add or remove divisions',
		  'choose_from_most_used'      => 'Choose from most used divisions',
		  'not_found'                  => 'No divisions found.'
		);

		$args = array(
		  'labels'       => $labels,
		  'slug'         => 'divisions',
		  'hierarchical' => true
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
	 * Sort notices by tax
	 *
	 * @param $wp_query WP_Query
	 */
	public function sort_notices_by_tax( $wp_query ) {
		if ( ! $wp_query->is_main_query() || is_admin() )
			return;

		if ( $wp_query->get( 'post_type' ) != self::$cpt_slug )
			return;

		$wp_query->query['orderby'] = self::$tax_slug;
		$wp_query->query['order'] = 'ASC';
	}
}