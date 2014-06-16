<?php
/**
 * This is the default template part for the
 * fields loop in the content-papers
 * template part.
 *
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file
 * to the exchange/content-papers/loops/ directory
 * located in your theme.
 */
?>

<?php do_action( 'it_exchange_content_papers_before_fields_loop' ); ?>


<?php do_action( 'it_exchange_content_papers_begin_fields_loop' ); ?>
<tr>
	<?php foreach ( it_exchange_get_template_part_elements( 'content_papers', 'fields', array( 'name', 'author', 'publish-date', 'view', 'download' ) ) as $field ) : ?>
		<?php
		/**
		 * Theme and add-on devs should add code to this loop by
		 * hooking into it_exchange_get_template_part_elements filter
		 * and adding the appropriate template file to their theme or add-on
		 */
		it_exchange_get_template_part( 'content-papers/elements/' . $field );
		?>
	<?php endforeach; ?>
</tr>
<?php do_action( 'it_exchange_content_papers_end_fields_loop' ); ?>

<?php do_action( 'it_exchange_content_papers_after_fields_loop' ); ?>
