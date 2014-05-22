<?php
/**
 * Functions for integration with Builder.
 *
 * @package IT_Exchange_Addon_Membership
*/

/**
 * Builder theme callback to determine if this a checkout view
 *
 * @package IT_Exchange_Addon_Membership
 * @since CHANGEME
 * @author Justin Kopepasah
*/
function it_exchange_is_membership_addon_builder_view() {
    return it_exchange_is_page( 'membership' );
}

/**
 * Add the views to Builder's list of available views.
 *
 * @package IT_Exchange_Addon_Membership
 * @since CHANGEME
 * @author Justin Kopepasah
 * @var $views
*/
function it_exchange_membership_addon_add_new_builder_views( $views ) {
	$exchange_views = array(
		'it_exchange_is_membership_addon_builder_view' => array(
			'name'        => _x( 'Exchange - Membership', 'view', 'it-l10n-exchange-addon-membership' ),
			'priority'    => '20',
			'description' => __( 'The Exchange customer\'s membership account page.', 'it-l10n-exchange-addon-membership' ),
		),
	);
	
	$views = array_merge( $views, $exchange_views );
	
	return $views;
}
add_filter( 'builder_get_available_views', 'it_exchange_membership_addon_add_new_builder_views', 100 );
