<?php
/**
 * This file contains the notice for the Wizard setup
 * @package IT_Exchange
 * @since 0.4.0
*/
// Just adding internal CSS rule here since it won't be around long.
?>
<div id="it-exchange-wizard-nag" class="it-exchange-nag">
	<?php
	$wizard_link    = add_query_arg( array( 'page' => 'it-exchange-setup' ), admin_url( 'admin.php' ) );
	$wizard_dismiss = add_query_arg( array( 'it_exchange_settings-dismiss-wizard-nag' => true ) );
	echo __( 'iThemes Exchange is now installed.', 'it-l10n-ithemes-exchange' ) . ' <a class="btn" href="' . $wizard_link . '">' . __( 'Go to Quick Setup', 'it-l10n-ithemes-exchange' ) . '</a>';
	?>
	<a class="dismiss btn" href="<?php esc_attr_e( $wizard_dismiss ); ?>">&times;</a>
</div>
<script type="text/javascript">
	jQuery( document ).ready( function() {
		if ( jQuery( '.wrap > h2' ).length == '1' ) {
			jQuery("#it-exchange-wizard-nag").insertAfter( '.wrap > h2' ).addClass( 'after-h2' );
		}
	});
</script>
