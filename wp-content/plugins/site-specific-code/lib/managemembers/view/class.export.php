<?php

/**
 *
 * @package LDMW
 * @subpackage Manage Members
 * @since 1.0
 */
class LDMW_ManageMembers_View_Export extends LDMW_ManageMembers_View {
	/**
	 * Render the page.
	 *
	 * @return void
	 */
	public function render() {
		?>

		<div class="wrap">
		    <h2>Export Members</h2>

			<h2><a href="<?php echo esc_attr( admin_url( 'user-new.php?create-member=1' ) ); ?>" class="add-new-h2">Create Member</a>
				<a href="?page=<?php echo $_GET['page']; ?>&view=import" class="add-new-h2">Import Members</a>
				<a href="?page=<?php echo $_GET['page']; ?>&view=export" class="add-new-h2">Export Members</a></h2>

		    <form id="export-members" method="GET">
			    <?php submit_button( "Export", 'primary', 'export' ); ?>
			    <input type="hidden" name="page" value="ldmw-manage-members">
			    <input type="hidden" name="view" value="export">
		    </form>
	    </div>
	<?php
	}

	/**
	 * Render the CSV.
	 */
	public function render_csv() {
		// Open the output stream
		$fh = fopen( 'php://output', 'w' );

		$columns = array();

		foreach ( $this->users[0] as $key => $value ) {
			$columns[$key] = ucwords( str_replace( "_", " ", $key ) );
		}

		// Start output buffering (to capture stream contents)
		ob_start();

		// add CSV headers
		fputcsv( $fh, $columns );

		// Loop over the * to export
		if ( ! empty( $this->users ) ) {
			foreach ( $this->users as $item ) {
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