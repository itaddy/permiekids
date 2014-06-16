<?php

/**
 *
 * @package LDMW
 * @subpackage Research
 * @since 5/21
 */
class LDMW_Research_Base {

	/**
	 * @var string
	 */
	public static $slug = 'research';

	/**
	 *
	 */
	public function __construct() {
		$this->register_post_type();
		$this->register_metabox();
	}

	/**
	 * Register our custom post type
	 */
	public function register_post_type() {
		$labels = array(
		  'name'          => 'Research',
		  'singular_name' => 'Research',
		  'add_new_item'  => 'Add New Research Item'
		);

		$args = array(
		  'labels'      => $labels,
		  'public'      => true,
		  'has_archive' => true,
		  'rewrite'     => array(
			'with_front' => false,
			'slug'       => 'research',
			'feeds'      => false,
		  ),
		  'supports'    => array( 'title', 'editor', 'excerpt' )
		);

		register_post_type( self::$slug, $args );
	}

	/**
	 * Register our post meta box
	 */
	public function register_metabox() {
		$fm = new Fieldmanager_Group( array(
			'name'     => 'ldmw_research_data',
			'sortable' => false,
			'limit'    => 1,
			'children' => array(
			  'link'     => new Fieldmanager_Link( "External Link" ),
			  'attachments' => new Fieldmanager_Group( array(
				  'collapsible'    => true,
				  'limit'          => 0,
				  'starting_count' => 0,
				  'add_more_label' => 'Add another',
				  'sortable'       => true,
				  'children'       => array(
					'attachment' => new Fieldmanager_Media( array(
						'name'               => 'attachment',
						'label'              => 'Attachment',
						'description'        => 'Upload any related attachments',
						'button_label'       => 'Select Attachment',
						'modal_title'        => 'Choose an Attachment',
						'modal_button_label' => 'Choose Attachment',
					  )
					)
				  )
				)
			  )
			)
		  )
		);

		$fm->add_meta_box( 'Research Information', array( self::$slug ) );
	}
}