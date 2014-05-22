<?php
/**
 * Loads APIs for iThemes Exchange
 *
 * @package IT_Exchange
 * @since 0.2.0
*/

if ( is_admin() ) {
	// Admin only
}

// Frontend only
if ( ! is_admin() || ( is_admin() && defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
	include( $this->_plugin_path . 'api/theme.php' );
}

// Contains functions for registering / retreiving Add-ons, Add-on categories, and Add-on sets
include( $this->_plugin_path . 'api/addons.php' );

// Product Features
include( $this->_plugin_path . 'api/product-features.php' );

// Register and retreive form actions
include( $this->_plugin_path . 'api/misc.php' );

// Product Type Add-ons
include( $this->_plugin_path . 'api/products.php' );

// Transaction Add-ons
include( $this->_plugin_path . 'api/transactions.php' );

// Sessions
include( $this->_plugin_path . 'api/sessions.php' );

// Storage
include( $this->_plugin_path . 'api/storage.php' );

// Shopping Cart API
include( $this->_plugin_path . 'api/cart.php' );

// Customers
include( $this->_plugin_path . 'api/customers.php' );

// Messages
include( $this->_plugin_path . 'api/messages.php' );

// Coupons
include( $this->_plugin_path . 'api/coupons.php' );

// Downloads
include( $this->_plugin_path . 'api/downloads.php' );

// Pages
include( $this->_plugin_path . 'api/pages.php' );

// Template Parts
include( $this->_plugin_path . 'api/template-parts.php' );

// Data Sets
include( $this->_plugin_path . 'api/data-sets.php' );

// Purchase Dialogs
include( $this->_plugin_path . 'api/purchase-dialogs.php' );

// Shipping API
include( $this->_plugin_path . 'api/shipping.php' );
include( $this->_plugin_path . 'api/shipping-features.php' );
