<?php
/**
 * This is the default template part for the
 * welcome element in the content-account template
 * part.
 *
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file
 * to the exchange/content-account/elements/
 * directory located in your theme.
*/
?>

<?php do_action( 'it_exchange_content_account_before_members_directory_element' ); ?>
<div class="it-exchange-members-directory">
	<?php it_exchange( 'permiekids_registration', 'members' ); ?>
</div>
<?php do_action( 'it_exchange_content_account_after_members_directory_element' ); ?>
