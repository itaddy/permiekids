<?php

/**
 *
 * @package LDMW
 * @subpackage Manage Members
 * @since 1.0
 */
class LDMW_ManageMembers_Controller_Import extends LDMW_ManageMembers_Controller {

	/**
	 * @var array of errors we encountered while parsing the CSV
	 */
	protected $errors = array();

	/**
	 * Add model
	 *
	 * @param $model LDMW_ManageMembers_Model_Users
	 */
	public function __construct( $model ) {
		parent::__construct( $model );

		if ( isset( $_POST['import'] ) ) {
			$this->process_files( $_FILES );
		}
	}

	/**
	 * Process the uploaded files.
	 *
	 * @param $files
	 *
	 * @throws InvalidArgumentException
	 */
	protected function process_files( $files ) {
		$path = $files['member_import']['tmp_name'];
		$this->parse_csv( $path );
	}

	/**
	 * Parse the actual CSV
	 *
	 * Columns
	 *  0 => ID
	 *  1 => Email
	 *  2 => First Name
	 *  3 => Last Name
	 *  4 => Membership Status
	 *  5 => Membership Grade
	 *  6 => Membership Division
	 *  7 => Admission Date YYYY-MM-DD
	 *  8 => Title
	 *  9 => Notes
	 *  10 => Company Name
	 *  11 => Address 1
	 *  12 => Address 2
	 *  13 => Suburb
	 *  14 => State
	 *  15 => Postcode
	 *  16 => Country
	 *  17 => Work Phone
	 *  18 => Mobile
	 *
	 * @param $csv string
	 *
	 * @throws InvalidArgumentException
	 */
	protected function parse_csv( $csv ) {
		require( LDMW_Plugin::$dir . "vendor/Coseva/CSV.php" );

		try {
			$coseva = new \Coseva\CSV( $csv );
		}
		catch ( Exception $e ) {
			$notification = new IBD_Notify_Admin_Notification( get_current_user_id(), 'AAS Failed Member Import', "File System Error, please try again", array( 'class' => 'error' ) );
			$notification->send();

			return;
		}

		$coseva->flushEmptyRows( true );

		/*
		 * If an ID is not valid, then we can't update a user.
		 *
		 * If an email is not valid, then we can't create a user.
		 *
		 * If we don't have either, then we can't do anything with this data
		 */
		$coseva->filter( function ( $row ) {
			  if ( $this->validate_user_row( $row ) === false ) {
				  $this->errors[] = "Member " . $row[2] . " " . $row[3] . " could not be imported, because there was no email address or member ID.";

				  return array(); // so let's drop this row.
			  }

			  return $row;
		  }
		);

		/*
		 * Default membership status should be current.
		 */
		$coseva->filter( 4, function ( $column ) {
			  if ( empty( $column ) )
				  $column = 'current';

			  return $column;
		  }
		);

		/*
		 * All members must have a grade specified.
		 */
		$coseva->filter( function ( $row ) {
			  if ( $this->editing_user( $row ) === false && ( empty( $row[5] ) || LDMW_Users_Util::membership_grade_slug_to_name( $row[5] ) == null ) ) {
				  $this->errors[] = "Member " . $row[2] . " " . $row[3] . " could not be imported, because there was no membership grade provided.";

				  return array();
			  }

			  return $row;
		  }
		);

		/*
		 * All members must have a division specified.
		 */
		$coseva->filter( function ( $row ) {
			  if ( $this->editing_user( $row ) === false && ( empty( $row[6] ) || LDMW_Users_Util::membership_division_slug_to_name( $row[6] ) == null ) ) {
				  $this->errors[] = "Member " . $row[2] . " " . $row[3] . " could not be imported, because there was no membership division provided.";

				  return array();
			  }

			  return $row;
		  }
		);

		/*
		 * Convert YYYY-MM-DD into an Epoch timestamp.
		 *
		 * If the time is improperly formatted,
		 * then set the time to now.
		 */
		$coseva->filter( 7, function ( $col ) {
			  try {
				  $time = new DateTime( $col );
			  }
			  catch ( Exception $e ) {
				  $time = new DateTime();
			  }

			  return $time->getTimestamp();
		  }
		);

		/*
		 * Ensure that a proper country value is given.
		 */
		$coseva->filter( function ( $row ) {
			  $row[16] = strtoupper( $row[16] );

			  if ( strlen( $row[16] ) > 2 || false === array_key_exists( $row[16], it_exchange_get_data_set( 'countries' ) ) ) {
				  $this->errors[] = "Member " . $row[2] . " " . $row[3] . " country is improperly formatted";

				  $row[16] = "";
			  }

			  return $row;
		  }
		);

		/*
		 * Make all states upper case
		 */
		$coseva->filter( 14, function ( $col ) {
			  return strtoupper( $col );
		  }
		);

		/*
		 * Work phone should only have digits.
		 */
		$coseva->filter( 17, function ( $col ) {
			  return preg_replace( '/\D/', '', $col );
		  }
		);

		/*
		 * Mobile phone should only have digits.
		 */
		$coseva->filter( 18, function ( $col ) {
			  return preg_replace( '/\D/', '', $col );
		  }
		);

		$coseva->parse();

		array_shift( $this->errors ); // remove first error from CSV header

		$parsed_csv = $coseva->getIterator();

		$user_data = $this->parsed_csv_to_user_data_array( $parsed_csv );

		if ( empty( $user_data ) ) {
			$notification = new IBD_Notify_Admin_Notification( get_current_user_id(), 'AAS', 'Invalid CSV Provided', array( 'class' => 'error' ) );
			$notification->send();
		}

		$results = $this->model->update_or_insert_users( $user_data );

		$message = "";

		if ( $results['insert'] > 0 )
			$message .= $results['insert'] . " members imported. ";

		if ( $results['update'] > 0 )
			$message .= $results['update'] . " members updated. ";

		if ( !empty( $message ) ) {
			$notification = new IBD_Notify_Admin_Notification( get_current_user_id(), 'AAS', $message );
			$notification->send();
		}

		$message = "";

		if ( $results['insert_fail'] > 0 )
			$message .= $results['insert_fail'] + count( $this->errors ) . " members failed to import. ";

		if ( $results['update_fail'] > 0 )
			$message .= $results['insert_fail'] + count( $this->errors ) . " members failed to be updated. ";

		if ( !empty( $message ) ) {
			$notification = new IBD_Notify_Admin_Notification( get_current_user_id(), 'AAS', $message, array( 'class' => 'error' ) );
			$notification->send();
		}

		$message = "";
		foreach ( $this->errors as $error ) {
			$message .= "<p>$error</p>";
		}

		if ( !empty( $message ) ) {
			$notification = new IBD_Notify_Admin_Notification( get_current_user_id(), 'AAS Failed Member Import', $message, array( 'class' => 'error' ) );
			$notification->send();
		}
	}

	/**
	 * Validate that the user info of an array is ok.
	 *
	 * @param $row array
	 *
	 * @return bool
	 */
	protected function validate_user_row( $row ) {
		if ( $this->editing_user( $row ) )
			return true;

		if ( is_email( trim( $row[1] ) ) )
			return true;

		return false;
	}

	/**
	 * Return true if we are editing a user.
	 *
	 * @param $row
	 *
	 * @return bool
	 */
	protected function editing_user( $row ) {
		return ( get_user_by( 'id', $row[0] ) || get_user_by( 'email', $row[1] ) );
	}

	/**
	 * Convert a parsed CSV into an array suitable for the model.
	 *
	 * ie, populate user_name fields, user_email fields, etc...
	 *
	 * @param $parsed_csv array|ArrayIterator
	 *
	 * @return array
	 */
	protected function parsed_csv_to_user_data_array( $parsed_csv ) {
		$users = array();
		foreach ( $parsed_csv as $row ) {

			$user_data = array(
			  'id'                  => $row[0], // user id, might be empty
			  'user_login'          => $row[1], // user email
			  'user_pass'           => wp_generate_password( 12 ), // for now we will have WP generate a PW for us
			  'user_email'          => $row[1], // user email
			  'first_name'          => $row[2], // first name
			  'last_name'           => $row[3], // last name
			  'membership_status'   => strtolower( $row[4] ), // membership status
			  'membership_grade'    => $row[5], // membership grade
			  'membership_division' => $row[6], // membership division
			  'start_date'          => $row[7], // admission date
			  'title'               => $row[8], // title
			  'customer_note'       => $row[9], // notes
			  'mobile_phone'        => $row[18], // mobile
			  'work_phone'          => $row[17], // work phone
			  'billing'             => array(
				'first-name'   => $row[2], // first name
				'last-name'    => $row[3], // last name
				'phone'        => $row[17], // work phone
				'company-name' => $row[10], // company name
				'email'        => $row[1], // user email
				'address1'     => $row[11], // address 1
				'address2'     => $row[12], // address 2
				'city'         => $row[13], // suburb
				'state'        => $row[14], // state
				'zip'          => $row[15], // postcode
				'country'      => $row[16], // country
			  )
			);

			$users[] = $user_data;
		}

		return $users;
	}

	/**
	 * Serve the page.
	 */
	public function serve() {
		$view = new LDMW_ManageMembers_View_Import( array() );
		$view->render();
	}
}