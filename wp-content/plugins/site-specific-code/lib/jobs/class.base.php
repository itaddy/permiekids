<?php

/**
 *
 * @package LDMW
 * @subpackage Jobs
 * @since 5/20
 */
class LDMW_Jobs_Base {

	public static $slug = 'jobs';

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
		  'name'          => 'Jobs',
		  'singular_name' => 'Job',
		  'add_new_item'  => 'Post New Job',
		  'menu_name'     => 'Jobs'
		);

		$args = array(
		  'labels'      => $labels,
		  'public'      => true,
		  'has_archive' => true,
		  'rewrite'     => array(
			'with_front' => false,
			'slug'       => 'jobs',
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
			'name'     => 'ldmw_jobs_data',
			'sortable' => false,
			'limit'    => 1,
			'children' => array(
			  'contact'     => new Fieldmanager_RichTextArea( "Contact Information" ),
			  'apply'       => new Fieldmanager_RichTextArea( "How to Apply" ),
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

		$fm->add_meta_box( 'Job Information', array( self::$slug ) );
	}
}