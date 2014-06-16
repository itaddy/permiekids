<?php

/**
 *
 * @package Conferences
 * @subpackage Tribe
 * @since 5/29
 */
class LDMW_Conference_TEC_Base {

	/**
	 * Responsible for registering admin metaboxes
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'register_attendees_metabox' ) );
	}

	/**
	 * Register metabox that allows access to attendees list
	 */
	public function register_attendees_metabox() {
		if ( current_user_can( 'list_users' ) )
			add_meta_box( 'ldmw_conference_attendees', 'View Attendees', array( $this, 'render_metabox' ), TribeEvents::POSTTYPE, 'side' );
	}

	/**
	 * Render the metabox
	 */
	public function render_metabox() {
		$id = get_post_meta( $_GET['post'], '_ExchangeEventProduct', true );
		$url = admin_url( "admin.php?page=ldmw-conference-attendees&id=$id" );

		echo '<p>View Conference Attendees</p>';
		echo '<a href="' . $url . '" class="button">View</a>';
	}
}