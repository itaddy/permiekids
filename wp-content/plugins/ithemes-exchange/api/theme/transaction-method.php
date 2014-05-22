<?php
/**
 * Theme API class for Transaction_Method
 * @package IT_Exchange
 * @since 0.4.0
*/

class IT_Theme_API_Transaction_Method implements IT_Theme_API {

	/**
	 * API context
	 * @var string $_context
	 * @since 0.4.0
	*/
	private $_context = 'transaction-method';

	/**
	 * Maps api tags to methods
	 * @var array $_tag_map
	 * @since 0.4.0
	*/
	public $_tag_map = array(
		'makepayment' => 'make_payment',
	);

	/**
	 * The current transaction method
	 * @var array $_transaction_method
	 * @since 0.4.0
	*/
	private $_transaction_method = false;

	/**
	 * Constructor
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function IT_Theme_API_Transaction_Method() {
		$this->_transaction_method = empty( $GLOBALS['it_exchange']['transaction_method'] ) ? false : $GLOBALS['it_exchange']['transaction_method'];
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
	 * Returns the payment action data/html
	 *
	 * @since 0.4.0
	 *
	 * @param array $options
	 * @return mixed
	*/
	function make_payment( $options=array() ) {
		return it_exchange_get_transaction_method_make_payment_button( $this->_transaction_method['slug'], $options );
	}
}
