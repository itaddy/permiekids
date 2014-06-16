<?php

/**
 *
 * @package LDMW
 * @subpackage Exchange/Account
 * @since 1.0
 */
abstract class LDMW_Exchange_Account_View {
	/**
	 * @var int
	 */
	protected $user_id;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->user_id = get_current_user_id();
		wp_enqueue_style( 'ldmw-account', LDMW_Plugin::$url . "lib/exchange/assets/css/account.css" );
	}

	/**
	 * Process submitted data.
	 *
	 * @param $data array
	 *
	 * @return void
	 */
	abstract function process_data( $data );

	/**
	 * Render the fields on the form
	 *
	 * @return void
	 */
	abstract function render();

}