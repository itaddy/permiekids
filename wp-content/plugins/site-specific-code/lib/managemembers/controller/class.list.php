<?php

/**
 *
 * @package LDMW
 * @subpackage Manage Members
 * @since 1.0
 */
class LDMW_ManageMembers_Controller_List extends LDMW_ManageMembers_Controller {
	/**
	 * Holds the search query.
	 *
	 * @var array
	 */
	protected $search_query = array();

	/**
	 * @var WP_User[]
	 */
	protected $users = array();

	/**
	 * Process applicable data.
	 *
	 * @param $model LDMW_ManageMembers_Model_Users
	 */
	public function __construct( $model ) {
		parent::__construct( $model );
		$this->search_query = static::parse_get( $_GET );
		$this->users = $this->get_users();
	}

	/**
	 * Parse the raw $_GET data to return a proper search query for WP_User_Query
	 *
	 * @param $get
	 *
	 * @return array
	 */
	public static function parse_get( $get ) {
		$search_query = array();
		$meta_query = array(
		  'relation' => "AND"
		);

		if ( !empty( $get['first_name'] ) ) {
			$meta_query[] = array(
			  'key'     => 'first_name',
			  'value'   => $get['first_name'],
			  'compare' => "LIKE"
			);
		}

		if ( !empty( $get['last_name'] ) ) {
			$meta_query[] = array(
			  'key'     => 'last_name',
			  'value'   => $get['last_name'],
			  'compare' => "LIKE"
			);
		}

		if ( !empty( $get['grade'] ) ) {
			$meta_query[] = array(
			  'key'     => 'ldmw_membership_grade',
			  'value'   => (array) $get['grade'],
			  'compare' => "IN"
			);
		}

		if ( !empty( $get['division'] ) ) {
			$meta_query[] = array(
			  'key'     => 'ldmw_membership_division',
			  'value'   => (array) $get['division'],
			  'compare' => "IN"
			);
		}

		if ( !empty( $get['status'] ) ) {
			$meta_query[] = array(
			  'key'     => 'ldmw_membership_status',
			  'value'   => (array) $get['status'],
			  'compare' => "IN"
			);
		}

		if ( !empty( $get['start_date'] ) && !( empty( $get['end_date'] ) ) ) {
			$meta_query[] = array(
			  'key'     => 'ldmw_membership_start_date',
			  'value'   => array( strtotime( $get['start_date'] ), strtotime( $get['end_date'] ) ),
			  'compare' => "BETWEEN",
			  'type'    => 'NUMERIC'
			);
		}
		else if ( !empty( $get['start_date'] ) ) {
			$meta_query[] = array(
			  'key'     => 'ldmw_membership_start_date',
			  'value'   => strtotime( $get['start_date'] ),
			  'compare' => ">=",
			  'type'    => 'NUMERIC'
			);
		}
		else if ( !empty( $get['end_date'] ) ) {
			$meta_query[] = array(
			  'key'     => 'ldmw_membership_start_date',
			  'value'   => strtotime( $get['end_date'] ),
			  'compare' => "<=",
			  'type'    => 'NUMERIC'
			);
		}

		if ( count( $meta_query ) > 1 ) // make sure we actually added some query parameters before doing a meta query.
			$search_query['meta_query'] = $meta_query;

		if ( !empty( $get['member_id'] ) ) {
			$search_query['search'] = $get['member_id'];
			$search_query['search_columns'] = array( 'ID' );
			unset( $search_query['meta_query'] ); // an ID is unique so no need for any other search data
		}
		else if ( !empty( $get['email'] ) ) {
			$search_query['search'] = "*" . $get['email'] . "*";
			$search_query['search_columns'] = array( 'user_email' );
		}

		if ( !empty( $get['paid_by'] ) ) {
			$search_query['paid_by'] = (array) $get['paid_by'];
		}

		$orderby = isset( $get['orderby'] ) && in_array( $get['orderby'], array( 'ID', 'email', 'first_name', 'last_name', 'grade', 'division', 'status', 'start_date', 'end_date' ) ) ? $get['orderby'] : 'ID';
		$order = isset( $get['order'] ) ? strtoupper( $get['order'] ) : 'ASC';

		if ( !in_array( $orderby, array( 'ID', 'email' ) ) ) { // WP handles ID and email searching separately
			if ( in_array( $orderby, array( 'first_name', 'last_name' ) ) ) // prefix our meta data with ldmw_membership_
				$meta_order = $orderby;
			else
				$meta_order = 'ldmw_membership_' . $orderby;

			foreach ( $meta_query as $key => $query ) { // loop through all of our existing meta queries looking for our $orderby
				if ( !is_array( $query ) ) // each meta query is an array, so if this isn't an array, skip it
					continue;

				// if this meta query is the same as our orderby parameter,
				// then move the key, value, and compare values to the main query so WP can order them
				if ( $query['key'] == $meta_order ) {
					$search_query['meta_key'] = $query['key'];
					$search_query['meta_value'] = $query['value'];
					$search_query['meta_compare'] = $query['compare'];
					unset( $meta_query[$key] );
				}
			}
			if ( !isset( $search_query['meta_key'] ) ) { // if we couldn't find anything in the data we are searching for, search for *
				$search_query['meta_key'] = $meta_order;
				$search_query['meta_value'] = "";
				$search_query['meta_compare'] = "LIKE";
			}

			$orderby = 'meta_value';
		}

		$search_query['orderby'] = $orderby;
		$search_query['order'] = $order;

		if ( function_exists( 'get_current_screen' ) ) {
			// we can't use WP_User_Query pagination if we are using paid by, because that requires a separate carry
			if ( empty( $search_query['paid_by'] ) ) {
				if ( isset( $get['paged'] ) )
					$current_page = $get['paged'] - 1;
				else
					$current_page = 0;

				$screen = get_current_screen();
				$screen_option = $screen->get_option( 'per_page', 'option' );
				$per_page = get_user_meta( get_current_user_id(), $screen_option, true );

				if ( empty ( $per_page ) || $per_page < 1 ) {
					$per_page = $screen->get_option( 'per_page', 'default' );
				}

				if ( !is_numeric( $per_page ) )
					$per_page = 20;

				$search_query['number'] = (int) $per_page;
				$search_query['offset'] = (int) $per_page * $current_page;
			}
		}

		return $search_query;
	}

	/**
	 * Get the users from WP_User_Query based on the $search_query
	 */
	protected function get_users() {
		return $this->model->get_users( $this->search_query );
	}

	/**
	 * Serve the page.
	 *
	 * Grab applicable view and render with data.
	 */
	public function serve() {
		$data = $this->prepare_users( $this->users );

		$view = new LDMW_ManageMembers_View_List( $data, new LDMW_ManageMembers_Controller_Table( $data ) );
		$view->render();
	}

	/**
	 * Prepare all of the users for display
	 *
	 * @param $users WP_User[]
	 *
	 * @return array
	 */
	protected function prepare_users( $users ) {
		$data = array();

		foreach ( $users as $user ) {
			$data[] = $this->prepare_user( $user );
		}

		return $data;
	}

	/**
	 * Prepare an individual user record.
	 *
	 * @param $user WP_User
	 *
	 * @return array
	 */
	protected function prepare_user( $user ) {
		$current_user = array();
		$current_user['ID'] = $user->ID;
		$current_user['first_name'] = $user->first_name;
		$current_user['last_name'] = $user->last_name;
		$current_user['email'] = $user->user_email;
		$current_user['grade'] = LDMW_Users_Util::membership_grade_slug_to_name( get_user_meta( $user->ID, 'ldmw_membership_grade', true ) );
		$current_user['division'] = LDMW_Users_Util::membership_division_slug_to_name( get_user_meta( $user->ID, 'ldmw_membership_division', true ) );
		$current_user['status'] = LDMW_Users_Util::get_membership_status_slug_to_name( get_user_meta( $user->ID, 'ldmw_membership_status', true ) );

		$transactions = it_exchange_get_customer_transactions( $user->ID );
		if ( empty( $transactions ) ) {
			$current_user['date_paid'] = "";
			$current_user['fee_paid'] = "";
			$current_user['paid_by'] = "";
			$current_user['invoice'] = "";
			$current_user['receipt'] = "";
		}
		else {
			/**
			 * @var $transaction IT_Exchange_Transaction
			 */
			$transaction = array_shift( $transactions );
			$current_user['date_paid'] = $transaction->get_date();
			$current_user['fee_paid'] = $transaction->cart_details->total;
			$current_user['paid_by'] = $transaction->transaction_method;
			$current_user['invoice'] = $transaction->ID == LDMW_Options_Model::get_instance()->membership_product ? get_user_meta( $user->ID, 'ldmw_membership_renewal_invoice_post_id', true ) : "";
			$current_user['receipt'] = $transaction->ID;
		}

		return $current_user;
	}
}