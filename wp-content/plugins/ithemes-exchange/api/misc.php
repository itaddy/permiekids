<?php
/**
 * These are hooks that add-ons should use for form actions
 * @since 0.3.7
 * @package IT_Exchange
*/

/**
 * Generate a unique hash, with microtime and uniqid this should always be unique
 *
 * @since 0.4.0
 *
 * @return string the hash
*/
function it_exchange_create_unique_hash() {
	$hash = str_replace( '.', '', microtime( true ) . uniqid() ); //Remove the period from microtime, cause it's ugly
	return apply_filters( 'it_exchange_generate_unique_hash', $hash );
}

/**
 * Pass a PHP date format string to this function to return its jQuery datepicker equivalent
 *
 * @since 0.4.16
 * @param string $date_format PHP Date Format
 * @return string jQuery datePicker Format
*/
function it_exchange_php_date_format_to_jquery_datepicker_format( $date_format ) {

	//http://us2.php.net/manual/en/function.date.php
	//http://api.jqueryui.com/datepicker/#utility-formatDate
	$php_format = array(
		//day
		'/d/', //Day of the month, 2 digits with leading zeros
		'/D/', //A textual representation of a day, three letters
		'/j/', //Day of the month without leading zeros
		'/l/', //A full textual representation of the day of the week
		//'/N/', //ISO-8601 numeric representation of the day of the week (added in PHP 5.1.0)
		//'/S/', //English ordinal suffix for the day of the month, 2 characters
		//'/w/', //Numeric representation of the day of the week
		'/z/', //The day of the year (starting from 0)

		//week
		//'/W/', //ISO-8601 week number of year, weeks starting on Monday (added in PHP 4.1.0)

		//month
		'/F/', //A full textual representation of a month, such as January or March
		'/m/', //Numeric representation of a month, with leading zeros
		'/M/', //A short textual representation of a month, three letters
		'/n/', //numeric month no leading zeros
		//'t/', //Number of days in the given month

		//year
		//'/L/', //Whether it's a leap year
		//'/o/', //ISO-8601 year number. This has the same value as Y, except that if the ISO week number (W) belongs to the previous or next year, that year is used instead. (added in PHP 5.1.0)
		'/Y/', //A full numeric representation of a year, 4 digits
		'/y/', //A two digit representation of a year
	);

	$datepicker_format = array(
		//day
		'dd', //day of month (two digit)
		'D',  //day name short
		'd',  //day of month (no leading zero)
		'DD', //day name long
		//'',   //N - Equivalent does not exist in datePicker
		//'',   //S - Equivalent does not exist in datePicker
		//'',   //w - Equivalent does not exist in datePicker
		'z' => 'o',  //The day of the year (starting from 0)

		//week
		//'',   //W - Equivalent does not exist in datePicker

		//month
		'MM', //month name long
		'mm', //month of year (two digit)
		'M',  //month name short
		'm',  //month of year (no leading zero)
		//'',   //t - Equivalent does not exist in datePicker

		//year
		//'',   //L - Equivalent does not exist in datePicker
		//'',   //o - Equivalent does not exist in datePicker
		'yy', //year (four digit)
		'y',  //month name long
	);

	return preg_replace( $php_format, $datepicker_format, preg_quote( $date_format ) );
}

/**
 * Returns an integer value of the price passed
 *
 * @since 0.4.16
 * @param string|int|float price to convert to database integer
 * @return string|int converted price
*/
function it_exchange_convert_to_database_number( $price ) {
	$settings = it_exchange_get_option( 'settings_general' );
	$sep = $settings['currency-decimals-separator'];

	$price = trim( $price );

	if ( strstr( $price, $sep ) )
		$price = preg_replace("/[^0-9]*/", '', $price );
	else //if we don't find a decimal separator, we want to multiply by 100 for future decimal operations
		$price = preg_replace("/[^0-9]*/", '', $price ) * 100;

	return $price;
}

/**
 * Returns a float value of the price passed from database
 *
 * @since 0.4.16
 * @param string|int price from database integer
 * @return float converted price
*/
function it_exchange_convert_from_database_number( $price ) {
	return number_format( $price /= 100, 2, '.', '' );
}

/**
 * Returns a field name used in links and forms
 *
 * @since 0.4.0
 * @param string $var var being requested
 * @return string var used in links / forms for different actions
*/
function it_exchange_get_field_name( $var ) {
	$field_names = it_exchange_get_field_names();
	$field_name = empty( $field_names[$var] ) ? false : $field_names[$var];
	return apply_filters( 'it_exchange_get_field_name', $field_name, $var );
}

/**
 * Returns an array of all field names registered with iThemes Exchange
 *
 * @since 0.4.0
 * @return array
*/
function it_exchange_get_field_names() {
	// required field names
	$required = array(
		'add_product_to_cart'      => 'it-exchange-add-product-to-cart',
		'buy_now'                  => 'it-exchange-buy-now',
		'remove_product_from_cart' => 'it-exchange-remove-product-from-cart',
		'update_cart_action'       => 'it-exchange-update-cart-request',
		'empty_cart'               => 'it-exchange-empty-cart',
		'continue_shopping'        => 'it-exchange-continue-shopping',
		'proceed_to_checkout'      => 'it-exchange-proceed-to-checkout',
		'view_cart'                => 'it-exchange-view-cart',
		'purchase_cart'            => 'it-exchange-purchase-cart',
		'alert_message'            => 'it-exchange-messages',
		'error_message'            => 'it-exchange-errors',
		'transaction_id'           => 'it-exchange-transaction-id',
		'transaction_method'       => 'it-exchange-transaction-method',
		'sw_cart_focus'            => 'ite-sw-cart-focus',
		'sw_ajax_call'             => 'it-exchange-sw-ajax',
		'sw_ajax_action'           => 'sw-action',
		'sw_ajax_product'          => 'sw-product',
		'sw_ajax_quantity'         => 'sw-quantity',
	);
	//We don't want users to modify the core vars, but we should let them add new ones.
	return apply_filters( 'it_exchange_get_field_names', array_merge( $required, apply_filters( 'it_exchange_default_field_names', array() ) ) );
}

/**
 * Grabs the current URL, removes all registerd exchange query_args from it
 *
 * Exempts args in first paramater
 * Cleans additional args in second paramater
 *
 * @since 0.4.0
 *
 * @param array $exempt optional array of query args not to clean
 * @param array $additional opitonal array of params to clean even if not found in register params
 * @return string
*/
function it_exchange_clean_query_args( $exempt=array(), $additional=array() ) {
	// Get registered
	$registered = array_values( (array) it_exchange_get_field_names() );
	$registered = array_merge( $registered, (array) array_values( $additional ) );

	// Additional args
	$registered[] = '_wpnonce';
	$registered[] = apply_filters( 'it_exchange_purchase_product_nonce_var' , '_wpnonce' );
	$registered[] = apply_filters( 'it_exchange_cart_action_nonce_var' , '_wpnonce' );
	$registered[] = apply_filters( 'it_exchange_remove_product_from_cart_nonce_var' , '_wpnonce' );
	$registered[] = apply_filters( 'it_exchange_checkout_action_nonce_var' , '_wpnonce' );
	$registered[] = 'it-exchange-basic-coupons-remove-coupon-cart';

	$registered = array_unique( $registered );

	$url = false;
	foreach( $registered as $key => $param ) {
		if ( ! in_array( $param, $exempt ) )
			$url = remove_query_arg( $param, $url );
	}

	return apply_filters( 'it_exchange_clean_query_args', $url );
}

/**
 * Replace Log in text with Log out text in nav menus
 *
 * @since 0.4.0
 *
 * @param string $page page setting
 * @return string url
*/
function it_exchange_wp_get_nav_menu_items_filter( $items, $menu, $args ) {
	if ( is_user_logged_in() ) {
		foreach ( $items as $item ) {
			if ( $item->url == it_exchange_get_page_url( 'login' ) || $item->url == it_exchange_get_page_url( 'logout' ) ) {

				$item->url = it_exchange_get_page_url( 'logout' );
				$item->title = it_exchange_get_page_name( 'logout' );
			}
		}
	}
	return apply_filters( 'it_exchange_wp_get_nav_menu_items_filter', $items, $menu, $args );

}
add_filter( 'wp_get_nav_menu_items', 'it_exchange_wp_get_nav_menu_items_filter', 10, 3 );

if ( ! function_exists( 'wp_nav_menu_disabled_check' ) && version_compare( $GLOBALS['wp_version'], '3.5.3', '<=' ) ) {

	/**
	 * From WordPress 3.6.0 for back-compat
	 * Check whether to disable the Menu Locations meta box submit button
	 *
	 * @since 0.4.0
	 *
	 * @uses global $one_theme_location_no_menus to determine if no menus exist
	 * @uses disabled() to output the disabled attribute in $other_attributes param in submit_button()
	 *
	 * @param int|string $nav_menu_selected_id (id, name or slug) of the currently-selected menu
	 * @return string Disabled attribute if at least one menu exists, false if not
	*/
	function wp_nav_menu_disabled_check( $nav_menu_selected_id ) {
		global $one_theme_location_no_menus;

		if ( $one_theme_location_no_menus )
			return false;

		return disabled( $nav_menu_selected_id, 0 );
	}

}

/**
 * Returns currency data
 *
 * Deprecated in 1.2.0.
 *
 * @since 0.3.4
 *
 * @deprecated 1.2.0 Use it_exchange_get_data_set( 'currencies' );
 * @return array
*/
function it_exchange_get_currency_options() {
	return it_exchange_get_data_set( 'currencies' );
}

/**
 * Returns the currency symbol based on the currency key
 *
 * @since 0.4.0
 *
 * @param string $country_code country code for the currency
 * @return string
*/
function it_exchange_get_currency_symbol( $country_code ) {
	$currencies = it_exchange_get_currency_options();
	$symbol = empty( $currencies[$country_code] ) ? '$' : $currencies[$country_code];
	$symbol = ( is_array( $symbol ) && ! empty( $symbol['symbol'] ) ) ? $symbol['symbol'] : '$';
	return apply_filters( 'it_exchange_get_currency_symbol', $symbol );
}

/**
 * Sets the value of a GLOBALS
 *
 * @since 0.4.0
 *
 * @param string $key in the GLOBALS array
 * @param mixed $value in the GLOBALS array
 * @return void
*/
function it_exchange_set_global( $key, $value ) {
	$GLOBALS['it_exchange'][$key] = $value;
}

/**
 * Returns the value of a GLOBALS
 *
 * @since 1.1.0
 *
 * @param string $key in the GLOBALS array
 * @return mixed value from the GLOBALS
*/
function it_exchange_get_global( $key ) {
	return isset( $GLOBALS['it_exchange'][$key] ) ? $GLOBALS['it_exchange'][$key] : NULL;
}

/**
 * Registers a purchase requirement
 *
 * @since 1.2.0
 *
 * @return void
*/
function it_exchange_register_purchase_requirement( $slug, $properties=array() ) {
	$defaults = array(
		'priority'               => 10,
		'requirement-met'        => '__return_true', // This is a callback, not a boolean.
		'sw-template-part'       => 'checkout',
		'checkout-template-part' => 'checkout',
		'notification'           => __( 'Please complete all purchase requirements before checkout out.', 'it-l10n-ithemes-exchange' ), // This really needs to be customized.
	);

	// Merge Defaults
	$properties = ITUtility::merge_defaults( $properties, $defaults );

	$properties['slug'] = $slug;

	// Don't allow false notification value. If you don't want a notification, make it ''.
	$properties['notification'] = ( false === $properties['notification'] ) ? $defaults['notification'] : $properties['notification'];

	// Grab existing requirements
	$requirements = it_exchange_get_purchase_requirements();

	// Add the purchase requriement
	$requirements[$slug] = $properties;

	// Write updated to global
	$GLOBALS['it_exchange']['purchase-requirements'] = $requirements;
}

/**
 * Grab all registered purchase requirements
 *
 * @since 1.2.0
 *
 * @return array
*/
function it_exchange_get_purchase_requirements() {
	$requirements  = empty( $GLOBALS['it_exchange']['purchase-requirements'] ) ? array() : (array) $GLOBALS['it_exchange']['purchase-requirements'];
	$requirements = (array) apply_filters( 'it_exchange_get_purchase_requirments', $requirements );

	// Sort the array by priority
	$priorities = array();
	foreach( $requirements as $key => $requirement ) {
		$priorities[$key] = $requirement['priority'];
	}
	array_multisort( $priorities, SORT_ASC, SORT_NUMERIC, $requirements );
	return $requirements;
}

/**
 * Returns the next required purchase requirement
 *
 * @since 1.2.0
 * @return string requirement string
*/
function it_exchange_get_next_purchase_requirement() {
	$requirements = it_exchange_get_purchase_requirements();

	foreach( (array) $requirements as $slug => $requirement ) {
		if ( is_callable( $requirement['requirement-met'] ) )
			$requirement_met = (boolean) call_user_func( $requirement['requirement-met'] );
		else
			$requirement_met = true;

		if ( ! $requirement_met )
			return $requirement;
	}
	return false;
}

/**
 * Returns a list of all page template parts for purchase requirements
 *
 * Purchase requirements need to register a template part file to be included
 * in the purchase-requirements loop at the top of the checkout page.
 *
 * @since 1.2.0
 *
 * @return array
*/
function it_exchange_get_all_purchase_requirement_checkout_element_template_parts() {
	$template_parts = array();
	foreach( (array) it_exchange_get_purchase_requirements() as $slug => $requirement ) {
		if ( ! empty( $requirement['checkout-template-part'] ) );
			$template_parts[] = $requirement['checkout-template-part'];
	}
	return $template_parts;
}

/**
 * Returns a specific property from the next required and unfulfilled purchase requriement
 *
 * @since 1.2.0
 *
 * @param string $prop the registered property we are looking for
 * @return mixed
*/
function it_exchange_get_next_purchase_requirement_property( $prop ) {
	$requirement = it_exchange_get_next_purchase_requirement();
	$property    = ! isset( $requirement[$prop] ) ? false : $requirement[$prop];

	// Send them to checkout in the SuperWidget if a template-part wasn't
	if ( 'sw-template-part' == $prop && ! $property )
		$property = 'checkout';

	return $property;
}

/**
 * Returns an array of all pending purchase requiremnts
 *
 * @since 1.3.0
 *
 * @return array
*/
function it_exchange_get_pending_purchase_requirements() {
	$pending      = array();
	$requirements = it_exchange_get_purchase_requirements();

	foreach( (array) $requirements as $slug => $requirement ) {
		if ( is_callable( $requirement['requirement-met'] ) )
			$requirement_met = (boolean) call_user_func( $requirement['requirement-met'] );
		else
			$requirement_met = true;

		if ( ! $requirement_met )
			$pending[] = $requirement['slug'];
	}
	return $pending;
}

/**
 * Returns boolean if passed paramater is the current checkout mode
 *
 * @since 1.5.0
 *
 * @param  string  $mode    the checkout mode we're testing
 * @param string   $context 'content' or 'sw'
 * @return boolean
*/
function it_exchange_is_checkout_mode( $mode, $context='content' ) {
	return apply_filters( 'it_exchange_is_' . $context . '_' . $mode . '_checkout_mode', false );
}

/**
 * Formats the Billing Address for display
 *
 * @todo this function sucks. Lets make a function for formatting any address. ^gta
 * @since 1.3.0
 *
 * @return string HTML
*/
function it_exchange_get_formatted_billing_address( $billing_address=false ) {
	$formatted   = array();
	$billing     = empty( $billing_address ) ? it_exchange_get_cart_billing_address() : $billing_address;
	$formatted[] = implode( ' ', array( $billing['first-name'], $billing['last-name'] ) );
	if ( ! empty( $billing['company-name'] ) )
		$formatted[] = $billing['company-name'];
	if ( ! empty( $billing['address1'] ) )
		$formatted[] = $billing['address1'];
	if ( ! empty( $billing['address2'] ) )
		$formatted[] = $billing['address2'];
	if ( ! empty( $billing['city'] ) || ! empty( $billing['state'] ) || ! empty( $billing['zip'] ) ) {
		$formatted[] = implode( ' ', array( ( empty( $billing['city'] ) ? '': $billing['city'] .',' ),
			( empty( $billing['state'] ) ? '': $billing['state'] ),
			( empty( $billing['zip'] ) ? '': $billing['zip'] ),
		) );
	}
	if ( ! empty( $billing['country'] ) )
		$formatted[] = $billing['country'];

	$formatted = implode( '<br />', $formatted );
	return apply_filters( 'it_exchange_get_formatted_billing_address', $formatted );
}

/**
 * Inits the IT_Exchange_Admin_Settings_Form class
 *
 * @since 1.3.1
 *
 * @param array  $options options for the class constructor
 * @return void
*/
function it_exchange_print_admin_settings_form( $options ) {
	if ( ! is_admin() )
		return;

	if ( $settings_form = new IT_Exchange_Admin_Settings_Form( $options ) )
		$settings_form->print_form();
}
