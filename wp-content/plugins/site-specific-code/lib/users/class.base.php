<?php

/**
 *
 * @package    LDMW
 * @subpackage Users
 * @since      1.0
 */
class LDMW_Users_Base {
	/**
	 * @var string The Role slug for committee members.
	 */
	public static $committee_role_slug = "committee-member";

	/**
	 * @var string The role slug for members.
	 */
	public static $member_role_slug = "member";

	/**
	 * @var string The role slug for divisional secretaries
	 */
	public static $federal_council_role_slug = "federal-council";

	/**
	 * @var string The role slug for the Federal Registrar
	 */
	public static $federal_registrar_role_slug = "federal-registrar";

	/**
	 * Constructor.
	 *
	 * Register roles and capabilites.
	 */
	public function __construct() {
		//$this->register_role();
		add_action( 'show_user_profile', array( $this, 'add_profile_fields' ) );
		add_action( 'edit_user_profile', array( $this, 'add_profile_fields' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_profile_fields' ) );
		add_action( 'personal_options_update', array( $this, 'save_profile_fields' ) );
		add_action( 'current_screen', array( $this, 'register_new_user_screen' ) );
		add_action( 'user_register', array( $this, 'save_new_user_data' ) );
		add_action( 'wp_login', array( $this, 'last_login_time' ), 10, 2 );
		add_filter( 'map_meta_cap', array( $this, 'map_meta_cap_application' ), 10, 4 );
		add_action( 'auth_redirect', array( $this, 'auth_redirect' ) );
	}

	/**
	 * Redirect on login to the account page if the current user can not read
	 *
	 * @param $user_id int
	 */
	public function auth_redirect( $user_id ) {
		if ( ! user_can( $user_id, 'read' ) || user_can( $user_id, 'subscriber' ) ) {
			wp_redirect( it_exchange_get_page_url( 'account' ) );
			exit;
		}
	}

	/**
	 * Register the committee member role.
	 */
	public function register_role() {
		remove_role( self::$committee_role_slug );

		add_role( self::$committee_role_slug, "Committee Member", array(
			'read'                => TRUE,
			'list_applications'   => true,
			'read_application'    => true,
			'edit_application'    => true,
			'approve_application' => true,
			'deny_application'    => true

		  )
		);

		add_role( self::$member_role_slug, "Member", array(
			'read' => FALSE
		  )
		);

		add_role( self::$federal_council_role_slug, "Federal Council", array(
			'read' => TRUE
		  )
		);

		add_role( self::$federal_registrar_role_slug, "Federal Registrar", array(
			'read' => TRUE
		  )
		);
	}

	/**
	 * Application related meta capabilites
	 *
	 * @return array
	 */
	public static function application_meta_caps() {
		return array(
		  'list_applications',
		  'read_application', 'edit_application',
		  'deny_application', 'approve_application',
		  'reject_application', 'send_application',
		  'delete_application', 'export_applications'
		);
	}

	/**
	 * Map our custom meta
	 *
	 * @param $caps
	 * @param $cap
	 * @param $user_id
	 * @param $args
	 *
	 * @return string
	 */
	public function map_meta_cap_application( $caps, $cap, $user_id, $args ) {
		if ( ! in_array( $cap, self::application_meta_caps() ) )
			return $caps;

		if ( current_user_can( 'administrator' ) )
			$caps = array();

		return $caps;
	}

	/**
	 * Add profile fields.
	 */
	public function add_profile_fields() {
		$view = new LDMW_Users_Profile_View();
		$view->render();
	}

	/**
	 * Save the profile fields.
	 *
	 * @param $user_id int
	 */
	public function save_profile_fields( $user_id ) {
		$view = new LDMW_Users_Profile_View( $user_id );
		$view->process_post( $_POST );
	}

	/**
	 * @param $screen WP_Screen
	 */
	public function register_new_user_screen( $screen ) {
		if ( $screen->base != 'user' || $screen->action != 'add' || ! isset( $_GET['create-member'] ) ) {
			add_action( 'user_new_form', function () {
				  ?><p>
				  <a href="<?php echo add_query_arg( 'create-member', 1 ); ?>">Create a new Member</a>
				  </p><?php
			  }
			);

			return;
		}

		add_filter( 'option_default_role', function () {
			  return LDMW_Users_Base::$member_role_slug;
		  }
		);

		add_action( 'user_new_form', function () {
			  ?>

			  <table class="form-table">
					<tr>
						<th><label for="ldmw_membership_status">Membership Status</label></th>
						<td><select id="ldmw_membership_status" name="ldmw_membership_status">
								<?php foreach ( LDMW_Users_Util::get_membership_statuses() as $slug => $status ) : ?>
									<option value="<?php echo $slug; ?>"><?php echo $status; ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th><label for="ldmw_membership_grade">Membership Grade</label></th>
						<td><select id="ldmw_membership_grade" name="ldmw_membership_grade">
								<?php foreach ( LDMW_Users_Util::get_membership_grades() as $slug => $grade ) : ?>
									<option value="<?php echo $slug; ?>"><?php echo $grade; ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th><label for="ldmw_membership_division">Membership Division</label></th>
						<td><select id="ldmw_membership_division" name="ldmw_membership_division">
								<?php foreach ( LDMW_Users_Util::get_membership_divisions() as $slug => $division ) : ?>
									<option value="<?php echo $slug; ?>"><?php echo $division; ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
				</table>
			  <input type="hidden" name="ldmw_create_member_manual" value="1">

		  <?php
		  }
		);
	}

	/**
	 * Save new user data from registration form.
	 */
	public function save_new_user_data( $user_id ) {
		if ( isset( $_POST['ldmw_create_member_manual'] ) ) {
			if ( $_POST['ldmw_membership_status'] == 'current' ) {
				LDMW_ManageMembers_Model_Users::add_membership( $user_id );
			}

			update_user_meta( $user_id, 'ldmw_membership_status', array_key_exists( $_POST['ldmw_membership_status'], LDMW_Users_Util::get_membership_statuses() ) ? $_POST['ldmw_membership_status'] : LDMW_Users_Util::get_membership_statuses()[0] );
			update_user_meta( $user_id, 'ldmw_membership_grade', array_key_exists( $_POST['ldmw_membership_grade'], LDMW_Users_Util::get_membership_grades() ) ? $_POST['ldmw_membership_grade'] : LDMW_Users_Util::get_membership_grades()[0] );
			update_user_meta( $user_id, 'ldmw_membership_division', array_key_exists( $_POST['ldmw_membership_division'], LDMW_Users_Util::get_membership_divisions() ) ? $_POST['ldmw_membership_division'] : LDMW_Users_Util::get_membership_divisions()[0] );

			update_user_option( $user_id, 'default_password_nag', true, true ); // Set up the Password change nag.
		}
	}

	/**
	 * Update the time that a user logged in.
	 *
	 * @param $user_login string
	 * @param $user WP_User
	 */
	public function last_login_time( $user_login, $user ) {
		update_user_meta( $user->ID, 'ldmw_last_loggedin_time', time() );
	}

}