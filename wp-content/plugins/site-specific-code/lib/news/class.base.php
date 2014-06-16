<?php

/**
 *
 * @package LDMW
 * @subpackage News
 * @since 5/19
 */
class LDMW_News_Base {
	/**
	 * Add our required actions
	 */
	public function __construct() {
		$this->register_featured_metabox();
		$this->register_misc_metabox();
	}

	/**
	 * Register our metabox that says whether this post is featured or not
	 */
	public function register_featured_metabox() {
		$fm = new Fieldmanager_Checkbox( array(
			'label' => 'Featured item?',
			'name'  => 'ldmw_featured'
		  )
		);

		$fm->add_meta_box( 'Featured', 'post' );
	}

	/**
	 * Register our metabox that contains miscellaneous information
	 */
	public function register_misc_metabox() {
		$fm = new Fieldmanager_Group( array(
			'name'        => 'ldmw_misc',
			'collapsible' => false,
			'children'    => array(
			  'pdf' => new Fieldmanager_Media( array(
				  'name'               => 'pdf',
				  'label'              => 'News PDF',
				  'description'        => 'Upload a PDF of the entire news item',
				  'button_label'       => 'Select PDF',
				  'modal_title'        => 'Choose a PDF',
				  'modal_button_label' => 'Choose PDF',
				)
			  )
			)
		  )
		);

		$fm->add_meta_box( 'News Information', 'post' );
	}

}