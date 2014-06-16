<?php

/**
 *
 * @package LDMW
 * @subpackage Application Approval
 * @since 1.0
 */
class LDMW_Application_Util {
	/**
	 * Get the status of an application.
	 *
	 * Possible values { "received", "approved", "denied", "incomplete", "forwarded", "rejected" }
	 *
	 * @param $entry_id int
	 *
	 * @return string
	 */
	public static function get_application_status( $entry_id ) {
		$application = LDMW_Gravity_Util::get_application_entry( $entry_id );

		return isset( $application['status'] ) ? $application['status'] : "received";
	}

	/**
	 * Get all application statuses.
	 *
	 * @return array
	 */
	public static function get_application_statuses() {
		return array( "received", "approved", "denied", "incomplete", "forwarded", 'rejected' );
	}

	/**
	 * Update the status of an application.
	 *
	 * @see LDMW_Application_Util::get_application_status
	 *
	 * @param $entry_id int
	 * @param $status string
	 */
	public static function update_application_status( $entry_id, $status ) {
		$application = LDMW_Gravity_Util::get_application_entry( $entry_id );
		$application['status'] = $status;
		LDMW_Gravity_Util::update_application_entry( $entry_id, $application );
	}

	/**
	 * Change an application meta value.
	 *
	 * @param $entry_id int
	 * @param $key string
	 * @param $value string
	 */
	public static function change_application_meta( $entry_id, $key, $value ) {
		$entry = LDMW_Gravity_Util::get_application_entry( $entry_id );
		$entry[$key] = $value;
		LDMW_Gravity_Util::update_application_entry( $entry_id, $entry );
	}

	/**
	 * Get the URL to an application entry
	 *
	 * @param $entry_id int
	 * @param $action string
	 *
	 * @return string
	 */
	public static function get_application_entry_link( $entry_id, $action = 'view' ) {
		$url = admin_url( 'admin.php' );
		$url = add_query_arg( array(
			'page'   => 'ldmw-applications',
			'view'   => 'single',
			'action' => $action,
			'id'     => $entry_id
		  ), $url
		);

		return $url;
	}

	/**
	 * Get different types of applications.
	 *
	 * @return array
	 */
	public static function get_application_types() {
		return array(
		  'new'     => 'New',
		  'upgrade' => 'Grade Transfer'
		);
	}

	/**
	 * Translate an application type slug to a proper name.
	 *
	 * @param $slug string
	 *
	 * @return string|null
	 */
	public static function application_type_slug_to_name( $slug ) {
		$types = self::get_application_types();

		if ( isset( $types[$slug] ) )
			return $types[$slug];
		else
			return null;
	}

	/**
	 * @param $search_data
	 *
	 * @return array
	 */
	public static function parse_search( $search_data ) {
		$entries = LDMW_Gravity_Util::get_application_entries();

		if ( empty( $entries ) || ! is_array( $entries ) )
			return array();

		foreach ( $entries as $key => $entry ) {
			if ( ! empty( $search_data['grade'] ) && ! in_array( $entry['grade'], $search_data['grade'] ) ) {
				unset( $entries[$key] );
				continue;
			}

			if ( ! empty( $search_data['division'] ) && ! in_array( $entry['division'], $search_data['division'] ) ) {
				unset( $entries[$key] );
				continue;
			}

			if ( ! empty( $search_data['type'] ) && ! in_array( $entry['application_type'], $search_data['type'] ) ) {
				unset( $entries[$key] );
				continue;
			}

			if ( ! empty( $search_data['status'] ) && ! in_array( isset( $entry['status'] ) ? $entry['status'] : 'received', $search_data['status'] ) ) {
				unset( $entries[$key] );
				continue;
			}

			$user = get_user_by( 'id', $entry['user_id'] );

			if ( ! empty( $search_data['first'] ) && strpos( $user->first_name, $search_data['first'] ) === false ) {
				unset( $entries[$key] );
				continue;
			}

			if ( ! empty( $search_data['last'] ) && strpos( $user->last_name, $search_data['last'] ) === false ) {
				unset( $entries[$key] );
				continue;
			}
		}

		return $entries;

	}

}