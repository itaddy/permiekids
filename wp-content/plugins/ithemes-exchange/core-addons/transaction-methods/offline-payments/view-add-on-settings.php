<?php
/**
 * This file contains the HTML for the add-on settings
 *
 * @since 0.3.6
 * @package IT_Exchange
*/
?>
<div class="wrap">
	<?php ITUtility::screen_icon( 'it-exchange' ); ?>
	<h2><?php _e( 'Offline Payments Settings', 'it-l10n-ithemes-exchange' ); ?></h2>

	<?php do_action( 'it_exchange_offline-payments_settings_page_top' ); ?>
	<?php do_action( 'it_exchange_addon_settings_page_top' ); ?>

	<?php $form->start_form( $form_options, 'it-exchange-offline-payments-settings' ); ?>
		<?php do_action( 'it_exchange_offline_payments_settings_form_top' ); ?>
		<?php $this->get_offline_payment_form_table( $form ); ?>
		<?php do_action( 'it_exchange_offline_payments_settings_form_bottom' ); ?>
		<p class="submit">
			<?php $form->add_submit( 'submit', array( 'value' => __( 'Save Changes', 'it-l10n-ithemes-exchange' ), 'class' => 'button button-primary' ) ); ?>
		</p>
	<?php $form->end_form(); ?>
	<?php do_action( 'it_exchange_offline_payments_settings_page_bottom' ); ?>
</div>
