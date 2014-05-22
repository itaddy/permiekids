<?php
/**
 * This is basically a fancy setting masquerading as an addon.
 * @package IT_Exchange
 * @since 1.3.0
*/
// No settings. This is either enabled or disabled.

/**
 * Enables multi item carts
 * @since 1.3.0
*/
add_filter( 'it_exchange_billing_address_purchase_requirement_enabled', '__return_true' );
