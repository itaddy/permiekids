<?php
/**
 * iThemes Exchange Recurring Payments Add-on
 * load theme API functions
 * @package exchange-addon-recurring-payments
 * @since 1.0.0
*/

if ( is_admin() ) {
	// Admin only
} else {
	// Frontend only
	include( 'theme.php' );
}