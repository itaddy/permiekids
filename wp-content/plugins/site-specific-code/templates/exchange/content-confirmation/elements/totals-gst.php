<?php
/**
 *
 * @package LDWM
 * @subpackage Templates/Exchange
 * @since 1.0
 */
?>
<div class="it-exchange-confirmation-totals-title it-exchange-table-column">
	<div class="it-exchange-table-column-inner">
		GST
	</div>
</div>
<div class="it-exchange-confirmation-totals-amount it-exchange-table-column">
	<div class="it-exchange-table-column-inner">
		<?php echo it_exchange_format_price( LDMW_Exchange_Util::calculate_gst( it_exchange( 'transaction', 'get-subtotal', array( 'format_currency' => false ) ) ) ); ?>
	</div>
</div>