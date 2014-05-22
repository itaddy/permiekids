<?php
/**
 * This file loads the iThemes Exchange Framework
 *
 * @since 0.2.0
 * @package IT_Exchange
*/

// IT Classes
require( 'classes/load.php' );

// IT Fonts
require( $this->_plugin_path . 'lib/icon-fonts/load.php' );

// Admin Functionality
require( $this->_plugin_path . 'lib/admin/class.admin.php' );

// Product Post Type
require( $this->_plugin_path . 'lib/products/class.products-post-type.php' );

// Product Object
require( $this->_plugin_path . 'lib/products/class.product.php' );

// Product Features
require( $this->_plugin_path . 'lib/product-features/load.php' );

// Transaction Post Type
require( $this->_plugin_path . 'lib/transactions/class.transactions-post-type.php' );

// Transaction Object
require( $this->_plugin_path . 'lib/transactions/class.transaction.php' );

// Template Functions
require( $this->_plugin_path . 'lib/functions/template-functions.php' );

// Other Functions
require( $this->_plugin_path . 'lib/functions/functions.php' );

// Integrations
require( $this->_plugin_path . 'lib/integrations/builder/init.php' );

// Customer Class
require( $this->_plugin_path . 'lib/customers/class.customer.php' );

// Pages
require( $this->_plugin_path . 'lib/pages/class.pages.php' );

// Super Widget
require( $this->_plugin_path . 'lib/super-widget/class.super-widget.php' );

// Coupons
require( $this->_plugin_path . 'lib/coupons/class.coupons-post-type.php' );
require( $this->_plugin_path . 'lib/coupons/class.coupon.php' );

// Email Notifications
require( $this->_plugin_path . 'lib/email-notifications/class.email-notifications.php' );

// Shipping
require( $this->_plugin_path . 'lib/shipping/class.shipping.php' );

// Deprecated Features
require( $this->_plugin_path . 'lib/deprecated/init.php' );

// Sessions
if ( ! is_admin() ) {
	require( $this->_plugin_path . 'lib/cart/class.cart.php' );
} else {
	require( $this->_plugin_path . 'lib/pages/class.nav-menus.php' );
	require( $this->_plugin_path . 'lib/admin/class-settings-form.php' );
}
