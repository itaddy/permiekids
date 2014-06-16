<?php

/**
 *
 * @package LDMW
 * @subpackage Exchange
 * @since 1.0
 */
class LDMW_Exchange_Util {

	/**
	 * Get products array of id => post title.
	 *
	 * @param $args array
	 *
	 * @return array
	 */
	public static function get_products_array( $args = array() ) {
		if ( false === ( $array = get_transient( "ldmw_exchange_get_products_array_all" ) ) ) {
			$products = get_posts( wp_parse_args( $args, array(
				  'post_type'   => 'it_exchange_prod',
				  'numberposts' => - 1
				)
			  )
			);
			$array = array();

			foreach ( $products as $product )
				$array[$product->ID] = $product->post_title;

			set_transient( "ldmw_exchange_get_products_array_all", $array, 86400 );
		}

		return $array;
	}

	/**
	 * Get the main membership product.
	 *
	 * @return IT_Exchange_Product
	 */
	public static function get_membership_product() {
		$id = LDMW_Options_Model::get_instance()->membership_product;

		return it_exchange_get_product( $id );
	}

	/**
	 * Get the product that is required to submit an application.
	 *
	 * @return IT_Exchange_Product
	 */
	public static function get_application_product() {
		$id = LDMW_Options_Model::get_instance()->application_form_product;

		return it_exchange_get_product( $id );
	}

	/**
	 * Get the transactions for all purchased of the application form product.
	 *
	 * @param $user_id int
	 *
	 * @return array
	 */
	public static function get_application_product_transactions( $user_id ) {
		$product = self::get_application_product();
		$transactions = it_exchange_get_customer_transactions( $user_id );
		$result = array();

		foreach ( $transactions as $transaction ) {
			$products = $transaction->cart_details->products;

			foreach ( $products as $cart_product ) {
				if ( $cart_product['product_id'] == $product->ID )
					$result[] = $transaction;
			}
		}

		return $result;
	}

	/**
	 * Return the most recent transaction
	 *
	 * @param $user_id int
	 *
	 * @return IT_Exchange_Transaction
	 */
	public static function get_application_product_recent_transaction( $user_id ) {
		$transactions = self::get_application_product_transactions( $user_id );

		return array_shift( $transactions );
	}

	/**
	 * Calculate GST for a product's price.
	 *
	 * @param $price float non-currency formated price
	 *
	 * @return float
	 */
	public static function calculate_gst( $price ) {
		$gst_percent = LDMW_Options_Model::get_instance()->gst_percentage;

		if ( empty( $gst_percent ) )
			return $price;

		$gst_percent /= (float) 100.0;

		return (float) $price * $gst_percent;
	}
}