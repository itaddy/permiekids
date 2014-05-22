<?php
/**
 * This addon gives customers the option to checkout as a guest
 *
 * When checking out as a guest they won't be asked to create an account but one will be created for them
 * without their knowledge. This is to maintain accurate records for the store owner. The guest will not
 * be aware of the account and will not have access to it.
 *
 * @package IT_Exchange
 * @since 1.6.0
*/

/**
 * This file includes our functions / hooks for adding a settings page and saving those settings.
 * You can roll your own settings page if you want, but our API will create the little gear for you
 * on the Exchange add-ons page.
*/
include( dirname( __FILE__ ) . '/lib/settings.php' );

/**
 * This file includes our functions that filter WP or Exchange via WP Hooks
*/
include( dirname( __FILE__ ) . '/lib/filters.php' );

/**
 * This file includes our helper functions for the guest checkout functionality
*/
include( dirname( __FILE__ ) . '/lib/functions.php' );

/**
 * This file includes our template related functions / hooks for template parts
*/
include( dirname( __FILE__ ) . '/lib/template-functions.php' );
