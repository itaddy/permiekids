<?php

/**
 *
 * @package LDMW
 * @subpackage Application Approval
 * @since 1.0
 */
class LDMW_Application_Export {

	/**
	 * @var array
	 */
	protected $entries = array();

	/**
	 * @param $entries array
	 */
	public function __construct( $entries ) {
		$this->entries = $this->prepare_data( $entries );
	}

	/**
	 * @param $data array
	 *
	 * @return array
	 */
	protected function prepare_data( $data ) {
		$prepared_data = array();

		foreach ( $data as $row ) {
			$prepared_data[] = $this->prepare_row( $row );
		}

		return $prepared_data;
	}

	/**
	 * @param $data array
	 *
	 * @return array
	 */
	protected function prepare_row( $data ) {
		$row = array();
		$user = get_user_by( 'id', $data['user_id'] );
		$row['user_id'] = $user->ID;
		$row['first_name'] = $user->first_name;
		$row['last_name'] = $user->last_name;
		$row['email'] = $user->user_email;
		$row['submit_date'] = ( new DateTime( $data['time'] ) )->format( 'm/d/y' );
		$row['type'] = LDMW_Application_Util::application_type_slug_to_name( $data['application_type'] );
		$row['grade'] = LDMW_Users_Util::membership_grade_slug_to_name( $data['grade'] );
		$row['division'] = LDMW_Users_Util::membership_division_slug_to_name( $data['division'] );

		$transaction = LDMW_Exchange_Util::get_application_product_recent_transaction( $user->ID );
		$row['fee'] = $transaction->cart_details->total;
		$row['date_paid'] = ( new DateTime( $transaction->post_date ) )->format( 'm/d/y' );
		$row['paid_by'] = ucfirst( str_replace( "-", " ", $transaction->transaction_method ) );
		$row['status'] = LDMW_Application_Util::get_application_status( $data['entry_id'] );
		$row['date_sent'] = isset( $data['registrars_advised'] ) ? ( new DateTime( "@" . $data['registrars_advised'] ) )->format( 'm/d/y' ) : 'Not Sent';

		return $row;
	}

	/**
	 * Render the final CSV
	 */
	public function render_csv() {
		// Open the output stream
		$fh = fopen( 'php://output', 'w' );

		// Start output buffering (to capture stream contents)
		ob_start();

		// add CSV headers
		fputcsv( $fh, array(
			'User ID', 'First Name', 'Last Name', 'Email', 'Submit Date', 'Type',
			'Grade', 'Division', 'Fee', 'Date Paid', 'Paid By', 'Status', 'Date Sent'
		  )
		);

		// Loop over the * to export
		if ( ! empty( $this->entries ) ) {
			foreach ( $this->entries as $item ) {
				fputcsv( $fh, $item );
			}
		}

		// Get the contents of the output buffer
		$string = ob_get_clean();

		$filename = 'csv_' . date( 'Ymd' ) . '_' . date( 'His' );

		// Output CSV-specific headers
		header( "Content-type: text/csv" );
		header( "Content-Disposition: attachment; filename=$filename.csv" );
		header( "Pragma: no-cache" );
		header( "Expires: 0" );

		exit( $string );
	}
}