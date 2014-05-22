<?php
/**
 * Restricted Content class for THEME API in Membership Add-on
 *
 * @package exchange-addon-recurring-payments
 * @since 1.0.0
*/

class IT_Theme_API_Recurring_Payments implements IT_Theme_API {
	
	/**
	 * API context
	 * @var string $_context
	 * @since 1.0.0
	*/
	private $_context = 'recurring-payments';

	/**
	 * The current transaction
	 * @var array
	 * @since 1.0.0
	*/
	public $_transaction = false;

	/**
	 * The current _transaction_product
	 * @var array
	 * @since 1.0.0
	*/
	public $_transaction_product = false;

	/**
	 * The current customer
	 * @var array
	 * @since 1.0.0
	*/
	public $_customer = false;
	
	/**
	 * Maps api tags to methods
	 * @var array $_tag_map
	 * @since 1.0.0
	*/
	public $_tag_map = array(
		'unsubscribe' => 'unsubscribe',
		'expiration'  => 'expiration',
		'payments'    => 'payments',
	);

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 *
	 * @return void
	*/
	function IT_Theme_API_Recurring_Payments () {
		$this->_transaction         = empty( $GLOBALS['it_exchange']['transaction'] ) ? false : $GLOBALS['it_exchange']['transaction'];
		$this->_transaction_product = empty( $GLOBALS['it_exchange']['transaction_product'] ) ? false : $GLOBALS['it_exchange']['transaction_product'];
		if ( is_user_logged_in() )
			$this->_customer = it_exchange_get_current_customer();
	}

	/**
	 * Returns the context. Also helps to confirm we are an iThemes Exchange theme API class
	 *
	 * @since 1.0.0
	 * 
	 * @return string
	*/
	function get_api_context() {
		return $this->_context;
	}

	/**
	 * @since 1.0.0
	 * @return string
	*/
	function unsubscribe( $options=array() ) {
		$defaults = array(
			'before' => '',
			'after'  => '',
			'class'  => 'it-exchange-recurring-payments-unsubscribe',
			'label'  => apply_filters( 'it_exchange_recurring_payments_addon_unsubscribe_label', __( 'Cancel this subscription', 'it-l10n-exchange-addon-membership' ) ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );
		$output = '';
		if ( it_exchange_get_recurring_payments_addon_transaction_subscription_id( $this->_transaction ) ) {
			$output .= $options['before'];
			$subscription_status = $this->_transaction->get_transaction_meta( 'subscriber_status' );
			
			switch( $subscription_status ) {
				case 'deactivated' :
					$output .= __( 'Subscription deactivated', 'it-l10n-exchange-addon-membership' );
					break;
				case 'cancelled' :
					$output .= __( 'Subscription cancelled', 'it-l10n-exchange-addon-membership' );
					break;
				case 'suspended' :
					$output .= __( 'Subscription suspended', 'it-l10n-exchange-addon-membership' );
					break;
				case 'active' :
				default:
					$transaction_method = it_exchange_get_transaction_method( $this->_transaction );
					$output .= apply_filters( 'it_exchange_' . $transaction_method . '_unsubscribe_action', '', $options, $this->_transaction );
					break;
			}
			$output .= $options['after'];
		}
		return $output;
	}

	/**
	 * @since 1.0.0
	 * @return string
	*/
	function expiration( $options=array() ) {
		$defaults = array(
			'date_format'      => get_option( 'date_format' ),
			'before'           => '',
			'after'            => '',
			'class'            => 'it-exchange-recurring-payments-expiration',
			'label'            => apply_filters( 'it_exchange_recurring_payments_addon_expiration_label', __( 'Expires', 'it-l10n-exchange-addon-membership' ) ),
			'show_auto_renews' => false,
		);
		$options = ITUtility::merge_defaults( $options, $defaults );
		$output = '';
		$product_id = $this->_transaction_product['product_id'];
		$expire = $this->_transaction->get_transaction_meta( 'subscription_expires_' . $product_id, true );
		$arenew = $this->_transaction->get_transaction_meta( 'subscription_autorenew_' . $product_id, true );
		if ( !empty( $expire ) ) {
			if ( $options['show_auto_renews'] || !$arenew ) {
				$output = $options['label'] . ': ' . date_i18n( $options['date_format'], $expire );
			}
		}
		return $output;
	}

	/**
	 * @since 1.0.0
	 * @return string
	*/
	function payments( $options=array() ) {
		$defaults = array(
			'date_format'      => get_option( 'date_format' ),
			'format_currency'  => true,
			'before'           => '',
			'after'            => '',
			'class'            => 'it-exchange-recurring-payments-payments',
			'label'            => apply_filters( 'it_exchange_recurring_payments_addon_payments_label', __( 'Payment', 'it-l10n-exchange-addon-membership' ) ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );
		$output = '';
		if ( $this->_transaction->has_children() ) {
			$payment_transactions = $this->_transaction->get_children();
			
			$output .= $options['before'];
			$output .= '<ul class="' . $options['class'] . '">';
			foreach ( $payment_transactions as $transaction ) {
				$output .= '<li>';
				$output .= $options['label'] . ' ' . __( 'of', 'it-l10n-exchange-addon-membership' ) . ' ' . it_exchange_get_transaction_total( $transaction, $options['format_currency'] ) . ' on ' . it_exchange_get_transaction_date( $transaction, $options['date_format'] ) . ' - ' . it_exchange_get_transaction_status_label( $transaction );
			}
			$output .= '</ul>';
			$output .= $options['after'];
		}
		return $output;
	}
}
