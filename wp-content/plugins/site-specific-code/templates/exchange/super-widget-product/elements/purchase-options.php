<?php
/**
 *
 * @package LDMW
 * @subpackage Templates/Exchange
 * @since 1.0
 */
?>
<?php do_action( 'it_exchange_super_widget_product_before_purchase_options_element' ); ?>
	<div class="purchase-options">
	<?php do_action( 'it_exchange_super_widget_product_begin_purchase_options_element' ); ?>
	<?php $options = array( 'add-to-cart-edit-quantity' => false, 'buy-now-edit-quantity' => false );
	global $post;

	if ( $post->ID == LDMW_Options_Model::get_instance()->application_form_product )
		$options['buy-now-label'] = "Pay Application Fee";

	?>
	<?php it_exchange( 'product', 'purchase-options', $options ); ?>
	<?php do_action( 'it_exchange_super_widget_product_end_purchase_options_element' ); ?>
</div>
<?php do_action( 'it_exchange_super_widget_product_after_purchase_options_element' ); ?>