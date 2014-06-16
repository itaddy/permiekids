<?php

/**
 *
 * @package LDMW
 * @subpackage Users/CRM
 * @since 1.0
 */
class LDMW_Users_Crm_View extends LDMW_Users_View {
	/**
	 * @var array
	 */
	protected $home_address = array();

	/**
	 * @var array
	 */
	protected $work_address = array();

	/**
	 * @var array
	 */
	protected $billing_address = array();

	/**
	 * @var array
	 */
	protected $communication_preferences = array();

	/**
	 * @var
	 */
	protected $first_name;
	/**
	 * @var
	 */
	protected $last_name;

	/**
	 *
	 */
	function __construct() {
		parent::__construct();
		$this->home_address = LDMW_Users_Util::get_home_address( $this->user_id );
		$this->work_address = LDMW_Users_Util::get_work_address( $this->user_id );
		$this->billing_address = it_exchange_get_customer_billing_address( $this->user_id );
		$this->communication_preferences = LDMW_Users_Util::get_communication_preferences( $this->user_id );

		$user = get_user_by( 'id', $this->user_id );
		$this->first_name = $user->first_name;
		$this->last_name = $user->last_name;
	}

	/**
	 * Render the tab's content.
	 *
	 * @return void
	 */
	public function render() {
		if ( ! current_user_can( 'edit_users' ) )
			return;

		?>
		<style type="text/css">
		.crm-profile-page .user-edit-block .block-row {
			border-top: 1px solid #cccccc;
		}

		.crm-profile-page .user-edit-block .block-row:nth-of-type(1) {
			border-top: 0;
		}
	    </style>
		<form action="" method="post" class="crm-profile-page">
		<div class="user-edit-block">

				<div class="heading-row block-row">
					<div class="heading-column block-column block-column-1">
						<p class="heading">Home Address</p>
					</div>
				</div>

				<div class="item-row block-row">
					<div class="item-column block-column">
						<p class="item">
							<?php echo $this->first_name; ?> <?php echo $this->last_name; ?><br>
							<?php echo $this->home_address['address_1']; ?><br>
							<?php if ( ! empty( $this->home_address['address_2'] ) ) : ?>
								<?php echo $this->home_address['address_2']; ?>
								<br>
							<?php endif; ?>
							<?php echo $this->home_address['suburb']; ?> <?php echo $this->home_address['state']; ?> <?php echo $this->home_address['postcode']; ?>
							<br>
							<?php echo $this->home_address['country']; ?>
						</p>
					</div>
				</div>

				<div class="heading-row block-row">
					<div class="heading-column block-column block-column-1">
						<p class="heading">Work Address</p>
					</div>
				</div>

				<div class="item-row block-row">
					<div class="item-column block-column">
						<p class="item">
							<?php echo $this->billing_address['company-name']; ?>
							<br>
							<?php echo $this->work_address['address_1']; ?><br>
							<?php if ( ! empty( $this->work_address['address_2'] ) ) : ?>
								<?php echo $this->work_address['address_2']; ?>
								<br>
							<?php endif; ?>
							<?php echo $this->work_address['suburb']; ?> <?php echo $this->work_address['state']; ?> <?php echo $this->work_address['postcode']; ?>
							<br>
							<?php echo $this->work_address['country']; ?>
						</p>
					</div>
				</div>

				<div class="heading-row block-row">
					<div class="heading-column block-column block-column-1">
						<p class="heading">Billing Address</p>
					</div>
				</div>

				<div class="item-row block-row">
					<div class="item-column block-column">
						<p class="item">
							<?php echo $this->billing_address['company-name']; ?>
							<br>
							<?php echo $this->billing_address['first-name']; ?> <?php echo $this->billing_address['last_name']; ?>
							<?php echo $this->billing_address['address1']; ?>
							<br>
							<?php if ( ! empty( $this->billing_address['address2'] ) ) : ?>
								<?php echo $this->billing_address['address2']; ?>
								<br>
							<?php endif; ?>
							<?php echo $this->billing_address['city']; ?> <?php echo $this->billing_address['state']; ?> <?php echo $this->billing_address['zip']; ?>
							<br>
							<?php echo $this->billing_address['country']; ?>
						</p>

						<p class="item">
							<?php echo $this->billing_address['phone']; ?><br>
							<?php echo $this->billing_address['email']; ?>
						</p>
					</div>
				</div>

				<div class="heading-row block-row">
					<div class="heading-column block-column block-column-1">
						<p class="heading">Communication Preferences</p>
					</div>
				</div>

			    <div class="item-row block-row">
					<div class="item-column block-column">
						<?php foreach ( $this->communication_preferences as $pref => $enabled ) : ?>
							<?php if ( $enabled === true && isset( $pref ) ) : ?>
								<p class="item"><?php echo $pref; ?></p>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
				</div>

	        </div>

		<?php wp_nonce_field( 'update-it-exchange-customer-info', '_it_exchange_customer_info_nonce' ); ?>

		<div class="update-user-info">
			    <input type="submit" class="button button-large" name="update_it_exchange_customer" value="<?php _e( 'Update Customer Info', 'it-l10n-ithemes-exchange' ) ?>"/>
			</div>

		</form><?php
	}

}