<?php
/**
 * Zero Sum Transaction Method
 * For situations when the Cart Total is 0 (free), we still want to record the transaction!
 *
 * @since 0.4.0
 * @package IT_Exchange
*/

/**
 * This proccesses a zer-sum transaction.
 *
 * @since 0.4.0
 *
 * @param string $status passed by WP filter.
 * @param object $transaction_object The transaction object
*/
function it_exchange_zero_sum_checkout_addon_process_transaction( $status, $transaction_object ) {
	// If this has been modified as true already, return.
	if ( $status )
		return $status;

	// Verify nonce
	if ( ! empty( $_REQUEST['_zero_sum_checkout_nonce'] ) && ! wp_verify_nonce( $_REQUEST['_zero_sum_checkout_nonce'], 'zero-sum-checkout-checkout' ) ) {
		it_exchange_add_message( 'error', __( 'Transaction Failed, unable to verify security token.', 'it-l10n-ithemes-exchange' ) );
		return false;
	} else {
		$uniqid = it_exchange_get_zero_sum_checkout_transaction_uniqid();

		// Get customer ID data
		$it_exchange_customer = it_exchange_get_current_customer();

		return it_exchange_add_transaction( 'zero-sum-checkout', $uniqid, 'Completed', $it_exchange_customer->id, $transaction_object );
	}

	return false;
}
add_action( 'it_exchange_do_transaction_zero-sum-checkout', 'it_exchange_zero_sum_checkout_addon_process_transaction', 10, 2 );

/**
 * Returns a boolean. Is this transaction a status that warrants delivery of any products attached to it?
 *
 * @since 0.4.2
 *
 * @param boolean $cleared passed in through WP filter. Ignored here.
 * @param object $transaction
 * @return boolean
*/
function it_exchange_zero_sum_checkout_transaction_is_cleared_for_delivery( $cleared, $transaction ) {
	$valid_stati = array( 'Completed' );
	return in_array( it_exchange_get_transaction_status( $transaction ), $valid_stati );
}
add_filter( 'it_exchange_zero-sum-checkout_transaction_is_cleared_for_delivery', 'it_exchange_zero_sum_checkout_transaction_is_cleared_for_delivery', 10, 2 );

function it_exchange_get_zero_sum_checkout_transaction_uniqid() {
	$uniqid = uniqid( '', true );

	if( !it_exchange_verify_zero_sum_checkout_transaction_unique_uniqid( $uniqid ) )
		$uniqid = it_exchange_get_zero_sum_checkout_transaction_uniqid();

	return $uniqid;
}

/**
 * Verifies if Unique ID is actually Unique
 *
 * @since 0.4.2
 *
 * @param string $unique id
 * @return boolean true if it is, false otherwise
*/
function it_exchange_verify_zero_sum_checkout_transaction_unique_uniqid( $uniqid ) {
	if ( !empty( $uniqid ) ) { //verify we get a valid 32 character md5 hash

		$args = array(
			'post_type' => 'it_exchange_tran',
			'meta_query' => array(
				array(
					'key' => '_it_exchange_transaction_method',
					'value' => 'zero-sum-checkout',
				),
				array(
					'key' => '_it_exchange_transaction_method_id',
					'value' => $uniqid ,
				),
			),
		);

		$query = new WP_Query( $args );

		return ( !empty( $query ) );
	}

	return false;
}

/**
 * Returns the button for making the payment
 *
 * @since 0.4.0
 *
 * @param array $options
 * @return string
*/
function it_exchange_zero_sum_checkout_addon_make_payment_button( $options ) {

	if ( 0 < it_exchange_get_cart_total( false ) )
		return;

	$products = it_exchange_get_cart_data( 'products' );

	$payment_form = '<form id="zero_sum_checkout_form" action="' . it_exchange_get_page_url( 'transaction' ) . '" method="post">';
	$payment_form .= '<input type="hidden" name="it-exchange-transaction-method" value="zero-sum-checkout" />';
	$payment_form .= wp_nonce_field( 'zero-sum-checkout-checkout', '_zero_sum_checkout_nonce', true, false );

	$payment_form .= '<input type="submit" id="zero-sum-checkout-button" name="zero_sum_checkout_purchase" value="' . apply_filters( 'zero_sum_checkout_button_label', 'Complete Purchase' ) .'" />';

	$payment_form .= '</form>';

	return $payment_form;
}
add_filter( 'it_exchange_get_zero-sum-checkout_make_payment_button', 'it_exchange_zero_sum_checkout_addon_make_payment_button', 10, 2 );

/*
 * Handles expired transactions that are zero sum checkout
 * If this product autorenews and is zero-sum, it should auto-renew unless the susbcriber status has been deactivated already
 * If it autorenews, it creates a zero-sum child transaction
 *
 * @since 1.3.1
 * @param bool $true Default True bool, passed from recurring payments expire schedule
 * @param int $product_id iThemes Exchange Product ID
 * @param object $transaction iThemes Exchange Transaction Object
 * @return bool True if expired, False if not Expired
*/
function it_exchange_zero_sum_checkout_handle_expired( $true, $product_id, $transaction ) {
	$transaction_method = it_exchange_get_transaction_method( $transaction->ID );

	if ( 'zero-sum-checkout' === $transaction_method ) {

		$autorenews = $transaction->get_transaction_meta( 'subscription_autorenew_' . $product_id, true );
		$status = $transaction->get_transaction_meta( 'subscriber_status', true );
		if ( $autorenews && empty( $status ) ) { //if the subscriber status is empty, it hasn't been set, which really means it's active for zero-sum-checkouts
			//if the transaction autorenews and is zero sum, we want to create a new child transaction until deactivated
			it_exchange_zero_sum_checkout_add_child_transaction( $transaction );
			return false;
		}

	}

	return $true;

}
add_filter( 'it_exchange_recurring_payments_handle_expired', 'it_exchange_zero_sum_checkout_handle_expired', 10, 3 );

/**
 * Add a new transaction, really only used for subscription payments.
 * If a subscription pays again, we want to create another transaction in Exchange
 * This transaction needs to be linked to the parent transaction.
 *
 * @since 1.3.1
 *
 * @param string $parent_txn_id Parent Transaction ID
 * @return bool
*/
function it_exchange_zero_sum_checkout_add_child_transaction( $parent_txn ) {
	$customer_id = get_post_meta( $parent_txn->ID, '_it_exchange_customer_id', true );
	if ( $customer_id ) {
		$uniqid = it_exchange_get_zero_sum_checkout_transaction_uniqid();
		$transaction_object = new stdClass;
		$transaction_object->total = 0;
		it_exchange_add_child_transaction( 'zero-sum-checkout', $uniqid, 'Completed', $customer_id, $parent_txn->ID, $transaction_object );
		return true;
	}
	return false;
}

/**
 * Output the Cancel URL for the Payments screen
 *
 * @since 1.3.1
 *
 * @param object $transaction iThemes Transaction object
 * @return void
*/
function it_exchange_zero_sum_checkout_after_payment_details_cancel_url( $transaction ) {
	$cart_object = get_post_meta( $transaction->ID, '_it_exchange_cart_object', true );
	foreach ( $cart_object->products as $product ) {
		$autorenews = $transaction->get_transaction_meta( 'subscription_autorenew_' . $product['product_id'], true );
		if ( $autorenews ) {
			$status = $transaction->get_transaction_meta( 'subscriber_status', true );
			switch( $status ) {

				case false: //active
				case '':
					$output = '<a href="' . add_query_arg( 'zero-sum-recurring-payment', 'cancel' ) . '">' . __( 'Cancel Recurring Payment', 'it-l10n-ithemes-exchange' ) . '</a>';
					break;

				case 'deactivated':
				default:
					$output = __( 'Recurring payment has been deactivated', 'it-l10n-ithemes-exchange' );
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
add_action( 'it_exchange_after_payment_details_cancel_url_for_zero-sum-checkout', 'it_exchange_zero_sum_checkout_after_payment_details_cancel_url' );

/**
 * Process Zero Sum Recurring Payments cancellations
 *
 * @since 1.3.1
 *
 * @return void
*/
function it_exchange_process_zero_sum_recurring_payment_cancel() {
	if ( !empty( $_REQUEST['zero-sum-recurring-payment'] ) && 'cancel' === $_REQUEST['zero-sum-recurring-payment'] ) {
		if ( !empty( $_REQUEST['post'] ) && $post_id = $_REQUEST['post'] ) {
			$transaction = it_exchange_get_transaction( $post_id );
			$status = $transaction->update_transaction_meta( 'subscriber_status', 'cancel' );
		}
	}
}
add_action( 'admin_init', 'it_exchange_process_zero_sum_recurring_payment_cancel' );
