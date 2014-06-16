<?php

/**
 *
 * @package    LDMW
 * @subpackage Membership
 * @since      1.0
 */
class LDMW_Membership_Base {
	/**
	 * Add actions and filters.
	 */
	public function __construct() {
		add_action( 'ldmw_setup_membership', array( $this, 'setup' ), 99, 3 );
		add_filter( 'it_exchange_recurring_payments_handle_expired', array( $this, 'deactivate_membership' ), 10, 3 );
		add_action( 'ldmw_daily_cron', array( $this, 'trigger_reset_yearly_membership' ) );
		add_action( 'ldmw_reset_membership', array( $this, 'reset_yearly_membership' ) );
		add_action( 'ldmw_days_to_renewal_-1', array( $this, 'mark_users_as_overdue' ) );

		$this->process_onboard();
	}

	/**
	 * Set up the membership.
	 *
	 * Save some custom metadata.
	 *
	 * @param $member_id          int
	 * @param $transaction        IT_Exchange_Transaction
	 * @param $membership_product IT_Exchange_Product
	 */
	public function setup( $member_id, $transaction, $membership_product ) {
		$user = get_user_by( 'id', $member_id );
		$user->set_role( LDMW_Users_Base::$member_role_slug );
		update_user_meta( $member_id, 'ldmw_membership_start_date', time() );
		update_user_meta( $member_id, 'ldmw_membership_status', 'current' );
		add_user_meta( $member_id, 'ldmw_membership_start_date', time() );

		$feature = it_exchange_get_product_feature( $membership_product->ID, 'prorated-subscriptions' );

		$date = $feature['until-date'];
		$date = new DateTime( "@$date" );
		$date->add( new DateInterval( "P150D" ) ); // 120 days for the last renewal email, and 30 days for a grace period

		$transaction->update_transaction_meta( 'subscription_expires_' . $membership_product->ID, $date->getTimestamp() );
	}

	/**
	 * Deactivate user memberships if they are unpaid and we are passed the 130 days.
	 *
	 * @param $handle      boolean Whether to deactivate the membership
	 * @param $product_id  int
	 * @param $transaction IT_Exchange_Transaction
	 *
	 * @return boolean
	 */
	public function deactivate_membership( $handle = true, $product_id, $transaction ) {
		if ( $product_id != LDMW_Options_Model::get_instance()->membership_product ) {
			return $handle;
		}

		$user = get_user_by( 'id', $transaction->customer_id );

		$user->set_role( 'subscriber' );

		return $handle;
	}

	/**
	 * Test if we want to trigger the membership reset action.
	 *
	 * We want to trigger this 1 day after memberships would expire.
	 */
	public function trigger_reset_yearly_membership() {
		$now = new DateTime();
		$renewal_date = new DateTime( "@" . LDMW_Options_Model::get_instance()->membership_start_date );
		$diff = $now->diff( $renewal_date );

		if ( $diff->days != 151 || $diff->invert != 1 ) { // 120 days for the last renewal email, and 30 days for a grace period
			return;
		}

		$query = new WP_User_Query( array(
			'role' => LDMW_Users_Base::$member_role_slug
		  )
		);
		$users = $query->get_results();

		foreach ( $users as $user ) {
			do_action( 'ldmw_reset_membership', $user->ID );
		}

		do_action( 'ldmw_reset_membership_complete' );
	}

	/**
	 * Reset the yearly membership.
	 *
	 * @param $user_id int
	 */
	public function reset_yearly_membership( $user_id ) {
		delete_user_meta( $user_id, 'ldmw_next_membership_paid' );

		$invoice_id = get_user_meta( $user_id, 'ldmw_membership_renewal_invoice_post_id', true );
		add_user_meta( $user_id, 'ldmw_membership_renewal_old_invoice_post_ids', $invoice_id );
		delete_user_meta( $user_id, 'ldmw_membership_renewal_invoice_post_id' );
	}

	/**
	 * Mark users as having their membership status overdue.
	 */
	public function mark_users_as_overdue() {
		$query = new WP_User_Query( array(
			'role'       => LDMW_Users_Base::$member_role_slug,
			'meta_query' => array(
			  'relation' => 'AND',
			  array(
				'key'     => 'ldmw_next_membership_paid',
				'value'   => 1,
				'compare' => 'NOT EXISTS'
			  )
			)
		  )
		);
		$users = $query->get_results();

		if ( empty( $users ) )
			return;

		foreach ( $users as $user ) {
			update_user_meta( $user->ID, 'ldmw_membership_status', 'overdue' );
		}
	}

	/**
	 * Process the onboard form submission.
	 */
	public function process_onboard() {
		if ( ! isset( $_POST['ldmw_onboard'] ) )
			return;

		if ( ! isset( $_POST['ldmw_nonce'] ) || ! wp_verify_nonce( $_POST['ldmw_nonce'], 'ldmw-onboard' ) )
			return;

		foreach ( array( 'home', 'mobile', 'work' ) as $phone ) {
			LDMW_Users_Util::update_phone( get_current_user_id(), $phone, $_POST[$phone . "_phone"] );
		}

		LDMW_Users_Util::update_home_address( get_current_user_id(), $_POST['home_m'] );
		LDMW_Users_Util::update_work_address( get_current_user_id(), $_POST['work_m'] );
		update_user_meta( get_current_user_id(), 'ldmw_onboarded_user', true );

		wp_update_user( array( 'ID' => get_current_user_id(), 'user_url' => $_POST['website'] ) );

		$prefs = array();

		foreach ( $_POST['contact'] as $key => $value ) {
			$prefs[str_replace( "_", " ", $key )] = true;
		}

		LDMW_Users_Util::update_communication_preferences( get_current_user_id(), $prefs );
	}

}