<?php
/**
 * This file inits deprecated features of Exchange
 * Some are alwasy included. Others require theme_support
 * or filters to be activated.
 *
 * @since 1.1.0
 * @package IT_Exchange
*/

/**
 * Loads the deprecated template parts
 *
 * @since 1.1.0
 *
 * @return void
*/
function it_exchange_load_deprecated_template_parts() {

	// Abandon if not supporting deprecated template parts
	if ( ! current_theme_supports( 'it-exchange-deprecated-template-parts' ) )
		return;

	// Get current filter because we add this function to two different hooks
	$current_hook = current_filter();

	// If current hook is print scripts, enqueue our deprecated CSS
	if ( 'wp_enqueue_scripts' == $current_hook ) {

		/**
		 * First, we're going to dequeue all of our core styles, including theme overrides
		 * Then we're going to enqueue the deprecated versions
		 * Then we're going to re-enqueue the theme overrides to maintain correct cascading order.
		*/

		// Dequeue our core styles
		wp_dequeue_style( 'it-exchange-public-css' );
		wp_dequeue_style( 'it-exchange-super-widget-frontend-global' );
		wp_dequeue_style( 'it-exchange-parent-theme-css' );
		wp_dequeue_style( 'it-exchange-child-theme-css' );

		// Enqueue the deprecated template part styles
		wp_enqueue_style( 'it-exchange-deprecated-template-parts-global', ITUtility::get_url_from_file( dirname( __FILE__ ) . '/templates/deprecated-template-part-styles.css' ) );

		// Parent theme /exchange/style.css if it exists
		$parent_theme_css = get_template_directory() . '/exchange/style.css';
		if ( is_file( $parent_theme_css ) )
			wp_enqueue_style( 'it-exchange-parent-theme-css', ITUtility::get_url_from_file( $parent_theme_css ) );

		// Child theme /exchange/style.css if it exists
		$child_theme_css = get_stylesheet_directory() . '/exchange/style.css';
		if ( is_file( $child_theme_css ) && ( $parent_theme_css != $child_theme_css || ! is_file( $parent_theme_css ) ) )
			wp_enqueue_style( 'it-exchange-child-theme-css', ITUtility::get_url_from_file( $child_theme_css ) );

		// Enqueue SW after custom styles since that's how it happens otherwise
		wp_enqueue_style( 'it-exchange-deprecated-template-parts-sw', ITUtility::get_url_from_file( dirname( __FILE__ ) . '/templates/deprecated-super-widget-template-styles.css' ) );

	} else if ( 'it_exchange_get_template_part' == $current_hook ) {
		// Tell exchange to look in our deprecated tempaltes folder for templates
		add_filter( 'it_exchange_possible_template_paths', 'it_exchange_register_deprecated_template_parts_directory' );
	}
}
add_action( 'it_exchange_get_template_part', 'it_exchange_load_deprecated_template_parts' );
add_action( 'wp_enqueue_scripts', 'it_exchange_load_deprecated_template_parts', 11 );

/**
 * This function adds our deprecated templates folder to the list of possible paths for templates
 *
 * @since 1.1.0
 *
 * @param array $possible_locations existing locations
 * @return array
*/
function it_exchange_register_deprecated_template_parts_directory( $possible_locations ) {
	$deprecated_path = dirname( __FILE__ ) . '/templates';
	$possible_locations[] = $deprecated_path;
	return $possible_locations;
}
