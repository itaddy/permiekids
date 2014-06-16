<?php

/**
 *
 * @package Conferences
 * @subpackage Papers/Admin
 * @since 5/29
 */
class LDMW_Conference_Papers_Admin_CPT {
	/**
	 * @var string
	 */
	public static $slug = 'c_paper';

	public static $connected_type = 'cpapers_to_conferences';

	/**
	 * Kick off all of our CPT code
	 */
	public function __construct() {
		$this->register_post_type();
		$this->register_metabox();
		add_action( 'p2p_init', array( $this, 'register_p2p_connection' ) );
	}

	/**
	 * Register our custom post type
	 */
	public function register_post_type() {
		$labels = array(
		  'name'          => 'Papers',
		  'singular_name' => 'Paper',
		  'add_new_item'  => 'Add New Paper'
		);

		$args = array(
		  'labels'      => $labels,
		  'public'      => true,
		  'has_archive' => false,
		  'rewrite'     => array(
			'with_front' => false,
			'slug'       => 'papers',
			'feeds'      => false,
		  ),
		  'supports'    => array( 'title', 'editor', 'excerpt', 'author' )
		);

		register_post_type( self::$slug, $args );
	}

	/**
	 * Register our post meta box
	 */
	public function register_metabox() {
		$fm = new Fieldmanager_Group( array(
			'name'        => 'cpaper',
			'collapsible' => false,
			'children'    => array(
			  'pdf'    => new Fieldmanager_Media( array(
				  'name'               => 'pdf',
				  'label'              => 'Paper PDF',
				  'description'        => 'Upload a PDF of the entire paper',
				  'button_label'       => 'Select PDF',
				  'modal_title'        => 'Choose a PDF',
				  'modal_button_label' => 'Choose PDF',
				)
			  ),
			  'author' => new Fieldmanager_AuthorField( array(
				  'label'       => 'Author',
				  'description' => 'To assign this post to an existing author, please use the author metabox above.'
				)
			  )
			)
		  )
		);

		$fm->add_meta_box( 'Paper Information', array( self::$slug ) );
	}

	/**
	 *
	 */
	public function register_p2p_connection() {
		p2p_register_connection_type( array(
			'name'        => self::$connected_type,
			'from'        => self::$slug,
			'to'          => TribeEvents::POSTTYPE,
			'cardinality' => 'many-to-one',
			'title'       => array(
			  'from' => 'Connected Conferences',
			  'to'   => 'Connected Papers'
			),
		  )
		);
	}
}