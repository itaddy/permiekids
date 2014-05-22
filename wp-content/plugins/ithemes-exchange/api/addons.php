<?php
/**
 * API Functions used to register / retrieve Exchange Add-ons
 *
 * @package IT_Exchange
 * @since 0.2.0
*/

/**
 * Register an Add-on with iThemes Exchange
 *
 * Core ‘category’ options for type of add-on
 * - product-type        Add-ons that create product types. eg: Digital, Membership
 * - transaction-method  Add-ons that create transactions. eg: Stripe, Tweet
 * - admin               General purpose admin functionality. eg: Reports, Export
 * - other               Everything else
 *
 * @since 0.2.0
 *
 * @param string $slug         string for identifying the add-on in code
 * @param array $params        key / value pairs.
*/
function it_exchange_register_addon( $slug, $params ) {

	$name         = empty( $params['name'] )        ? false   : $params['name'];
	$author       = empty( $params['author'] )      ? false   : $params['author'];
	$author_url   = empty( $params['author_url'] )  ? false   : $params['author_url'];
	$description  = empty( $params['description'] ) ? ''      : $params['description'];
	$file         = empty( $params['file'] )        ? false   : $params['file'];
	$options      = empty( $params['options'] )     ? array() : (array) $params['options'];

	// Basic Validation
	$slug   = empty( $slug )        ? false : sanitize_key( $slug );
	$name   = empty( $name )        ? false : sanitize_text_field( $name );
	$author = empty( $author )      ? false : sanitize_text_field( $author );
	$file   = file_exists( $file )  ? $file : false;

	if ( ! $slug  )
		return new WP_Error( 'it_exchange_add_registration_error', __( 'All iThemes Exchange Add-ons require a slug parameter.', 'it-l10n-ithemes-exchange' ) );

	if ( ! $name )
		return new WP_Error( 'it_exchange_add_registration_error', __( 'All iThemes Exchange Add-ons require a name parameter.', 'it-l10n-ithemes-exchange' ) );

	if ( ! $file )
		return new WP_Error( 'it_exchange_add_registration_error', __( 'All iThemes Exchange Add-ons require a file parameter.', 'it-l10n-ithemes-exchange' ) );

	$allowed_keys = array( 'category', 'tag', 'supports', 'labels', 'settings-callback', 'icon', 'wizard-icon' );

	foreach ( $params as $key => $value )
		if ( in_array( $key, $allowed_keys ) )
			$options[$key] = $value;

	if ( empty( $options['category'] ) )
		$options['category'] = 'other';

	// Add the add-on to our Global
	$GLOBALS['it_exchange']['add_ons']['registered'][$slug] = apply_filters( 'it_exchange_register_addon', array(
		'slug' 			=> $slug,
		'name' 			=> $name,
		'author' 		=> $author,
		'author_url' 	=> $author_url,
		'description' 	=> $description,
		'file' 			=> $file,
		'options' 		=> $options,
	) );
}

/**
 * Register an Add-on category with iThemes Exchange
 *
 * When registering an add-on category, you can set required/default support features that any add-on
 * in this category will be required to have. If add-ons register in this category without a key
 * they will be provided with a the value registered to the add-on category by default.
 * - eg: $options['supports'] = array( 'feature' => 'default_value' );
 *
 * @since 0.2.0
 *
 * @param string $slug         var for identifying the add-on in code
 * @param string $name         name of add-on used in UI
 * @param string $description  description of the add-on
 * @param array  $options      key / value pairs.
*/
function it_exchange_register_addon_category( $slug, $name, $description, $options = array() ) {
	// Basic Validation
	$slug                     = empty( $slug ) ? false : sanitize_key( $slug );
	$name                     = empty( $name ) ? false : sanitize_text_field( $name );
	$options['supports']      = empty( $options['supports'] ) ? array() : $options['supports'];

	if ( ! $slug  )
		return new WP_Error( 'it_exchange_add_registration_error', __( 'All iThemes Exchange Add-on categories require a slug parameter.', 'it-l10n-ithemes-exchange' ) );

	if ( ! $name )
		return new WP_Error( 'it_exchange_add_registration_error', __( 'All iThemes Exchange Add-ons categories require a name parameter.', 'it-l10n-ithemes-exchange' ) );

	// Add the add-on to our Global
	$GLOBALS['it_exchange']['add_on_categories'][$slug] = apply_filters( 'it_exchange_register_addon_category', array(
		'slug'        => $slug,
		'name'        => $name,
		'description' => $description,
		'options'     => $options,
	) );
}

/**
 * Returns an array of registered add-ons
 *
 * @since 0.2.0
 *
 * @param array $options  For filtering by category, use $options['category'] = array( 'cat1', 'cat2', 'etc' );
 * @return array  registered add-ons
*/
function it_exchange_get_addons( $options=array() ) {
	$defaults = array(
		'show_required' => true,
	);
	$options = wp_parse_args( $options, $defaults );

	if ( empty( $GLOBALS['it_exchange']['add_ons']['registered'] ) )
		return array();
	else
		$add_ons = $GLOBALS['it_exchange']['add_ons']['registered'];

	// Loop through addons and filter out required if not set to show.
	foreach ( $add_ons as $key => $addon ) {
		if ( ! $options['show_required'] && ! empty( $addon['options']['tag'] ) && 'required' === $addon['options']['tag'] )
			unset( $add_ons[$key] );

	}

	// Possibly filter by category
	if ( ! empty( $options['category'] ) )
		$add_ons = it_exchange_filter_addons_by_category( $add_ons, $options['category'] );

	ksort( $add_ons );
	return apply_filters( 'it_exchange_get_addons', $add_ons, $options );
}

/**
 * We do not want to permanently enable all these addons, we just need to load them one time temporarily
 *
 * @since 0.4.5
 *
 * @param array $add_ons
 * @return void
*/
function it_exchange_temporarily_load_addons( $add_ons ) {
	$enabled_addons = it_exchange_get_enabled_addons();

	// Init all enabled addons
	foreach( (array) $add_ons as $slug => $params ) {
		if( ! isset( $enabled_addons[$slug] ) ) {
			if ( ! empty( $params['file'] ) && is_file( $params['file'] ) ) {
				include_once( $params['file'] );
			}
		}
	}
}

/**
 * We do not want to permanently enable this addon, we just need to load it one time temporarily
 *
 * @since 0.4.5
 *
 * @param string $add_on slug
 * @return void
*/
function it_exchange_temporarily_load_addon( $add_on ) {
	$enabled_addons = it_exchange_get_enabled_addons();

	// Init all enabled addons
	if( ! isset( $enabled_addons[$add_on] ) ) {
		if ( ! empty( $params['file'] ) && is_file( $params['file'] ) ) {
			include_once( $params['file'] );
		}
	}
}

/**
 * Returns a specific add-on by its slug
 *
 * @since 0.3.2
 *
 * @param string $slug  the add-on's slug
 * @return array  the add_on array
*/
function it_exchange_get_addon( $slug ) {
	if ( $add_ons = it_exchange_get_addons() ) {
		if ( ! empty( $add_ons[$slug] ) )
			return $add_ons[$slug];
	}
	return apply_filters( 'it_exchange_get_addon', false, $slug, $add_ons );
}

/**
 * Returns an array of registered add-on categories
 *
 * @since 0.2.0
 *
 * @return array  registered add-on categories
*/
function it_exchange_get_addon_categories() {
	if ( empty( $GLOBALS['it_exchange']['add_on_categories'] ) )
		$categories = array() ;
	else
		$categories = $GLOBALS['it_exchange']['add_on_categories'];

	return apply_filters( 'it_exchange_get_addon_categories', $categories );
}

/**
 * Grabs list of currently enabled add-ons
 *
 * Can optionally filter by categories
 *
 * @since 0.3.0
 *
 * @param array $options  For filtering by category, use $options['category'] = array( 'cat1', 'cat2', 'etc' );
 * @return array  Enabled add-ons
*/
function it_exchange_get_enabled_addons( $options=array() ) {
	$defaults = array(
		'show_required' => true,
		'break_cache'   => false,
	);
	$options = wp_parse_args( $options, $defaults );

	// Grab all registered add-ons
	$registered = it_exchange_get_addons();
	$enabled = array();

	// Grab enabled add-ons from options
	if ( false === $enabled_addons = it_exchange_get_option( 'enabled_add_ons', $options['break_cache'] ) )
		$enabled_addons = array();

	// Set each enabled with registered params
	if ( ! empty( $enabled_addons ) ) {
		foreach ( $enabled_addons as $addon => $params ) {
			if ( ! empty( $registered[$addon] ) ) {
				if ( $options['show_required'] )
					$enabled[$addon] = $registered[$addon];
				else if ( empty( $registered[$addon]['options']['tag'] ) || 'required' !== $registered[$addon]['options']['tag'] )
					$enabled[$addon] = $registered[$addon];
			}
		}
	}

	if ( ! empty( $options['category'] ) )
		$enabled = it_exchange_filter_addons_by_category( $enabled, $options['category'] );

	ksort( $enabled );
	return apply_filters( 'it_exchange_get_enabled_addons', empty( $enabled ) ? array() : $enabled, $options );
}

/**
 * Grabs list of currently disabled add-ons
 *
 * Can optionally filter by categories
 *
 * @since 0.4.0
 *
 * @param array $options  For filtering by category, use $options['category'] = array( 'cat1', 'cat2', 'etc' );
 * @return array  Disabled add-ons
*/
function it_exchange_get_disabled_addons( $options=array() ) {
	// Grab all registered add-ons
	$registered = it_exchange_get_addons();
	$disabled = array();

	// Grab enabled add-ons from options
	if ( false === $enabled_addons = it_exchange_get_option( 'enabled_add_ons' ) )
		$enabled_addons = array();

	// Loop through registered addons
	foreach ( $registered as $slug => $params )
		if ( ! in_array( $slug, array_keys( $enabled_addons ) ) )
			$disabled[$slug] = $params;

	if ( ! empty( $options['category'] ) )
		$disabled = it_exchange_filter_addons_by_category( $disabled, $options['category'] );

	if ( ! empty( $disabled ) )
		ksort( $disabled );

	return apply_filters( 'it_exchange_get_disabled_addons', empty( $disabled ) ? array() : $disabled, $options );
}

/**
 * Grabs list of currently available add-ons from iThemes
 *
 * Can optionally filter by categories
 *
 * @since 0.4.0
 *
 * @param array $options  For filtering by category, use $options['category'] = array( 'cat1', 'cat2', 'etc' );
 * @return array  All add-ons available from iThemes
*/
function it_exchange_get_more_addons( $options=array() ) {
	// Grab all registered add-ons
	$remote_get = wp_remote_get( 'https://api.ithemes.com/exchange/addons/' );

	// Return empty array if remote_get errored out.
	if ( is_wp_error( $remote_get ) )
		return array();

	$addons = json_decode( $remote_get['body'], true );

	if ( ! empty( $options['category'] ) )
		$addons = it_exchange_filter_addons_by_category( $addons, $options['category'] );

	ksort( $addons );

	return apply_filters( 'it_exchange_get_more_addons', empty( $addons ) ? array() : $addons, $options );
}

/**
 * Resorts addon list from get_more_addons so featured add-ons are on top
 *
 * @since 0.4.0
 *
 * @param array $addons  Current add-on array from it_exchange_get_more_addons()
 * @return array  Restorted add-ons array
*/
function it_exchange_featured_addons_on_top( $addons ) {

	$sorted_addons = array();

	foreach( $addons as $slug => $addon ) {
		if ( true === $addon['featured'] ) {
			$sorted_addons[$slug] = $addon;
			unset( $addons[$slug] );
		}
	}

	ksort( $sorted_addons );
	return apply_filters( 'it_exchange_get_more_addons', array_merge( $sorted_addons, $addons ), $addons );
}

/**
 * Takes an array of add-ons and filters by passed category
 *
 * @since 0.3.0
 *
 * @param array $add_ons  an array of add-ons formatted like $GLOBALS['it_exchange']['add_ons'] array
 * @param array $categories  contains categories we want filters: array( 'cat1', 'cat2', 'etc' );
 * @return array  Filtered add-ons
*/
function it_exchange_filter_addons_by_category( $add_ons, $categories ) {
	foreach( $add_ons as $slug => $params ) {
		if ( ! empty( $params['options']['category'] ) ) {
			if ( ! in_array( $params['options']['category'], (array) $categories ) )
				unset( $add_ons[$slug] );
		}
	}
	return apply_filters( 'it_exchange_filter_addons_by_category', $add_ons, $categories );
}

/**
 * Enable a registerd add_on
 *
 * @since 0.3.2
 *
 * @param string $add_on  add_on to enable
 * @return bool
*/
function it_exchange_enable_addon( $add_on ) {
	$registered = it_exchange_get_addons();
	$enabled_add_ons = it_exchange_get_enabled_addons( array( 'break_cache' => true ) );
	$success = false;

	if ( isset( $registered[$add_on] ) && ! isset( $enabled_add_ons[$add_on] ) ) {
		$enabled_add_ons[$add_on] = $registered[$add_on];
		if ( it_exchange_save_option( 'enabled_add_ons', $enabled_add_ons ) ) {
			include_once( $registered[$add_on]['file'] );
			do_action( 'it_exchange_add_on_enabled', $registered[$add_on] );
			update_option( '_it-exchange-flush-rewrites', true );
			$success = true;
		}
	}

	return apply_filters( 'it_exchange_enable_addon', $success, $add_on );
}

/**
 * Checks if an add-on is enabled
 *
 * @since 0.4.0
 *
 * @param string $add_on_slug  add_on slug to check
 * @return boolean
*/
function it_exchange_is_addon_enabled( $add_on_slug ) {
	$enabled = array_keys( it_exchange_get_enabled_addons( array( 'break_cache' => true ) ) );
	$success = false;

	if ( in_array( $add_on_slug, $enabled ) )
		$success = true;

	return apply_filters( 'it_exchange_is_addon_enabled', $success, $add_on_slug );
}

/**
 * Checks if an add-on is registered
 *
 * @since 0.4.0
 *
 * @param string $add_on  add_on slug to check
 * @return bool
*/
function it_exchange_is_addon_registered( $add_on_slug ) {
	$installed = it_exchange_get_addons();
	$success = false;

	if ( array_key_exists( $add_on_slug, $installed ) )
		$success = true;

	return apply_filters( 'it_exchange_is_addon_registered', $success, $add_on_slug );
}

/**
 * Disable a registerd add_on
 *
 * @since 0.3.2
 *
 * @param string $add_on add_on to disable
 * @return bool
*/
function it_exchange_disable_addon( $add_on ) {
	$registered = it_exchange_get_addons();
	$enabled_addons = it_exchange_get_enabled_addons( array( 'break_cache' => true ) );
	$success = false;

	if ( ! empty( $enabled_addons[$add_on] ) ) {
		unset( $enabled_addons[$add_on] );
		if ( it_exchange_save_option( 'enabled_add_ons', $enabled_addons ) ) {
			if ( ! empty( $registered[$add_on] ) )
				do_action( 'it_exchange_add_on_disabled', $registered[$add_on] );
			update_option( '_it-exchange-flush-rewrites', true );
			$success = true;
		}
	}

	return $success;
}

/**
 * Does the given add-on support a specific feature?
 *
 * @since 0.3.3
 *
 * @param string $add_on   add_on slug
 * @param string $feature type of feature we are testing for support
 * @return bool
*/
function it_exchange_addon_supports( $add_on, $feature ) {
	$add_ons = it_exchange_get_addons();

	// Return false if add-on is not registered
	if ( ! isset( $add_ons[$add_on] ) )
		return false;

	// Return false if feature is not supported
	if ( empty( $add_ons[$add_on]['options']['supports'][$feature] ) )
		return false;

	return true;
}

/**
 * Add's add-on support for a specific feature
 *
 * @since 0.3.3
 *
 * @param string $add_on   the slug for the add-on being targeted
 * @param string $feature the feature slug that needs to be enabled
 * @return void
*/
function it_exchange_add_addon_support( $add_on, $feature ) {
	$add_ons = it_exchange_get_addons();

	// Return false if add-on is not registered
	if ( ! isset( $add_ons[$add_on] ) )
		return false;

	// Set add-on support to true for this add-on / feature combo
	if ( empty( $add_ons[$add_on]['options'] ) )
		$GLOBALS['it_exchange']['add_ons']['registered'][$add_on]['options']['supports'][$feature] = true;
}

/**
 * Remove's add-on support for a specific feature
 *
 * @since 0.3.3
 *
 * @param string $add_on   the slug for the add-on being targeted
 * @param string $feature the feature slug that needs to be enabled
 * @return void
*/
function it_exchange_remove_addon_support( $add_on, $feature ) {
	$add_ons = it_exchange_get_addons();

	// Return false if add-on is not registered
	if ( ! isset( $add_ons[$add_on] ) )
		return false;

	// Set add-on support to false for this add-on / feature combo
	if ( empty( $add_ons[$add_on]['options'] ) )
		$GLOBALS['it_exchange']['add_ons']['registered'][$add_on]['options']['supports'][$feature] = false;
}

/**
 * Return the default values for an add-on support key
 *
 * @since 0.3.3
 *
 * @param string $add_on the slug for the add-on being targeted
 * @param string $feature the feature the slug is targeting
 * @return mixed the value of the key
*/
function it_exchange_get_addon_support( $add_on, $feature ) {
	$add_ons = it_exchange_get_addons();

	// Return false if feature isn't recorded
	if ( empty( $add_ons[$add_on]['options']['supports'][$feature] ) )
		return false;

	return apply_filters( 'it_exchange_get_addon_support', $add_ons[$add_on]['options']['supports'][$feature], $add_on, $feature );
}

/**
 * Returns true if addon is in ithemes-exchange/core-addons directory
 *
 * @since 0.4.0
 *
 * @param string $slug the addon slug
 * @return boolean
*/
function it_exchange_is_core_addon( $slug ) {
	$addon = it_exchange_get_addon( $slug );
	if ( empty( $addon['file'] ) )
		return false;

	// Don't add a filter here.
	return ( 'ithemes-exchange/core-addons/' == substr( plugin_basename( $addon['file'] ), 0, 29 ) );
}
