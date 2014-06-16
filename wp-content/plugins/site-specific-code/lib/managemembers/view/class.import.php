<?php

/**
 *
 * @package LDMW
 * @subpackage Manage Members
 * @since 1.0
 */
class LDMW_ManageMembers_View_Import extends LDMW_ManageMembers_View {

	/**
	 * @param array $users
	 */
	public function __construct( $users ) {
		parent::__construct( $users );
	}

	/**
	 * Render the page.
	 *
	 * @return void
	 */
	public function render() {
		?>

		<div class="wrap">
		<h2>Import Members</h2>

			<h2><a href="<?php echo esc_attr( admin_url( 'user-new.php?create-member=1' ) ); ?>" class="add-new-h2">Create Member</a>
				<a href="?page=<?php echo $_GET['page']; ?>&view=import" class="add-new-h2">Import Members</a>
				<a href="?page=<?php echo $_GET['page']; ?>&view=export" class="add-new-h2">Export Members</a></h2>

			<form id="import-members" method="POST" enctype="multipart/form-data">
				<p>Upload a CSV of members to import into the system. New records with email addresses in the system, will have their records updated with the new data.</p>
				<label>CSV &nbsp;&nbsp;<input type="file" name="member_import" id="member_import"></label>
				<?php submit_button( "Import", 'primary', 'import' ); ?>
			</form>
		</div>

	<?php
	}

}