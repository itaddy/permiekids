<?php
/**
 * This file contains the contents of the Help/Support page
 * @since 0.4.14
 * @package IT_Exchange
*/
?>
<div class="wrap help-wrap">
	<?php ITUtility::screen_icon( 'it-exchange' );  ?>
	<h2><?php _e( 'Help and Resources', 'it-l10n-ithemes-exchange' ); ?></h2>

	<p class="top-description"><?php printf( __( 'We\'ve built %s to simplify ecommerce for WordPress. However, ecommerce is not always easy, so we\'ve taken the time to create some resources to help you get started.', 'it-l10n-ithemes-exchange' ), '<a title="iThemes Exchange" href="http://ithemes.com/exchange/" target="_blank">iThemes Exchange</a>' ); ?></p>

	<div class="help-section-wrap clearfix">
		<h3><?php _e( 'Quick Links', 'it-l10n-ithemes-exchange' ); ?></h3>
		<div class="help-action exchange-wizard help-tip" title="<?php _e( 'This is a link back to the Quick Setup page that opens after Exchange is installed. This page walks through the necessary information and settings needed to set up your store.', 'it-l10n-ithemes-exchange' ); ?>">
			<img src="<?php echo ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/images/e32.png' ); ?>" />
			<p><?php _e( 'Go back to the Exchange Quick Setup Wizard.', 'it-l10n-ithemes-exchange' ); ?></p>
			<p><a href="<?php echo get_admin_url( NULL, 'admin.php?page=it-exchange-setup' ); ?>" target="_self"><?php _e( 'Open the Wizard', 'it-l10n-ithemes-exchange' ); ?></a></p>
		</div>
	</div>

	<div class="help-section-wrap clearfix">
		<h3><?php _e( 'Resources', 'it-l10n-ithemes-exchange' ); ?></h3>
		<div class="help-action exchange-tutorials" title="">
			<img src="<?php echo ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/images/e32.png' ); ?>" />
			<p><?php _e( 'Short video tutorials to help you become an Exchange expert.', 'it-l10n-ithemes-exchange' ); ?></p>
			<p><a href="http://ithemes.com/tutorials/ithemes-exchange" target="_blank"><?php _e( 'Checkout some tutorials', 'it-l10n-ithemes-exchange' ); ?></a></p>
		</div>
		<div class="help-action exchange-codex" title="">
			<img src="<?php echo ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/images/e32.png' ); ?>" />
			<p><?php _e( 'Read through the Exchange documentation.', 'it-l10n-ithemes-exchange' ); ?></p>
			<p><a href="http://ithemes.com/codex/page/Exchange" target="_blank"><?php _e( 'Dig Deep into Exchange', 'it-l10n-ithemes-exchange' ); ?></a></p>
		</div>
	</div>

	<div class="help-section-wrap clearfix">
		<h3><?php _e( 'Support', 'it-l10n-ithemes-exchange' ); ?></h3>
		<div class="help-action exchange-support" title="">
			<img src="<?php echo ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/images/e32.png' ); ?>" />
			<p><?php _e( 'Get free help on some basics of Exchange.', 'it-l10n-ithemes-exchange' ); ?></p>
			<p><a href="http://ithemes.com/forum/forum/207-exchange-ecommerce-plugin/" target="_blank"><?php _e( 'Get Basic Help', 'it-l10n-ithemes-exchange' ); ?></a></p>
		</div>
		<div class="help-action exchange-paid-support" title="">
			<img src="<?php echo ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/images/e32.png' ); ?>" />
			<p><?php _e( 'Get premium and priority support for Exchange.', 'it-l10n-ithemes-exchange' ); ?></p>
			<p><a href="http://ithemes.com/exchange/support/" target="_blank"><?php _e( 'Get Premuim Support', 'it-l10n-ithemes-exchange' ); ?></a></p>
		</div>
	</div>

	<div class="help-section-wrap clearfix">
		<h3><?php _e( 'Report &amp Request', 'it-l10n-ithemes-exchange' ); ?></h3>
		<div class="help-action exchange-report" title="">
			<img src="<?php echo ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/images/e32.png' ); ?>" />
			<p><?php _e( 'Help us fix an issue that you have found in Exchange.', 'it-l10n-ithemes-exchange' ); ?></p>
			<p><a href="http://ithemes.com/exchange/bugs/" target="_blank"><?php _e( 'Report a Bug or Problem', 'it-l10n-ithemes-exchange' ); ?></a></p>
		</div>
		<div class="help-action exchange-request" title="">
			<img src="<?php echo ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/images/e32.png' ); ?>" />
			<p><?php _e( 'Help us improve Exchange for everyone.', 'it-l10n-ithemes-exchange' ); ?></p>
			<p><a href="http://ithemes.com/exchange/feature-request/" target="_blank"><?php _e( 'Request a Feature', 'it-l10n-ithemes-exchange' ); ?></a></p>
		</div>
	</div>
</div>
