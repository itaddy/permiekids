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

<?php do_action( 'it_exchange_super_widget_registration_before_contact_information_elements' ); ?>
<div style="clear:both;"></div>
<div class="contact_information">
    <?php it_exchange( 'permiekids_registration', 'contact_information' ); ?>
</div>
<?php do_action( 'it_exchange_super_widget_registration_after_contact_information_elements' ); ?>
