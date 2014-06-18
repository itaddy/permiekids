<?php
/**
 * Default template for displaying the an exchange
 * customer's purchased papers.
 *
 * @since 0.4.0
 * @version 1.0.0
 * @link http://ithemes.com/codex/page/Exchange_Template_Updates
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, simply copy over this
 * file's content to the exchange/ directory located
 * in your theme.
 */
?>

<?php do_action( 'it_exchange_content_papers_before_wrap' ); ?>

	<div id="it-exchange-papers" class="it-exchange-wrap it-exchange-account">
	<?php do_action( 'it_exchange_content_papers_begin_wrap' ); ?>
		<?php it_exchange_get_template_part( 'messages' ); ?>
		<?php it_exchange( 'customer', 'menu' ); ?>

		<?php if ( it_exchange( 'papers', 'has-articles' ) ) : ?>

		<div class="table-responsive">
	        <table class="table">
		        <thead>
		            <tr>
			            <th>Paper Name</th>
			            <th>Author</th>
			            <th>Published</th>
			            <th></th>
			            <th></th>
		            </tr>
		        </thead>
		        <tfoot>
		            <tr>
			            <th>Paper Name</th>
			            <th>Author</th>
			            <th>Published</th>
			            <th></th>
			            <th></th>
		            </tr>
		        </tfoot>
		        <tbody>
				<?php while ( it_exchange( 'papers', 'articles' ) ) : ?>

					<?php it_exchange_get_template_part( 'content-papers/loops/fields' ); ?>

				<?php endwhile; ?>
		        </tbody>
		    </table>
		</div>
		<?php endif; ?>

		<?php do_action( 'it_exchange_content_papers_end_wrap' ); ?>
</div>

<?php do_action( 'it_exchange_content_papers_after_wrap' ); ?>