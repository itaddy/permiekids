<?php
/**
 *
 * @package LDMW
 * @subpackage Templates/Exchange
 * @since 1.0
 */
?>

<tr>
	<td><?php it_exchange( 'transaction', 'product-attribute', array( 'attribute' => 'product_subtotal' ) ); ?></td>
	<td><?php it_exchange( 'transaction', 'product-attribute', array( 'attribute' => 'title' ) ); ?></td>
	<td><?php it_exchange( 'transaction', 'date' ); ?></td>
	<td><?php it_exchange( 'transaction', 'status', array( 'label' => '%s' ) ); ?></td>
	<td><?php echo it_exchange_get_transaction_method_name( $GLOBALS['it_exchange']['transaction'] ); ?></td>
	<td><?php it_exchange( 'transaction', 'order-number', array( 'label' => '%s' ) ); ?></td>
	<td><a href="#" class="resend-receipt btn rounded btn-flat btn-default btn-small" style="margin:0" data-nonce="<?php echo wp_create_nonce( 'ldmw-resend-receipt-' . $GLOBALS['it_exchange']['transaction']->ID ); ?>" data-id="<?php echo $GLOBALS['it_exchange']['transaction']->ID; ?>">Email Receipt</a>
		<a href="<?php echo it_exchange_get_transaction_confirmation_url( $GLOBALS['it_exchange']['transaction']->ID ); ?>" class="btn rounded btn-flat btn-default btn-small" style="margin: 0">View</a>
	</td>
</tr>