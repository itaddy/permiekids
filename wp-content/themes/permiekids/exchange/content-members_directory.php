<?php
/**
 * Default template for displaying the an exchange
 * member's directory page.
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

<?php do_action( 'it_exchange_content_members_directory_before_wrap' ); ?>
<div id="it-exchange-account" class="it-exchange-wrap it-exchange-account">
<?php do_action( 'it_exchange_content_members_directory_begin_wrap' ); ?>
<?php it_exchange_get_template_part( 'messages' ); ?>
<?php it_exchange( 'customer', 'menu' ); ?>
<?php do_action( 'it_exchange_content_members_directory_before_form' ); ?>
<?php it_exchange_get_template_part( 'content-members-directory/loops/content' ); ?>
<?php do_action( 'it_exchange_content_members_directory_after_form' ); ?>
<?php do_action( 'it_exchange_content_members_directory_end_wrap' ); ?>
</div>
<?php do_action( 'it_exchange_content_members_directory_after_wrap' ); ?>