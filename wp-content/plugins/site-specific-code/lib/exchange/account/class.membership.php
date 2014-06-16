<?php

/**
 *
 * @package LDMW
 * @subpackage Exchange/Account
 * @since 1.0
 */
class LDMW_Exchange_Account_Membership extends LDMW_Exchange_Account_View {
	/**
	 * @var array
	 */
	protected $competence_form = array();
	/**
	 * @var array
	 */
	protected $competence_prefs = array();

	/**
	 * @var array
	 */
	protected $interest_form = array();

	/**
	 * @var array
	 */
	protected $interest_prefs = array();

	/**
	 * Hooks and filters.
	 */
	function __construct() {
		parent::__construct();

		$this->competence_form = RGFormsModel::get_form_meta( LDMW_Options_Model::get_instance()->areas_competence );
		$this->competence_prefs = LDMW_Users_Util::get_areas_of_competence( $this->user_id );

		$this->interest_form = RGFormsModel::get_form_meta( LDMW_Options_Model::get_instance()->fields_interest );
		$this->interest_prefs = LDMW_Users_Util::get_fields_of_interest( $this->user_id );

		add_action( 'wp_enqueue_scripts', array( $this, 'scripts_and_styles' ) );
		add_action( 'it_exchange_membership_addon_content_memberships_after_welcome_element', array( $this, 'render' ) );

		if ( ! empty( $_POST ) )
			$this->process_data( $_POST );
	}

	/**
	 * Process submitted data.
	 *
	 * @param $data array
	 *
	 * @return void
	 */
	function process_data( $data ) {
		if ( ! isset( $data['ldmw_nonce'] ) || ! wp_verify_nonce( $data['ldmw_nonce'], 'ldmw-account-membership' ) )
			return;

		if ( isset( $data['membership_form'] ) ) {

			foreach ( $this->competence_form['fields'] as $field ) {
				foreach ( $field['inputs'] as $input ) {
					$key = str_replace( " ", "_", $input['label'] );
					if ( isset( $data[$key] ) ) {
						$this->competence_prefs[$input['label']] = true;
					}
					else {
						unset( $this->competence_prefs[$input['label']] );
					}
				}
			}

			foreach ( $this->interest_form['fields'] as $field ) {
				foreach ( $field['inputs'] as $input ) {
					$key = str_replace( " ", "_", $input['label'] );
					if ( isset( $data[$key] ) ) {
						$this->interest_prefs[$input['label']] = true;
					}
					else {
						unset( $this->interest_prefs[$input['label']] );
					}
				}
			}

			LDMW_Users_Util::update_areas_of_competence( $this->user_id, $this->competence_prefs );
			LDMW_Users_Util::update_fields_of_interest( $this->user_id, $this->interest_prefs );

			it_exchange_add_message( 'notice', 'Successfully saved profile!' );
		}
	}

	/**
	 * Enqueue scripts and styles.
	 */
	function scripts_and_styles() {
		wp_enqueue_style( 'ldmw-display' );
	}

	/**
	 * Render the fields on the form
	 *
	 * @return void
	 */
	function render() {
		?>
		<form method="GET" action="<?php echo get_permalink( LDMW_Options_Model::get_instance()->application_form_page ); ?>">
			<h3>Membership</h3>
			<div class="table-responsive">
			<table class="table">
				<tr>
					<th>Member Since</th>
					<th>Grade</th>
					<th>Division</th>
					<th>Status</th>
					<th>Transfer</th>
					<th></th>
				</tr>
				<tr>
					<td><?php if ( "" != $time = get_user_meta( $this->user_id, 'ldmw_membership_start_date', true ) ) echo ( new DateTime( "@" . $time ) )->format( get_option( 'date_format' ) ); ?></td>
					<td><?php echo LDMW_Users_Util::membership_grade_slug_to_name( LDMW_Users_Util::get_membership_grade( $this->user_id ) ); ?></td>
					<td><?php echo LDMW_Users_Util::membership_division_slug_to_name( LDMW_Users_Util::get_membership_division( $this->user_id ) ); ?></td>
					<td><?php echo LDMW_Users_Util::get_membership_status_slug_to_name( LDMW_Users_Util::get_membership_status( $this->user_id ) ); ?></td>
					<td>
						<select id="transfer" name="grade">
								<option>Choose Grade</option>
							<?php foreach ( LDMW_Users_Util::get_applicable_membership_grades() as $slug => $grade ) : ?>
								<option value="<?php echo $slug ?>"><?php echo $grade; ?></option>
							<?php endforeach; ?>
						</select>
					</td>
					<td><input type="submit" id="request-transfer" name="request_tranfer" style="display: inline" value="Apply"></td>
				</tr>
			</table>
			</div>
			<input type="hidden" name="type" value="upgrade">
			<input type="hidden" name="division" value="<?php echo LDMW_Users_Util::get_membership_division( $this->user_id ); ?>">
		</form>

		<form method="POST" action="">
			<h3>Areas of Competence</h3>

			<?php foreach ( $this->competence_form['fields'] as $field ) : ?>
				<?php foreach ( $field['inputs'] as $input ) : ?>
					<div class="field">
					<input type="checkbox" name="<?php echo $input['label'] ?>" id="<?php echo $input['label'] ?>"
					  <?php if ( isset( $this->competence_prefs[$input['label']] ) ) checked( $this->competence_prefs[$input['label']] ); ?>>
					<label for="<?php echo $input['label'] ?>"><?php echo $input['label']; ?></label>
					</div>
				<?php endforeach; ?>
			<?php endforeach; ?>

			<h3>Fields of Interest</h3>

			<?php foreach ( $this->interest_form['fields'] as $field ) : ?>
				<?php foreach ( $field['inputs'] as $input ) : ?>
					<div class="field">
					<input type="checkbox" name="<?php echo $input['label'] ?>" id="<?php echo $input['label'] ?>"
					  <?php if ( isset( $this->interest_prefs[$input['label']] ) ) checked( $this->interest_prefs[$input['label']] ); ?>>
					<label for="<?php echo $input['label'] ?>"><?php echo $input['label']; ?></label>
					</div>
				<?php endforeach; ?>
			<?php endforeach; ?>

			<p><input type="submit" id="membership-form" name="membership_form" value="Save Profile"></p>

			<?php wp_nonce_field( 'ldmw-account-membership', 'ldmw_nonce' ); ?>
	    </form>
	<?
	}
}