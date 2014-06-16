<?php

/**
 *
 * @package LDMW
 * @subpackage Settings
 * @since 1.0
 */
class LDMW_Options_Controller {
	/**
	 * @var array
	 */
	private $options_fields = array();

	/**
	 * @var LDMW_Options_Controller|null
	 */
	private static $instance = null;

	/**
	 * Private constructor
	 */
	private function __construct() {
		$this->add_fields();
		$this->options_fields = apply_filters( 'ldmw_options_controller', $this->options_fields );
	}

	/**
	 * @return LDMW_Options_Controller
	 */
	public static function get_instance() {
		if ( self::$instance == null )
			self::$instance = new self;

		return self::$instance;
	}

	/**
	 * Sanitize new options data
	 *
	 * @param $values
	 */
	private function sanitize( $values ) {
		foreach ( $values as $key => $value ) {
			switch ( $key ) {
				case 'membership_start_date'  :
					$date = new DateTime( $value );
					$value = $date->getTimestamp();
			}

			$value = stripslashes_deep( $value );

			$values[$key] = apply_filters( 'ldmw_options_sanitize', $value, $key );
		}

		return $values;
	}

	/**
	 * Save data to options
	 *
	 * @param $values
	 */
	public function save( $values ) {
		$sanitized = $this->sanitize( $values );

		$options = LDMW_Options_Model::get_instance();
		$options->update( $sanitized );
		$options->save();
	}

	/**
	 * Return an array of the default values
	 *
	 * slug => default
	 */
	public function get_defaults() {
		$defaults = array();

		foreach ( $this->get_fields() as $field ) {
			if ( isset( $field['default'] ) )
				$defaults[$field['slug']] = $field['default'];
		}

		return $defaults;
	}

	/**
	 * Get options field
	 *
	 * @return array
	 */
	public function get_fields() {
		return $this->options_fields;
	}

	/**
	 * Add fields to controller
	 */
	private function add_fields() {
		$this->options_fields[] = array(
		  'field_type' => 'section_title',
		  'title'      => 'The Basics'
		);

		$this->options_fields['logo_url'] = array(
		  'slug'        => 'logo_url',
		  'label'       => 'URL to Logo',
		  'field_type'  => 'text',
		  'description' => 'Used on invoices'
		);

		$this->options_fields['general_secretary'] = array(
		  'slug'        => 'general_secretary',
		  'label'       => 'General Secretary User',
		  'field_type'  => 'select',
		  'description' => 'Which user is the general secretary',
		  'options'     => LDMW_Util::get_users_list( 'administrator' )
		);

		$this->options_fields['gst_percentage'] = array(
		  'slug'        => 'gst_percentage',
		  'label'       => 'GST Percentage',
		  'field_type'  => 'number',
		  'description' => 'GST Percentage that will be displayed next to products'
		);

		$this->options_fields[] = array(
		  'field_type' => 'section_title',
		  'title'      => __( 'Application Approval' )
		);

		$this->options_fields['application_form'] = array(
		  'slug'        => 'application_form',
		  'default'     => "",
		  'label'       => __( 'Application Form' ),
		  'description' => __( "Which form is the application form?" ),
		  'field_type'  => 'select',
		  'options'     => LDMW_Gravity_Util::get_gravity_forms_select_data()
		);

		$this->options_fields['application_form_page'] = array(
		  'slug'        => 'application_form_page',
		  'default'     => "",
		  'label'       => __( 'Application Form Page' ),
		  'description' => __( "Which page is the application form embedded on?" ),
		  'field_type'  => 'select',
		  'options'     => LDMW_Util::get_pages_list()
		);

		$this->options_fields['application_form_product'] = array(
		  'slug'        => 'application_form_product',
		  'default'     => "",
		  'label'       => __( 'Application Form Product' ),
		  'description' => __( "Which product must users purchase to submit an application" ),
		  'field_type'  => 'select',
		  'options'     => LDMW_Exchange_Util::get_products_array()
		);

		$this->options_fields[] = array(
		  'field_type' => 'hr'
		);

		$this->options_fields[] = array(
		  'field_type' => 'section_title',
		  'title'      => __( 'Membership' )
		);

		$this->options_fields['membership_info_page'] = array(
		  'slug'        => 'membership_info_page',
		  'default'     => "",
		  'label'       => "Membership Info Page",
		  'description' => "WordPress Page that explains AAS Membership",
		  'field_type'  => 'select',
		  'options'     => LDMW_Util::get_pages_list()
		);

		$this->options_fields['membership_product'] = array(
		  'slug'        => 'membership_product',
		  'default'     => "",
		  'label'       => __( 'Membership Product' ),
		  'description' => __( "Which product is the main membership product." ),
		  'field_type'  => 'select',
		  'options'     => LDMW_Exchange_Util::get_products_array()
		);

		$this->options_fields['membership_start_date'] = array(
		  'slug'        => 'membership_start_date',
		  'default'     => "",
		  'label'       => __( 'Membership Start Date' ),
		  'description' => __( "The date that the " . date( "Y" ) . " – " . ( (int) date( "Y" ) + 1 ) . " membership starts. Should be updated on the first of January." ),
		  'field_type'  => 'date',
		  'options'     => array(
			'date-format' => get_option( 'date_format' )
		  )
		);

		$this->options_fields['membership_late_fee'] = array(
		  'slug'        => 'membership_late_fee',
		  'default'     => "",
		  'label'       => __( 'Membership Late Fee' ),
		  'description' => __( "The fee that is applied once the payment is made after the deadline." ),
		  'field_type'  => 'number'
		);

		$this->options_fields[] = array(
		  'field_type' => 'hr'
		);

		$this->options_fields[] = array(
		  'field_type' => 'section_title',
		  'title'      => __( 'Membership Fees' )
		);

		foreach ( LDMW_Users_Util::get_membership_grades() as $slug => $grade ) {
			$this->options_fields["membership_fee_$slug"] = array(
			  'slug'        => "membership_fee_$slug",
			  'default'     => '',
			  'description' => '',
			  'label'       => "$grade Membership Fee",
			  'field_type'  => 'number'
			);
		}

		$this->options_fields[] = array(
		  'field_type' => 'hr'
		);

		$this->options_fields[] = array(
		  'field_type' => 'section_title',
		  'title'      => __( 'Member Forms' )
		);

		$this->options_fields['communication_preference'] = array(
		  'slug'        => 'communication_preference',
		  'default'     => "",
		  'label'       => __( 'Communication Preference' ),
		  'description' => __( "Which form should control the communication's preferences" ),
		  'field_type'  => 'select',
		  'options'     => LDMW_Gravity_Util::get_gravity_forms_select_data()
		);

		$this->options_fields['areas_competence'] = array(
		  'slug'        => 'areas_competence',
		  'default'     => "",
		  'label'       => __( 'Areas of Competence' ),
		  'description' => __( "Which form should control the areas of competence" ),
		  'field_type'  => 'select',
		  'options'     => LDMW_Gravity_Util::get_gravity_forms_select_data()
		);

		$this->options_fields['fields_interest'] = array(
		  'slug'        => 'fields_interest',
		  'default'     => "",
		  'label'       => __( 'Fields of Interest' ),
		  'description' => __( "Which form should control the fields of interest" ),
		  'field_type'  => 'select',
		  'options'     => LDMW_Gravity_Util::get_gravity_forms_select_data()
		);

		$this->options_fields[] = array(
		  'field_type' => 'hr'
		);

		$this->options_fields[] = array(
		  'field_type' => 'section_title',
		  'title'      => __( 'Emails' )
		);

		$this->options_fields['offline_payments_payment_email'] = array(
		  'slug'        => 'offline_payments_payment_email',
		  'description' => 'Email that gets sent as a receipt to people who pay via offline-payments. You can use the same shortcodes as you would for the iThemes Exchange emails',
		  'default'     => "",
		  'label'       => __( 'Offline Payments Email' ),
		  'field_type'  => 'editor',
		  'options'     => array(
			'media_buttons' => false,
			'teeny'         => true,
			'textarea_rows' => 3
		  )
		);

		$this->options_fields[] = array(
		  'field_type'  => 'description',
		  'label'       => 'Email Shortcodes',
		  'description' => LDMW_Notifications_Email::rule_descriptions_option()
		);

		$this->options_fields[] = array(
		  'field_type' => 'hr'
		);

		$this->options_fields['approved_email_subject'] = array(
		  'slug'        => 'approved_email_subject',
		  'default'     => "",
		  'label'       => "Approved Email Subject",
		  'description' => '',
		  'field_type'  => 'text'
		);

		$this->options_fields['approved_email'] = array(
		  'slug'        => 'approved_email',
		  'default'     => "",
		  'label'       => __( 'Approved Email' ),
		  'description' => __( "Email that gets sent to the applicant when their application is approved." ),
		  'field_type'  => 'editor',
		  'options'     => array(
			'media_buttons' => false,
			'teeny'         => true,
			'textarea_rows' => 3
		  )
		);

		$this->options_fields['transfer_approved_email_subject'] = array(
		  'slug'        => 'transfer_approved_email_subject',
		  'default'     => "",
		  'label'       => "Transfer Approved Email Subject",
		  'description' => '',
		  'field_type'  => 'text'
		);

		$this->options_fields['transfer_approved_email'] = array(
		  'slug'        => 'transfer_approved_email',
		  'default'     => "",
		  'label'       => __( 'Transfer Approved Email' ),
		  'description' => __( "Email that gets sent to the applicant when their transfer application is approved." ),
		  'field_type'  => 'editor',
		  'options'     => array(
			'media_buttons' => false,
			'teeny'         => true,
			'textarea_rows' => 3
		  )
		);

		$this->options_fields['denied_email_subject'] = array(
		  'slug'        => 'denied_email_subject',
		  'default'     => "",
		  'label'       => "Denied Email Subject",
		  'description' => '',
		  'field_type'  => 'text'
		);

		$this->options_fields['denied_email'] = array(
		  'slug'        => 'denied_email',
		  'default'     => "",
		  'label'       => __( 'Denied Email' ),
		  'description' => __( "Email that gets sent to the applicant when their application is denied." ),
		  'field_type'  => 'editor',
		  'options'     => array(
			'media_buttons' => false,
			'teeny'         => true,
			'textarea_rows' => 3
		  )
		);

		$this->options_fields['rejected_email_subject'] = array(
		  'slug'        => 'rejected_email_subject',
		  'default'     => "",
		  'label'       => "Rejected Email Subject",
		  'description' => '',
		  'field_type'  => 'text'
		);

		$this->options_fields['rejected_email'] = array(
		  'slug'        => 'rejected_email',
		  'default'     => "",
		  'label'       => __( 'Rejected Email' ),
		  'description' => __( "Email that gets sent to the applicant when their application is rejected because it was incomplete." ),
		  'field_type'  => 'editor',
		  'options'     => array(
			'media_buttons' => false,
			'teeny'         => true,
			'textarea_rows' => 3
		  )
		);

		$this->options_fields['notify_committee_email_subject'] = array(
		  'slug'        => 'notify_committee_email_subject',
		  'default'     => "",
		  'label'       => "Notify Committee Email Subject",
		  'description' => '',
		  'field_type'  => 'text'
		);

		$this->options_fields['notify_committee_email'] = array(
		  'slug'        => 'notify_committee_email',
		  'default'     => "",
		  'label'       => __( 'Notify Committee Email' ),
		  'description' => __( "Email that gets sent to the committee from the pending applications page." ),
		  'field_type'  => 'editor',
		  'options'     => array(
			'media_buttons' => false,
			'teeny'         => true,
			'textarea_rows' => 3
		  )
		);

		$this->options_fields['notify_federal_council_email_subject'] = array(
		  'slug'        => 'notify_federal_council_email_subject',
		  'default'     => "",
		  'label'       => "Notify Federal Council Email Subject",
		  'description' => '',
		  'field_type'  => 'text'
		);

		$this->options_fields['notify_federal_council_email'] = array(
		  'slug'        => 'notify_federal_council_email',
		  'default'     => "",
		  'label'       => __( 'Notify Federal Council Email' ),
		  'description' => __( "Email that gets sent to the Federal Council members when an application is approved." ),
		  'field_type'  => 'editor',
		  'options'     => array(
			'media_buttons' => false,
			'teeny'         => true,
			'textarea_rows' => 3
		  )
		);

		$this->options_fields[] = array(
		  'field_type' => 'section_title',
		  'title'      => __( 'Renewal Emails' )
		);

		$this->options_fields[] = array(
		  'field_type' => 'hr'
		);

		$this->options_fields['original_renewal_notice_subject'] = array(
		  'slug'        => 'original_renewal_notice_subject',
		  'default'     => "",
		  'label'       => "Original Renewal Notice Email Subject",
		  'description' => '',
		  'field_type'  => 'text'
		);

		$this->options_fields['original_renewal_notice'] = array(
		  'slug'        => 'original_renewal_notice',
		  'default'     => "",
		  'label'       => __( 'Original Renewal Invoice' ),
		  'description' => __( "90 days before membership due" ),
		  'field_type'  => 'editor',
		  'options'     => array(
			'media_buttons' => false,
			'teeny'         => true,
			'textarea_rows' => 3
		  )
		);

		$this->options_fields['original_renewal_notice_sustaining_members_subject'] = array(
		  'slug'        => 'original_renewal_notice_sustaining_members_subject',
		  'default'     => "",
		  'label'       => "Original Renewal Notice Sustaining Members Email Subject",
		  'description' => '',
		  'field_type'  => 'text'
		);

		$this->options_fields['original_renewal_notice_sustaining_members'] = array(
		  'slug'        => 'original_renewal_notice_sustaining_members',
		  'default'     => "",
		  'label'       => __( 'Original invoice to sustaining members' ),
		  'description' => __( "90 days before membership due" ),
		  'field_type'  => 'editor',
		  'options'     => array(
			'media_buttons' => false,
			'teeny'         => true,
			'textarea_rows' => 3
		  )
		);

		$this->options_fields['reminder_invoice_subject'] = array(
		  'slug'        => 'reminder_invoice_subject',
		  'default'     => "",
		  'label'       => "Reminder Invoice Subject",
		  'description' => '',
		  'field_type'  => 'text'
		);

		$this->options_fields['reminder_invoice'] = array(
		  'slug'        => 'reminder_invoice',
		  'default'     => "",
		  'label'       => __( 'Reminder invoice (early August)' ),
		  'description' => __( "1 day before membership due" ),
		  'field_type'  => 'editor',
		  'options'     => array(
			'media_buttons' => false,
			'teeny'         => true,
			'textarea_rows' => 3
		  )
		);

		$this->options_fields['overdue_notice_subject'] = array(
		  'slug'        => 'overdue_notice_subject',
		  'default'     => "",
		  'label'       => "Overdue Notice Email Subject",
		  'description' => '',
		  'field_type'  => 'text'
		);

		$this->options_fields['overdue_notice'] = array(
		  'slug'        => 'overdue_notice',
		  'default'     => "",
		  'label'       => __( 'Overdue Notice' ),
		  'description' => __( "60 days after membership due" ),
		  'field_type'  => 'editor',
		  'options'     => array(
			'media_buttons' => false,
			'teeny'         => true,
			'textarea_rows' => 3
		  )
		);

		$this->options_fields['final_notice_subject'] = array(
		  'slug'        => 'final_notice_subject',
		  'default'     => "",
		  'label'       => "Final Notice Email Subject",
		  'description' => '',
		  'field_type'  => 'text'
		);

		$this->options_fields['final_notice'] = array(
		  'slug'        => 'final_notice',
		  'default'     => "",
		  'label'       => __( 'Final notice invoice' ),
		  'description' => __( "120 days after membership due" ),
		  'field_type'  => 'editor',
		  'options'     => array(
			'media_buttons' => false,
			'teeny'         => true,
			'textarea_rows' => 3
		  )
		);

		$this->options_fields[] = array(
		  'field_type'  => 'description',
		  'label'       => 'Conferences',
		  'description' => LDMW_Notifications_Email::rule_descriptions_option()
		);

		$this->options_fields[] = array(
		  'field_type' => 'hr'
		);

		$this->options_fields['late_conference_payment_subject'] = array(
		  'slug'        => 'late_conference_payment_subject',
		  'default'     => "",
		  'label'       => "Late conference payment subject",
		  'description' => '',
		  'field_type'  => 'text'
		);

		$this->options_fields['late_conference_payment'] = array(
		  'slug'        => 'late_conference_payment',
		  'default'     => "",
		  'label'       => 'Late Conference Payment',
		  'description' => 'Email sent when manually triggered to users who paid via offline payments and haven\'t paid yet.',
		  'field_type'  => 'editor',
		  'options'     => array(
			'media_buttons' => false,
			'teeny'         => true,
			'textarea_rows' => 3
		  )
		);
	}
}