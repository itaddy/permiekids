<?php
/**
 * 
 * @package Conferences
 * @subpackage Papers/Admin
 * @since 5/29
 */

class LDMW_Conference_Papers_Admin_Taxonomy
{
	/**
	 * @var string
	 */
	public static $slug = 'paper_themes';

	/**
	 * Kick off all of our taxonomy code
	 */
	public function __construct() {
		$this->register_taxonomy();
	}

	/**
	 * Register our journal taxonomy
	 */
	public function register_taxonomy() {
		$labels = array(
		  'name'                       => 'Themes',
		  'menu_name'                  => 'Themes',
		  'singular_name'              => 'Theme',
		  'search_items'               => 'Search Themes',
		  'add_new_item'               => 'Add New Theme',
		  'all_items'                  => 'All Themes',
		  'edit_item'                  => 'Edit Theme',
		  'view_item'                  => 'View Theme',
		  'new_item_name'              => 'New Theme',
		  'separate_items_with_commas' => 'Separate themes with commas',
		  'add_or_remove_items'        => 'Add or remove themes',
		  'choose_from_most_used'      => 'Choose from most used themes',
		  'not_found'                  => 'No themes'
		);

		$args = array(
		  'labels'       => $labels,
		  'slug'         => self::$slug,
		  'hierarchical' => false,
		  'rewrite'      => array(
			'with_front'   => false,
			'slug'         => 'themes',
			'hierarchical' => false,
		  )
		);

		register_taxonomy( self::$slug, LDMW_Conference_Papers_Admin_CPT::$slug, $args );
	}
}