<?php

/**
 *
 * @package LDMW
 * @subpackage Application Approval
 * @since 1.0
 */
class LDMW_Application_Approval_List_View {
	/**
	 * Array of all application entries.
	 *
	 * @var array[]
	 */
	private $entries;

	/**
	 * @var array
	 */
	private $search_data = array();

	/**
	 * Set up the list view.
	 *
	 * @param $entries array[] all of the application entries
	 */
	public function __construct( $entries ) {
		$this->entries = $entries;
		$this->search_data = $_GET;
	}

	/**
	 * Render the page
	 */
	public function render() {
		wp_enqueue_style( 'ldmw-managemembers-list', LDMW_Plugin::$url . "lib/managemembers/assets/css/list.css" );
		?>

		<div class="wrap">
		    <h2>Applications</h2><br>

			<form id="pending-applications" method="GET" action="">

				<div id="search-controls">
					<div style="float:left;width: 47%;padding-right: 3%">
						<table>
							<tr>
							    <th><label for="first">First Name</label></th>
							    <td><input type="text" id="first" name="first" value="<?php echo isset( $this->search_data['first'] ) ? esc_attr( $this->search_data['first'] ) : ''; ?>"></td>
						    </tr>
							<tr>
							    <th><label for="last">Last Name</label></th>
							    <td><input type="text" id="last" name="last" value="<?php echo isset( $this->search_data['last'] ) ? esc_attr( $this->search_data['last'] ) : ''; ?>"></td>
						    </tr>

							<tr>
								<th class="label-top"><label for="type">Type</label></th>
								<td>
									<select id="type" name="type[]" multiple>
										<?php foreach ( LDMW_Application_Util::get_application_types() as $slug => $type ) : ?>
											<option value="<?php echo $slug; ?>" <?php if ( isset( $this->search_data['type'] ) && in_array( $slug, $this->search_data['type'] ) ) echo 'selected="selected"'; ?>><?php echo $type; ?></option>
										<?php endforeach; ?>
									</select>
								</td>
							</tr>
							<tr>
								<th class="label-top"><label for="status">Status</label></th>
								<td>
									<select id="status" name="status[]" multiple>
										<?php foreach ( LDMW_Application_Util::get_application_statuses() as $status ) : ?>
											<option value="<?php echo $status; ?>" <?php if ( isset( $this->search_data['status'] ) && in_array( $status, $this->search_data['status'] ) ) echo 'selected="selected"'; ?>><?php echo ucfirst( $status ); ?></option>
										<?php endforeach; ?>
									</select>
								</td>
							</tr>
						</table>
					</div>

					<div style="float:left;width: 47%;padding-right: 3%">
						<table>
							<tr>
							    <th class="label-top"><label for="grade">Grade</label></th>
							    <td>
								    <select id="grade" name="grade[]" multiple>
									    <?php foreach ( LDMW_Users_Util::get_membership_grades() as $slug => $grade ) : ?>
										    <option value="<?php echo $slug; ?>" <?php if ( isset( $this->search_data['grade'] ) && in_array( $slug, $this->search_data['grade'] ) ) echo 'selected="selected"'; ?>><?php echo $grade; ?></option>
									    <?php endforeach; ?>
							        </select>
							    </td>
						    </tr>
							<tr>
							    <th class="label-top"><label for="division">Division</label></th>
							    <td>
								    <select id="division" name="division[]" multiple>
									    <?php foreach ( LDMW_Users_Util::get_membership_divisions() as $slug => $division ) : ?>
										    <option value="<?php echo $slug; ?>" <?php if ( isset( $this->search_data['division'] ) && in_array( $slug, $this->search_data['division'] ) ) echo 'selected="selected"'; ?>><?php echo $division; ?></option>
									    <?php endforeach; ?>
							        </select>
							    </td>
						    </tr>
							<tr>
							    <td></td>
							    <td><?php submit_button( "Search", 'primary', 'search' ); ?></td>
						    </tr>
						    <tr>
							    <td></td>
							    <td><a href="<?php echo admin_url( 'admin.php?page=ldmw-applications' ); ?>" id="reset" class="button">Reset Search</a></td>
						    </tr>
						</table>
					</div>
				</div>

				<div class="cf"></div>

				<?php if ( current_user_can( 'export_applications' ) ) : ?>
					<?php submit_button( 'Export', 'secondary', 'ldmw_applications_export' ); ?>
				<?php endif; ?>

				<div id="results" style="margin-top:10px">
				    <table class="widefat">
					    <thead>
					        <tr>
						        <th>First</th>
						        <th>Last</th>
						        <th>Submit Date</th>
						        <th>Type</th>
						        <th>Grade</th>
						        <th>Fee</th>
						        <th>Date Paid</th>
						        <th>Paid by</th>
						        <th>Status</th>
						        <th>Date Sent</th>
					        </tr>
					    </thead>
					    <tfoot>
					        <tr>
						        <th>First</th>
						        <th>Last</th>
						        <th>Submit Date</th>
						        <th>Type</th>
						        <th>Grade</th>
						        <th>Fee</th>
						        <th>Date Paid</th>
						        <th>Paid by</th>
						        <th>Status</th>
						        <th>Date Sent</th>
					        </tr>
					    </tfoot>
					    <tbody>
					        <?php foreach ( $this->entries as $entry ) : ?>
						        <?php $user = get_user_by( "id", $entry['user_id'] ); ?>
						        <tr>
							        <?php if ( current_user_can( 'read_application' ) ) : ?>
								        <td><a href="<?php echo LDMW_Application_Approval_Dispatcher::get_redirect_url( 'single', 'view', $entry['entry_id'] ); ?>"><?php echo $user->first_name; ?></a></td>
							        <?php else : ?>
								        <td><?php echo $user->first_name; ?></td>
							        <?php endif; ?>

							        <td><?php echo $user->last_name; ?></td>
							        <td><?php $date = new DateTime( $entry['time'] );
								        echo $date->format( "m/d/y" ); ?></td>
							        <td><?php echo LDMW_Application_Util::application_type_slug_to_name( $entry['application_type'] ); ?></td>
							        <td><?php echo LDMW_Users_Util::membership_grade_slug_to_name( $entry['grade'] ); ?></td>
							        <td><?php $transaction = LDMW_Exchange_Util::get_application_product_recent_transaction( $user->ID );
								        if ( $transaction !== null ) echo $transaction->cart_details->total; ?></td>
							        <td><?php if ( $transaction !== null ) echo ( new DateTime( $transaction->post_date ) )->format( "m/d/y" ); ?></td>
							        <td><?php if ( $transaction !== null ) echo it_exchange_get_transaction_method_name( $transaction ); ?></td>
							        <td><?php echo ucfirst( LDMW_Application_Util::get_application_status( $entry['entry_id'] ) ); ?></td>
							        <td><?php if ( isset( $entry['registrars_advised'] ) ) echo ( new DateTime( "@" . $entry['registrars_advised'] ) )->format( get_option( 'date_format' ) ); ?></td>
						        </tr>
					        <?php endforeach; ?>
					    </tbody>
				    </table>
				</div>
				<input type="hidden" name="page" value="<?php echo esc_attr( $_GET['page'] ); ?>">
			</form>
		</div>

	<?php
	}
}