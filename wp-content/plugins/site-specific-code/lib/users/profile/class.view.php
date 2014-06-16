<?php

/**
 *
 * @package    LDMW
 * @subpackage Users
 * @since      1.0
 */
class LDMW_Users_Profile_View extends LDMW_Users_View {
	/**
	 * Add necessary hooks and filters.
	 *
	 * @param $user_id
	 */
	public function __construct( $user_id = null ) {
		parent::__construct( $user_id );
	}

	/**
	 * Process the post data.
	 *
	 * @param $post
	 */
	public function process_post( $post ) {

		if ( ! isset( $post['ldmw_nonce'] ) || ! wp_verify_nonce( $post['ldmw_nonce'], 'ldmw-profile-update' ) ) {
			return;
		}
		if ( ! $this->can_edit_page() ) {
			return;
		}

		if ( ! empty( $post['division'] ) && null !== LDMW_Users_Util::membership_division_slug_to_name( $post['division'] ) ) {
			update_user_meta( $this->user_id, 'ldmw_membership_division', $post['division'] );
		}

		if ( ! empty( $post['grade'] ) && null !== LDMW_Users_Util::membership_grade_slug_to_name( $post['grade'] ) ) {
			update_user_meta( $this->user_id, 'ldmw_membership_grade', $post['grade'] );
		}

		if ( ! empty( $post['status'] ) && null !== LDMW_Users_Util::get_membership_status_slug_to_name( $post['status'] ) ) {
			update_user_meta( $this->user_id, 'ldmw_membership_status', $post['status'] );
		}

		if ( empty( $post['next_membership_paid'] ) ) {
			delete_user_meta( $this->user_id, 'ldmw_next_membership_paid' );
		}
		elseif ( $post['next_membership_paid'] == "on" ) {
			update_user_meta( $this->user_id, 'ldmw_next_membership_paid', true );
		}
	}

	/**
	 * Render the additional fields.
	 */
	public function render() {
		?>
		<table class="form-table">
			<tr>
				<th>User Created Time</th>
				<td>
					<?php $user = get_user_by( 'id', $this->user_id );
					echo ( new DateTime( $user->user_registered ) )->format( get_option( 'date_format' ) ); ?>
				</td>
			</tr>
			<tr>
				<th>Last Logged In Time</th>
				<td>
					<?php $time = get_user_meta( $this->user_id, 'ldmw_last_loggedin_time', true );
					if ( ! empty( $time ) ) {
						echo ( new DateTime( "@$time" ) )->format( get_option( 'date_format' ) );
					}; ?>
				</td>
			</tr>
		</table>

		<?php

		if ( ! $this->can_edit_page() ) {
			return;
		}

		?>

		<table class="form-table">
			<tr>
				<th><label for="division">Membership Division</label></th>
				<td>
					<select id="division" name="division">
						<?php foreach ( LDMW_Users_Util::get_membership_divisions() as $slug => $name ) : ?>
							<option value="<?php echo $slug; ?>" <?php selected( $slug, get_user_meta( $this->user_id, 'ldmw_membership_division', true ) ); ?>><?php echo $name; ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="grade">Membership Grade</label></th>
				<td>
					<select id="grade" name="grade">
						<?php foreach ( LDMW_Users_Util::get_membership_grades() as $slug => $name ) : ?>
							<option value="<?php echo $slug; ?>" <?php selected( $slug, get_user_meta( $this->user_id, 'ldmw_membership_grade', true ) ); ?>><?php echo $name; ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="status">Membership Status</label></th>
				<td>
					<select id="status" name="status">
						<?php foreach ( LDMW_Users_Util::get_membership_statuses() as $slug => $name ) : ?>
							<option value="<?php echo $slug; ?>" <?php selected( $slug, get_user_meta( $this->user_id, 'ldmw_membership_status', true ) ); ?>><?php echo $name; ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="next_membership_paid">Next Membership Paid</label></th>
				<td>
					<input type="checkbox" id="next_membership_paid" name="next_membership_paid" <?php checked( get_user_meta( $this->user_id, 'ldmw_next_membership_paid', true ) ); ?>>
				</td>
			</tr>
		</table>
		<?php wp_nonce_field( 'ldmw-profile-update', 'ldmw_nonce' ); ?>

	<?php
	}
}