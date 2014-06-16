<?php

/**
 *
 * @package LDMW
 * @subpackage Users
 * @since 1.0
 */
class LDMW_Users_Membership_View extends LDMW_Users_View {
	/**
	 * Add necessary hooks and filters, and set up data.
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Render the tab's content.
	 *
	 * @return void
	 */
	public function render() {
		if ( ! $this->can_edit_page() )
			return;
		?>
		<style type="text/css">
		.membership-profile-page .user-edit-block .block-row {
			border-top: 1px solid #cccccc;
		}

		.membership-profile-page .user-edit-block .block-row:nth-of-type(1) {
			border-top: 0;
		}
	    </style>
		<form action="" method="post" class="membership-profile-page">
		    <div class="user-edit-block">
			    <?php if ( "" != ( $id = get_user_meta( $this->user_id, 'ldmw_membership_application', true ) ) ) : ?>
				    <div class="heading-row block-row">
					    <div class="heading-column block-column block-column-1">
						    <p class="heading">Member's Application</p>
					    </div>
				    </div>
				    <div class="item-row block-row">
					    <div class="item-column block-column">
						    <p class="item">
							    <a href="<?php echo LDMW_Application_Util::get_application_entry_link( $id ) ?>">View most recent application</a>
						    </p>
						    <p class="item">
							    <a href="<?php echo LDMW_Application_Util::get_application_entry_link( $id, 'edit' ) ?>">Edit most recent application</a>
						    </p>
					    </div>
				    </div>
			    <?php endif; ?>

			    <div class="heading-row block-row">
				    <div class="heading-column block-column block-column-1">
					    <p class="heading">Renewal Invoice</p>
				    </div>
			    </div>

			    <?php if ( "" !== ( $id = get_user_meta( $this->user_id, 'ldmw_membership_renewal_invoice_post_id', true ) ) ) : ?>

				    <div class="item-row block-row">
					    <div class="item-column block-column">
						    <p class="item">
							    <a href="<?php echo get_permalink( $id ); ?>">View renewal invoice</a>
						    </p>
						    <p class="item">
							    <a href="<?php echo get_edit_post_link( $id, '' ) ?>">Edit renewal invoice</a>
						    </p>
					    </div>
				    </div>

			    <?php endif; ?>

			    <div class="item-row block-row">
				    <div class="item-column block-column">
					    <p class="item">
						    <label for="ldmw-resend-renewal-invoice">Resend Renewal Invoice</label><br>
						    <select name="ldmw_resend_renewal_invoice_email" id="ldmw-resend-renewal-invoice">
							    <option value="-1">Select a renewal notice...</option>
							    <?php $renewals = array(
							      'original_renewal_notice' => "Original Renewal Invoice",
							      'reminder_invoice'        => "Reminder Invoice",
							      'overdue_notice'          => "Overdue Notice",
							      'final_notice'            => "Final Notice"
							    ); ?>
							    <?php foreach ( $renewals as $value => $label ) : ?>
								    <option value="<?php echo $value ?>"><?php echo $label; ?></option>
							    <?php endforeach; ?>
						    </select>
						    <input type="submit" class="button button-large" name="ldmw_resend_renewal_invoice" value="Send">
					    </p>
				    </div>
			    </div>

			    <div class="heading-row block-row">
					<div class="heading-column block-column block-column-1">
						<p class="heading">Communication Preference</p>
					</div>
				</div>

			    <div class="item-row block-row">
					<div class="item-column block-column">
						<?php foreach ( LDMW_Users_Util::get_communication_preferences( $this->user_id ) as $pref => $enabled ) : ?>
							<?php if ( $enabled === true && isset( $pref ) ) : ?>
								<p class="item"><?php echo $pref; ?></p>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
				</div>

			    <div class="heading-row block-row">
					<div class="heading-column block-column block-column-1">
						<p class="heading">Areas of Competence</p>
					</div>
				</div>

			    <div class="item-row block-row">
					<div class="item-column block-column">
						<?php foreach ( LDMW_Users_Util::get_areas_of_competence( $this->user_id ) as $pref => $enabled ) : ?>
							<?php if ( $enabled === true && isset( $pref ) ) : ?>
								<p class="item"><?php echo $pref; ?></p>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
				</div>

			    <div class="heading-row block-row">
					<div class="heading-column block-column block-column-1">
						<p class="heading">Fields of Interest</p>
					</div>
				</div>

			    <div class="item-row block-row">
					<div class="item-column block-column">
						<?php foreach ( LDMW_Users_Util::get_fields_of_interest( $this->user_id ) as $pref => $enabled ) : ?>
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

		</form>

	<?php
	}

}