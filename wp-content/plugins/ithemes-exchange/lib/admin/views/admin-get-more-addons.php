<?php
/**
 * This file prints the add-ons page in the Admin
 *
 * @since 0.3.6
 * @package IT_Exchange
*/
?>
<div id="it-exchange-add-ons-wrap" class="wrap">
	<?php ITUtility::screen_icon( 'it-exchange-add-ons' );  ?>

	<h2>Add-ons</h2>
	<p class="top-description"><?php _e( 'Add-ons are features that you can add or remove depending on your needs. Selling your stuff should only be as complicated as you need it to be. If you have already purchased additional Exchange add-ons, please upload and activate them through the WordPress plugins menu.', 'it-l10n-ithemes-exchange' ); ?></p>

	<?php
		$this->print_add_ons_page_tabs();
		do_action( 'it_exchange_add_ons_page_top' );

		$addons = it_exchange_get_more_addons();
		$addons = it_exchange_featured_addons_on_top( $addons );

		$default_icon = ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/images/exchange50px.png' );

		$class = '';
	?>

	<div class="add-ons-wrapper">
		<div class="add-on-block pro-pack open get-more-tab">
			<h3><?php _e( 'Get the Exchange Pro Pack', 'it-l10n-ithemes-exchange' ); ?></h3>
			<p><?php _e( 'The Pro Pack gets you access to iThemes Exchange add-ons that unlock so much more that Exchange can do … like Membership, Invoices, Variants, Easy U.S. Sales Taxes, Recurring Payments, Stripe and MailChimp. With the Pro Pack, you get access to all our iThemes-built Exchange Add-ons plus any more we build in the next year (which will be a lot).', 'it-l10n-ithemes-exchange' ); ?></p>
			<a class="btn-pro" href="http://ithemes.com/exchange/pro-pack/" target="_blank"><?php _e( 'Get all our add-ons for $197', 'it-l10n-ithemes-exchange' ); ?></a>
		</div>
		<?php if ( ! empty( $addons ) ) : ?>
			<?php
				$count = 0;
				foreach( (array) $addons as $addon ) : ?>
				<?php if ( ! it_exchange_is_addon_registered( $addon['slug'] ) ) : ?>
					<?php
						if ( $addon['featured'] )
							$class .= ' featured';

						if ( $addon['new'] )
							$class .= ' new';

						if ( $addon['sale'] )
							$class .= ' sale';
					?>
					<?php $icon = empty( $addon['icon'] ) ? $default_icon : $addon['icon']; ?>
					<div class="add-on-block <?php echo $class; ?>">
						<div class="add-on-icon">
							<div class="image-wrapper">
								<img src="<?php echo $icon; ?>" alt="" />
							</div>
						</div>
						<div class="add-on-info">
							<h4><?php echo $addon['name']; ?></h4>
							<span class="add-on-author"><?php _e( 'by', 'it-l10n-ithemes-exchange' ); ?> <a href="<?php echo $addon['author_url']; ?>"><?php echo $addon['author']; ?></a></span>
							<p class="add-on-description"><?php echo $addon['description']; ?></p>
						</div>
						<div class="add-on-actions">
							<?php if ( it_exchange_is_addon_registered( $addon['slug'] ) ) : ?>
								<div class="add-on-installed"><?php _e( 'Installed', 'it-l10n-ithemes-exchange' ); ?></div>
							<?php else : ?>
								<div class="add-on-price">
									<span class="regular-price"><?php echo $addon['price']; ?></span>
									<?php if ( $addon['sale'] ) : ?>
										<span class="sale-price"><?php echo $addon['sale']; ?></span>
									<?php endif; ?>
								</div>
								<div class="add-on-buy-now">
									<a href="<?php echo $addon['addon_url']; ?>"><?php _e( 'Buy Now', 'it-l10n-ithemes-exchange' )  ?></a>
								</div>
							<?php  endif; ?>
						</div>
					</div>
				<?php $count++; ?>
				<?php endif; ?>
			<?php endforeach; ?>

			<?php if ( 0 === $count ) : ?>
				<div class="addons-achievement">
					<div class="achievement-notice">
						<span><?php _e( 'ACHIEVEMENT UNLOCKED', 'it-l10n-ithemes-exchange' ) ?></span>
						<span><?php _e( 'Acquired all Exchange Add-ons', 'it-l10n-ithemes-exchange' ) ?></span>
					</div>
					<h2><?php echo sprintf( __( 'You have all %s currently has to offer!', 'it-l10n-ithemes-exchange' ), 'iThemes Exchange' ); ?></h2>
					<p><?php _e( 'Got an idea for an add-on that would make life easier?', 'it-l10n-ithemes-exchange' ); ?></p>
					<a class="it-exchange-button" target="_blank" href="http://ithemes.com/contact"><?php _e( 'Send us a message', 'it-l10n-ithemes-exchange' ); ?></a>

					<div class="email-signup">
                    	<?php
						if ( ! empty( $_REQUEST['optin-email'] ) ) {

							IT_Exchange_Admin::mail_chimp_signup( $_REQUEST['optin-email'] );

						}
						?>
						<form action="" method="post" accept-charset="utf-8">
                            <p><label for="optin-email"><?php _e( 'Sign up to be notified via email when new Add-ons and updates are released.', 'it-l10n-ithemes-exchange' ); ?></label></p>
							<input type="text" name="optin-email" value="<?php echo get_bloginfo( 'admin_email' ); ?>">
							<input class="it-exchange-button" type="submit" value="Subscribe">
						</form>
					</div>
				</div>
			<?php endif; ?>

		<?php else : ?>
			<div class="no-addons-found">
				<p><?php echo sprintf( __( 'Looks like there\'s a problem loading available add-ons. Go to %s to check out other available add-ons.', 'it-l10n-ithemes-exchange' ), '<a href="http://ithemes.com/exchange">iThemes Exchange</a>' ); ?></p>
			</div>
		<?php endif; ?>
	</div>
</div>
