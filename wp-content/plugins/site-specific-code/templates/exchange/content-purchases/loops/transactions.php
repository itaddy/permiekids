<?php
/**
 *
 * @package LDMW
 * @subpackage Templates/Exchange
 * @since 1.0
 */
?>
<div class="table-responsive">
<table class="table">
	<thead><?php it_exchange_get_template_part( 'content-purchases/elements/table-ends' ); ?></thead>
	<tfoot><?php it_exchange_get_template_part( 'content-purchases/elements/table-ends' ); ?></tfoot>
	<tbody>
	<?php while ( it_exchange( 'transactions', 'exist' ) ) : ?>
		<?php if ( it_exchange( 'transaction', 'has-products' ) ) : ?>
			<?php it_exchange_get_template_part( 'content-purchases/loops/products' ); ?>
		<?php else : ?>
			<?php it_exchange_get_template_part( 'content-purchases/elements/no-transaction-products-found' ); ?>
		<?php endif; ?>
	<?php endwhile; ?>
	</tbody>
</table>
</div>