<?php

/**
 *
 * @package Journal
 * @subpackage Admin
 * @since 5/3
 */
class LDMW_Journal_Admin_CPT {

	/**
	 * @var string
	 */
	public static $slug = 'article';

	/**
	 * Kick off all of our CPT code
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
		  'name'          => 'Articles',
		  'singular_name' => 'Article',
		  'add_new_item'  => 'Add New Article'
		);

		$args = array(
		  'labels'      => $labels,
		  'public'      => true,
		  'has_archive' => false,
		  'rewrite'     => array(
			'with_front' => false,
			'slug'       => 'articles',
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
			'name'        => 'article',
			'collapsible' => false,
			'children'    => array(
			  'pdf'    => new Fieldmanager_Media( array(
				  'name'               => 'pdf',
				  'label'              => 'Article PDF',
				  'description'        => 'Upload a PDF of the entire article',
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

		$fm->add_meta_box( 'Article Information', array( self::$slug ) );
	}
}