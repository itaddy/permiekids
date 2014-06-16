<?php

/**
 *
 * @package LDMW
 * @subpackage Notifications
 * @since 1.0
 */
class LDMW_Notifications_Renewal {

	/**
	 * Add hooks and filters for sending payment reminder emails.
	 */
	public function __construct() {
		add_action( 'ldmw_daily_cron', array( $this, 'send_renewal_reminders' ) );
		add_action( 'ldmw_send_renewal_notices', array( $this, 'send_renewal_reminders_to_users' ), 10, 2 );
		add_action( 'current_screen', array( $this, 'resend_renewal_email' ) );
	}

	/**
	 * Send renewal reminder notices.
	 */
	public function send_renewal_reminders() {
		$renewal_date = LDMW_Options_Model::get_instance()->membership_start_date;

		if ( empty( $renewal_date ) )
			return;

		$now = new DateTime();
		$diff = $now->diff( new DateTime( "@" . $renewal_date ) );
		$days = $diff->days;

		if ( $diff->invert == 1 )
			$days = - $days;

		$email_type = $this->renewal_reminders_days_to_email( $days );

		do_action( 'ldmw_days_to_renewal_' . $days );

		if ( $email_type === false )
			return;

		$this->send_renewal_reminders_to_users( $email_type );
	}

	/**
	 * Send the actual renewal reminders 25 users at a time.
	 *
	 * Recursion like by calling wp_schedule_single_event, with updated user offset.
	 *
	 * @param $email_type string
	 * @param int $user_offset
	 */
	public function send_renewal_reminders_to_users( $email_type, $user_offset = 0 ) {
		$query = new WP_User_Query( array(
			'role'       => LDMW_Users_Base::$member_role_slug,
			'number'     => 5,
			'offset'     => $user_offset,
			'meta_query' => array(
			  'relation' => 'AND',
			  array(
				'key'     => 'ldmw_next_membership_paid',
				'value'   => 1,
				'compare' => 'NOT EXISTS'
			  ),
			  array(
				'key'     => 'ldmw_membership_status',
				'value'   => 'overdue',
				'compare' => '!='
			  )
			)
		  )
		);
		$users = $query->get_results();

		if ( empty( $users ) ) // once we have sent emails to all the users, exit, thereby stopping the schedule single event.
			return;

		foreach ( $users as $user ) {
			$post_id = get_user_meta( $user->ID, 'ldmw_membership_renewal_invoice_post_id', true );

			if ( empty( $post_id ) || null == get_post( $post_id ) ) {
				$post_id = LDMW_Exchange_Base::generate_renewal_invoice( $user->id );
				update_user_meta( $user->ID, 'ldmw_membership_renewal_invoice_post_id', $post_id );
			}

			$this->send_renewal_email( $user->ID, null, $email_type, $post_id );
		}

		wp_schedule_single_event( time() + ( 5 * 60 ), 'ldmw_send_renewal_notices', array( $email_type, $user_offset + 5 ) );
	}

	/**
	 * Send the actual renewal email.
	 *
	 * @param $user_id int
	 * @param $entry_id int
	 * @param $template string
	 * @param $invoice_id int
	 */
	protected function send_renewal_email( $user_id, $entry_id, $template, $invoice_id ) {
		if ( $template == "original_renewal_notice" && LDMW_Users_Util::is_sustaining_member( $user_id ) )
			$template .= '_sustaining_members';

		$subject_template = $template . "_subject"; // subjects are stored in the options as email_template_subject

		$template_html = LDMW_Options_Model::get_instance()->$template;
		$subject = LDMW_Options_Model::get_instance()->$subject_template;

		$email_template = new IBD_Notify_Email_Dynamic_Notification( LDMW_Notifications_Email::get_rule_content( $user_id, $entry_id, $invoice_id ), wpautop( $template_html ) );
		$notification = new IBD_Notify_Email_Notification( $user_id, $subject, "Please renew your AAS Membership.", array(
			'template' => $email_template
		  )
		);
		$notification->save();

		do_action( 'ldmw_' . $template . '_sent', $user_id );
	}

	/**
	 * Get reminder email from date diff.
	 *
	 * @param $days int
	 *
	 * @return bool|string
	 */
	protected function renewal_reminders_days_to_email( $days ) {
		switch ( $days ) {
			case 90 :
				return 'original_renewal_notice';
			case 1 :
				return 'reminder_invoice';
			case - 60 :
				return 'overdue_notice';
			case - 120 :
				return 'final_notice';
			default :
				return false;
		}
	}

	/**
	 * Resend the renewal invoice to members when triggered from the customer data pane
	 */
	public function resend_renewal_email() {
		if ( ! isset( $_POST['ldmw_resend_renewal_invoice'] ) || ! isset( $_GET['user_id'] ) )
			return;

		if ( ! isset( $_POST['_it_exchange_customer_info_nonce'] ) || ! wp_verify_nonce( $_POST['_it_exchange_customer_info_nonce'], 'update-it-exchange-customer-info' ) )
			return;

		$user_id = $_GET['user_id'];

		$invoice_id = get_user_meta( $user_id, 'ldmw_membership_renewal_invoice_post_id', true );

		if ( empty( $invoice_id ) || null == get_post( $invoice_id ) ) {
			$invoice_id = LDMW_Exchange_Base::generate_renewal_invoice( $user_id );
			update_user_meta( $user_id, 'ldmw_membership_renewal_invoice_post_id', $invoice_id );
		}

		$this->send_renewal_email( $user_id, null, $_POST['ldmw_resend_renewal_invoice_email'], $invoice_id );
	}
}