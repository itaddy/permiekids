<?php
/**
 * This is a wrapper to our Purchase Dialog class.
 *
 * If an addon doesn't want to call this helper, they need to include the class (once),
 * Extend it for thier use, and init it with their slug and options.
 *
 * @since 1.3.0
 *
 * @param string $transaction_method_slug
 * @param array $options
 * @return string
*/
function it_exchange_generate_purchase_dialog( $transaction_method_slug, $options=array() ) {
	include_once( dirname( dirname( __FILE__ ) ) . '/lib/purchase-dialog/purchase-dialog.php' );
	$dialog = new IT_Exchange_Purchase_Dialog( $transaction_method_slug, $options );
	$GLOBALS['it_exchange']['purchase_dialog'] = $dialog;
	return $dialog->insert_dialog();
}

/**
 * Get the credit card values
 *
 * @since 1.3.0
 *
 * @param string $transaction_method_slug
 * @param array $options
 * @return array
*/
function it_exchange_get_purchase_dialog_submitted_values( $transaction_method_slug, $options=array() ) {
	include_once( dirname( dirname( __FILE__ ) ) . '/lib/purchase-dialog/purchase-dialog.php' );
	$dialog = new IT_Exchange_Purchase_Dialog( $transaction_method_slug, $options );
	$GLOBALS['it_exchange']['purchase_dialog'] = $dialog;
	return $dialog->get_submitted_form_values();
}

/**
 * Are the submitted values valid
 *
 * @since 1.3.0
 *
 * @param string $transaction_method_slug
 * @param array $options
 * @return array
*/
function it_exchange_submitted_purchase_dialog_values_are_valid( $transaction_method_slug, $options=array() ) {
	include_once( dirname( dirname( __FILE__ ) ) . '/lib/purchase-dialog/purchase-dialog.php' );
	$dialog = new IT_Exchange_Purchase_Dialog( $transaction_method_slug, $options );
	$GLOBALS['it_exchange']['purchase_dialog'] = $dialog;
	return $dialog->is_submitted_form_valid();
}

function it_exchange_get_current_purchase_dialog() {
	if ( ! empty( $GLOBALS['it_exchange']['purchase_dialog'] ) && is_object( $GLOBALS['it_exchange']['purchase_dialog'] ) )
		return $GLOBALS['it_exchange']['purchase_dialog'];
	else
		return false;
}

function it_exchange_flag_purchase_dialog_error( $transaction_method_slug ) {
	it_exchange_add_session_data( $transaction_method_slug . '_purchase_dialog_error', true );
}

function it_exchange_purchase_dialog_has_error( $transaction_method_slug ) {
	return (boolean) it_exchange_get_session_data( $transaction_method_slug . '_purchase_dialog_error' );
}

function it_exchange_clear_purchase_dialog_error_flag( $transaction_method_slug ) {
	it_exchange_clear_session_data( $transaction_method_slug . '_purchase_dialog_error' );
}
