<?php
/**
 *
 * @package LDMW
 * @subpackage Templates/Exchange
 * @since 1.0
 */
?>

<?php while ( it_exchange( 'transaction', 'products' ) ) : ?>
	<?php it_exchange_get_template_part( 'content-purchases/elements/product' ); ?>
<?php endwhile; ?>