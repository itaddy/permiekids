<?php

/**
 *
 * @package LDMW
 * @subpackage Gravity Forms
 * @since 1.0
 */
class LDMW_Gravity_Util {
	/**
	 * Option name that stores application entries.
	 */
	const OPTION_NAME = "application_entries";

	/**
	 * Get all gravity forms.
	 *
	 * @return Object[]
	 */
	public static function get_gravity_forms() {
		return GFFormsModel::get_forms( 1 );
	}

	/**
	 * Get an array of form_id => title.
	 *
	 * @return array
	 */
	public static function get_gravity_forms_select_data() {
		$select_data = array();
		$select_data[false] = "";

		foreach ( self::get_gravity_forms() as $form ) {
			$select_data[$form->id] = $form->title;
		}

		return $select_data;
	}

	/**
	 * Get the selected communication preference's form.
	 *
	 * @return array|false
	 */
	public static function get_communication_preference_form() {
		return GFFormsModel::get_form( LDMW_Options_Model::get_instance()->communication_preference );
	}

	/**
	 * Get the selected areas of competence form.
	 *
	 * @return array|false
	 */
	public static function get_areas_competence_form() {
		return GFFormsModel::get_form( LDMW_Options_Model::get_instance()->areas_competence );
	}

	/**
	 * Get the selected fields of interest form.
	 *
	 * @return array|false
	 */
	public static function get_fields_interest_form() {
		return GFFormsModel::get_form( LDMW_Options_Model::get_instance()->fields_interest );
	}

	/**
	 * Get the selected application form.
	 *
	 * @return array|false
	 */
	public static function get_selected_application_form() {
		$form_id = LDMW_Options_Model::get_instance()->application_form;

		if ( empty( $form_id ) )
			return false;

		return GFFormsModel::get_form( $form_id );
	}

	/**
	 * Get the form ID of the selected gravity form.
	 *
	 * @return int
	 */
	public static function get_selected_application_form_id() {
		return LDMW_Options_Model::get_instance()->application_form;
	}

	/**
	 * Get a user's membership application.
	 *
	 * @param $user_id int
	 *
	 * @return array
	 */
	public static function get_user_application( $user_id ) {
		$entry_id = get_user_meta( $user_id, 'ldmw_membership_application', true );

		return self::get_application_entry( $entry_id );
	}

	/**
	 * Get all application entries.
	 *
	 * @return array
	 */
	public static function get_application_entries() {
		return get_option( self::OPTION_NAME, array() );
	}

	/**
	 * Get the application based off of an entry ID
	 *
	 * @param $entry_id int
	 *
	 * @return array
	 */
	public static function get_application_entry( $entry_id ) {
		$entries = self::get_application_entries();

		if ( isset( $entries[$entry_id] ) )
			return $entries[$entry_id];
		else
			return null;
	}

	/**
	 * Add application entry
	 *
	 * @param $entry array
	 */
	public static function add_application_entry( $entry ) {
		$entries = self::get_application_entries();
		$entries[$entry['entry_id']] = $entry;
		self::update_application_entries( $entries );
	}

	/**
	 * Update all application entries
	 *
	 * @param $entries array
	 */
	public static function update_application_entries( $entries ) {
		update_option( self::OPTION_NAME, $entries );
	}

	/**
	 * Update the application entry.
	 *
	 * @param $entry_id int
	 * @param $entry array
	 */
	public static function update_application_entry( $entry_id, $entry ) {
		$entries = self::get_application_entries();
		$entries[$entry_id] = $entry;
		self::update_application_entries( $entries );
	}

	/**
	 * Delete an application entry.
	 *
	 * @param $entry_id int
	 */
	public static function delete_application_entry( $entry_id ) {
		$entries = self::get_application_entries();
		unset( $entries[$entry_id] );
		do_action( 'ldmw_delete_application', $entry_id );
		self::update_application_entries( $entries );
		do_action( 'ldmw_deleted_application', $entry_id );
	}
}