<?php

/**
 *
 * @package Conference
 * @subpackage Attendees
 * @since 6/2
 */
class LDMW_Conference_Attendees_Table extends LDMW_ManageMembers_Controller_Table {
	/**
	 * @param array $users
	 */
	function __construct( $users ) {
		parent::__construct( $users );
	}

	/**
	 * Determine the total number of items we have
	 *
	 * @param array $data
	 *
	 * @return int
	 */
	protected function determine_total_items( $data ) {
		/**
		 * Let's check how many items are in our data array.
		 */
		if ( isset( $_GET['paid_by'] ) ) {
			// if we are doing a paid by search, then we had to return all of our members, so that is the total number of users
			$total_items = count( $data );
		}
		else {
			$total_items = $this->get_total_items_for_full_search();
		}

		return $total_items;
	}

	/**
	 * Get the total items used when doing a full item search
	 *
	 * @return array
	 */
	protected function get_total_items_for_full_search() {
		$defaults = array(
		  'fields'     => 'ID',
		  'meta_query' => array(
			'relation' => 'AND',
			array(
			  'key'     => 'ldmw_conference_purchased_' . $_GET['id'],
			  'compare' => 'EXISTS'
			)
		  )
		);

		return ( new WP_User_Query( array_merge( LDMW_Conference_Attendees_Controller::parse_get( $_GET ), $defaults ) ) )->get_total();
	}

}