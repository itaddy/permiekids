<?php

/**
 *
 * @package LDMW
 * @subpackage Notifications
 * @since 1.0
 */
class LDMW_Notifications_Transactional {
	/**
	 * Add necessary hooks to let us know when to fire off notifications.
	 */
	public function __construct() {
		add_action( 'ldmw_application_approve', array( $this, 'application_approved' ) );
		add_action( 'ldmw_application_upgrade_approve', array( $this, 'application_upgrade_approved' ) );
		add_action( 'ldmw_application_deny', array( $this, 'application_denied' ) );
		add_action( 'ldmw_application_reject', array( $this, 'application_rejected' ) );
		add_action( 'ldmw_flatco_notification_sent_' . get_user_meta( get_current_user_id(), 'ldmw_membership_approved_notification', true ), array( $this, 'resend_approved_notification' ) );
	}

	/**
	 * Send Growl notification when an application is approved.
	 *
	 * @param $entry_id int
	 */
	public function application_approved( $entry_id ) {
		$entry = LDMW_Gravity_Util::get_application_entry( $entry_id );
		$user_id = $entry['user_id'];

		$notification_body = 'Your application was approved. <a href="' . esc_url( get_permalink( LDMW_Options_Model::get_instance()->membership_product ) ) . '">Pay for your membership now.</a>';

		$notification = new IBD_Notify_Growl_Notification( $user_id, 'Application Approved', $notification_body, array(
			'class'    => 'success',
			'sticky'   => true,
			'callback' => array( "LDMW_Notifications_Transactional", 'application_approved_callback' )
		  )
		);
		$notification->save();
	}

	/**
	 * Add a new notification, when growl notification is dismissed, and not on product page.
	 *
	 * @param $notification IBD_Notify_Growl_Notification
	 */
	public static function application_approved_callback( $notification ) {
		if ( ! isset( $_POST['notification'] ) || $_POST['notification']['id'] != $notification->get_id() )
			return;

		$message = 'Your application was approved. <a href="' . esc_url( get_permalink( LDMW_Options_Model::get_instance()->membership_product ) ) . '">Pay for your membership now.</a>';

		$flatco = new LDMW_Notifications_Flatco_Notification( $notification->get_user_id(), 'Application Approved', $message );
		$flatco->save();

		update_user_meta( $notification->get_user_id(), 'ldmw_membership_approved_notification', $flatco->get_id() );

		$notification->notify_ajax();
	}

	/**
	 * Resend the application approved flatco notification
	 */
	public function resend_approved_notification() {
		if ( ! LDMW_Users_Util::is_member( wp_get_current_user() ) ) {
			$message = 'Your application was approved. <a href="' . esc_url( get_permalink( LDMW_Options_Model::get_instance()->membership_product ) ) . '">Pay for your membership now.</a>';

			$flatco = new LDMW_Notifications_Flatco_Notification( get_current_user_id(), 'Application Approved', $message, array(
				'can_send_function' => array( 'LDMW_Notifications_Transactional', 'can_send_approved_notification' )
			  )
			);
			$flatco->save();

			update_user_meta( $flatco->get_user_id(), 'ldmw_membership_approved_notification', $flatco->get_id() );
		}
	}

	/**
	 * Check if we can send the approved notification
	 *
	 * @param $notification LDMW_Notifications_Flatco_Notification
	 *
	 * @return bool
	 */
	public static function can_send_approved_notification( $notification ) {
		return ( ! LDMW_Users_Util::is_member( get_user_by( "id", $notification->get_user_id() ) ) );
	}

	/**
	 * Send growl notification when a transfer application is approved.
	 *
	 * @param $entry_id int
	 */
	public function application_upgrade_approved( $entry_id ) {
		$entry = LDMW_Gravity_Util::get_application_entry( $entry_id );
		$user_id = $entry['user_id'];

		$notification_body = 'Your transfer application was approved.';

		$notification = new IBD_Notify_Growl_Notification( $user_id, 'Application Approved', $notification_body, array(
			'class'  => 'success',
			'sticky' => true
		  )
		);
		$notification->save();
	}

	/**
	 * Send Growl notification when an application is approved.
	 *
	 * @param $entry_id int
	 */
	public function application_denied( $entry_id ) {
		$entry = LDMW_Gravity_Util::get_application_entry( $entry_id );
		$user_id = $entry['user_id'];

		$notification = new IBD_Notify_Growl_Notification( $user_id, 'Application Denied', "Your application was denied.", array(
			'class'  => 'error',
			'sticky' => true
		  )
		);
		$notification->save();
	}

	/**
	 * Send Growl notification when an application is approved.
	 *
	 * @param $entry_id int
	 */
	public function application_rejected( $entry_id ) {
		$entry = LDMW_Gravity_Util::get_application_entry( $entry_id );
		$user_id = $entry['user_id'];

		$notification = new IBD_Notify_Growl_Notification( $user_id, 'Incomplete Application', "You submitted an incomplete application. Check your email for more information", array(
			'class'  => 'warning',
			'sticky' => true
		  )
		);
		$notification->save();
	}
}