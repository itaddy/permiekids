<?php

/**
 *
 * @package LDMW
 * @subpackage Exchange
 * @since 1.0
 */
class LDMW_Exchange_Base {
	/**
	 * Add necessary hooks and filters.
	 */
	public function __construct() {
		add_action( 'it_exchange_add_transaction_success', array( $this, 'convert_paid_invoice_to_membership' ) );
		add_action( 'it_exchange_add_transaction_success', array( $this, 'setup_membership' ) );
		add_filter( 'it_exchange_get_cart_product_base_price', array( $this, 'apply_late_fee' ), 9999, 3 );
		add_filter( 'it_exchange_get_product_feature_base-price', array( $this, 'change_membership_price' ), 999, 3 );

		add_action( 'it_exchange_get_content_checkout/elements/purchase-requirements/billing-address/elements/_fields_elements', array( $this, 'force_company_name_at_checkout' ) );
		add_action( 'it_exchange_get_super_widget_billing_address_fields_elements', array( $this, 'force_company_name_at_super_widget_checkout' ) );

		add_filter( 'it_exchange_get_stripe_make_payment_button', array( $this, 'change_stripe_checkout_button_text' ), 999 );

		add_filter( 'it_exchange_get_content_confirmation_address_meta_elements', array( $this, 'remove_shipping_address' ), 50 );
		add_filter( 'it_exchange_get_page_name', array( $this, 'modify_page_names' ), 999, 2 );

		add_filter( 'it_exchange_email_notification_order_table_product_name', array( $this, 'remove_view_available_content' ), 5, 2 );
		add_action( 'wp_ajax_ldmw_country_to_states', array( $this, 'country_to_states' ) );

		add_filter( 'it_exchange_get_customer_billing_address', array( $this, 'set_default_component_of_billing_address' ) );
	}

	/**
	 * Change the membership fee of a product, based on the AAS Option.
	 *
	 * @param $base_price string
	 * @param $product_id int
	 * @param array $options
	 *
	 * @return string
	 */
	public function change_membership_price( $base_price, $product_id, $options = array() ) {
		$db_product = it_exchange_get_product( $product_id );

		if ( $db_product === false || $product_id != LDMW_Options_Model::get_instance()->membership_product )
			return $base_price;

		if ( isset( $options['user_id'] ) )
			$user_id = $options['user_id'];
		else
			$user_id = it_exchange_get_current_customer_id();

		$customer_membership_grade = get_user_meta( $user_id, 'ldmw_membership_grade', true );
		$fee_option = "membership_fee_$customer_membership_grade";
		$price = LDMW_Options_Model::get_instance()->$fee_option;

		if ( empty( $customer_membership_grade ) || empty( $price ) )
			return $base_price;

		return $price;
	}

	/**
	 * Set up the membership.
	 *
	 * @param $transaction_id int
	 */
	public function setup_membership( $transaction_id ) {
		$transaction = it_exchange_get_transaction( $transaction_id );

		$products = $transaction->cart_details->products;

		foreach ( $products as $product ) {
			if ( $product['product_id'] == LDMW_Options_Model::get_instance()->membership_product )
				$membership_product = it_exchange_get_product( $product['product_id'] );
		}

		if ( ! isset( $membership_product ) )
			return;

		$customer_id = $transaction->customer_id;

		do_action( 'ldmw_setup_membership', $customer_id, $transaction, $membership_product );
	}

	/**
	 * @param $db_base_price string
	 * @param $product array
	 * @param $format bool
	 *
	 * @return float|string
	 */
	public function apply_late_fee( $db_base_price, $product, $format = true ) {

		$db_base_price = IT_Exchange_Prorated_Subscriptions::remove_currency_format( $db_base_price );
		$db_product = it_exchange_get_product( $product['product_id'] );

		if ( $db_product === false || $product['product_id'] != get_user_meta( get_current_user_id(), 'ldmw_membership_renewal_invoice_post_id', true ) )
			return ( $format === true ) ? it_exchange_format_price( $db_base_price ) : $db_base_price;

		$renewal_date = LDMW_Options_Model::get_instance()->membership_start_date;
		$renewal_date = new DateTime( "@$renewal_date" );
		$diff = ( new DateTime() )->diff( $renewal_date );
		$days = $diff->days;

		if ( $diff->invert == 1 )
			$days = - $days;

		if ( $days > 0 )
			return ( $format === true ) ? it_exchange_format_price( $db_base_price ) : $db_base_price;

		$db_base_price += (float) LDMW_Options_Model::get_instance()->membership_late_fee;

		return ( $format === true ) ? it_exchange_format_price( $db_base_price ) : $db_base_price;
	}

	/**
	 * Convert the payment renewal invoice to a paid membership for the next year.
	 *
	 * @param $transaction_id int
	 */
	public function convert_paid_invoice_to_membership( $transaction_id ) {
		$transaction = it_exchange_get_transaction( $transaction_id );

		$products = $transaction->cart_details->products;

		foreach ( $products as $product ) {
			if ( $product['product_id'] == get_user_meta( $transaction->customer_id, 'ldmw_membership_renewal_invoice_post_id', true ) )
				$invoice_product = $product;
		}

		if ( ! isset( $invoice_product ) )
			return;

		update_user_meta( $transaction->customer_id, 'ldmw_next_membership_paid', true );

		$time = $transaction->get_transaction_meta( 'subscription_expires_' . LDMW_Options_Model::get_instance()->membership_product );

		$new_time = new DateTime( $time );
		$new_time->add( new DateInterval( "P1Y" ) );

		$transaction->update_transaction_meta( 'subscription_expires_' . LDMW_Options_Model::get_instance()->membership_product, $new_time->getTimestamp() );

		update_user_meta( $transaction->customer_id, 'ldmw_membership_status', 'current' );

		$notification = new LDMW_Notifications_Flatco_Notification( $transaction->customer_id, 'AAS',
		  'Please login to your account and update your Communication Preferences, Areas of Competence and Fields of Interest.
		  This information is displayed in the membership section of our website and should be updated annually.'
		);
		$notification->save();
	}

	/**
	 * Generate an invoice for the product renewal.
	 *
	 * @param $user_id int
	 *
	 * @return int
	 */
	public static function generate_renewal_invoice( $user_id ) {
		$user = get_user_by( 'id', $user_id );
		$application_product = LDMW_Exchange_Util::get_membership_product();
		$base_price = it_exchange_get_product_feature( $application_product->ID, 'base-price', array( 'user_id' => $user_id ) );

		$post_id = wp_insert_post( array(
			'post_content'   => '',
			'post_title'     => 'Membership Renewal ' . date( "Y" ) . "-" . ( ( (int) date( "y" ) ) + 1 ),
			'post_name'      => "membership-renewal-" . date( "Y" ) . "-{$user_id}",
			'post_author'    => 1,
			'post_type'      => 'it_exchange_prod',
			'post_status'    => 'publish',
			'comment_status' => 'closed'
		  )
		);

		$purchase_message = "Please ensure you update your profile and areas of competence and fields of interest by logging in to your account at ";
		$purchase_message .= it_exchange_get_page_url( 'account' );
		$purchase_message .= " This information shows in our online Member Directory so it is important your profile is up to date.";

		update_post_meta( $post_id, '_it_exchange_product_type', 'invoices-product-type' );
		update_post_meta( $post_id, '_it-exchange-visibility', 'hidden' );
		it_exchange_update_product_feature( $post_id, 'base-price', $base_price );
		it_exchange_update_product_feature( $post_id, 'purchase-message', str_replace( "\r\n", "", $purchase_message ) );

		$notes = "<b>Membership Grade:</b> " . LDMW_Users_Util::membership_grade_slug_to_name( LDMW_Users_Util::get_membership_grade( $user_id ), true );
		$notes .= "<br>";
		$notes .= "<b>Membership Division:</b> " . LDMW_Users_Util::membership_division_slug_to_name( LDMW_Users_Util::get_membership_division( $user_id ) );

		$invoice_data = array(
		  'client'       => $user_id, // user_id
		  'date_issued'  => time(), // timestamp of when invoice was issued
		  'company'      => get_user_meta( $user_id, 'ldmw_company_name', true ), // company name
		  'number'       => date( "Y" ) . $user_id, // the invoice number
		  'emails'       => $user->user_email, // email the invoice is associated with
		  'po'           => '', // po number
		  'terms'        => 'net-90', // slug of the terms
		  'notes'        => $notes, // extra notes for an invoice
		  'use_password' => false, // unknown atm
		  'password'     => '', // what the password would be
		  'status'       => 0, // invoice status
		  'hash'         => it_exchange_create_unique_hash(), // hash checked on save and such
		);

		update_post_meta( $post_id, '_it-exchange-invoice-data', $invoice_data );

		update_post_meta( $post_id, 'ldmw_invoice_is_renewal_invoice', true );

		return $post_id;
	}

	/**
	 * Display the company_name element at checkout.
	 *
	 * @param $elements array
	 *
	 * @return array
	 */
	public function force_company_name_at_checkout( $elements ) {
		array_unshift( $elements, 'company_name' );

		return $elements;
	}

	/**
	 * Display the company_name element at checkout, when checking out with the super widget.
	 *
	 * @param $elements array
	 *
	 * @return array
	 */
	public function force_company_name_at_super_widget_checkout( $elements ) {
		array_unshift( $elements, 'company_name' );

		return $elements;
	}

	/**
	 * Change the blue button label on the Stripe checkout to Pay Now, from Checkout.
	 *
	 * @param $text
	 *
	 * @return mixed
	 */
	public function change_stripe_checkout_button_text( $text ) {
		return str_replace( '"Checkout"', '"Pay Now"', $text );
	}

	/**
	 * Remove shipping address from checkout pages.
	 *
	 * @param $parts array
	 *
	 * @return array
	 */
	public function remove_shipping_address( $parts ) {
		foreach ( $parts as $key => $part ) {
			if ( $part == 'shipping-address' )
				unset( $parts[$key] );
		}

		return $parts;
	}

	/**
	 * @param $pagename
	 * @param $page
	 *
	 * @return string
	 */
	public function modify_page_names( $pagename, $page ) {
		if ( $page == 'purchases' )
			$pagename = 'Transactions';

		return $pagename;
	}

	/**
	 * Remove the view available content link from receipt emails.
	 *
	 * @param $name string
	 * @param $product array
	 *
	 * @return string
	 */
	public function remove_view_available_content( $name, $product ) {
		if ( $product['product_id'] == LDMW_Options_Model::get_instance()->application_form_product ) {
			remove_filter( 'it_exchange_email_notification_order_table_product_name', 'it_exchange_membership_addon_email_notification_order_table_product_name' );
		}

		return $name;
	}

	/**
	 * Get the states for a passed country
	 *
	 * @return array
	 */
	public function country_to_states() {
		$country = trim( $_POST['country'] );

		$states = it_exchange_get_data_set( 'states', array( 'country' => $country ) );

		if ( is_array( $states ) )
			echo json_encode( $states );
		else
			echo false;

		die();
	}

	/**
	 * @param $billing_address array
	 *
	 * @return array
	 */
	public function set_default_component_of_billing_address( $billing_address ) {
		if ( ! is_array( $billing_address ) ) {
			$billing_address = array(
			  'first_name'   => '',
			  'last_name'    => '',
			  'company_name' => '',
			  'email'        => '',
			  'address1'     => '',
			  'address2'     => '',
			  'city'         => '',
			  'state'        => '',
			  'country'      => '',
			  'zip'          => '',
			  'phone'        => ''
			);
		}

		return $billing_address;
	}
}