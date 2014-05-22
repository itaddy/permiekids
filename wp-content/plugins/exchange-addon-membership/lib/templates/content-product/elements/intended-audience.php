<?php
/**
 * The default template part for the membership's intended audience in
 * the content-product template part's content elements
 *
 * @since 1.0.7 
 * @version 1.0.0
 * @package IT_Exchange_Addon_Membership
 * 
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file
 * to the exchange/content-product/elements/ directory
 * located in your theme.
*/
?>

<?php do_action( 'it_exchange_membership_addon_content_product_before_intended_audience_element' ); ?>
<div class="it-exchange-membership-intended-audience it-exchange-advanced-item">
    <p><?php it_exchange( 'membership-product', 'intended-audience' ); ?></p>
</div>
<?php do_action( 'it_exchange_membership_addon_content_product_after_intended_audience_element' ); ?>
