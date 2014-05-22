<?php
/**
 * This is the default template part for the
 * apply_coupon element in the content-cart template
 * part.
 *
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file
 * to the exchange/content-cart/elements/ directory
 * located in your theme.
*/
?>

<?php do_action( 'it_exchange_content_cart_before_apply_coupon_element' ); ?>
<?php if ( it_exchange( 'coupons', 'supported', array( 'type' => 'cart' ) ) && it_exchange( 'coupons', 'accepting', array( 'type' => 'cart' ) ) ) : ?>
	<?php do_action( 'it_exchange_content_cart_begin_apply_coupon_element' ); ?>
	<div class="it-exchange-cart-apply-coupons">
		<?php it_exchange( 'coupons', 'apply', array( 'type' => 'cart' ) ); ?>
		<?php it_exchange( 'cart', 'update', array( 'label' => __( 'Apply Coupon', 'it-l10n-ithemes-exchange' ) ) ); ?>
	</div>
	<?php do_action( 'it_exchange_content_cart_end_apply_coupon_element' ); ?>
<?php endif; ?>
<?php do_action( 'it_exchange_content_cart_after_apply_coupon_element' ); ?>