<?php
/**
 * Loads APIs for iThemes Exchange - Recurring Payments Add-on
 *
 * @package exchange-addon-recurring-payments
 * @since 1.0.0
*/

if ( is_admin() ) {
	// Admin only
} else {
	// Frontend only
	include( 'theme.php' );
}

// Transaction Add-ons
include( 'transactions.php' );