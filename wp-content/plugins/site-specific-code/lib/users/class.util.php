<?php

/**
 *
 * @package LDMW
 * @subpackage Users
 * @since 1.0
 */
class LDMW_Users_Util {

	/**
	 * Is this a sustaining member.
	 *
	 * @param $user_id int
	 *
	 * @return bool
	 */
	public static function is_sustaining_member( $user_id ) {
		return 'sustaining' == self::get_membership_grade( $user_id );
	}

	/**
	 * Determine if a given user is a valid member
	 *
	 * @param $user WP_User
	 *
	 * @return bool
	 */
	public static function is_member( $user ) {
		return in_array( LDMW_Users_Base::$member_role_slug, $user->roles );
	}

	/**
	 * Determine if WP_User is a committee member.
	 *
	 * @param WP_User $user
	 * @param string|null $committee
	 *
	 * @return bool
	 */
	public static function is_committee_member( WP_User $user, $committee = null ) {
		if ( $committee === null ) {
			return ( in_array( LDMW_Users_Base::$committee_role_slug, $user->roles ) );
		}
		else {
			return ( in_array( LDMW_Users_Base::$committee_role_slug, $user->roles ) && in_array( $committee, self::get_a_members_committees( $user ) ) );
		}
	}

	/**
	 * Get all the committees a member serves on.
	 *
	 * @param WP_User $user
	 *
	 * @return array
	 */
	public static function get_a_members_committees( WP_User $user ) {
		return is_array( $meta = get_user_meta( $user->ID, 'ldmw_committees_member', true ) ) ? $meta : array();
	}

	/**
	 * Get all committee members.
	 *
	 * @param $committee string|null
	 *
	 * @return WP_User[]
	 */
	public static function get_committee_members( $committee = null ) {
		$committee_members = array();

		$query = new WP_User_Query( array(
			'role' => LDMW_Users_Base::$committee_role_slug
		  )
		);

		$users = $query->get_results();

		foreach ( $users as $user ) {
			if ( LDMW_Users_Util::is_committee_member( $user, $committee ) ) {
				$committee_members[] = $user;
			}
		}

		return $committee_members;
	}

	/**
	 * Array of slug => committee name.
	 *
	 * @return array
	 */
	public static function committees() {
		return array(
		  'aas_federal_council'         => "AAS Federal Council",
		  'aas_divisional_chairpersons' => "AAS Divisional Chairpersons",
		  'divisional_committees'       => "Divisional Committees",
		  'divisional_secretaries'      => "Divisional Secretaries",
		  'divisional_treasuries'       => "Divisional Treasuries",
		  'nsw_grading_committee'       => "NSW Grading Committee",
		  'qld_grading_committee'       => "QLD Grading Committee",
		  'sa_grading_committee'        => "SA Grading Committee",
		  'vic_grading_committee'       => "VIC Grading Committee",
		  'wa_grading_committee'        => "WA Grading Committee",
		  'aas_standards_committee'     => "AAS Standards Committee Reps"
		);
	}

	/**
	 * Array of slug => committee name.
	 *
	 * But only the grading committees.
	 *
	 * @return array
	 */
	public static function grading_committees() {
		return array(
		  'nsw_grading_committee' => "NSW Grading Committee",
		  'qld_grading_committee' => "QLD Grading Committee",
		  'sa_grading_committee'  => "SA Grading Committee",
		  'vic_grading_committee' => "VIC Grading Committee",
		  'wa_grading_committee'  => "WA Grading Committee"
		);
	}

	/**
	 * Get the name of a committee from a slug.
	 *
	 * @param $slug string
	 *
	 * @return string|bool
	 */
	public static function committee_slug_to_name( $slug ) {
		$committees = self::committees();

		if ( isset( $committees[$slug] ) ) {
			return $committees[$slug];
		}
		else {
			return false;
		}
	}

	/**
	 * Get the slug of a committee from a committee name.
	 *
	 * @param $name string
	 *
	 * @return bool|string
	 */
	public static function committee_name_to_slug( $name ) {
		foreach ( self::committees() as $slug => $c_name ) {
			if ( $name == $c_name ) {
				return $slug;
			}
		}

		return false;
	}

	/**
	 * Determine if valid committee slug.
	 *
	 * @param $slug string
	 *
	 * @return bool
	 */
	public static function is_valid_committee( $slug ) {
		$committees = self::committees();

		return isset( $committees[$slug] );
	}

	/**
	 * Get the different membership statuses.
	 *
	 * @return array
	 */
	public static function get_membership_statuses() {
		return array(
		  'current' => 'Current',
		  'overdue' => "Overdue"
		);
	}

	/**
	 * @param $slug
	 *
	 * @return null
	 */
	public static function get_membership_status_slug_to_name( $slug ) {
		$statuses = self::get_membership_statuses();

		if ( isset( $statuses[$slug] ) ) {
			return $statuses[$slug];
		}
		else {
			return null;
		}
	}

	/**
	 * Get the different types of membership grades.
	 *
	 * @return array
	 */
	public static function get_membership_grades() {
		return array(
		  'aaas'            => "AAAS",
		  'aas-grad-1'      => "AAS (Grad) 1",
		  'aas-grad-2'      => "AAS (Grad) 2",
		  'aas-grad-3'      => "AAS (Grad) 3",
		  'aas-grad-4'      => "AAS (Grad) 4",
		  'faas'            => "FAAS",
		  'hon'             => "HON",
		  'life'            => "LIFE",
		  'maas'            => "MAAS",
		  'maternity-leave' => "Maternity Leave",
		  'retired'         => "RETIRED",
		  'resigned'        => "RESIGNED",
		  'reinstated'      => "REINSTATED",
		  'sr'              => "SR",
		  'st'              => "ST",
		  'sustaining'      => "SUSTAINING",
		);
	}

	/**
	 * Get the different types of membership grades,
	 * but with the full display title.
	 *
	 * @return array
	 */
	public static function get_membership_grades_full_title() {
		return array(
		  'aaas'            => "Associate [AAAS]",
		  'aas-grad-1'      => "Graduate - First year [AAS (Grad) 1]",
		  'aas-grad-2'      => "Graduate - Second year [AAS (Grad) 2]",
		  'aas-grad-3'      => "Graduate - Third year [AAS (Grad) 3]",
		  'aas-grad-4'      => "Graduate - Fourth year [AAS (Grad) 4]",
		  'faas'            => "Fellow [FAAS]",
		  'hon'             => "Honorary Member [HON]",
		  'life'            => "Life Member [LIFE]",
		  'maas'            => "Member [MAAS]",
		  'maternity-leave' => "Maternity Leave",
		  'retired'         => "Retired Member",
		  'resigned'        => "Resigned Member",
		  'reinstated'      => "Reinstated Member",
		  'sr'              => "Subscriber [SR]",
		  'st'              => "Student [ST]",
		  'sustaining'      => "Sustaining Member",
		);
	}

	/**
	 * Get the different types of membership grades
	 * that people can apply to.
	 *
	 * @return array
	 */
	public static function get_applicable_membership_grades() {
		return array(
		  'aaas'       => "Associate [AAAS]",
		  'aas-grad-1' => "Graduate",
		  'maas'       => "Member [MAAS]",
		  'sr'         => "Subscriber [SR]",
		  'st'         => "Student [ST]",
		  'sustaining' => "Sustaining Member (Invitation Only)"
		);
	}

	/**
	 * Translate a membership grade slug to a proper name.
	 *
	 * @param $slug string
	 * @param $full boolean
	 *
	 * @return string|null
	 */
	public static function membership_grade_slug_to_name( $slug, $full = false ) {
		if ( false === $full ) {
			$types = self::get_membership_grades();

			if ( isset( $types[$slug] ) ) {
				return $types[$slug];
			}
			else {
				return null;
			}
		}
		else {
			$types = self::get_membership_grades_full_title();

			if ( isset( $types[$slug] ) ) {
				return $types[$slug];
			}
			else {
				return null;
			}
		}
	}

	/**
	 * Get the different types of membership divisions.
	 *
	 * @return array
	 */
	public static function get_membership_divisions() {
		return array(
		  'nsw' => "New South Wales/ACT",
		  'qld' => "Queensland",
		  'sa'  => "South Australia",
		  'vic' => "Victoria/Tasmania",
		  'wa'  => "Western Australia/Northern Territory",
		);
	}

	/**
	 * Translate a membership division slug to a proper name.
	 *
	 * @param $slug string
	 *
	 * @return string|null
	 */
	public static function membership_division_slug_to_name( $slug ) {
		$types = self::get_membership_divisions();

		if ( isset( $types[$slug] ) ) {
			return $types[$slug];
		}
		else {
			return null;
		}
	}

	/**
	 * Get a user's communication preferences.
	 *
	 * @param $user_id
	 *
	 * @return array
	 */
	public static function get_communication_preferences( $user_id ) {
		$prefs = get_user_meta( $user_id, 'ldmw_communication_preferences', true );

		if ( ! is_array( $prefs ) ) {
			$prefs = array();
		}

		return $prefs;
	}

	/**
	 * Update a user's communication preferences.
	 *
	 * @param $user_id int
	 * @param array $prefs
	 *
	 * @return bool
	 */
	public static function update_communication_preferences( $user_id, array $prefs = array() ) {
		return update_user_meta( $user_id, 'ldmw_communication_preferences', $prefs );
	}

	/**
	 * Get a user's fields of interest.
	 *
	 * @param $user_id
	 *
	 * @return array
	 */
	public static function get_fields_of_interest( $user_id ) {

		$prefs = get_user_meta( $user_id, 'ldmw_fields_interest', true );

		if ( ! is_array( $prefs ) ) {
			$prefs = array();
		}

		return $prefs;
	}

	/**
	 * @param $user_id int
	 * @param array $prefs
	 *
	 * @return bool
	 */
	public static function update_fields_of_interest( $user_id, $prefs = array() ) {
		return update_user_meta( $user_id, 'ldmw_fields_interest', $prefs );
	}

	/**
	 * Get a user's areas of competence.
	 *
	 * @param $user_id
	 *
	 * @return array
	 */
	public static function get_areas_of_competence( $user_id ) {
		$prefs = get_user_meta( $user_id, 'ldmw_areas_competence', true );

		if ( ! is_array( $prefs ) ) {
			$prefs = array();
		}

		return $prefs;
	}

	/**
	 * @param $user_id int
	 * @param array $prefs
	 *
	 * @return bool
	 */
	public static function update_areas_of_competence( $user_id, $prefs = array() ) {
		return update_user_meta( $user_id, 'ldmw_areas_competence', $prefs );
	}

	/**
	 * Get a user's home address.
	 *
	 * @param $user_id int
	 *
	 * @return array
	 */
	public static function get_home_address( $user_id ) {
		$defaults = array(
		  'address_1' => '',
		  'address_2' => '',
		  'suburb'    => '',
		  'state'     => '',
		  'postcode'  => '',
		  'country'   => 'AU'
		);
		$address = get_user_meta( $user_id, 'ldmw_home_address', true );

		if ( ! is_array( $address ) ) {
			$address = array();
		}

		$address = ITUtility::merge_defaults( $address, $defaults );

		return $address;
	}

	/**
	 * Update a user's home address.
	 *
	 * @param $user_id int
	 * @param $address array
	 *
	 * @return boolean
	 */
	public static function update_home_address( $user_id, $address ) {
		$defaults = array(
		  'address_1',
		  'address_2',
		  'suburb',
		  'state',
		  'postcode',
		  'country'
		);

		$save_address = array();

		foreach ( $defaults as $value ) {
			if ( isset( $address[$value] ) ) {
				$save_address[$value] = sanitize_text_field( $address[$value] );
			}
		}

		return update_user_meta( $user_id, 'ldmw_home_address', $save_address );
	}

	/**
	 * Get a user's work address.
	 *
	 * @param $user_id int
	 *
	 * @return array
	 */
	public static function get_work_address( $user_id ) {
		$defaults = array(
		  'address_1' => '',
		  'address_2' => '',
		  'suburb'    => '',
		  'state'     => '',
		  'postcode'  => '',
		  'country'   => 'AU'
		);
		$address = get_user_meta( $user_id, 'ldmw_work_address', true );

		if ( ! is_array( $address ) ) {
			$address = array();
		}

		$address = ITUtility::merge_defaults( $address, $defaults );

		return $address;
	}

	/**
	 * Update a user's work address.
	 *
	 * @param $user_id int
	 * @param $address array
	 *
	 * @return boolean
	 */
	public static function update_work_address( $user_id, $address ) {
		$defaults = array(
		  'address_1',
		  'address_2',
		  'suburb',
		  'state',
		  'postcode',
		  'country'
		);

		$save_address = array();

		foreach ( $defaults as $value ) {
			if ( isset( $address[$value] ) ) {
				$save_address[$value] = sanitize_text_field( $address[$value] );
			}
		}

		return update_user_meta( $user_id, 'ldmw_work_address', $save_address );
	}

	/**
	 * Get a user's phone number.
	 *
	 * @param $user_id int
	 * @param $type string (home|work|mobile)
	 *
	 * @return string
	 */
	public static function get_phone( $user_id, $type ) {
		return get_user_meta( $user_id, "ldmw_{$type}_phone", true );
	}

	/**
	 * Update a user's phone number.
	 *
	 * @param $user_id int
	 * @param $type string (home|work|mobile)
	 * @param $value string
	 *
	 * @return bool
	 */
	public static function update_phone( $user_id, $type, $value ) {
		return update_user_meta( $user_id, "ldmw_{$type}_phone", $value );
	}

	/**
	 * @param $user_id
	 *
	 * @return string
	 */
	public static function get_membership_grade( $user_id ) {
		return get_user_meta( $user_id, 'ldmw_membership_grade', true );
	}

	/**
	 * @param $user_id
	 *
	 * @return string
	 */
	public static function get_membership_division( $user_id ) {
		return get_user_meta( $user_id, 'ldmw_membership_division', true );
	}

	/**
	 * @param $user_id
	 *
	 * @return string
	 */
	public static function get_membership_status( $user_id ) {
		$status = get_user_meta( $user_id, 'ldmw_membership_status', true );

		if ( null === self::get_membership_status_slug_to_name( $status ) ) {
			$status = 'current';
			update_user_meta( $user_id, 'ldmw_membership_status', $status );
		}

		return $status;
	}

	/**
	 * Get the divisions a committee member represents.
	 *
	 * @param $user_id int
	 *
	 * @return array
	 */
	public static function get_committee_persons_division( $user_id ) {
		return get_user_meta( $user_id, 'ldmw_division', true );
	}

	/**
	 * Update the divisions a committee member represents
	 *
	 * @param $user_id int
	 * @param $division string
	 */
	public static function update_committee_persons_division( $user_id, $division ) {
		update_user_meta( $user_id, 'ldmw_division', $division );
	}

	/**
	 * Get all the committee members of a certain division
	 *
	 * @param null|string $division
	 *
	 * @return WP_User[]
	 */
	public static function get_committee_members_of_division( $division = null ) {
		$args = array(
		  'role'   => LDMW_Users_Base::$federal_council_role_slug,
		  'fields' => 'all_with_meta'
		);

		if ( $division !== null ) {
			$args['meta_query'] = array(
			  'relation' => 'AND',
			  array(
				'key'   => 'ldmw_division',
				'value' => $division
			  )
			);
		}

		$query = new WP_User_Query( $args );

		return $query->get_results();
	}

	/**
	 * Get the general secretary user
	 *
	 * @return null|WP_User
	 */
	public static function get_general_secretary_user() {
		$id = LDMW_Options_Model::get_instance()->general_secretary;

		if ( empty( $id ) )
			return null;

		$user = get_user_by( 'id', $id );

		if ( false === $user )
			return null;

		return $user;
	}
}