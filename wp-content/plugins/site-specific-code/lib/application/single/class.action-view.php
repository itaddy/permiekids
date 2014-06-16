<?php

/**
 *
 * @package LDMW
 * @subpackage Application Approval
 * @since 1.0
 */
class LDMW_Application_Approval_Single_View {
	/**
	 * The array of data for the current entry.
	 *
	 * @var $entry array
	 */
	protected $entry;

	/**
	 * Set up entry data
	 *
	 * @param $entry array
	 */
	public function __construct( $entry ) {
		$this->entry = $entry;
		$this->process_post();
		$this->add_meta_boxes();

		wp_enqueue_script( "postbox" );
		wp_enqueue_script( "ldmw-aa-single", LDMW_Plugin::$url . "lib/application/assets/js/single.js" );
		wp_enqueue_style( "ldmw-aa-single", LDMW_Plugin::$url . "lib/application/assets/css/single.css" );

		wp_enqueue_script( 'jquery.modal', LDMW_Plugin::$url . "assets/vendor/jquery.modal/jquery.modal.min.js", array( 'jquery' ) );
		wp_enqueue_style( 'jquery.modal', LDMW_Plugin::$url . "assets/vendor/jquery.modal/jquery.modal.css" );
	}

	/**
	 *
	 */
	public function add_meta_boxes() {
		add_meta_box( 'applicant-information', 'Applicant Information', array( $this, 'render_applicant_information' ), null, 'postboxcontainer1', 'high' );
		add_meta_box( 'attachment-information', 'Attachment Information', array( $this, 'render_attachment_information' ), null, 'postboxcontainer1', 'low' );

		if ( current_user_can( 'reject_application' ) )
			add_meta_box( 'approval', 'Approval', array( $this, 'render_approval' ), null, 'postboxcontainer2', 'high' );

		add_meta_box( 'division-assessment', 'Division Assesment', array( $this, 'render_division_assessment' ), null, 'postboxcontainer2', 'high' );
		add_meta_box( 'membership-information', 'Membership Information', array( $this, 'render_membership_information' ), null, 'postboxcontainer2', 'default' );
		add_meta_box( 'payment-history', 'Payment History', array( $this, 'render_payment_history' ), null, 'postboxcontainer2', 'low' );
	}

	/**
	 * Process the POST data, and add applicable hooks.
	 */
	protected function process_post() {
		if ( ! isset( $_POST['ldmw-single-view-nonce'] ) || wp_verify_nonce( $_POST['ldmw-single-view-nonce'], "ldmw-single-view-{$this->get_entry_value( "entry_id" )}" ) === false )
			return;

		if ( ! empty( $_POST['notes'] ) )
			LDMW_Application_Util::change_application_meta( $this->get_entry_value( 'entry_id' ), 'notes', $_POST['notes'] );

		if ( ! empty( $_POST['incomplete_notes'] ) )
			LDMW_Application_Util::change_application_meta( $this->get_entry_value( 'entry_id' ), 'incomplete_notes', $_POST['incomplete_notes'] );

		if ( isset( $_POST['approve'] ) && current_user_can( 'approve_application' ) ) {
			if ( ( $type = $this->get_entry_value( 'application_type' ) ) == 'new' ) {
				do_action( "ldmw_application_approve", $_POST['entry_id'] );
				$approval = new IBD_Notify_Admin_Notification( get_current_user_id(), 'AAS', "Application Approved" );
				$approval->send();
				update_user_meta( $this->get_entry_value( 'user_id' ), 'ldmw_membership_approved', true );
			}
			else if ( $type == 'upgrade' ) {
				do_action( 'ldmw_application_upgrade_approve', $_POST['entry_id'] );
				$approval = new IBD_Notify_Admin_Notification( get_current_user_id(), 'AAS', "Membership Upgrade Approved" );
				$approval->send();
				update_user_meta( $this->get_entry_value( 'user_id' ), 'ldmw_membership_grade', $this->get_entry_value( 'grade' ) );
			}
		}

		if ( isset( $_POST['deny'] ) && current_user_can( 'deny_application' ) ) {
			do_action( "ldmw_application_deny", $_POST['entry_id'] );
			$deny = new IBD_Notify_Admin_Notification( get_current_user_id(), "AAS", "Application Denied", array( 'class' => "error" ) );
			$deny->send();
		}

		if ( isset( $_POST['reject'] ) && current_user_can( 'reject_application' ) ) {
			$approval = new IBD_Notify_Admin_Notification( get_current_user_id(), 'AAS', "Application Rejected for Incompleteness" );
			$approval->send();
			do_action( 'ldmw_application_reject', $_POST['entry_id'] );
		}

		if ( isset( $_POST['send'] ) && current_user_can( 'send_application' ) ) {
			do_action( "ldmw_application_send", $_POST['entry_id'], $_POST['send-to-division'] );
			$sent = new IBD_Notify_Admin_Notification( get_current_user_id(), "AAS", "Application Sent" );
			$sent->send();
		}

		if ( isset( $_POST['certificate_sent'] ) && current_user_can( 'send_application' ) ) {
			do_action( 'ldmw_certificate_sent', $_POST['entry_id'] );
			( new IBD_Notify_Admin_Notification( get_current_user_id(), "AAS", "Certificate Marked as Sent" ) )->send();

			LDMW_Application_Util::change_application_meta( $this->get_entry_value( 'entry_id' ), 'certificate_sent', time() );
		}
		$this->entry = LDMW_Gravity_Util::get_application_entry( $_POST['entry_id'] );
	}

	/**
	 * Get the fields for the form entry.
	 *
	 * @return array
	 */
	protected function get_fields() {
		$entry = $this->entry;

		return $entry['fields'];
	}

	/**
	 * Get the value of a particular entry.
	 *
	 * @param $value string
	 *
	 * @return mixed|bool
	 */
	protected function get_entry_value( $value ) {
		$entry = $this->entry;

		if ( isset( $entry[$value] ) )
			return $entry[$value];
		else
			return false;
	}

	/**
	 * Get a DateTime object for the time the form was specified.
	 *
	 * @return DateTime
	 */
	protected function prepare_date() {
		return new DateTime( $this->get_entry_value( 'time' ) );

	}

	/**
	 * @param $epoch
	 *
	 * @return DateTime
	 */
	protected function get_date_time( $epoch ) {
		return new DateTime( "@$epoch" );
	}

	/**
	 * Get all transactions for the current applicant.
	 *
	 * @return IT_Exchange_Transaction[]
	 */
	protected function get_applicant_transactions() {
		return it_exchange_get_customer_transactions( $this->get_entry_value( 'user_id' ) );
	}

	/**
	 * Render the screen
	 */
	public function render() {
		?>

		<div class="wrap js">
			<form method="POST" id="applicant_form">
				<?php wp_nonce_field( "ldmw-single-view-{$this->get_entry_value( 'entry_id' )}", "ldmw-single-view-nonce" ); ?>
				<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
				<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce' ); ?>
				<input type="hidden" name="entry_id" value="<?php echo $this->get_entry_value( "entry_id" ); ?>">
				<h2>View Application</h2>

				<?php if ( $this->entry === null ) : ?>
					<div class="error"><p>No Application Found.</p></div>
				<?php else : ?>

					<div class="postbox-container metabox-holder columns-2">

						<div class="postbox-container meta-box-sortables" id="postboxcontainer1">
							<?php do_meta_boxes( get_current_screen(), 'postboxcontainer1', (object) $this->entry ); ?>
						</div>

						<div class="postbox-container meta-box-sortables" id="postboxcontainer2">
							<?php do_meta_boxes( get_current_screen(), 'postboxcontainer2', (object) $this->entry ); ?>
						</div>

	                </div>

				<?php endif; ?>
			</form>
	    </div>

	<?php
	}

	/**
	 * Render a user's payment history.
	 *
	 * @param $entry object
	 */
	public function render_payment_history( $entry ) {
		?>
		<table class="payment-history">
			<thead>
				<tr>
					<th>Date</th>
					<th>Fee</th>
					<th>Method</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $this->get_applicant_transactions() as $transaction ) : ?>
					<?php $date = new DateTime( $transaction->post_date ); ?>
					<tr>
						<td><?php echo $date->format( "m/d/y" ); ?></td>
						<td><?php echo $transaction->cart_details->total; ?></td>
						<td><?php echo it_exchange_get_transaction_method_name( $transaction ) ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
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
				<th>Application Type</th>
				<td><?php echo LDMW_Application_Util::application_type_slug_to_name( $this->get_entry_value( 'application_type' ) ); ?></td>
			</tr>
			<tr>
				<th>Membership Grade</th>
				<td><?php echo LDMW_Users_Util::membership_grade_slug_to_name( $this->get_entry_value( "grade" ) ); ?></td>
			</tr>
			<tr>
				<th>Division</th>
				<td><?php echo LDMW_Users_Util::membership_division_slug_to_name( $this->get_entry_value( "division" ) ); ?></td>
			</tr>
			<tr>
				<th>Status</th>
				<td><?php echo ucfirst( LDMW_Application_Util::get_application_status( $this->get_entry_value( 'entry_id' ) ) ); ?></td>
			</tr>
			<tr>
				<th>User</th>
				<td><a href="<?php echo admin_url( 'user-edit.php?user_id=' . $this->get_entry_value( 'user_id' ) ); ?>">View User</a></td>
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

			<?php if ( ! empty( $notes ) ) : ?>

				<label for="notes">Notes</label>
				<p><?php echo $notes; ?></p>
				<textarea id="notes" name="notes" style="display: none"><?php echo $notes; ?></textarea>

			<?php endif; ?>

	    </div>
		<div class="major-actions">
			<div>
				<?php if ( current_user_can( 'edit_application' ) ) : ?>
					<div class="edit-link">
					<a href="<?php echo LDMW_Application_Approval_Dispatcher::get_redirect_url( "single", "edit", $this->get_entry_value( "entry_id" ) ); ?>" class="button">Edit</a>
				</div>
				<?php endif; ?>

				<?php if ( current_user_can( 'approve_application' ) ) : ?>
					<?php submit_button( "Approve", 'primary division-assessment', 'approve', false ); ?>
				<?php endif; ?>

				<?php if ( current_user_can( 'deny_application' ) ) : ?>
					<?php submit_button( "Deny", "secondary division-assessment", 'deny', false ); ?>
				<?php endif; ?>
			</div>
			<div class="cf"></div>
		</div>

		<?php if ( empty( $notes ) ) : ?>

			<div class="modal" id="note_modal">
				<label for="notes">Application Approval/Denial Notes</label>
				<p>Please add some notes to let the applicant know why there application was either approved, or denied.</p>
				<textarea id="notes" name="notes" rows="10"></textarea>

				<div style="float: right">
					<input type="submit" class="button" name="deny" value="Deny">
					<input type="submit" class="button button-primary" name="approve" value="Approve">
				</div>
			</div>

		<?php endif; ?>

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
						<th>Certificate Sent</th>
						<td><?php $certificate_sent = $this->get_entry_value( 'certificate_sent' );
							echo ! empty( $certificate_sent ) ? ( new DateTime( "@" . $certificate_sent ) )->format( 'm/j/y' )
							  : get_submit_button( 'Certificate Sent', 'secondary', 'certificate_sent', false ); ?></td>
					</tr>
				</table>
			</div>

			<?php $notes = $this->get_entry_value( 'incomplete_notes' ); ?>

			<?php if ( ! empty( $notes ) ) : ?>

				<hr class="section-divider">

				<label for="incomplete_notes">Incomplete application notes.</label>
				<p><?php echo $notes; ?></p>
				<textarea id="incomplete_notes" name="incomplete_notes" style="display: none"><?php echo $notes; ?></textarea>

			<?php endif; ?>
		</div>

		<div class="major-actions">
			<div style="float: right">
				<?php submit_button( "Reject as Incomplete", 'secondary', 'reject', false ); ?>
			</div>
			<div class="cf"></div>
		</div>

		<?php if ( empty( $notes ) ) : ?>
			<div class="modal" id="incomplete_notes_modal">
				<label for="incomplete_notes">Incomplete Notes</label>
				<p>You are marking this application as incomplete. Please add some notes to let the applicant know why.</p>
				<textarea id="incomplete_notes" name="incomplete_notes" rows="10"></textarea>
				<input type="submit" name="reject" class="button button-warning" style="float: right" value="Reject">
			</div>
		<?php endif; ?>

	<?php
	}

	/**
	 * Render the attachment information.
	 *
	 * @param $entry object
	 */
	public function render_attachment_information( $entry ) {
		?>

		<?php foreach ( $this->get_entry_value( 'files' ) as $file ) : ?>
			<a href="<?php echo $file['value']; ?>" target="_blank"><label class="post-format-icon post-format-aside"><?php echo $file['label']; ?></label></a>
		<?php endforeach; ?>

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
			<tr><td colspan="2"><h4>Name</h4></td></tr>
			<tr><th>First</th><td><?php $user = get_user_by( 'id', $this->get_entry_value( 'user_id' ) );
					echo $user->first_name; ?></td></tr>
			<tr><th>Last</th><td><?php echo $user->last_name; ?></td></tr>

			<tr class="spacer"><td></td></tr>
			<tr><th>Email</th><td><?php echo $user->user_email; ?></td></tr>

			<tr><td colspan="2"><h4>Home Address</h4></td></tr>
			<?php foreach ( LDMW_Users_Util::get_home_address( $this->get_entry_value( 'user_id' ) ) as $key => $value ) : ?>
				<tr>
					<th><?php echo ucfirst( str_replace( "_", " ", $key ) ); ?></th>
					<td><?php echo $value; ?></td>
				</tr>
			<?php endforeach; ?>

			<tr><td colspan="2"><h4>Work Address</h4></td></tr>
			<?php foreach ( LDMW_Users_Util::get_work_address( $this->get_entry_value( 'user_id' ) ) as $key => $value ) : ?>
				<tr>
					<th><?php echo ucfirst( str_replace( "_", " ", $key ) ); ?></th>
					<td><?php echo $value; ?></td>
				</tr>
			<?php endforeach; ?>

			<tr><td colspan="2"><h4>Billing Address</h4></td></tr>
			<?php foreach ( it_exchange_get_customer_billing_address( $this->get_entry_value( 'user_id' ) ) as $key => $value ) : ?>
				<tr>
					<th><?php echo ucfirst( str_replace( array( "_", '-' ), " ", $key ) ); ?></th>
					<td><?php echo $value; ?></td>
				</tr>
			<?php endforeach; ?>

			<?php foreach ( $fields = $this->get_fields() as $key => $field ) : ?>
				<?php if ( isset( $field['inputs'] ) ) : ?>
					<tr>
					    <td colspan="2"><h4><?php echo $field['label']; ?></h4></td>
				    </tr>
			        <?php foreach ( $field['inputs'] as $input ) : ?>
						<tr>
                            <th><?php echo $input['label']; ?></th>
                            <td><?php echo isset( $input['value'] ) ? esc_html( $input['value'] ) : ""; ?></td>
                        </tr>
					<?php endforeach; ?>
				<?php else: ?>
					<?php if ( isset( $fields[$key - 1] ) && isset( $fields[$key - 1]['inputs'] ) ) : ?>
						<tr class="spacer"><td></td></tr>
					<?php endif; ?>







					<tr>
                        <th><?php echo $field['label']; ?></th>
                        <td>
                            <?php if ( isset( $field['value'] ) && wp_http_validate_url( $field['value'] ) !== false ) : ?>
	                            <a href="<?php echo $field['value']; ?>"><?php echo substr( $field['value'], 0, 30 ) . "..."; ?></a>
                            <?php else : ?>
	                            <?php echo isset( $field['value'] ) ? esc_html( $field['value'] ) : ""; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
				<?php endif; ?>

			<?php endforeach; ?>
		</table>

	<?php
	}
}