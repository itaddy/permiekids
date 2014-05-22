<?php
/**
 * This file prints the content added to the user-edit.php WordPress page
 *
 * @since 0.4.0
 * @package IT_Exchange
*/

$headings = array(
	__( 'Products', 'it-l10n-ithemes-exchange' ),
	__( 'Transaction', 'it-l10n-ithemes-exchange' ),
);

$product_list = array();
foreach( (array) it_exchange_get_customer_products( $user_id ) as $product ) {
	// Build Product Link
	$product_id   = $product['product_id'];
	$product_url  = get_admin_url() . '/post.php?action=edit&post=' . esc_attr( $product_id );
	$product_name = it_exchange_get_transaction_product_feature( $product, 'product_name' );
	$product_link = '<a href="' . $product_url . '">' . $product_name . '</a>';

	// Build Transaction Link
	$transaction_id     = it_exchange_get_transaction_product_feature( $product, 'transaction_id' );
	$transaction_url    = get_admin_url() . '/post.php?action=edit&post=' . esc_attr( $transaction_id );
	$transaction_number = it_exchange_get_transaction_order_number( $transaction_id );
	$transaction_link   = '<a href="' . $transaction_url . '">' . $transaction_number . '</a>';

	$transaction_downloads = it_exchange_get_transaction_download_hash_index( $transaction_id );

	if ( !empty( $transaction_downloads[$product_id] ) ) {

		$downloads = $transaction_downloads[$product_id];
		$product_link .= __( '<span class="details-toggle hide-if-no-js">&#61;</span><span class="details-toggle hide-if-no-js">&#61;</span>', 'it-l10n-ithemes-exchange' );

	} else {

		$downloads = array();

	}

	$product_list[] = array(
		'product_link'     => $product_link,
		'transaction_link' => $transaction_link,
		'downloads'        => $downloads,
	);
}

do_action( 'it-exchange-before-admin-user-products' );
?>
<div class="user-edit-block <?php echo $tab; ?>-user-edit-block">
	<div class="heading-row block-row">
		<?php $column = 0; ?>
		<?php foreach ( $headings as $heading ) : ?>
			<?php $column++ ?>
			<div class="heading-column block-column block-column-<?php echo $column; ?>">
				<p class="heading"><?php echo $heading; ?></p>
			</div>
		<?php endforeach; ?>
	</div>

	<?php foreach ( (array) $product_list as $product_id => $data ) : ?>
		<div class="item-row block-row">
			<div class="item-column block-column block-column-1">
				<p class="item"><?php echo $data['product_link']; ?></p>
			</div>
			<div class="item-column block-column block-column-2">
				<p class="item"><?php echo $data['transaction_link']; ?></p>
			</div>

			<?php if ( ! empty( $data['downloads'] ) ) : ?>
				<div class="item-column block-column-full hide-if-js">
					<h3><?php _e( 'Downloads', 'it-l10n-ithemes-exchange' ); ?></h3>
					<div class="downloads-wrapper">
						<?php foreach ( $data['downloads'] as $download_id => $download_hashes ) : ?>
							<?php $download_info = it_exchange_get_download_info( $download_id ); ?>

							<?php if ( ! empty( $download_info['source'] ) ) : ?>
								<h4><?php echo get_the_title( $download_id ) ?><span><?php echo end( ( explode( '/', $download_info['source'] ) ) ) ?></span></h4>
								<ul class="download-hashes">
									<?php foreach ( $download_hashes as $download_hash ) : ?>
										<?php $download_data = it_exchange_get_download_data( $download_id, $download_hash ); ?>
										<?php if ( ! empty( $download_data ) ) : ?>
											<li>
												<span class="hash"><?php echo $download_data['hash'] ?></span>

												<?php if ( ! empty( $download_data['download_limit'] ) ) : ?>
													<span class="limit"><?php echo sprintf( __( '%s of %s downloads remaining', 'it-l10n-ithemes-exchange' ), $download_data['download_limit'] - $download_data['downloads'], $download_data['download_limit'] ); ?></span>
												<?php else : ?>
													<span class="limit"><?php _e( 'Unlimited Downloads', 'it-l10n-ithemes-exchange' );  ?>
												<?php endif; ?>

												<?php if ( $expires = it_exchange_get_download_expiration_date( $download_data ) ) : ?>
													<span class="expires"><?php echo sprintf( __( 'Expires %s', 'it-l10n-ithemes-exchange' ), $expires ); ?></span>
												<?php else : ?>
													<span class="expires"><?php _e( 'Never Expires', 'it-l10n-ithemes-exchange' );  ?>
												<?php endif; ?>
											</li>
										<?php endif; ?>
									<?php endforeach; ?>
								</ul>
							<?php endif ?>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
	<?php endforeach; ?>
</div>
<?php
do_action( 'it-exchange-after-admin-user-products' );