<?php

/**
 *
 * @package Conferences
 * @subpackage Attendees
 * @since 6/2
 */
class LDMW_Conference_Attendees_Model extends LDMW_ManageMembers_Model_Users {
	/**
	 * @var int
	 */
	protected $conference_id;

	/**
	 * Register the conference ID
	 *
	 * @param $conference_id int
	 */
	public function __construct( $conference_id ) {
		$this->conference_id = $conference_id;
	}
	/**
	 * Get the default args for the conference attendees list
	 *
	 * @return array
	 */
	protected function get_users_default_args() {
		return array(
		  'meta_query' => array(
			'relation' => 'AND',
			array(
			  'key'     => 'ldmw_conference_purchased_' . $this->conference_id,
			  'compare' => 'EXISTS'
			)
		  )
		);
	}

}