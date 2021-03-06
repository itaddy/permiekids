<?php
/**
 * This is the default template part for the
 * first name element in the super-widget-registration template
 * part.
 *
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file
 * to the exchange/super-widget-registration/elements/
 * directory located in your theme.
*/
?>

<?php do_action( 'it_exchange_super_widget_registration_before_twitter_elements' ); ?>
<div class="twitter">
    <?php it_exchange( 'permiekids_registration', 'twitter' ); ?>
</div>
<?php do_action( 'it_exchange_super_widget_registration_after_twitter_elements' ); ?>
