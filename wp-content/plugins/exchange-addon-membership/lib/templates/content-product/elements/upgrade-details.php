<?php
/**
 * The default template part for the membership's upgrade details in
 * the content-product template part's content elements
 *
 * @since CHANGEME
 * @version 1.0.0
 * @package IT_Exchange_Addon_Membership
 * 
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file
 * to the exchange/content-product/elements/ directory
 * located in your theme.
*/
?>

<?php if ( it_exchange( 'membership-product', 'has-upgrade-details' ) ) : ?>
	<?php do_action( 'it_exchange_membership_addon_content_product_before_upgrade-details_element' ); ?>
	<div class="it-exchange-membership-upgrade-details it-exchange-advanced-item">
	    <p><?php it_exchange( 'membership-product', 'upgrade-details' ); ?></p>
	</div>
	<?php do_action( 'it_exchange_membership_addon_content_product_after_upgrade-details_element' ); ?>
<?php endif; ?>
