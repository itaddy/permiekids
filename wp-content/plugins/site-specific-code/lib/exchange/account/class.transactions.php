<?php

/**
 *
 * @package LDMW
 * @subpackage Exchange/Account
 * @since 1.0
 */
class LDMW_Exchange_Account_Transactions extends LDMW_Exchange_Account_View {
	/**
	 * Constructor.
	 */
	function __construct() {
		parent::__construct();

		add_action( 'wp_ajax_ldmw_resend_email_receipt', array( $this, 'process_data' ) );

		$this->render();
	}

	/**
	 * Process submitted data.
	 *
	 * @param $data array
	 *
	 * @return void
	 */
	function process_data( $data ) {
		$data = $_POST;
		if ( isset( $data['resend_email'] ) && isset( $data['nonce'] ) && wp_verify_nonce( $data['nonce'], 'ldmw-resend-receipt-' . $data['resend_email'] ) ) {
			$emails = new IT_Exchange_Email_Notifications();
			$emails->send_purchase_emails( $data['resend_email'], false );
			echo "Email sent!";
			die();
		}
	}

	/**
	 * Render the fields on the form
	 *
	 * @return void
	 */
	function render() {
		wp_enqueue_style( 'ldmw-display' );
		wp_enqueue_script( 'ldmw-account-ajax', LDMW_Plugin::$url . "lib/exchange/assets/js/ajax.js", array( 'jquery' ) );
		wp_localize_script( 'ldmw-account-ajax', 'ldmw', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' )
		  )
		);
	}

}