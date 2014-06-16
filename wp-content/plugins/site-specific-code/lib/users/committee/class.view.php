<?php

/**
 *
 * @package LDMW
 * @subpackage Users
 * @since 1.0
 */
class LDMW_Users_Committee_View extends LDMW_Users_View {

	/**
	 * Add necessary info.
	 */
	public function __construct() {
		parent::__construct();

		$this->save();
	}

	/**
	 * Save the committee data.
	 */
	public function save() {
		if ( ! isset( $_POST['_it_exchange_customer_info_nonce'] ) || ! wp_verify_nonce( $_POST['_it_exchange_customer_info_nonce'], 'update-it-exchange-customer-info' ) || ! current_user_can( 'edit_user', $this->user_id ) )
			return;

		$committees = $_POST['committees'];
		$new_committees = array();

		if ( is_array( $committees ) ) {
			foreach ( $committees as $slug => $on ) {
				if ( LDMW_Users_Util::is_valid_committee( $slug ) )
					$new_committees[] = $slug;
			}
		}

		update_user_meta( $this->user_id, 'ldmw_committees_member', $new_committees );
	}

	/**
	 * Override the page permissions check. This page is only visible for committee members.
	 *
	 * @return bool
	 */
	protected function can_edit_page() {
		return user_can( $this->user_id, LDMW_Users_Base::$committee_role_slug );
	}

	/**
	 * Render the content for the committees tab.
	 */
	public function render() {

		if ( ! $this->can_edit_page() )
			return;

		$current_committees = LDMW_Users_Util::get_a_members_committees( new WP_User( $this->user_id ) );
		?>
		<form action="" method="post">
		    <div class="user-edit-block">

				<div class="heading-row block-row">
					<div class="heading-column block-column block-column-1">
						<p class="heading">Select the committees that this member serves on.</p>
					</div>
				</div>

			    <?php foreach ( LDMW_Users_Util::committees() as $slug => $name ) : ?>
				    <div class="item-row block-row">
						<div class="item-column block-column">
							<p class="item"><label><input type="checkbox" name="committees[<?php echo $slug; ?>]" <?php echo in_array( $slug, $current_committees ) ? 'checked="checked"' : ""; ?>><?php echo $name; ?></label></p>
						</div>
					</div>
			    <?php endforeach ?>

		    </div>

			<?php wp_nonce_field( 'update-it-exchange-customer-info', '_it_exchange_customer_info_nonce' ); ?>

			<div class="update-user-info">
			    <input type="submit" class="button button-large" name="update_it_exchange_customer" value="<?php _e( 'Update Customer Info', 'it-l10n-ithemes-exchange' ) ?>"/>
			</div>

		</form>

	<?php
	}
}