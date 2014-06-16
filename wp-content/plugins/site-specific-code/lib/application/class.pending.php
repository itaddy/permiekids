<?php

/**
 *
 * @package LDMW
 * @subpackage Appllcations
 * @since 1.0
 */
class LDMW_Application_Pending {
	/**
	 * @var WP_User[] of WP_User objects
	 */
	protected $applicants = array();

	/**
	 * Prepare everything for display
	 */
	public function __construct() {
	}

	/**
	 * Set up everything, and render the page.
	 */
	public function init() {
		$this->applicants = self::get_pending_applicants( $_GET );
		$this->render();
	}

	/**
	 * Get all users who have purchased the application product, but have not yet submitted an application
	 *
	 * @param $get array
	 *
	 * @return WP_User[]
	 */
	protected static function get_pending_applicants( $get ) {
		$query = array();

		$meta_query = array(
		  'relation' => 'AND',
		  array(
			'key'     => 'ldmw_membership_application',
			'value'   => '1',
			'compare' => 'NOT EXISTS'
		  )
		);

		if ( ! empty( $get['first_name'] ) ) {
			$meta_query[] = array(
			  'key'     => 'first_name',
			  'value'   => $get['first_name'],
			  'compare' => "LIKE"
			);
		}

		if ( ! empty( $get['last_name'] ) ) {
			$meta_query[] = array(
			  'key'     => 'last_name',
			  'value'   => $get['last_name'],
			  'compare' => "LIKE"
			);
		}

		if ( ! empty( $get['email'] ) ) {
			$query['search'] = "*" . $get['email'] . "*";
			$query['search_columns'] = array( 'user_email' );
		}

		$query['meta_query'] = $meta_query;
		$query['role'] = get_option( 'default_role', 'subscriber' );

		$users = ( new WP_User_Query( $query ) )->get_results();

		$final_users = array();

		foreach ( $users as $key => $user ) {
			$products = it_exchange_get_customer_products( $user->ID );
			$found = false;

			foreach ( $products as $product ) {
				if ( $product['product_id'] == LDMW_Options_Model::get_instance()->application_form_product ) {
					$found = true;
					break;
				}
			}

			if ( $found === true ) {
				$final_users[] = $user;
			}
		}

		return $final_users;
	}

	/**
	 * Prepare applicant data for display.
	 *
	 * @param $user WP_User
	 *
	 * @return array
	 */
	protected function prepare_applicant_data( $user ) {
		$data = array(
		  'user_id'      => $user->ID,
		  'first_name'   => $user->first_name,
		  'last_name'    => $user->last_name,
		  'email'        => $user->user_email,
		  'account_time' => ( new DateTime( $user->user_registered ) )->format( get_option( 'date_format' ) )
		);

		return $data;
	}

	/**
	 *
	 */
	public function render() {
		wp_enqueue_style( 'ldmw-managemembers-list', LDMW_Plugin::$url . "lib/managemembers/assets/css/list.css" );
		?>
		<div class="wrap">

		<form id="pending-applications" method="GET" action="">

		<h2>Pending Applications</h2>

			<div style="width: 100%; max-width: 500px;padding: 50px 0">
				<table>
					<tr>
						<th><label for="first_name">First Name</label></th>
						<td><input type="text" id="first_name" name="first_name" value="<?php echo isset( $_GET['first_name'] ) ? esc_attr( $_GET['first_name'] ) : ''; ?>"></td>
					</tr>
					<tr>
						<th><label for="last_name">Last Name</label></th>
						<td><input type="text" id="last_name" name="last_name" value="<?php echo isset( $_GET['last_name'] ) ? esc_attr( $_GET['last_name'] ) : ''; ?>"></td>
					</tr>
					<tr>
						<th><label for="email">Email</label></th>
						<td><input type="text" id="email" name="email" value="<?php echo isset( $_GET['email'] ) ? esc_attr( $_GET['email'] ) : ''; ?>"></td>
					</tr>
					<tr>
					    <td></td>
					    <td><?php submit_button( "Search", 'primary', 'search' ); ?></td>
				    </tr>
				    <tr>
					    <td></td>
					    <td><a href="<?php echo admin_url( 'admin.php?page=ldmw-pending-applications' ); ?>" id="reset" class="button">Reset Search</a></td>
				    </tr>
				</table>
			</div>

			<table class="widefat">
				<thead>
					<tr>
						<th>User ID</th>
						<th>First Name</th>
						<th>Last Name</th>
						<th>Email Address</th>
						<th>Account Creation Time</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th>User ID</th>
						<th>First Name</th>
						<th>Last Name</th>
						<th>Email Address</th>
						<th>Account Creation Time</th>
					</tr>
				</tfoot>
				<tbody>
					<?php foreach ( $this->applicants as $applicant ) : $applicant = $this->prepare_applicant_data( $applicant ); ?>
						<tr>
							<td><a href="<?php echo get_edit_user_link( $applicant['user_id'] ); ?>"><?php echo $applicant['user_id']; ?></a></td>
							<td><?php echo $applicant['first_name']; ?></td>
							<td><?php echo $applicant['last_name']; ?></td>
							<td><?php echo $applicant['email']; ?></td>
							<td><?php echo $applicant['account_time']; ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<input type="hidden" name="page" value="<?php echo esc_attr( $_GET['page'] ); ?>">

		</form>
		</div><?php
	}
}