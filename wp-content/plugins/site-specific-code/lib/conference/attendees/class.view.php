<?php

/**
 *
 * @package Conferences
 * @subpackage Attendees
 * @since 6/2
 */
class LDMW_Conference_Attendees_View extends LDMW_ManageMembers_View_List {
	/**
	 * Just passing through
	 *
	 * @param array $users
	 * @param WP_List_Table $table
	 */
	public function __construct( $users, $table ) {
		parent::__construct( $users, $table );
	}

	/**
	 * We don't have any nav urls
	 *
	 * @return array
	 */
	protected function get_nav_urls() {
		return array();
	}

	/**
	 * We don't have any search controls either
	 *
	 * @return string
	 */
	protected function render_search_controls() {
		echo '<input type="hidden" name="id" value="' . esc_attr( $_GET['id'] ) . '">';
	}

}