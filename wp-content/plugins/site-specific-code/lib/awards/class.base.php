<?php

/**
 *
 * @package LDMW
 * @subpackage Awards
 * @since 5/20
 */
class LDMW_Awards_Base {
	public static $slug = 'awards';

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
		  'name'          => 'Awards',
		  'singular_name' => 'Award',
		  'add_new_item'  => 'Add New Award'
		);

		$args = array(
		  'labels'      => $labels,
		  'public'      => true,
		  'has_archive' => true,
		  'rewrite'     => array(
			'with_front' => false,
			'slug'       => 'awards',
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
			'name'     => 'ldmw_awards_data',
			'sortable' => false,
			'limit'    => 1,
			'children' => array(
			  'requirements'     => new Fieldmanager_RichTextArea( "Requirements" ),
			  'valid_applicants' => new Fieldmanager_RichTextArea( "Who can Apply" ),
			  'apply'            => new Fieldmanager_RichTextArea( "How to Apply" ),
			  'deadline'         => new Fieldmanager_Datepicker( "Deadline" ),
			  'contact'          => new Fieldmanager_RichTextArea( "Contact Information" ),
			  'attachments'      => new Fieldmanager_Group( array(
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

		$fm->add_meta_box( 'Award Information', array( self::$slug ) );
	}
}