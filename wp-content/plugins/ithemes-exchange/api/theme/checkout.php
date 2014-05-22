<?php
/**
 * Theme API class for Checkout
 * @package IT_Exchange
 * @since 0.4.0
*/

class IT_Theme_API_Checkout implements IT_Theme_API {

	/**
	 * API context
	 * @var string $_context
	 * @since 0.4.0
	*/
	private $_context = 'checkout';

	/**
	 * Maps api tags to methods
	 * @var array $_tag_map
	 * @since 0.4.0
	*/
	public $_tag_map = array(
		'transactionmethods' => 'transaction_methods',
		'cancel'             => 'cancel',
	);

	/**
	 * Constructor
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function IT_Theme_API_Checkout() {
	}

	/**
	 * Returns the context. Also helps to confirm we are an iThemes Exchange theme API class
	 *
	 * @since 0.4.0
	 *
	 * @return string
	*/
	function get_api_context() {
		return $this->_context;
	}

	/**
	 * Sets up transaction method loop
	 *
	 * @since 0.4.0
	 *
	 * @param array $options
	 * @return mixed
	*/
	function transaction_methods( $options=array() ) {
		// Do we have any transaction methods
		if ( ! empty( $options['has'] ) )
			return (boolean) it_exchange_get_enabled_addons( array( 'category' => 'transaction-methods' ) );

		// If we made it here, we're doing a loop of applied coupons
		// This will init/reset the applied_coupons global and loop through them.
		if ( empty( $GLOBALS['it_exchange']['transaction_methods'] ) ) {
			$GLOBALS['it_exchange']['transaction_methods'] = it_exchange_get_enabled_addons( array( 'category' => 'transaction-methods' ) );
			$GLOBALS['it_exchange']['transaction_method'] = reset( $GLOBALS['it_exchange']['transaction_methods'] );
			return true;
		} else {
			if ( next( $GLOBALS['it_exchange']['transaction_methods'] ) ) {
				$GLOBALS['it_exchange']['transaction_method'] = current( $GLOBALS['it_exchange']['transaction_methods'] );
				return true;
			} else {
				$GLOBALS['it_exchange']['transaction_methods'] = array();
				end( $GLOBALS['it_exchange']['transaction_methods'] );
				$GLOBALS['it_exchange']['transaction_method'] = false;
				return false;
			}
		}
	}

	/**
	 * Returns data/html for cancel action
	 *
	 * @since 0.4.0
	 *
	 * @param array $options
	 * @return mixed
	*/
	function cancel( $options=array() ) {
		$defaults = array(
			'before' => '',
			'after'  => '',
			'format' => 'link',
			'label'  => __( 'Cancel', 'it-l10n-ithemes-exchange' ),
			'class'  => '',
			'focus'  => false
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$class = empty( $options['class'] ) ? 'it-exchange-cancel-checkout' : 'it-exchange-cancel-checkout ' . $options['class'];

		// Set URL
		if ( it_exchange_in_superwidget() && 2 > it_exchange_get_cart_products_count() ) {
			// Get clean url without any exchange query args
			$url = it_exchange_clean_query_args();
			$url = add_query_arg( 'ite-sw-state', 'cart', $url );
			$url = in_array( $options['focus'], array( 'coupon', 'quantity' ) ) ? add_query_arg( it_exchange_get_field_name( 'sw_cart_focus' ), $options['focus'], $url ) : $url;
		} else {
			$url = it_exchange_get_page_url ( 'cart' );
		}

		if ( 'link' == $options['format'] )
			return $options['before'] . '<a class="' . esc_attr( $class ) . '" href="' . $url . '">' . $options['label'] . '</a>' . $options['after'];

		return $url;
	}
}
