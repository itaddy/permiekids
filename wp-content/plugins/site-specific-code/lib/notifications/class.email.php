<?php

/**
 *
 * @package LDMW
 * @subpackage Notifications
 * @since 1.0
 */
class LDMW_Notifications_Email {
	/**
	 * Add necessary hooks and filters.
	 */
	public function __construct() {
		add_action( 'ldmw_application_approve', array( $this, 'send_approved' ), 99 );
		add_action( 'ldmw_application_approve', array( $this, 'send_approved_to_federal_council' ), 99 );
		add_action( 'ldmw_application_upgrade_approve', array( $this, 'send_transfer_approved' ), 99 );
		add_action( 'ldmw_application_deny', array( $this, 'send_denied' ) );
		add_action( 'ldmw_application_reject', array( $this, 'send_reject_incomplete' ) );
		add_action( 'ldmw_application_send', array( $this, 'send_application' ), 10, 2 );
		add_filter( 'retrieve_password_message', array( $this, 'convert_password_reset_to_html' ) );
	}

	/**
	 * Send an email to the applicant that their application was approved.
	 *
	 * @param $entry_id int
	 */
	public function send_approved( $entry_id ) {
		$entry = LDMW_Gravity_Util::get_application_entry( $entry_id );
		$user_id = $entry['user_id'];
		$template_html = LDMW_Options_Model::get_instance()->approved_email;

		$template = new IBD_Notify_Email_Dynamic_Notification( self::get_rule_content( $user_id, $entry_id ), wpautop( $template_html ) );
		$notification = new IBD_Notify_Email_Notification( $user_id, LDMW_Options_Model::get_instance()->approved_email_subject, "Your application was approved!", array(
			'template' => $template
		  )
		);
		$notification->save();
	}

	/**
	 * Send an email to federal council members of the application's division
	 *
	 * @param $entry_id int
	 */
	public function send_approved_to_federal_council( $entry_id ) {
		$template_html = LDMW_Options_Model::get_instance()->notify_federal_council_email;
		$subject = LDMW_Options_Model::get_instance()->notify_federal_council_email_subject;
		$entry = LDMW_Gravity_Util::get_application_entry( $entry_id );
		$member_id = $entry['user_id'];

		foreach ( LDMW_Users_Util::get_committee_members_of_division( $entry['division'] ) as $user ) {
			$template = new IBD_Notify_Email_Dynamic_Notification( self::get_rule_content( $member_id, $entry_id ), wpautop( $template_html ) );
			$notification = new IBD_Notify_Email_Notification( $user->ID, $subject, "A new application has been approved.", array(
				'template' => $template
			  )
			);

			$notification->save();
		}

		$federal_registrars = ( new WP_User_Query( array( 'role' => LDMW_Users_Base::$federal_registrar_role_slug ) ) )->get_results();

		foreach ( $federal_registrars as $user ) {
			$template = new IBD_Notify_Email_Dynamic_Notification( self::get_rule_content( $member_id, $entry_id ), wpautop( $template_html ) );
			$notification = new IBD_Notify_Email_Notification( $user->ID, $subject, "A new application has been approved.", array(
				'template' => $template
			  )
			);

			$notification->save();
		}

		$gen_sec = LDMW_Users_Util::get_general_secretary_user();

		if ( is_null( $gen_sec ) )
			return;

		$template = new IBD_Notify_Email_Dynamic_Notification( self::get_rule_content( $member_id, $entry_id ), wpautop( $template_html ) );
		$notification = new IBD_Notify_Email_Notification( $gen_sec->ID, $subject, "A new application has been approved.", array(
			'template' => $template
		  )
		);

		$notification->save();
	}

	/**
	 * Send an email to the applicant that their transfer application was approved.
	 *
	 * @param $entry_id int
	 */
	public function send_transfer_approved( $entry_id ) {
		$entry = LDMW_Gravity_Util::get_application_entry( $entry_id );
		$user_id = $entry['user_id'];
		$template_html = LDMW_Options_Model::get_instance()->transfer_approved_email;

		$template = new IBD_Notify_Email_Dynamic_Notification( self::get_rule_content( $user_id, $entry_id ), wpautop( $template_html ) );
		$notification = new IBD_Notify_Email_Notification( $user_id, LDMW_Options_Model::get_instance()->transfer_approved_email_subject, "Your transfAllower application was approved!", array(
			'template' => $template
		  )
		);
		$notification->save();
	}

	/**
	 * Send an email to the applicant that their application was denied.
	 *
	 * @param $entry_id int
	 */
	public function send_denied( $entry_id ) {
		$entry = LDMW_Gravity_Util::get_application_entry( $entry_id );
		$user_id = $entry['user_id'];
		$template_html = LDMW_Options_Model::get_instance()->denied_email;

		$template = new IBD_Notify_Email_Dynamic_Notification( self::get_rule_content( $user_id, $entry_id ), wpautop( $template_html ) );
		$notification = new IBD_Notify_Email_Notification( $user_id, LDMW_Options_Model::get_instance()->denied_email_subject, "Your application was denied.", array(
			'template' => $template
		  )
		);
		$notification->save();
		$notification = new IBD_Notify_Growl_Notification( $user_id, "Membership", "Sorry, your application was denied.", array( 'class' => 'error', 'sticky' => true ) );
		$notification->save();
	}

	/**
	 * Send an email to the applicant that their application was marked as incomplete.
	 *
	 * @param $entry_id int
	 */
	public function send_reject_incomplete( $entry_id ) {
		$entry = LDMW_Gravity_Util::get_application_entry( $entry_id );
		$user_id = $entry['user_id'];
		$template_html = LDMW_Options_Model::get_instance()->rejected_email;

		$template = new IBD_Notify_Email_Dynamic_Notification( self::get_rule_content( $user_id, $entry_id ), wpautop( $template_html ) );
		$notification = new IBD_Notify_Email_Notification( $user_id, LDMW_Options_Model::get_instance()->rejected_email_subject, "Your application was marked as incomplete.", array(
			'template' => $template
		  )
		);
		$notification->save();
	}

	/**
	 * Send the application to the specified committee.
	 *
	 * @param $entry_id
	 * @param $committee
	 */
	public function send_application( $entry_id, $committee ) {
		$template_html = LDMW_Options_Model::get_instance()->notify_committee_email;
		$subject = LDMW_Options_Model::get_instance()->notify_committee_email_subject;
		$entry = LDMW_Gravity_Util::get_application_entry( $entry_id );

		foreach ( LDMW_Users_Util::get_committee_members( $committee ) as $user ) {
			$template = new IBD_Notify_Email_Dynamic_Notification( self::get_rule_content( $user->ID, $entry_id ), wpautop( $template_html ) );
			$notification = new IBD_Notify_Email_Notification( $user->ID, $subject, "A new application has been forwarded to you.", array(
				'template' => $template
			  )
			);

			if ( isset( $entry['files'] ) && is_array( $entry['files'] ) ) {
				foreach ( $entry['files'] as $file ) {
					$notification->add_attachment( ITUtility::get_file_from_url( $file['value'] ) );
				}
			}

			$notification->save();
		}
	}

	/**
	 * Fill up the rules with data for the user.
	 *
	 * @param int|null $user_id
	 * @param int|null $entry_id
	 * @param int|null $invoice_post_id
	 *
	 * @return array
	 */
	public static function get_rule_content( $user_id = null, $entry_id = null, $invoice_post_id = null ) {
		if ( $user_id === null )
			$user_id = get_current_user_id();

		$user = get_user_by( 'id', $user_id );

		$rule_content = array();
		$rule_content['first_name'] = $user->first_name;
		$rule_content['last_name'] = $user->last_name;
		$rule_content['email'] = $user->user_email;
		$rule_content['username'] = $user->user_login;
		$rule_content['membership_division'] = LDMW_Users_Util::membership_division_slug_to_name( LDMW_Users_Util::get_membership_division( $user_id ) );
		$rule_content['membership_grade'] = LDMW_Users_Util::membership_grade_slug_to_name( LDMW_Users_Util::get_membership_grade( $user_id ) );
		$rule_content['membership_status'] = LDMW_Users_Util::get_membership_status_slug_to_name( LDMW_Users_Util::get_membership_status( $user_id ) );

		if ( $entry_id !== null ) {
			$entry = LDMW_Gravity_Util::get_application_entry( $entry_id );

			$rule_content['application_entry_link'] = LDMW_Application_Util::get_application_entry_link( $entry_id );

			$time = new DateTime( $entry['time'] );
			$rule_content['application_entry_date'] = $time->format( get_option( 'date_format' ) );
			$rule_content['application_notes'] = isset( $entry['notes'] ) ? $entry['notes'] : "";
			$rule_content['application_incomplete_notes'] = isset( $entry['incomplete_notes'] ) ? $entry['incomplete_notes'] : "";
		}

		if ( $invoice_post_id !== null && get_post( $invoice_post_id ) !== null ) {
			$invoice = get_post( $invoice_post_id );
			$meta = it_exchange_get_product_feature( $invoice_post_id, 'invoices' );

			$rule_content['invoice_link'] = add_query_arg( 'client', $meta['hash'], get_permalink( $invoice_post_id ) );
			$rule_content['invoice_amount'] = it_exchange_format_price( it_exchange_get_product_feature( $invoice_post_id, 'base-price' ) );
		}

		return $rule_content;
	}

	/**
	 * Build an array of the rules and their descriptions.
	 *
	 * @return array
	 */
	public static function rule_descriptions_array() {
		return array(
		  'first_name'                   => "The user's first name",
		  'last_name'                    => "The user's last name",
		  'email'                        => "The user's email",
		  'username'                     => "The user's username on this website",
		  'membership_division'          => "The division the member belongs to",
		  'membership_grade'             => "The user's membership grade",
		  'membership_status'            => "The status of the user's membership ( active, inactive )",
		  'application_entry_link'       => "A direct link to the application. User must be logged in to view. Should only be used for the email sent to the committee.",
		  'application_entry_date'       => "The date the application was submitted.",
		  'application_notes'            => 'Any notes that were entered by the reviewing committee as to why the application was approved or denied.',
		  'application_incomplete_notes' => 'Any notes that were entered by the General Secretary as to why the application was marked as incomplete.',
		  'invoice_link'                 => "A direct link to the invoice. Only should be used in the renewal emails.",
		  'invoice_amount'               => "The total price of the invoice. Only should be used in the renewal emails."
		);
	}

	/**
	 * Take the rule descriptions and prepare them for the options page.
	 *
	 * @return string
	 */
	public static function rule_descriptions_option() {
		$return = "<p>You can use these shortcodes to add user content to your emails. You can use them like this {{first_name}}.</p>";
		foreach ( self::rule_descriptions_array() as $rule => $description ) {
			$return .= "<p><em>$rule</em> – $description</p>";
		}

		return $return;
	}

	/**
	 * Convert the password reset email to HTML by
	 *
	 * replacing line breaks with <br> tags
	 *
	 * wrapping it in HTML tags
	 *
	 * @param $message string
	 *
	 * @return string
	 */
	public function convert_password_reset_to_html( $message ) {
		$message = str_replace( array( "<", ">" ), "", $message );
		$message = str_replace( "\r\n", '<br>', $message );

		$message = "<html><head></head><body>" . $message . "</body></html>";

		return $message;
	}
}