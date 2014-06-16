<?php

/**
 *
 * @package LDMW
 * @subpackage Testing
 * @since 1.0
 */
class LDMW_Testing_Base {
	/**
	 * Add necessary hooks and filters.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );

		if ( isset( $_POST['go'] ) )
			$this->process_post( $_POST );
	}

	/**
	 * Register the tools menu.
	 */
	public function register_menu() {
		add_management_page( "Testing", 'Testing', 'manage_options', 'ldmw-testing', array( $this, 'render' ) );
	}

	/**
	 * Process the POST data
	 */
	public function process_post( $post ) {
		if ( ! isset( $post['_wpnonce'] ) || ! wp_verify_nonce( $post['_wpnonce'], 'ldmw-testing' ) )
			return;

		if ( isset( $post['action_original_renewal_notice'] ) ) {
			add_action( 'current_screen', function () {
				  do_action( 'ldmw_send_renewal_notices', 'original_renewal_notice' );
			  }
			);
		}

		if ( isset( $post['action_reminder_invoice'] ) ) {
			add_action( 'current_screen', function () {
				  do_action( 'ldmw_send_renewal_notices', 'reminder_invoice' );
			  }
			);
		}

		if ( isset( $post['action_overdue_notice'] ) ) {
			add_action( 'current_screen', function () {
				  do_action( 'ldmw_send_renewal_notices', 'overdue_notice' );
			  }
			);
		}

		if ( isset( $post['action_final_notice'] ) ) {
			add_action( 'current_screen', function () {
				  do_action( 'ldmw_send_renewal_notices', 'final_notice' );
			  }
			);
		}
	}

	/**
	 *
	 */
	public function render() {
		?>

		<div class="wrap">
			<form method="POST" action="" id="testing_page">

		        <h2>AAS Testing Suite</h2>

				<!--<table class="form-table">
					<tr>
						<th colspan="2"><h4 style="margin-bottom: 0">Session Type</h4></th>
					</tr>
					<tr>
						<td colspan="2" style="padding: 0"><p style="max-width: 600px">What session type do you want to employ? Single session will perform the checked actions, and then destroy the user.
								Multi session will perform the checked actions, and then you will have to manually delete the user.
								Helpful for when you want to take additional steps after the initially checked tasks.</p>
						</td>
					</tr>
					<tr>
						<th><label for="single_session">Single Session</label></th>
						<td><input type="radio" id="single_session" name="session_type" value="single_session" checked="checked"></td>
					</tr>
					<tr>
						<th><label for="multi_session">Multi Session</label></th>
						<td><input type="radio" id="multi_session" name="session_type" value="multi_session"></td>
					</tr>
				</table>

			    <h3>Mock User</h3>
			    <table class="form-table">
				    <tr>
					    <th><label for="username">Username</label></th>
					    <td><input type="text" id="username" name="username"></td>
				    </tr>
				    <tr>
					    <th><label for="email">Email</label></th>
					    <td><input type="text" id="email" name="email"></td>
				    </tr>
				    <tr>
					    <th><label for="first_name">First Name</label></th>
					    <td><input type="text" id="first_name" name="first_name"></td>
				    </tr>
				    <tr>
					    <th><label for="last_name">Last Name</label></th>
					    <td><input type="text" id="last_name" name="last_name"></td>
				    </tr>
				    <tr>
					    <th><label for="membership_division">Membership Division</label></th>
					    <td>
						    <select id="membership_division" name="membership_division">
							    <?php /*foreach ( LDMW_Users_Util::get_membership_divisions() as $slug => $division ) : */ ?>
								    <option value="<?php /*echo $slug; */ ?>"><?php /*echo $division; */ ?></option>
							    <?php /*endforeach; */ ?>
						    </select>
					    </td>
				    </tr>
				    <tr>
					    <th><label for="membership_grade">Membership Grade</label></th>
					    <td>
						    <select id="membership_grade" name="membership_grade">
							    <?php /*foreach ( LDMW_Users_Util::get_membership_grades() as $slug => $grade ) : */ ?>
								    <option value="<?php /*echo $slug; */ ?>"><?php /*echo $grade; */ ?></option>
							    <?php /*endforeach; */ ?>
						    </select>
					    </td>
				    </tr>
				    <tr>
					    <th><label for="membership_status">Membership Status</label></th>
					    <td>
						    <select id="membership_status" name="membership_status">
							    <?php /*foreach ( LDMW_Users_Util::get_membership_statuses() as $slug => $status ) : */ ?>
								    <option value="<?php /*echo $slug; */ ?>"><?php /*echo $status; */ ?></option>
							    <?php /*endforeach; */ ?>
						    </select>
					    </td>
				    </tr>
				    <tr>
					    <th><label for="sustaining">Sustaining Member</label></th>
					    <td><input type="checkbox" id="sustaining" name="sustaining"></td>
				    </tr>
			    </table>-->

			    <h3>Membership</h3>
				<p>Check the boxes of the actions you want to simulate.</p>
				<table class="form-table">
					<tr>
						<th><label for="action_original_renewal_notice">Trigger Renewal Notice</label></th>
						<td><input type="checkbox" id="action_original_renewal_notice" name="action_original_renewal_notice"></td>
					</tr>
					<tr>
						<th><label for="action_reminder_invoice">Trigger Reminder Invoice</label></th>
						<td><input type="checkbox" id="action_reminder_invoice" name="action_reminder_invoice"></td>
					</tr>
					<tr>
						<th><label for="action_overdue_notice">Trigger Overdue Notice</label></th>
						<td><input type="checkbox" id="action_overdue_notice" name="action_overdue_notice"></td>
					</tr>
					<tr>
						<th><label for="action_final_notice">Trigger Final Notice</label></th>
						<td><input type="checkbox" id="action_final_notice" name="action_final_notice"></td>
					</tr>
				</table>

				<?php submit_button( "Go", 'primary', 'go' ); ?>

				<?php wp_nonce_field( "ldmw-testing" ); ?>
			</form>

	    </div>

	<?php
	}

}