<?php

/**
 *
 * @package LDMW
 * @subpackage Application Approval
 * @since 1.0
 */
require_once( "class.action-view.php" );

/**
 * Class LDMW_Application_Approval_Single_Edit
 */
class LDMW_Application_Approval_Single_Edit extends LDMW_Application_Approval_Single_View {

	/**
	 * Set up entry data
	 *
	 * @param $entry array
	 */
	public function __construct( $entry ) {
		parent::__construct( $entry );
		wp_enqueue_media();
	}

	/**
	 * Process the POST data, and add applicable hooks.
	 */
	protected function process_post() {
		if ( ! isset( $_POST['ldmw-single-view-nonce'] ) || wp_verify_nonce( $_POST['ldmw-single-view-nonce'], "ldmw-single-view-{$this->get_entry_value( "entry_id" )}" ) === false )
			return;

		if ( isset( $_POST['save'] ) && current_user_can( 'edit_application' ) ) {
			$saved = new IBD_Notify_Admin_Notification( get_current_user_id(), "AAS", "Application Saved" );
			$saved->send();
			try {
				$_POST['certificate_sent'] = ( new DateTime( $_POST['certificate_sent'] ) )->getTimestamp();
			}
			catch ( Exception $e ) {

			}

			if ( isset( $_POST['new_files'] ) ) {
				foreach ( $_POST['new_files'] as $name => $url ) {
					$this->entry['files'][] = array(
					  'value' => $url,
					  'label' => $name
					);
				}

				unset( $_POST['new_files'] );
			}

			foreach ( $_POST as $key => $values ) {
				if ( isset( $this->entry[$key] ) ) {
					$this->entry[$key] = $values;
				}
				else if ( in_array( $key, array( 'notes', 'incomplete_notes', 'certificate_sent' ) ) ) {
					$this->entry[$key] = $values;
				}
				else {
					foreach ( $this->entry['fields'] as &$field ) {
						if ( $field['label'] == self::form_name_to_field_label( $key ) ) {
							if ( isset( $field['inputs'] ) && is_array( $values ) ) {
								foreach ( $field['inputs'] as &$input ) {
									foreach ( $values as $label => $value ) {
										if ( $input['label'] == self::form_name_to_field_label( $label ) ) {
											$input['value'] = $value;
											unset( $values[$label] );

											break;
										}
									}
								}
							}
							else {
								$field['value'] = $values;

								break;
							}
						}
					}
				}
			}

			LDMW_Gravity_Util::update_application_entry( $this->get_entry_value( 'entry_id' ), $this->entry );
		}

		if ( isset( $_POST['delete'] ) && current_user_can( 'delete_application' ) ) {
			$deleted = new IBD_Notify_Admin_Notification( get_current_user_id(), 'AAS', "Application Deleted", array( 'class' => 'error' ) );
			$deleted->send();

			LDMW_Gravity_Util::delete_application_entry( $this->get_entry_value( 'entry_id' ) );
		}
		$this->entry = LDMW_Gravity_Util::get_application_entry( $_POST['entry_id'] );
	}

	/**
	 * @param $form_name string
	 *
	 * @return string
	 */
	public static function form_name_to_field_label( $form_name ) {
		return str_replace( "_", " ", $form_name );
	}

	/**
	 * Render the screen
	 */
	public function render() {
		?>

		<div class="wrap js">
			<form method="POST">
				<?php wp_nonce_field( "ldmw-single-view-{$this->get_entry_value( 'entry_id' )}", "ldmw-single-view-nonce" ); ?>
				<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
				<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce' ); ?>
				<input type="hidden" name="entry_id" value="<?php echo $this->get_entry_value( "entry_id" ); ?>">
				<h2>Edit Application</h2>

				<?php if ( $this->entry === null ) : ?>
					<div class="error"><p>No Application Found.</p></div>
				<?php else : ?>

					<div class="postbox-container metabox-holder columns-2">

						<div class="postbox-container meta-box-sortables" id="postboxcontainer1">
							<?php do_meta_boxes( 'toplevel_page_ldmw-applications', 'postboxcontainer1', (object) $this->entry ); ?>
						</div>

						<div class="postbox-container meta-box-sortables" id="postboxcontainer2">
							<?php do_meta_boxes( 'toplevel_page_ldmw-applications', 'postboxcontainer2', (object) $this->entry ); ?>
						</div>

	                </div>

				<?php endif; ?>
			</form>
	    </div>

	<?php
	}

	/**
	 * Render the membership information.
	 *
	 * @param $entry object
	 */
	public function render_membership_information( $entry ) {
		?>

		<table class="applicant-information">
			<tr>
				<th>Application Date</th>
				<td><?php echo $this->prepare_date()->format( "M j, Y" ); ?></td>
			</tr>
			<tr>
				<th><label for="application_type">Application Type</label></th>
				<td>
					<select name="application_type" id="application_type">
						<?php foreach ( LDMW_Application_Util::get_application_types() as $slug => $type ) : ?>
							<option <?php selected( $slug, $this->get_entry_value( 'application_type' ) ); ?> value="<?php echo $slug; ?>"><?php echo $type; ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="grade">Membership Grade</label></th>
				<td>
					<select name="grade" id="grade">
						<?php foreach ( LDMW_Users_Util::get_membership_grades() as $slug => $grade ) : ?>
							<option <?php selected( $slug, $this->get_entry_value( 'grade' ) ); ?> value="<?php echo $slug; ?>"><?php echo $grade; ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="division">Division</label></th>
				<td>
					<select name="division" id="division">
						<?php foreach ( LDMW_Users_Util::get_membership_divisions() as $slug => $division ) : ?>
							<option <?php selected( $slug, $this->get_entry_value( 'division' ) ); ?> value="<?php echo $slug; ?>"><?php echo $division; ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th>Status</th>
				<td><?php echo ucfirst( LDMW_Application_Util::get_application_status( $this->get_entry_value( 'entry_id' ) ) ); ?></td>
			</tr>
		</table>

	<?php
	}

	/**
	 * Render the division assessment meta-box.
	 *
	 * @param $entry object
	 */
	public function render_division_assessment( $entry ) {
		?>
		<div class="minor-actions">
			<p>
			    <label>Division Assessment</label>
			    <span><?php echo LDMW_Users_Util::membership_division_slug_to_name( $this->get_entry_value( 'division' ) ); ?></span>
			</p>

			<?php $notes = $this->get_entry_value( 'notes' ); ?>

			<label for="notes">Notes</label>
			<textarea id="notes" name="notes"><?php echo false === $notes ? "" : $notes; ?></textarea>

	    </div>

		<div class="major-actions">
			<div>
				<?php submit_button( "Save", 'primary', 'save', false ); ?>
				<a href="<?php echo LDMW_Application_Approval_Dispatcher::get_redirect_url( "single", "view", $this->get_entry_value( "entry_id" ) ); ?>" id="view" class="button">View</a>
			</div>

			<?php if ( current_user_can( 'delete_application' ) ) : ?>
				<div>
					<?php submit_button( "Delete", 'secondary button-warning', 'delete', false ); ?>
				</div>
			<?php endif; ?>

			<div class="cf"></div>
		</div>

	<?php

	}

	/**
	 * Render the approvals meta-box.
	 *
	 * @param $entry object
	 */
	public function render_approval( $entry ) {
		?>

		<div class="minor-actions">
			<div id="send-to-division-section" class="section">
				<label for="send-to-division">Division</label>
				<select id="send-to-division" name="send-to-division">
					<?php foreach ( LDMW_Users_Util::grading_committees() as $slug => $name ) : ?>
						<option value="<?php echo $slug; ?>"><?php echo $name; ?></option>
					<?php endforeach; ?>
				</select>
				<?php submit_button( "Send", "secondary", "send", false ); ?>
			</div>

			<hr class="section-divider">

			<div id="send-to-division-info" class="section">
				<table>
					<tr>
						<th>Applicant Advised</th>
						<td><?php echo ( $epoch = $this->get_entry_value( 'applicant_advised' ) ) !== false ? $this->get_date_time( $epoch )->format( "m/j/y" ) : ""; ?></td>
					</tr>
					<tr>
						<th>Assessment Advised</th>
						<td><?php echo ( $epoch = $this->get_entry_value( 'assessment_advised' ) ) !== false ? $this->get_date_time( $epoch )->format( "m/j/y" ) : ""; ?></td>
					</tr>
					<tr>
						<th>Registrars Advised</th>
						<td><?php echo ( $epoch = $this->get_entry_value( 'registrars_advised' ) ) !== false ? $this->get_date_time( $epoch )->format( "m/j/y" ) : ""; ?></td>
					</tr>
					<tr>
						<th><label for="certificate_sent">Certificate Sent</label></th>
						<td><input type="text" id="certificate_sent" class="datepicker" name="certificate_sent" data-date-format="<?php echo LDMW_Util::dateStringToDatepickerFormat( "m/d/y" ); ?>" value="<?php echo ( $epoch = $this->get_entry_value( 'certificate_sent' ) ) != false ? ( new DateTime( "@" . $epoch ) )->format( 'm/d/y' ) : ''; ?>"></td>
					</tr>
					<tr>
						<th>User</th>
						<td><a href="<?php echo admin_url( 'user-edit.php?user_id=' . $this->get_entry_value( 'user_id' ) ); ?>">View User</a></td>
					</tr>
				</table>
			</div>

			<hr class="section-divider">

			<label for="incomplete_notes">Incomplete application notes.</label>
			<textarea id="incomplete_notes" name="incomplete_notes"><?php echo $this->get_entry_value( 'incomplete_notes' ); ?></textarea>

		</div>

	<?php
	}

	/**
	 * Render the applicants information.
	 *
	 * @param $entry object
	 */
	public function render_applicant_information( $entry ) {
		?>

		<table class="applicant-information">
			<?php foreach ( $fields = $this->get_fields() as $key => $field ) : ?>
				<?php if ( isset( $field['inputs'] ) ) : ?>
					<tr>
					    <td colspan="2"><h4><?php echo $field['label']; ?></h4></td>
				    </tr>
			        <?php foreach ( $field['inputs'] as $input ) : ?>
						<tr>
                            <th><label for="<?php echo $input['label']; ?>"><?php echo $input['label']; ?></label></th>
                            <td><input type="text" id="<?php echo $input['label']; ?>" name="<?php echo $field['label']; ?>[<?php echo $input['label']; ?>]" value="<?php echo $input['value']; ?>"></td>
                        </tr>
					<?php endforeach; ?>
				<?php else: ?>
					<?php if ( isset( $fields[$key - 1] ) && isset( $fields[$key - 1]['inputs'] ) ) : ?>
						<tr class="spacer"><td></td></tr>
					<?php endif; ?>




					<tr>
                        <th><label for="<?php echo $field['label']; ?>"><?php echo $field['label']; ?></label></th>
                        <td><input type="text" id="<?php echo $field['label']; ?>" name="<?php echo $field['label']; ?>" value="<?php echo ! empty( $field['value'] ) ? $field['value'] : ""; ?>"></td>
                    </tr>

				<?php endif; ?>

			<?php endforeach; ?>
		</table>

	<?php
	}

	public function render_attachment_information( $entry ) {
		parent::render_attachment_information( $entry );
		?>
		<h4>Add a File</h4>
		<div id="file-container"></div>
		<input type="button" class="button" name="add-file_button" id="add-file_button" value="Upload">
	<?php
	}
}