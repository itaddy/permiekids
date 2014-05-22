<?php
/**
 * This file contains functions for interacting with the addon
 * @since 1.0.0
 * @package IT_Exchange
*/

/**
 * Get taxes for cart
 *
 * @since 1.0.0
 *
*/
function it_exchange_addon_get_simple_taxes_for_cart( $format_price=true ) {

	// Grab the tax rate
	$options  = it_exchange_get_option( 'addon_taxes_simple' );
	$tax_rate = empty( $options['default-tax-rate'] ) ? 1 : (float) $options['default-tax-rate'];
	$process_after_discounts = ! empty( $options['calculate-after-discounts'] );

	// Grab the cart subtotal or the cart total depending on the process after discounts option
	$cart_total = it_exchange_get_cart_subtotal( false );

	if ( $process_after_discounts )
		$cart_total -= it_exchange_get_total_coupons_discount( 'cart', array( 'format_price' => false ) );

	if ( 0 > $cart_total )
		$cart_total = 0;

	// Calculate taxes
	$cart_taxes = $cart_total * ( $tax_rate / 100 );

	$taxes = apply_filters( 'it_exchange_addon_get_simple_taxes_for_cart', $cart_taxes );
	if ( $format_price )
		$taxes = it_exchange_format_price( $taxes );
	return $taxes;
}

/**
 * Get labels from settings
 *
 * @since 1.2.1
 *
 * @param string $label which label do you want to return? tax or taxes
 * @return string
*/
function it_exchange_add_simple_taxes_get_label( $label ) {
	$settings = it_exchange_get_option( 'addon_taxes_simple' );
	if ( 'tax' == $label )
		$label = 'tax-label-singular';
	if ( 'taxes' == $label )
		$label = 'tax-label-plural';

	return empty( $settings[$label] ) ? '' : esc_attr( $settings[$label] );
}
