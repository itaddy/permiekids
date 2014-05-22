<?php
/**
 * Hooks for PayPal Standard (insecure) add-on
 *
 * @package IT_Exchange
 * @since 0.2.0
*/

if ( !defined( 'PAYPAL_LIVE_URL' ) )
	define( 'PAYPAL_LIVE_URL', 'https://www.paypal.com/' );

if ( !defined( 'PAYPAL_PAYMENT_URL' ) )
	define( 'PAYPAL_PAYMENT_URL', 'https://www.paypal.com/cgi-bin/webscr' );

/**
 * Outputs wizard settings for PayPal
 *
 * @since 0.4.0
 * @todo make this better, probably
 * @param object $form Current IT Form object
 * @return void
*/
function it_exchange_print_paypal_standard_wizard_settings( $form ) {
	$IT_Exchange_PayPal_Standard_Add_On = new IT_Exchange_PayPal_Standard_Add_On();
	$settings = it_exchange_get_option( 'addon_paypal_standard', true );
	$form_values = ITUtility::merge_defaults( ITForm::get_post_data(), $settings );

	// Alter setting keys for wizard
	foreach( $form_values as $key => $value ) {
		$form_values['paypal-standard-' . $key] = $value;
		unset( $form_values[$key] );
	}

	$hide_if_js =  it_exchange_is_addon_enabled( 'paypal-standard' ) ? '' : 'hide-if-js';
	?>
	<div class="field paypal-standard-wizard <?php echo $hide_if_js; ?>">
	<?php if ( empty( $hide_if_js ) ) { ?>
		<input class="enable-paypal-standard" type="hidden" name="it-exchange-transaction-methods[]" value="paypal-standard" />
	<?php } ?>
	<?php $IT_Exchange_PayPal_Standard_Add_On->get_paypal_standard_payment_form_table( $form, $form_values ); ?>
	</div>
	<?php
}
add_action( 'it_exchange_print_paypal-standard_wizard_settings', 'it_exchange_print_paypal_standard_wizard_settings' );

/**
 * Stripe URL to perform refunds
 *
 * @since 0.4.0
 *
 * @param string $url passed by WP filter.
 * @param string $url transaction URL
*/
function it_exchange_refund_url_for_paypal_standard( $url ) {

	return 'https://paypal.com/';

}
add_filter( 'it_exchange_refund_url_for_paypal-standard', 'it_exchange_refund_url_for_paypal_standard' );
/**
 * This proccesses a paypal transaction.
 *
 * @since 0.4.0
 *
 * @param string $status passed by WP filter.
 * @param object $transaction_object The transaction object
*/
function it_exchange_process_paypal_standard_addon_transaction( $status, $transaction_object ) {

	if ( $status ) //if this has been modified as true already, return.
		return $status;

	if ( !empty( $_REQUEST['it-exchange-transaction-method'] ) && 'paypal-standard' === $_REQUEST['it-exchange-transaction-method'] ) {

		if ( !empty( $_REQUEST['tx'] ) ) //if PDT is enabled
			$transaction_id = $_REQUEST['tx'];
		else if ( !empty( $_REQUEST['txn_id'] ) ) //if PDT is not enabled
			$transaction_id = $_REQUEST['txn_id'];
		else
			$transaction_id = NULL;

		if ( !empty( $_REQUEST['cm'] ) )
			$transient_transaction_id = $_REQUEST['cm'];
		else
			$transient_transaction_id = NULL;

		if ( !empty( $_REQUEST['amt'] ) ) //if PDT is enabled
			$transaction_amount = $_REQUEST['amt'];
		else if ( !empty( $_REQUEST['mc_gross'] ) ) //if PDT is not enabled
			$transaction_amount = $_REQUEST['mc_gross'];
		else
			$transaction_amount = NULL;

		if ( !empty( $_REQUEST['st'] ) ) //if PDT is enabled
			$transaction_status = $_REQUEST['st'];
		else if ( !empty( $_REQUEST['payment_status'] ) ) //if PDT is not enabled
			$transaction_status = $_REQUEST['payment_status'];
		else
			$transaction_status = NULL;

		if ( !empty( $transaction_id ) && !empty( $transient_transaction_id ) && !empty( $transaction_amount ) && !empty( $transaction_status ) ) {

			try {

				$general_settings = it_exchange_get_option( 'settings_general' );
				$paypal_settings = it_exchange_get_option( 'addon_paypal_standard' );

				$it_exchange_customer = it_exchange_get_current_customer();

				if ( number_format( $transaction_amount, '2', '', '' ) != number_format( $transaction_object->total, '2', '', '' ) )
					throw new Exception( __( 'Error: Amount charged is not the same as the cart total!', 'it-l10n-ithemes-exchange' ) );

				//If the transient still exists, delete it and add the official transaction
				if ( it_exchange_get_transient_transaction( 'paypal-standard', $transient_transaction_id ) ) {
					it_exchange_delete_transient_transaction( 'paypal-standard', $transient_transaction_id  );
					$ite_transaction_id = it_exchange_add_transaction( 'paypal-standard', $transaction_id, $transaction_status, $it_exchange_customer->id, $transaction_object );
					return $ite_transaction_id;
				}

			}
			catch ( Exception $e ) {

				it_exchange_add_message( 'error', $e->getMessage() );
				return false;

			}

			return it_exchange_paypal_standard_addon_get_ite_transaction_id( $transaction_id );

		}

		it_exchange_add_message( 'error', __( 'Unknown error while processing with PayPal. Please try again later.', 'it-l10n-ithemes-exchange' ) );

	}
	return false;

}
add_action( 'it_exchange_do_transaction_paypal-standard', 'it_exchange_process_paypal_standard_addon_transaction', 10, 2 );

/**
 * Grab the paypal customer ID for a WP user
 *
 * @since 0.4.0
 *
 * @param integer $customer_id the WP customer ID
 * @return string
*/
function it_exchange_get_paypal_standard_addon_customer_id( $customer_id ) {
	return get_user_meta( $customer_id, '_it_exchange_paypal_standard_id', true );
}

/**
 * Add the paypal customer email as user meta on a WP user
 *
 * @since 0.4.0
 *
 * @param integer $customer_id the WP user ID
 * @param integer $paypal_standard_id the paypal customer ID
 * @return boolean
*/
function it_exchange_set_paypal_standard_addon_customer_id( $customer_id, $paypal_standard_id ) {
	return update_user_meta( $customer_id, '_it_exchange_paypal_standard_id', $paypal_standard_id );
}

/**
 * Grab the paypal customer email for a WP user
 *
 * @since 0.4.0
 *
 * @param integer $customer_id the WP customer ID
 * @return string
*/
function it_exchange_get_paypal_standard_addon_customer_email( $customer_id ) {
	return get_user_meta( $customer_id, '_it_exchange_paypal_standard_email', true );
}

/**
 * Add the paypal customer email as user meta on a WP user
 *
 * @since 0.4.0
 *
 * @param integer $customer_id the WP user ID
 * @param string $paypal_standard_email the paypal customer email
 * @return boolean
*/
function it_exchange_set_paypal_standard_addon_customer_email( $customer_id, $paypal_standard_email ) {
	return update_user_meta( $customer_id, '_it_exchange_paypal_standard_email', $paypal_standard_email );
}

/**
 * This is the function registered in the options array when it_exchange_register_addon was called for paypal
 *
 * It tells Exchange where to find the settings page
 *
 * @return void
*/
function it_exchange_paypal_standard_settings_callback() {
	$IT_Exchange_PayPal_Standard_Add_On = new IT_Exchange_PayPal_Standard_Add_On();
	$IT_Exchange_PayPal_Standard_Add_On->print_settings_page();
}

/**
 * This is the function prints the payment form on the Wizard Settings screen
 *
 * @return void
*/
function paypal_standard_print_wizard_settings( $form ) {
	$IT_Exchange_PayPal_Standard_Add_On = new IT_Exchange_PayPal_Standard_Add_On();
	$settings = it_exchange_get_option( 'addon_paypal_standard', true );
	?>
	<div class="field paypal_standard-wizard hide-if-js">
	<?php $IT_Exchange_PayPal_Standard_Add_On->get_paypal_standard_payment_form_table( $form, $settings ); ?>
	</div>
	<?php
}

/**
 * Saves paypal settings when the Wizard is saved
 *
 * @since 0.4.0
 *
 * @return void
*/
function it_exchange_save_paypal_standard_wizard_settings( $errors ) {
	if ( ! empty( $errors ) )
		return $errors;

	$IT_Exchange_PayPal_Standard_Add_On = new IT_Exchange_PayPal_Standard_Add_On();
	return $IT_Exchange_PayPal_Standard_Add_On->paypal_standard_save_wizard_settings();
}
add_action( 'it_exchange_save_paypal-standard_wizard_settings', 'it_exchange_save_paypal_standard_wizard_settings' );

/**
 * Default settings for paypal_standard
 *
 * @since 0.4.0
 *
 * @param array $values
 * @return array
*/
function it_exchange_paypal_standard_addon_default_settings( $values ) {
	$defaults = array(
		'live-email-address' => '',
		'purchase-button-label' => __( 'Pay with PayPal', 'it-l10n-ithemes-exchange' ),
	);
	$values = ITUtility::merge_defaults( $values, $defaults );
	return $values;
}
add_filter( 'it_storage_get_defaults_exchange_addon_paypal_standard', 'it_exchange_paypal_standard_addon_default_settings' );

/**
 * Returns the button for making the PayPal faux payment button
 *
 * @since 0.4.19
 *
 * @param array $options
 * @return string HTML button
*/
function it_exchange_paypal_standard_addon_make_payment_button( $options ) {

	if ( 0 >= it_exchange_get_cart_total( false ) )
		return;

	$general_settings = it_exchange_get_option( 'settings_general' );
	$paypal_settings  = it_exchange_get_option( 'addon_paypal_standard' );

	$payment_form = '';

	if ( $paypal_email = $paypal_settings['live-email-address'] ) {

		$it_exchange_customer = it_exchange_get_current_customer();

		$payment_form .= '<form action="" method="post">';
		$payment_form .= '<input type="submit" class="it-exchange-paypal-standard-button" name="paypal_standard_purchase" value="' . $paypal_settings['purchase-button-label'] .'" />';
		$payment_form .= '</form>';

	}

	return $payment_form;

}
add_filter( 'it_exchange_get_paypal-standard_make_payment_button', 'it_exchange_paypal_standard_addon_make_payment_button', 10, 2 );

/**
 * Process the faux PayPal Standard form
 *
 * @since 0.4.19
 *
 * @param array $options
 * @return string HTML button
*/
function it_exchange_process_paypal_standard_form() {

	$paypal_settings  = it_exchange_get_option( 'addon_paypal_standard' );

	if ( ! empty( $_REQUEST['paypal_standard_purchase'] ) ) {

		if ( $paypal_email = $paypal_settings['live-email-address']  ) {

			$it_exchange_customer = it_exchange_get_current_customer();
			$temp_id = it_exchange_create_unique_hash();

			$transaction_object = it_exchange_generate_transaction_object();

			it_exchange_add_transient_transaction( 'paypal-standard', $temp_id, $it_exchange_customer->id, $transaction_object );

			wp_redirect( it_exchange_paypal_standard_addon_get_payment_url( $temp_id ) );

		} else {

			it_exchange_add_message( 'error', __( 'Error processing PayPal form. Missing valid PayPal account.', 'it-l10n-ithemes-exchange' ) );
			wp_redirect( it_exchange_get_page_url( 'checkout' ) );

		}

	}

}
add_action( 'template_redirect', 'it_exchange_process_paypal_standard_form', 11 );

/**
 * Returns the button for making the PayPal real payment button
 *
 * @since 0.4.19
 *
 * @param string $temp_id Temporary ID we reference late with IPN
 * @return string HTML button
*/
function it_exchange_paypal_standard_addon_get_payment_url( $temp_id ) {

	if ( 0 >= it_exchange_get_cart_total( false ) )
		return;

	$general_settings = it_exchange_get_option( 'settings_general' );
	$paypal_settings  = it_exchange_get_option( 'addon_paypal_standard' );

	$paypal_payment_url = '';

	if ( $paypal_email = $paypal_settings['live-email-address'] ) {

		$subscription = false;
		$it_exchange_customer = it_exchange_get_current_customer();

		remove_filter( 'the_title', 'wptexturize' ); // remove this because it screws up the product titles in PayPal

		if ( 1 === it_exchange_get_cart_products_count() ) {
			$cart = it_exchange_get_cart_products();
			foreach( $cart as $product ) {
				if ( it_exchange_product_supports_feature( $product['product_id'], 'recurring-payments', array( 'setting' => 'auto-renew' ) ) ) {
					if ( it_exchange_product_has_feature( $product['product_id'], 'recurring-payments', array( 'setting' => 'auto-renew' ) ) ) {
						$time = it_exchange_get_product_feature( $product['product_id'], 'recurring-payments', array( 'setting' => 'time' ) );
						switch( $time ) {

							case 'yearly':
								$unit = 'Y';
								break;

							case 'monthly':
							default:
								$unit = 'M';
								break;

						}
						$unit = apply_filters( 'it_exchange_paypal-standard_subscription_unit', $unit, $time );
						$duration = apply_filters( 'it_exchange_paypal-standard_subscription_duration', 1, $time );
						$subscription = true;
					}
				}
			}
		}

		if ( $subscription ) {
		//https://developer.paypal.com/webapps/developer/docs/classic/paypal-payments-standard/integration-guide/Appx_websitestandard_htmlvariables/#id08A6HI00JQU
			//a1, t1, p1 are for the first trial periods which is not supported with the Recurring Payments add-on
			//a2, t2, p2 are for the second trial period, which is not supported with the Recurring Payments add-on
			//a3, t3, p3 are required for the actual subscription details
			$paypal_args = array(
				'cmd' => '_xclick-subscriptions',
				'a3'  => number_format( it_exchange_get_cart_total( false ), 2, '.', '' ), //Regular subscription price.
				'p3'  => $duration, //Subscription duration. Specify an integer value in the allowable range for the units of duration that you specify with t3.
				't3'  => $unit, //Regular subscription units of duration. (D, W, M, Y) -- we only use M,Y by default
				'src' => 1, //Recurring payments.
			);

		} else {

			$paypal_args = array (
				'cmd'      => '_xclick',
				'amount'   => number_format( it_exchange_get_cart_total( false ), 2, '.', '' ),
				'quantity' => '1',
			);

		}

		$query = array(
			'business'      => $paypal_email,
			'item_name'     => it_exchange_get_cart_description(),

			'return'       => add_query_arg( 'it-exchange-transaction-method', 'paypal-standard', it_exchange_get_page_url( 'transaction' ) ),
			'currency_code' => $general_settings['default-currency'],
			'notify_url'    => get_site_url() . '/?' . it_exchange_get_webhook( 'paypal-standard' ) . '=1',
			'no_note'       => '1',
			'no_shipping'   => '1',
			'shipping'      => '0',
			'email'         => $it_exchange_customer->data->user_email,
			'rm'            => '2',
			'cancel_return' => it_exchange_get_page_url( 'cart' ),
			'custom'        => $temp_id,
		);

		$query = array_merge( $paypal_args, $query );
		$query = apply_filters( 'it_exchange_paypal_standad_query', $query );
		
		$paypal_payment_url = PAYPAL_PAYMENT_URL . '?' .  http_build_query( $query );

	} else {

		it_exchange_add_message( 'error', __( 'ERROR: Invalid PayPal Setup' ) );
		$paypal_payment_url = it_exchange_get_page_url( 'cart' );

	}

	return $paypal_payment_url;

}

/**
 * Adds the paypal webhook to the global array of keys to listen for
 *
 * @since 0.4.0
 *
 * @param array $webhooks existing
 * @return array
*/
function it_exchange_paypal_standard_addon_register_webhook() {
	$key   = 'paypal-standard';
	$param = apply_filters( 'it_exchange_paypal-standard_webhook', 'it_exchange_paypal-standard' );
	it_exchange_register_webhook( $key, $param );
}
add_filter( 'init', 'it_exchange_paypal_standard_addon_register_webhook' );

/**
 * Processes webhooks for PayPal Web Standard
 *
 * @since 0.4.0
 * @todo actually handle the exceptions
 *
 * @param array $request really just passing  $_REQUEST
 */
function it_exchange_paypal_standard_addon_process_webhook( $request ) {

	$general_settings = it_exchange_get_option( 'settings_general' );
	$settings = it_exchange_get_option( 'addon_paypal_standard' );

	$subscriber_id = !empty( $request['subscr_id'] ) ? $request['subscr_id'] : false;
	$subscriber_id = !empty( $request['recurring_payment_id'] ) ? $request['recurring_payment_id'] : $subscriber_id;

	if ( !empty( $request['txn_type'] ) ) {

		if ( !empty( $request['transaction_subject'] ) && $transient_data = it_exchange_get_transient_transaction( 'paypal-standard', $request['transaction_subject'] ) ) {
			it_exchange_delete_transient_transaction( 'paypal-standard', $request['transaction_subject']  );
			$ite_transaction_id = it_exchange_add_transaction( 'paypal-standard', $request['txn_id'], $request['payment_status'], $transient_data['customer_id'], $transient_data['transaction_object'] );
			return $ite_transaction_id;
		}

		switch( $request['txn_type'] ) {

			case 'web_accept':
				switch( strtolower( $request['payment_status'] ) ) {

					case 'completed' :
						it_exchange_paypal_standard_addon_update_transaction_status( $request['txn_id'], $request['payment_status'] );
						break;
					case 'reversed' :
						it_exchange_paypal_standard_addon_update_transaction_status( $request['parent_txn_id'], $request['reason_code'] );
						break;
				}
				break;

			case 'subscr_payment':
				switch( strtolower( $request['payment_status'] ) ) {
					case 'completed' :
						if ( !it_exchange_paypal_standard_addon_update_transaction_status( $request['txn_id'], $request['payment_status'] ) ) {
							//If the transaction isn't found, we've got a new payment
							it_exchange_paypal_standard_addon_add_child_transaction( $request['txn_id'], $request['payment_status'], $subscriber_id, $request['mc_gross'] );
						} else {
							//If it is found, make sure the subscriber ID is attached to it
							it_exchange_paypal_standard_addon_update_subscriber_id( $request['txn_id'], $subscriber_id );
						}
						it_exchange_paypal_standard_addon_update_subscriber_status( $subscriber_id, 'active' );
						break;
				}
				break;

			case 'subscr_signup':
				it_exchange_paypal_standard_addon_update_subscriber_status( $subscriber_id, 'active' );
				break;

			case 'recurring_payment_suspended':
				it_exchange_paypal_standard_addon_update_subscriber_status( $subscriber_id, 'suspended' );
				break;

			case 'subscr_cancel':
				it_exchange_paypal_standard_addon_update_subscriber_status( $subscriber_id, 'cancelled' );
				break;

			case 'subscr_eot':
				it_exchange_paypal_standard_addon_update_subscriber_status( $subscriber_id, 'deactivated' );
				break;

		}

	} else {

		//These IPNs don't have txn_types, why PayPal!? WHY!?
		if ( !empty( $request['reason_code'] ) ) {

			switch( $request['reason_code'] ) {

				case 'refund' :
					it_exchange_paypal_standard_addon_update_transaction_status( $request['parent_txn_id'], $request['payment_status'] );
					it_exchange_paypal_standard_addon_add_refund_to_transaction( $request['parent_txn_id'], $request['mc_gross'] );
					if ( $subscriber_id )
						it_exchange_paypal_standard_addon_update_subscriber_status( $subscriber_id, 'refunded' );
					break;

			}

		}

	}

}
add_action( 'it_exchange_webhook_it_exchange_paypal-standard', 'it_exchange_paypal_standard_addon_process_webhook' );

/**
 * Gets iThemes Exchange's Transaction ID from PayPal Standard's Transaction ID
 *
 * @since 0.4.19
 *
 * @param integer $paypal_standard_id id of paypal transaction
 * @return integer iTheme Exchange's Transaction ID
*/
function it_exchange_paypal_standard_addon_get_ite_transaction_id( $paypal_standard_id ) {
	$transactions = it_exchange_paypal_standard_addon_get_transaction_id( $paypal_standard_id );
	foreach( $transactions as $transaction ) { //really only one
		return $transaction->ID;
	}
}

/**
 * Grab a transaction from the paypal transaction ID
 *
 * @since 0.4.0
 *
 * @param integer $paypal_standard_id id of paypal transaction
 * @return transaction object
*/
function it_exchange_paypal_standard_addon_get_transaction_id( $paypal_standard_id ) {
	$args = array(
		'meta_key'    => '_it_exchange_transaction_method_id',
		'meta_value'  => $paypal_standard_id,
		'numberposts' => 1, //we should only have one, so limit to 1
	);
	return it_exchange_get_transactions( $args );
}

/**
 * Grab a transaction from the paypal transaction ID
 *
 * @since 0.4.0
 *
 * @param integer $paypal_standard_id id of paypal transaction
 * @return transaction object
*/
function it_exchange_paypal_standard_addon_get_transaction_id_by_subscriber_id( $subscriber_id ) {
	$args = array(
		'meta_key'    => '_it_exchange_transaction_subscriber_id',
		'meta_value'  => $subscriber_id,
		'numberposts' => 1, //we should only have one, so limit to 1
	);
	return it_exchange_get_transactions( $args );
}

/**
 * Updates a paypals transaction status based on paypal ID
 *
 * @since 0.4.0
 *
 * @param integer $paypal_standard_id id of paypal transaction
 * @param string $new_status new status
 * @return bool
*/
function it_exchange_paypal_standard_addon_update_transaction_status( $paypal_standard_id, $new_status ) {
	$transactions = it_exchange_paypal_standard_addon_get_transaction_id( $paypal_standard_id );
	foreach( $transactions as $transaction ) { //really only one
		$current_status = it_exchange_get_transaction_status( $transaction );
		if ( $new_status !== $current_status )
			it_exchange_update_transaction_status( $transaction, $new_status );
		return true;
	}
	return false;
}

/**
 * Add a new transaction, really only used for subscription payments.
 * If a subscription pays again, we want to create another transaction in Exchange
 * This transaction needs to be linked to the parent transaction.
 *
 * @since 1.3.0
 *
 * @param integer $paypal_standard_id id of paypal transaction
 * @param string $payment_status new status
 * @param string $subscriber_id from PayPal (optional)
 * @return bool
*/
function it_exchange_paypal_standard_addon_add_child_transaction( $paypal_standard_id, $payment_status, $subscriber_id=false, $amount ) {
	$transactions = it_exchange_paypal_standard_addon_get_transaction_id( $paypal_standard_id );
	if ( !empty( $transactions ) ) {
		//this transaction DOES exist, don't try to create a new one, just update the status
		it_exchange_paypal_standard_addon_update_transaction_status( $paypal_standard_id, $payment_status );
	} else {

		if ( !empty( $subscriber_id ) ) {

			$transactions = it_exchange_paypal_standard_addon_get_transaction_id_by_subscriber_id( $subscriber_id );
			foreach( $transactions as $transaction ) { //really only one
				$parent_tx_id = $transaction->ID;
				$customer_id = get_post_meta( $transaction->ID, '_it_exchange_customer_id', true );
			}

		} else {
			$parent_tx_id = false;
			$customer_id = false;
		}

		if ( $parent_tx_id && $customer_id ) {
			$transaction_object = new stdClass;
			$transaction_object->total = $amount;
			it_exchange_add_child_transaction( 'paypal-standard', $paypal_standard_id, $payment_status, $customer_id, $parent_tx_id, $transaction_object );
			return true;
		}
	}
	return false;
}

/**
 * Adds a refund to post_meta for a stripe transaction
 *
 * @since 0.4.0
 * @param string $paypal_standard_id PayPal Transaction ID
 * @param string $refund Refund Amount
*/
function it_exchange_paypal_standard_addon_add_refund_to_transaction( $paypal_standard_id, $refund ) {
	$transactions = it_exchange_paypal_standard_addon_get_transaction_id( $paypal_standard_id );
	foreach( $transactions as $transaction ) { //really only one
		it_exchange_add_refund_to_transaction( $transaction, number_format( abs( $refund ), '2', '.', '' ) );
	}
}

/**
 * Updates a subscription ID to post_meta for a paypal transaction
 *
 * @since 1.3.0
 * @param string $paypal_standard_id PayPal Transaction ID
 * @param string $subscriber_id PayPal Subscriber ID
*/
function it_exchange_paypal_standard_addon_update_subscriber_id( $paypal_standard_id, $subscriber_id ) {
	$transactions = it_exchange_paypal_standard_addon_get_transaction_id( $paypal_standard_id );
	foreach( $transactions as $transaction ) { //really only one
		do_action( 'it_exchange_update_transaction_subscription_id', $transaction, $subscriber_id );
	}
}

/**
 * Updates a subscription status to post_meta for a paypal transaction
 *
 * @since 1.3.0
 * @param string $subscriber_id PayPal Subscriber ID
 * @param string $status Status of Subscription
*/
function it_exchange_paypal_standard_addon_update_subscriber_status( $subscriber_id, $status ) {
	$transactions = it_exchange_paypal_standard_addon_get_transaction_id_by_subscriber_id( $subscriber_id );
	foreach( $transactions as $transaction ) { //really only one
		// If the subscription has been cancelled/suspended and fully refunded, they need to be deactivated
		if ( !in_array( $status, array( 'active', 'deactivated' ) ) ) {
			if ( $transaction->has_refunds() && 0 === it_exchange_get_transaction_total( $transaction, false ) )
				$status = 'deactivated';

			if ( $transaction->has_children() ) {
				//Get the last child and make sure it hasn't been fully refunded
				$args = array(
					'numberposts' => 1,
					'order'       => 'ASC',
				);
				$last_child_transaction = $transaction->get_children( $args );
				foreach( $last_child_transaction as $last_transaction ) { //really only one
					$last_transaction = it_exchange_get_transaction( $last_transaction );
					if ( $last_transaction->has_refunds() && 0 === it_exchange_get_transaction_total( $last_transaction, false ) )
						$status = 'deactivated';
				}
			}
		}
		do_action( 'it_exchange_update_transaction_subscription_status', $transaction, $subscriber_id, $status );
	}
}

/**
 * Gets the interpretted transaction status from valid paypal transaction statuses
 *
 * @since 0.4.0
 *
 * @param string $status the string of the paypal transaction
 * @return string translaction transaction status
*/
function it_exchange_paypal_standard_addon_transaction_status_label( $status ) {

	switch ( strtolower( $status ) ) {

		case 'completed':
		case 'success':
		case 'canceled_reversal':
		case 'processed' :
			return __( 'Paid', 'it-l10n-ithemes-exchange' );
		case 'refunded':
		case 'refund':
			return __( 'Refund', 'it-l10n-ithemes-exchange' );
		case 'reversed':
			return __( 'Reversed', 'it-l10n-ithemes-exchange' );
		case 'buyer_complaint':
			return __( 'Buyer Complaint', 'it-l10n-ithemes-exchange' );
		case 'denied' :
			return __( 'Denied', 'it-l10n-ithemes-exchange' );
		case 'expired' :
			return __( 'Expired', 'it-l10n-ithemes-exchange' );
		case 'failed' :
			return __( 'Failed', 'it-l10n-ithemes-exchange' );
		case 'pending' :
			return __( 'Pending', 'it-l10n-ithemes-exchange' );
		case 'voided' :
			return __( 'Voided', 'it-l10n-ithemes-exchange' );
		default:
			return __( 'Unknown', 'it-l10n-ithemes-exchange' );
	}

}
add_filter( 'it_exchange_transaction_status_label_paypal-standard', 'it_exchange_paypal_standard_addon_transaction_status_label' );

/**
 * Returns a boolean. Is this transaction a status that warrants delivery of any products attached to it?
 *
 * @since 0.4.2
 *
 * @param boolean $cleared passed in through WP filter. Ignored here.
 * @param object $transaction
 * @return boolean
*/
function it_exchange_paypal_standard_transaction_is_cleared_for_delivery( $cleared, $transaction ) {
    $valid_stati = array(
		'completed',
		'success',
		'canceled_reversal',
		'processed',
	);
    return in_array( strtolower( it_exchange_get_transaction_status( $transaction ) ), $valid_stati );
}
add_filter( 'it_exchange_paypal-standard_transaction_is_cleared_for_delivery', 'it_exchange_paypal_standard_transaction_is_cleared_for_delivery', 10, 2 );

/*
 * Returns the unsubscribe action for PayPal autorenewing payments
 *
 * @since 1.3.0
 *
 * @param string $output Should be an empty string
 * @param array $options Array of options passed from Recurring Payments add-on
 * @return string $output Unsubscribe action
*/
function it_exchange_paypal_standard_unsubscribe_action( $output, $options ) {
	$paypal_settings      = it_exchange_get_option( 'addon_paypal_standard' );
	$paypal_url           = PAYPAL_PAYMENT_URL;
	$paypal_email         = $paypal_settings['live-email-address'];

	$output  = '<a class="button" href="' . $paypal_url . '?cmd=_subscr-find&alias=' . urlencode( $paypal_email ) . '">';
	$output .= $options['label'];
	$output .= '</a>';

	return $output;
}
add_filter( 'it_exchange_paypal-standard_unsubscribe_action', 'it_exchange_paypal_standard_unsubscribe_action', 10, 2 );

/**
 * Output the Cancel URL for the Payments screen
 *
 * @since 1.3.1
 *
 * @param object $transaction iThemes Transaction object
 * @return void
*/
function it_exchange_paypal_standard_after_payment_details_cancel_url( $transaction ) {
	$cart_object = get_post_meta( $transaction->ID, '_it_exchange_cart_object', true );
	foreach ( $cart_object->products as $product ) {
		$autorenews = $transaction->get_transaction_meta( 'subscription_autorenew_' . $product['product_id'], true );
		if ( $autorenews ) {
			$subscriber_id = $transaction->get_transaction_meta( 'subscriber_id', true );
			$status = $transaction->get_transaction_meta( 'subscriber_status', true );
			switch( $status ) {

				case 'deactivated':
					$output = __( 'Recurring payment has been deactivated', 'it-l10n-ithemes-exchange' );
					break;

				case 'cancelled':
					$output = __( 'Recurring payment has been cancelled', 'it-l10n-ithemes-exchange' );
					break;

				case 'suspended':
					$output = __( 'Recurring payment has been suspended', 'it-l10n-ithemes-exchange' );
					break;

				case 'active':
				default:
					$output = '<a href="' . PAYPAL_LIVE_URL . '">' . __( 'Cancel Recurring Payment', 'it-l10n-ithemes-exchange' ) . ' (' . __( 'Profile ID', 'it-l10n-ithemes-exchange' ) . ': ' . $subscriber_id . ')</a>';
					break;
			}
			?>
			<div class="transaction-autorenews clearfix spacing-wrapper">
				<div class="recurring-payment-cancel-options left">
					<div class="recurring-payment-status-name"><?php echo $output; ?></div>
				</div>
			</div>
			<?php
			continue;
		}
	}
}
add_action( 'it_exchange_after_payment_details_cancel_url_for_paypal-standard', 'it_exchange_paypal_standard_after_payment_details_cancel_url' );

/**
 * Convert old option keys to new option keys
 *
 * Our original option keys for this plugin were generating form field names 80+ chars in length
 *
 * @since 1.6.2
 *
 * @param  array   $options         options as pulled from the DB
 * @param  string  $key             the key for the options
 * @param  boolean $break_cache     was the flag to break cache passed?
 * @param  boolean $merge_defaults  was the flag to merge defaults passed?
 * @return array
*/
function it_exchange_paypal_standard_convert_option_keys( $options, $key, $break_cache, $merge_defaults ) {
    if ( 'addon_paypal_standard' != $key )
        return $options;

    foreach( $options as $key => $value ) {
        if ( 'paypal-standard-' == substr( $key, 0, 16 ) && empty( $opitons[substr( $key, 16 )] ) ) {
            $options[substr( $key, 16 )] = $value;
            unset( $options[$key] );
        }    
    }    
    return $options;
}
add_filter( 'it_exchange_get_option', 'it_exchange_paypal_standard_convert_option_keys', 10, 4 ); 

/**
 * Class for Stripe
 * @since 0.4.0
*/
class IT_Exchange_PayPal_Standard_Add_On {

	/**
	 * @var boolean $_is_admin true or false
	 * @since 0.4.0
	*/
	var $_is_admin;

	/**
	 * @var string $_current_page Current $_GET['page'] value
	 * @since 0.4.0
	*/
	var $_current_page;

	/**
	 * @var string $_current_add_on Current $_GET['add-on-settings'] value
	 * @since 0.4.0
	*/
	var $_current_add_on;

	/**
	 * @var string $status_message will be displayed if not empty
	 * @since 0.4.0
	*/
	var $status_message;

	/**
	 * @var string $error_message will be displayed if not empty
	 * @since 0.4.0
	*/
	var $error_message;

	/**
	 * Class constructor
	 *
	 * Sets up the class.
	 * @since 0.4.0
	 * @return void
	*/
	function IT_Exchange_PayPal_Standard_Add_On() {
		$this->_is_admin       = is_admin();
		$this->_current_page   = empty( $_GET['page'] ) ? false : $_GET['page'];
		$this->_current_add_on = empty( $_GET['add-on-settings'] ) ? false : $_GET['add-on-settings'];

		if ( ! empty( $_POST ) && $this->_is_admin && 'it-exchange-addons' == $this->_current_page && 'paypal-standard' == $this->_current_add_on ) {
			$this->save_settings();
		}

	}

	function print_settings_page() {
		$settings = it_exchange_get_option( 'addon_paypal_standard', true );
		$form_values  = empty( $this->error_message ) ? $settings : ITForm::get_post_data();
		$form_options = array(
			'id'      => apply_filters( 'it_exchange_add_on_paypal-standard', 'it-exchange-add-on-paypal-standard-settings' ),
			'enctype' => apply_filters( 'it_exchange_add_on_paypal-standard_settings_form_enctype', false ),
			'action'  => 'admin.php?page=it-exchange-addons&add-on-settings=paypal-standard',
		);
		$form         = new ITForm( $form_values, array( 'prefix' => 'it-exchange-add-on-paypal_standard' ) );

		if ( ! empty ( $this->status_message ) )
			ITUtility::show_status_message( $this->status_message );
		if ( ! empty( $this->error_message ) )
			ITUtility::show_error_message( $this->error_message );

		?>
		<div class="wrap">
			<?php ITUtility::screen_icon( 'it-exchange' ); ?>
			<h2><?php _e( 'PayPal Standard Settings - Basic', 'it-l10n-ithemes-exchange' ); ?></h2>

			<?php do_action( 'it_exchange_paypal-standard_settings_page_top' ); ?>
			<?php do_action( 'it_exchange_addon_settings_page_top' ); ?>

			<?php $form->start_form( $form_options, 'it-exchange-paypal-standard-settings' ); ?>
				<?php do_action( 'it_exchange_paypal-standard_settings_form_top' ); ?>
				<?php $this->get_paypal_standard_payment_form_table( $form, $form_values ); ?>
				<?php do_action( 'it_exchange_paypal-standard_settings_form_bottom' ); ?>
				<p class="submit">
					<?php $form->add_submit( 'submit', array( 'value' => __( 'Save Changes', 'it-l10n-ithemes-exchange' ), 'class' => 'button button-primary button-large' ) ); ?>
				</p>
			<?php $form->end_form(); ?>
			<?php do_action( 'it_exchange_paypal-standard_settings_page_bottom' ); ?>
			<?php do_action( 'it_exchange_addon_settings_page_bottom' ); ?>
		</div>
		<?php
	}

	function get_paypal_standard_payment_form_table( $form, $settings = array() ) {

		$general_settings = it_exchange_get_option( 'settings_general' );

		if ( ! empty( $_GET['page'] ) && 'it-exchange-setup' == $_GET['page'] ) : ?>
			<h3><?php _e( 'PayPal Standard - Basic (Fastest Setup)', 'it-l10n-ithemes-exchange' ); ?></h3>
		<?php endif;

		if ( !empty( $settings ) )
			foreach ( $settings as $key => $var )
				$form->set_option( $key, $var );

		?>
		<div class="it-exchange-addon-settings it-exchange-paypal-addon-settings">
            <p>
				<?php _e( 'This is the simple and fast version to get PayPal setup for your store. You might use this version just to get your store going, but we highly suggest you switch to the PayPal Standard Secure option. To get PayPal set up for use with Exchange, you\'ll need to add the following information from your PayPal account.', 'it-l10n-ithemes-exchange' ); ?><br /><br />
				<?php _e( 'Video:', 'it-l10n-ithemes-exchange' ); ?>&nbsp;<a href="http://ithemes.com/tutorials/setting-up-paypal-standard-basic/" target="_blank"><?php _e( 'Setting Up PayPal Standard Basic', 'it-l10n-ithemes-exchange' ); ?></a>
			</p>
			<p><?php _e( 'Don\'t have a PayPal account yet?', 'it-l10n-ithemes-exchange' ); ?> <a href="http://paypal.com" target="_blank"><?php _e( 'Go set one up here', 'it-l10n-ithemes-exchange' ); ?></a>.</p>
            <h4><?php _e( 'What is your PayPal email address?', 'it-l10n-ithemes-exchange' ); ?></h4>
			<p>
				<label for="live-email-address"><?php _e( 'PayPal Email Address', 'it-l10n-ithemes-exchange' ); ?> <span class="tip" title="<?php _e( 'We need this to tie payments to your account.', 'it-l10n-ithemes-exchange' ); ?>">i</span></label>
				<?php 
				if ( ! empty( $_GET['page'] ) && 'it-exchange-setup' == $_GET['page'] )
					$form->add_text_box( 'paypal-standard-live-email-address' );
				else
					$form->add_text_box( 'live-email-address' );
				?>
			</p>
			<p>
				<label for="purchase-button-label"><?php _e( 'Purchase Button Label', 'it-l10n-ithemes-exchange' ); ?> <span class="tip" title="<?php _e( 'This is the text inside the button your customers will press to purchase with PayPal Standard', 'it-l10n-ithemes-exchange' ); ?>">i</span></label>
				<?php
				if ( ! empty( $_GET['page'] ) && 'it-exchange-setup' == $_GET['page'] )
					$form->add_text_box( 'paypal-standard-purchase-button-label' );
				else
					$form->add_text_box( 'purchase-button-label' );
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * Save settings
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function save_settings() {
		$defaults = it_exchange_get_option( 'addon_paypal_standard' );
		$new_values = wp_parse_args( ITForm::get_post_data(), $defaults );

		// Check nonce
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'it-exchange-paypal-standard-settings' ) ) {
			$this->error_message = __( 'Error. Please try again', 'it-l10n-ithemes-exchange' );
			return;
		}

		$errors = apply_filters( 'it_exchange_add_on_paypal_standard_validate_settings', $this->get_form_errors( $new_values ), $new_values );
		if ( ! $errors && it_exchange_save_option( 'addon_paypal_standard', $new_values ) ) {
			ITUtility::show_status_message( __( 'Settings saved.', 'it-l10n-ithemes-exchange' ) );
		} else if ( $errors ) {
			$errors = implode( '<br />', $errors );
			$this->error_message = $errors;
		} else {
			$this->status_message = __( 'Settings not saved.', 'it-l10n-ithemes-exchange' );
		}

		do_action( 'it_exchange_save_add_on_settings_paypal-standard' );

	}

	function paypal_standard_save_wizard_settings() {
		if ( empty( $_REQUEST['it_exchange_settings-wizard-submitted'] ) )
			return;

		$paypal_standard_settings = array();

		$fields = array(
			'live-email-address',
			'purchase-button-label',
		);
		$default_wizard_paypal_standard_settings = apply_filters( 'default_wizard_paypal-standard_settings', $fields );

		foreach( $default_wizard_paypal_standard_settings as $var ) {

			if ( isset( $_REQUEST['it_exchange_settings-paypal-standard-' . $var] ) ) {
				$paypal_standard_settings[$var] = $_REQUEST['it_exchange_settings-paypal-standard-' . $var];
			}

		}

		$settings = wp_parse_args( $paypal_standard_settings, it_exchange_get_option( 'addon_paypal_standard' ) );

		if ( $error_msg = $this->get_form_errors( $settings ) ) {

			return $error_msg;

		} else {
			it_exchange_save_option( 'addon_paypal_standard', $settings );
			$this->status_message = __( 'Settings Saved.', 'it-l10n-ithemes-exchange' );
		}

		return;

	}

	/**
	 * Validates for values
	 *
	 * Returns string of errors if anything is invalid
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function get_form_errors( $values ) {

		$errors = array();
		if ( empty( $values['live-email-address'] ) )
			$errors[] = __( 'Please include your PayPal Email Address', 'it-l10n-ithemes-exchange' );

		return $errors;
	}
}
