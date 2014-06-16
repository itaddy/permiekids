<?php

/**
 *
 * @package LDMW
 * @subpackage Users
 * @since 1.0
 */
class LDMW_Users_FederalCouncil_View extends LDMW_Users_View {
	/**
	 *
	 */
	function __construct() {
		parent::__construct();

		$this->save();
	}

	/**
	 * Save the committee data.
	 */
	public function save() {
		if ( ! isset( $_POST['_it_exchange_customer_info_nonce'] ) || ! wp_verify_nonce( $_POST['_it_exchange_customer_info_nonce'], 'update-it-exchange-customer-info' ) || ! current_user_can( 'edit_user', $this->user_id ) )
			return;

		LDMW_Users_Util::update_committee_persons_division( $this->user_id, $_POST['division'] );
	}

	/**
	 * Override the page permissions check. This page is only visible for committee members.
	 *
	 * @return bool
	 */
	protected function can_edit_page() {
		return user_can( $this->user_id, LDMW_Users_Base::$federal_council_role_slug ) && current_user_can( 'edit_user', $this->user_id );
	}

	/**
	 * Render the content for the committees tab.
	 */
	public function render() {
		if ( ! $this->can_edit_page() )
			return;

		$current_division = LDMW_Users_Util::get_committee_persons_division( $this->user_id );
		?>
		<form action="" method="post">
		    <div class="user-edit-block">

				<div class="heading-row block-row">
					<div class="heading-column block-column block-column-1">
						<p class="heading">Select the divisions that this member serves.</p>
					</div>
				</div>

			    <?php foreach ( LDMW_Users_Util::get_membership_divisions() as $slug => $name ) : ?>
				    <div class="item-row block-row">
						<div class="item-column block-column">
							<p class="item"><label><input type="radio" name="division" <?php checked( $current_division, $slug ); ?> value="<?php echo $slug; ?>"><?php echo $name; ?></label></p>
						</div>
					</div>
			    <?php endforeach ?>

		    </div>

			<?php wp_nonce_field( 'update-it-exchange-customer-info', '_it_exchange_customer_info_nonce' ); ?>

			<div class="update-user-info">
			    <input type="submit" class="button button-large" name="update_it_exchange_customer" value="Update Council Member's Info"/>
			</div>

		</form>

	<?php
	}

}