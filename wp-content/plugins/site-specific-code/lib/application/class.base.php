<?php

/**
 *
 * @package LDMW
 * @subpackage Application Information
 * @since 1.0
 */
class LDMW_Application_Base {
	/**
	 * Add require hooks and filters.
	 */
	public function __construct() {
		add_action( 'ldmw_application_approve', array( $this, 'application_approve' ), 5 );
		add_action( 'ldmw_application_upgrade_approve', array( $this, 'application_approve' ), 1 );
		add_action( 'ldmw_application_deny', array( $this, 'application_deny' ) );
		add_action( 'ldmw_application_reject', array( $this, 'application_reject' ) );
		add_action( 'ldmw_application_send', array( $this, 'application_send' ), 10, 2 );
	}

	/**
	 * Process an approved application
	 *
	 * @param $entry_id int
	 */
	public function application_approve( $entry_id ) {
		LDMW_Application_Util::update_application_status( $entry_id, "approved" );
		LDMW_Application_Util::change_application_meta( $entry_id, 'applicant_advised', time() );
		LDMW_Application_Util::change_application_meta( $entry_id, 'assessment_advised', time() );
		LDMW_Application_Util::change_application_meta( $entry_id, 'registrars_advised', time() );
		$entry = LDMW_Gravity_Util::get_application_entry( $entry_id );
		update_user_meta( $entry['user_id'], 'ldmw_membership_grade', $entry['grade'] );
		update_user_meta( $entry['user_id'], 'ldmw_membership_division', $entry['division'] );

		add_user_meta( $entry['user_id'], 'ldmw_membership_grade_history', array( 'value' => $entry['grade'], 'time' => time() ) );
		add_user_meta( $entry['user_id'], 'ldmw_membership_division_history', array( 'value' => $entry['division'], 'time' => time() ) );
	}

	/**
	 * Process a denied application.
	 *
	 * @param $entry_id int
	 */
	public function application_deny( $entry_id ) {
		LDMW_Application_Util::update_application_status( $entry_id, "denied" );
		LDMW_Application_Util::change_application_meta( $entry_id, 'applicant_advised', time() );
		LDMW_Application_Util::change_application_meta( $entry_id, 'assessment_advised', time() );
		LDMW_Application_Util::change_application_meta( $entry_id, 'registrars_advised', time() );
	}

	/**
	 * Mark application as denied.
	 *
	 * @param $entry_id int
	 */
	public function application_reject( $entry_id ) {
		LDMW_Application_Util::update_application_status( $entry_id, 'rejected' );
	}

	/**
	 * Send the application to a committee
	 *
	 * @param $entry_id int
	 * @param $committee string
	 */
	public function application_send( $entry_id, $committee ) {
		LDMW_Application_Util::update_application_status( $entry_id, "forwarded" );
	}
}

new LDMW_Application_Base();