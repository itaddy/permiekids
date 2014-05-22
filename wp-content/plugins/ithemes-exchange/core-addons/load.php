<?php
/**
 * Registers all add-ons shipped with iThemes Exchange
 *
 * @since 0.2.0
 * @uses apply_filters()
 * @uses it_exchange_register_add_on()
 * @return void
*/
function it_exchange_register_core_addons() {

	// An array of add-ons provided by iThemes Exchange
	$add_ons = array(
		// Offline Payments
		'offline-payments' => array(
			'name'              => __( 'Offline Payments', 'it-l10n-ithemes-exchange' ),
			'description'       => __( 'Process transactions offline via check or cash.', 'it-l10n-ithemes-exchange' ),
			'author'            => 'iThemes',
			'author_url'        => 'http://ithemes.com',
			'file'              => dirname( __FILE__ ) . '/transaction-methods/offline-payments/init.php',
			'category'          => 'transaction-methods',
			'tag'               => 'core',
			'settings-callback' => 'it_exchange_offline_payments_settings_callback',
		),
		//For situations when the Cart Total is 0 (free), we still want to record the transaction!
		'zero-sum-checkout' => array(
			'name'              => __( 'Zero Sum Checkout', 'it-l10n-ithemes-exchange' ),
			'description'       => __( 'Used for processing 0 sum checkout (free).', 'it-l10n-ithemes-exchange' ),
			'author'            => 'iThemes',
			'author_url'        => 'http://ithemes.com',
			'file'              => dirname( __FILE__ ) . '/transaction-methods/zero-sum-checkout/init.php',
			'category'          => 'transaction-methods',
			'tag'               => 'required',
		),
		// PayPal Standard Transaction Method
		'paypal-standard' => array(
			'name'              => __( 'PayPal Standard - Basic', 'it-l10n-ithemes-exchange' ),
			'description'       => __( 'This is the simple and fast version to get PayPal setup for your store. You might use this version just to get your store going, but we highly suggest you switch to the PayPal Standard Secure option.', 'it-l10n-ithemes-exchange' ),
			'author'            => 'iThemes',
			'author_url'        => 'http://ithemes.com',
			'icon'              => ITUtility::get_url_from_file( dirname( __FILE__ ) . '/transaction-methods/paypal-standard/images/paypal50px.png' ),
			'wizard-icon'       => ITUtility::get_url_from_file( dirname( __FILE__ ) . '/transaction-methods/paypal-standard/images/wizard-paypal.png' ),
			'file'              => dirname( __FILE__ ) . '/transaction-methods/paypal-standard/init.php',
			'category'          => 'transaction-methods',
			'tag'               => 'core',
			'settings-callback' => 'it_exchange_paypal_standard_settings_callback',
		),
		// PayPal Standard Transaction Method
		'paypal-standard-secure' => array(
			'name'              => __( 'PayPal Standard - Secure', 'it-l10n-ithemes-exchange' ),
			'description'       => __( 'Although this PayPal version for iThemes Exchange takes more effort and time, it is well worth it for the security options for your store.', 'it-l10n-ithemes-exchange' ),
			'author'            => 'iThemes',
			'author_url'        => 'http://ithemes.com',
			'icon'              => ITUtility::get_url_from_file( dirname( __FILE__ ) . '/transaction-methods/paypal-standard-secure/images/paypal50px.png' ),
			'wizard-icon'       => ITUtility::get_url_from_file( dirname( __FILE__ ) . '/transaction-methods/paypal-standard-secure/images/wizard-paypal.png' ),
			'file'              => dirname( __FILE__ ) . '/transaction-methods/paypal-standard-secure/init.php',
			'category'          => 'transaction-methods',
			'tag'               => 'core',
			'settings-callback' => 'it_exchange_paypal_standard_secure_settings_callback',
		),
		// Digital Download Product Types
		'digital-downloads-product-type' => array(
			'name'              => __( 'Digital Downloads', 'it-l10n-ithemes-exchange' ),
			'description'       => __( 'This adds a product type for distributing digital downloads through iThemes Exchange.', 'it-l10n-ithemes-exchange' ),
			'author'            => 'iThemes',
			'author_url'        => 'http://ithemes.com',
			'wizard-icon'       => ITUtility::get_url_from_file( dirname( __FILE__ ) . '/product-types/digital-downloads/images/wizard-downloads.png' ),
			'file'              => dirname( __FILE__ ) . '/product-types/digital-downloads/init.php',
			'category'          => 'product-type',
			'tag'               => 'core',
			'labels'            => array(
				'singular_name' => __( 'Digital Download', 'it-l10n-ithemes-exchange' ),
			),
			'supports'          => apply_filters( 'it_exchange_register_digital_downloads_default_features', array(
				'inventory' => false,
			) ),
			'settings-callback' => 'it_exchange_digital_downloads_settings_callback',
		),
		// Simple Product Types
		'simple-product-type' => array(
			'name'        => __( 'Simple Products', 'it-l10n-ithemes-exchange' ),
			'description' => __( 'This is a basic product type for selling simple items.', 'it-l10n-ithemes-exchange' ),
			'author'      => 'iThemes',
			'author_url'  => 'http://ithemes.com',
			'file'        => dirname( __FILE__ ) . '/product-types/simple-products/init.php',
			'category'    => 'product-type',
			'tag'         => 'core',
			'labels'      => array(
				'singular_name' => __( 'Simple Product', 'it-l10n-ithemes-exchange' ),
			),
		),
		// Physical Product Type
		'physical-product-type' => array(
			'name'        => __( 'Physical Products', 'it-l10n-ithemes-exchange' ),
			'description' => __( 'Products you can put your hands on. Things you might want to ship.', 'it-l10n-ithemes-exchange' ),
			'author'      => 'iThemes',
			'author_url'  => 'http://ithemes.com',
			'wizard-icon' => ITUtility::get_url_from_file( dirname( __FILE__ ) . '/product-types/physical-products/images/wizard-physical.png' ),
			'file'        => dirname( __FILE__ ) . '/product-types/physical-products/init.php',
			'category'    => 'product-type',
			'tag'         => 'core',
			'labels'      => array(
				'singular_name' => __( 'Physical Product', 'it-l10n-ithemes-exchange' ),
			),
		),
		// Product Type admin Metabox
		'switch-product-type-metabox' => array(
			'name'        => __( 'Switch Product Types', 'it-l10n-ithemes-exchange' ),
			'description' => __( 'Gives Store Owners the ability to change a Product Type after creation of the Product via the Advanced options', 'it-l10n-ithemes-exchange' ),
			'author'      => 'iThemes',
			'author_url'  => 'http://ithemes.com',
			'file'        => dirname( __FILE__ ) . '/admin/product-type-metabox/init.php',
			'tag'         => 'core',
			'options'     => array( 'category' => 'admin' ),
		),
		// Multi item cart
		'multi-item-cart-option' => array(
			'name'              => __( 'Multi-item Cart', 'it-l10n-ithemes-exchange' ),
			'description'       => __( 'Enabling this add-on allows your customers to purchase multiple products with one transaction. There are no settings for this add-on.', 'it-l10n-ithemes-exchange' ),
			'author'            => 'iThemes',
			'author_url'        => 'http://ithemes.com',
			'file'              => dirname( __FILE__ ) . '/admin/multi-item-cart/init.php',
			'category'          => 'admin',
			'tag'               => 'core',
			'settings-callback' => 'it_exchange_multi_item_cart_settings_callback',
			'supports'          => apply_filters( 'it_exchange_register_multi_item_cart_default_features', array(
			) ),
		),
		// Guest Checkout
		'guest-checkout' => array(
			'name'              => __( 'Guest Checkout', 'it-l10n-ithemes-exchange' ),
			'description'       => __( 'Enabling this add-on gives customers the ability to checkout as a guest, without registering', 'it-l10n-ithemes-exchange' ),
			'author'            => 'iThemes',
			'author_url'        => 'http://ithemes.com',
			'file'              => dirname( __FILE__ ) . '/admin/guest-checkout/init.php',
			'category'          => 'admin',
			'tag'               => 'core',
			'settings-callback' => 'it_exchange_guest_checkout_settings_callback',
			'supports'          => apply_filters( 'it_exchange_register_guest_checkout_default_features', array(
			) ),
		),
		// Page Templates
		'page-template' => array(
			'name'              => __( 'WordPress Page Templates', 'it-l10n-ithemes-exchange' ),
			'description'       => __( 'Enable the WordPress Page Templates meta box for products', 'it-l10n-ithemes-exchange' ),
			'author'            => 'iThemes',
			'author_url'        => 'http://ithemes.com',
			'file'              => dirname( __FILE__ ) . '/product-features/page-templates/init.php',
			'category'          => 'admin',
			'tag'               => 'core',
			'supports'          => apply_filters( 'it_exchange_register_page_template_default_features', array(
			) ),
		),
		// Billing Address Purchase Requirement
		'billing-address-purchase-requirement' => array(
			'name'        => __( 'Billing Address', 'it-l10n-ithemes-exchange' ),
			'description' => __( 'Enabling this add-on allows you to collect a billing address at checkout. There are no settings for this add-on.', 'it-l10n-ithemes-exchange' ),
			'author'      => 'iThemes',
			'author_url'  => 'http://ithemes.com',
			'file'        => dirname( __FILE__ ) . '/admin/billing-address/init.php',
			'category'    => 'admin',
			'tag'         => 'core',
		),
		// Basic Reporting Dashboard Widget
		'basic-reporting' => array(
			'name'        => __( 'Basic Reporting Dashboard Widget', 'it-l10n-ithemes-exchange' ),
			'description' => __( 'Adds a widget to the Admin dashboard to give basic sales statistics.', 'it-l10n-ithemes-exchange' ),
			'author'      => 'iThemes',
			'author_url'  => 'http://ithemes.com',
			'file'        => dirname( __FILE__ ) . '/admin/basic-reporting/init.php',
			'category'    => 'admin',
			'tag'         => 'core',
			'supports'    => apply_filters( 'it_exchange_register_basic_reporting_default_features', array(
			) ),
		),
		// Basic Coupons
		'it-basic-coupons' => array(
			'name'        => __( 'Coupons', 'it-l10n-ithemes-exchange' ),
			'description' => __( 'This add-on allows you to generate coupons that apply to all products in your store.', 'it-l10n-ithemes-exchange' ),
			'author'      => 'iThemes',
			'author_url'  => 'http://ithemes.com',
			'file'        => dirname( __FILE__ ) . '/coupons/basic-coupons/init.php',
			'category'    => 'coupons',
			'tag'         => 'core',
			'supports'    => apply_filters( 'it_exchange_register_basic_coupons_default_features', array(
			) ),
		),
		// Category Taxonomy
		'taxonomy-type-category' => array(
			'name'        => __( 'Product Categories', 'it-l10n-ithemes-exchange' ),
			'description' => __( 'This adds a category taxonomy for all products in iThemes Exchange.', 'it-l10n-ithemes-exchange' ),
			'author'      => 'iThemes',
			'author_url'  => 'http://ithemes.com',
			'file'        => dirname( __FILE__ ) . '/product-features/categories/init.php',
			'category'    => 'taxonomy-type',
			'tag'         => 'core',
			'labels'      => array(
				'singular_name' => __( 'Product Category', 'it-l10n-ithemes-exchange' ),
			),
		),
		// Tag Taxonomy
		'taxonomy-type-tag' => array(
			'name'        => __( 'Product Tags', 'it-l10n-ithemes-exchange' ),
			'description' => __( 'This adds a tag taxonomy for all products in iThemes Exchange.', 'it-l10n-ithemes-exchange' ),
			'author'      => 'iThemes',
			'author_url'  => 'http://ithemes.com',
			'file'        => dirname( __FILE__ ) . '/product-features/tags/init.php',
			'category'    => 'taxonomy-type',
			'tag'         => 'core',
			'labels'      => array(
				'singular_name' => __( 'Product Tag', 'it-l10n-ithemes-exchange' ),
			),
		),
		// Simple Taxes
		'taxes-simple' => array(
			'name'              => __( 'Simple Taxes', 'it-l10n-ithemes-exchange' ),
			'description'       => __( 'This gives the admin ability to apply a default tax rate to all sales.', 'it-l10n-ithemes-exchange' ),
			'author'            => 'iThemes',
			'author_url'        => 'http://ithemes.com',
			'file'              => dirname( __FILE__ ) . '/taxes/taxes-simple/init.php',
			'category'          => 'taxes',
			'tag'               => 'core',
			'settings-callback' => 'it_exchange_taxes_simple_settings_callback',
		),
		// Duplicate Products
		'duplicate-products' => array(
			'name'              => __( 'Duplicate Products', 'it-l10n-ithemes-exchange' ),
			'description'       => __( 'This gives the admin the ability to duplicate an existing product.', 'it-l10n-ithemes-exchange' ),
			'author'            => 'iThemes',
			'author_url'        => 'http://ithemes.com',
			'file'              => dirname( __FILE__ ) . '/product-features/duplicate-products/init.php',
			'category'          => 'other',
			'tag'               => 'core',
			'labels'      => array(
				'singular_name' => __( 'Duplicate', 'it-l10n-ithemes-exchange' ),
			),
		),
		'simple-shipping'        => array(
			'name'              => __( 'Simple Shipping', 'it-l10n-ithemes-exchange' ),
			'description'       => __( 'Flat rate and free shipping for your physcial products', 'it-l10n-ithemes-exchange' ),
			'author'            => 'iThemes',
			'author_url'        => 'http://ithemes.com',
			'file'              => dirname( __FILE__ ) . '/shipping/simple-shipping/init.php',
			'category'          => 'shipping',
			'tag'               => 'core',
			'settings-callback' => 'it_exchange_simple_shipping_settings_callback',
		),
	);
	$add_ons = apply_filters( 'it_exchange_core_addons', $add_ons );

	// Loop through add-ons and register each one individually
	foreach( (array) $add_ons as $slug => $params )
		it_exchange_register_addon( $slug, $params );
}
add_action( 'it_exchange_register_addons', 'it_exchange_register_core_addons' );

/**
 * Register's Core iThemes Exchange Add-on Categories
 *
 * @since 0.2.0
 * @uses it_exchange_register_add_on_category()
 * @return void
*/
function it_exchange_register_core_addon_categories() {

	// An array of our core add-on categories
	$cats = array(
		'product-type' => array(
			'name'        => __( 'Product Type', 'it-l10n-ithemes-exchange' ),
			'description' => __( 'Add-ons responsible for the differing types of products available in iThemes Exchange.', 'it-l10n-ithemes-exchange' ),
			'options'     => array(
			),
		),
		'transaction-methods' => array(
			'name'        => __( 'Transaction Methods', 'it-l10n-ithemes-exchange' ),
			'description' => __( 'Add-ons that create transactions. eg: Stripe, PayPal.', 'it-l10n-ithemes-exchange' ),
			'options'     => array(
				'supports' => apply_filters( 'it_exchange_register_transaction_method_supports', array(
					'title' => array(
						'key'       => 'post_title',
						'componant' => 'post_type_support',
						'default'   => false,
					),
					'transaction_status' => array(
						'key'       => '_it_exchange_transaction_status',
						'componant' => 'post_meta',
						'options'   => array(
							'pending'    => _x( 'Pending', 'Transaction Status', 'it-l10n-ithemes-exchange' ),
							'authorized' => _x( 'Authorized', 'Transaction Status', 'it-l10n-ithemes-exchange' ),
							'paid'       => _x( 'Paid', 'Transaction Status', 'it-l10n-ithemes-exchange' ),
							'refunded'   => _x( 'Refunded', 'Transaction Status', 'it-l10n-ithemes-exchange' ),
							'voided'     => _x( 'Voided', 'Transaction Status', 'it-l10n-ithemes-exchange' ),
						),
						'default'   => 'pending',
					)
				) ),
			),
		),
		'admin' => array(
			'name'        => __( 'Admin Add-ons', 'it-l10n-ithemes-exchange' ),
			'description' => __( 'Add-ons that create general purpose admin functionality. eg: Reports, Export.', 'it-l10n-ithemes-exchange' ),
			'options'     => array(),
		),
		'coupons' => array(
			'name'        => __( 'Coupon Add-ons', 'it-l10n-ithemes-exchange' ),
			'description' => __( 'Add-ons that create coupons for your customers.', 'it-l10n-ithemes-exchange' ),
			'options'     => array(),
		),
		'taxonomy-type' => array(
			'name'        => __( 'Taxonomy Add-ons', 'it-l10n-ithemes-exchange' ),
			'description' => __( 'Add-ons that create new taxonomies specifically for Exchange products.', 'it-l10n-ithemes-exchange' ),
			'options'     => array(),
		),
		'email' => array(
			'name'        => __( 'Email', 'it-l10n-ithemes-exchange' ),
			'description' => __( 'Add-ons that help store owners manage their email.', 'it-l10n-ithemes-exchange' ),
			'options'     => array(),
		),
		'other' => array(
			'name'        => __( 'Other', 'it-l10n-ithemes-exchange' ),
			'description' => __( 'Add-ons that don\'t fit in any other add-on category.', 'it-l10n-ithemes-exchange' ),
			'options'     => array(),
		),
	);
	$cats = apply_filters( 'it_exchange_core_addon_categories', $cats );

	// Loop through categories and register each one individually
	foreach( (array) $cats as $slug => $params ) {
		$name        = empty( $params['name'] )        ? false   : $params['name'];
		$description = empty( $params['description'] ) ? ''      : $params['description'];
		$options     = empty( $params['options'] )     ? array() : (array) $params['options'];

		it_exchange_register_addon_category( $slug, $name, $description, $options );
	}
}
add_action( 'it_libraries_loaded', 'it_exchange_register_core_addon_categories' );
