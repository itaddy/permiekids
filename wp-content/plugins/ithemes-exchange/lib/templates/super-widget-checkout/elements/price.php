<?php
/**
 * This is the default template for the
 * super-widget-checkout price element.
 *
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file
 * to the exchange/super-widget-checkout/elements directory
 * located in your theme.
*/
?>

<?php do_action( 'it_exchange_super_widget_checkout_before_price_element' ); ?>
<?php if ( it_exchange( 'cart-item', 'get-quantity', array( 'format' => 'var_value' ) ) > 1 ) : ?>
	<?php it_exchange( 'cart-item', 'price' ); ?> &times; <?php it_exchange( 'cart-item', 'quantity', array( 'format' => 'var_value' ) ); ?> &#61; <?php it_exchange( 'cart', 'subtotal' ); ?>
 <?php else : ?>
	 <?php it_exchange( 'cart-item', 'price' ); ?>
<?php endif;  ?>
<?php do_action( 'it_exchange_super_widget_checkout_after_price_element' ); ?>
