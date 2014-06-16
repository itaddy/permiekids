<?php

/**
 *
 * @package LDMW
 * @subpackage Educational Opportunities
 * @since 5/19
 */
class LDMW_EO_Base {
	/**
	 * @var string
	 */
	public static $slug = 'edu-opps';

	/**
	 * Load necessary hooks for cpt
	 */
	public function __construct() {
		$this->register_post_type();
	}

	/**
	 * Register our custom post type
	 */
	public function register_post_type() {
		$labels = array(
		  'name'          => 'Educational Opportunities',
		  'singular_name' => 'Educational Opportunity',
		  'add_new_item'  => 'Add New Opportunity',
		  'menu_name'     => 'EDU Opps'
		);

		$args = array(
		  'labels'      => $labels,
		  'public'      => true,
		  'has_archive' => true,
		  'rewrite'     => array(
			'with_front' => false,
			'slug'       => 'educational-opportunities',
			'feeds'      => false,
		  ),
		  'supports'    => array( 'title', 'editor', 'excerpt' )
		);

		register_post_type( self::$slug, $args );
	}
}