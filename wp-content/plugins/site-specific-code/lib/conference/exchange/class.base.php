<?php

/**
 *
 * @package Conference
 * @subpackage Exchange
 * @since 5/29
 */
class LDMW_Conference_Exchange_Base {
	/**
	 *
	 */
	public function __construct() {
		add_action( 'it_exchange_add_transaction_success', array( $this, 'mark_user_as_purchased_conference' ) );
		add_action( 'add_meta_boxes', array( $this, 'register_metabox' ) );

		if ( isset( $_GET['ldmw_send_conference_reminders'] ) )
			$this->process_post();
	}

	/**
	 * Mark a user as having purchased a certain conference
	 *
	 * @param $transaction_id int
	 */
	public function mark_user_as_purchased_conference( $transaction_id ) {
		$transaction = it_exchange_get_transaction( $transaction_id );

		foreach ( $transaction->cart_details->products as $product ) {
			/**
			 * @var $product_object IT_Exchange_Product
			 */
			$product_object = it_exchange_get_product( $product['product_id'] );

			if ( false === $product_object )
				continue;

			if ( $product_object->product_type != 'event-product-type' )
				continue;

			update_user_meta( $transaction->customer_id, 'ldmw_conference_purchased_' . $product_object->ID, $transaction_id );
			update_user_meta( $transaction->customer_id, 'ldmw_conference_purchased', $transaction_id );
			add_user_meta( $transaction->customer_id, 'ldmw_conferences_purchased', $transaction_id );
		}
	}

	/**
	 * Register our metabox for allowing reminders to be sent
	 */
	public function register_metabox() {
		if ( current_user_can( 'edit_users' ) )
			add_meta_box( 'ldmw_send_conference_reminders', 'Send Payment Reminders', array( $this, 'render_metabox' ), TribeEvents::POSTTYPE, 'side' );
	}

	/**
	 * Render the metabox
	 */
	public function render_metabox() {
		echo '<p>Send payment reminders to users whose payment is late</p>';
		echo '<a href="' . add_query_arg( 'ldmw_send_conference_reminders', '1' ) . '" class="button">Send</a>';
	}

	/**
	 * Process the reminder action
	 */
	public function process_post() {
		if ( !current_user_can( 'edit_users' ) )
			return;

		$transactions = it_exchange_get_transactions( array(
			'transaction_method' => 'offline-payments',
			'status'             => 'pending'
		  )
		);

		/**
		 * @var $transaction IT_Exchange_Transaction
		 */
		foreach ( $transactions as $transaction ) {
			foreach ( $transaction->cart_details->products as $product ) {
				if ( $product['product_id'] != get_post_meta( $_GET['post'], '_ExchangeEventProduct', true ) )
					continue;

				$user_id = $transaction->customer_id;
				$template_html = LDMW_Options_Model::get_instance()->late_conference_payment;

				$template = new IBD_Notify_Email_Dynamic_Notification( LDMW_Notifications_Email::get_rule_content( $user_id ), wpautop( $template_html ) );
				$notification = new IBD_Notify_Email_Notification( $user_id, LDMW_Options_Model::get_instance()->late_conference_payment_subject, "Payment Required", array(
					'template' => $template
				  )
				);
				$notification->save();
			}
		}
	}

}