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
	<p class="top-description"><?php _e( 'Add-ons are features that you can add or remove depending on your needs. Selling your stuff should only be as complicated as you need it to be. Visit the Get More tab to see what else Exchange can do.', 'it-l10n-ithemes-exchange' ); ?></p>

	<?php $this->print_add_ons_page_tabs(); ?>
	<?php do_action( 'it_exchange_add_ons_page_top' ); ?>

	<?php
		$tab = ! empty( $_REQUEST['tab'] ) ? $_REQUEST['tab'] : 'all';
		$propack = false;
		
		switch ( $tab ) {
			case 'enabled':
				$addons = it_exchange_get_enabled_addons( array( 'show_required' => false ) );
				break;

			case 'disabled':
				$addons = it_exchange_get_disabled_addons( array( 'show_required' => false ) );
				break;

			case 'all':
			default:
				$addons = it_exchange_get_addons( array( 'show_required' => false ) );
				$propack = true;
				break;
		}
	?>
	<div class="add-ons-wrapper">
		<?php if ( ! empty( $addons ) ) : ?>
			<?php if ( $propack ) { ?>
				<div class="add-on-block pro-pack">
					<h3><?php _e( 'Get the Exchange Pro Pack', 'it-l10n-ithemes-exchange' ); ?><span><a href="#">Learn More</a></span></h3>
					<p><?php _e( 'The Pro Pack gets you access to iThemes Exchange add-ons that unlock so much more that Exchange can do … like Membership, Invoices, Variants, Easy U.S. Sales Taxes, Recurring Payments, Stripe and MailChimp. With the Pro Pack, you get access to all our iThemes-built Exchange Add-ons plus any more we build in the next year (which will be a lot).', 'it-l10n-ithemes-exchange' ); ?></p>
					<a class="btn-pro" href="http://ithemes.com/exchange/pro-pack/" target="_blank"><?php _e( 'Get all our add-ons for $197', 'it-l10n-ithemes-exchange' ); ?></a><a href="#" class="dismiss">No thanks</a>
				</div>
			<?php } ?>

			<?php $default_icon = ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/images/exchange50px.png' ); ?>

			<?php foreach( (array) $addons as $addon ) : ?>

				<?php

				if ( !empty( $addon['options']['tag'] ) && 'required' === $addon['options']['tag'] )
					continue;

				?>

				<?php $icon = empty( $addon['options']['icon'] ) ? $default_icon : $addon['options']['icon']; ?>
				<div class="add-on-block">
					<div class="add-on-icon">
						<div class="image-wrapper">
							<img src="<?php echo $icon; ?>" alt="" />
						</div>
					</div>
					<div class="add-on-info">
						<h4><?php echo $addon['name']; ?></h4>
						<span class="add-on-author">by <a href="<?php echo $addon['author_url']; ?>"><?php echo $addon['author']; ?></a></span>
                        <?php if ( !empty( $addon['options']['tag'] ) ) { ?>
						<span class="add-on-tag"><?php echo $addon['options']['tag']; ?></span>
                        <?php } ?>
						<p class="add-on-description"><?php echo $addon['description']; ?></p>
					</div>
					<div class="add-on-actions">
						<?php if ( it_exchange_is_addon_enabled( $addon['slug'] ) ) : ?>
							<?php $url = it_exchange_is_core_addon( $addon['slug'] ) ? wp_nonce_url( get_site_url() . '/wp-admin/admin.php?page=it-exchange-addons&it-exchange-disable-addon=' . $addon['slug'] . '&tab=' . $tab, 'exchange-disable-add-on' ) : admin_url() . 'plugins.php'; ?>
							<div class="add-on-enabled"><a href="<?php echo $url; ?>" data-text-disable="&times;&nbsp; Disable" data-text-enabled="&#x2714;&nbsp; Enabled">&#x2714;&nbsp; Enabled</a></div>
						<?php else : ?>
							<div class="add-on-disabled"><a href="<?php echo wp_nonce_url( get_site_url() . '/wp-admin/admin.php?page=it-exchange-addons&it-exchange-enable-addon=' . $addon['slug'] . '&tab=' . $tab, 'exchange-enable-add-on' ); ?>" data-text-enable="&#x2714;&nbsp; Enable" data-text-disabled="&times;&nbsp; Disabled">&times;&nbsp; Disabled</a></div>
						<?php endif; ?>

						<?php if ( ! empty( $addon['options']['settings-callback'] ) && is_callable( $addon['options']['settings-callback'] ) ) : ?>
							<div class="add-on-settings"><a href="<?php echo admin_url( 'admin.php?page=it-exchange-addons&add-on-settings=' . $addon['slug'] ); ?>">S</a></div>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		<?php else : ?>
			<?php
			if ( 'all' == $tab ) {
				$vars = array(
					'target' => add_query_arg( 'tab', 'get-more' ),
					'text'   => __( 'No add-ons installed. How\'d you do that?', 'it-l10n-ithemes-exchange' ),
					'link'   => __( 'Go get some now', 'it-l10n-ithemes-exchange' )
				);
			} else if ( 'enabled' == $tab ) {
				$vars = array(
					'target' => add_query_arg( 'tab', 'disabled' ),
					'text'   => __( 'No add-ons are currently enabled', 'it-l10n-ithemes-exchange' ),
					'link'   => __( 'Go enable some now', 'it-l10n-ithemes-exchange' )
				);
			} else if ( 'disabled' == $tab ) {
				$vars = array(
					'target' => add_query_arg( 'tab', 'get-more' ),
					'text'   => __( 'All add-ons are currently enabled', 'it-l10n-ithemes-exchange' ),
					'link'   => __( 'Go get more', 'it-l10n-ithemes-exchange' )
				);
			}
			?>
			<div class="addons-achievement">
				<h2><?php echo $vars['text']; ?></h2>
				<p>
					<a href="<?php echo $vars['target']; ?>" class="it-exchange-button"><?php echo $vars['link'] ?></a>
				</p>
			</div>
		<?php endif; ?>
	</div>
</div>
