<?php
/**
 * This file holds the class for an iThemes Exchange Coupon
 *
 * @package IT_Exchange
 * @since 0.4.0
*/

/**
 * Merges a WP Post with iThemes Exchange Coupon data
 *
 * @since 0.4.0
*/
class IT_Exchange_Coupon {

	// WP Post Type Properties
	var $ID;
	var $post_author;
	var $post_date;
	var $post_date_gmt;
	var $post_content;
	var $post_title;
	var $post_excerpt;
	var $post_status;
	var $comment_status;
	var $ping_status;
	var $post_password;
	var $post_name;
	var $to_ping;
	var $pinged;
	var $post_modified;
	var $post_modified_gmt;
	var $post_content_filtered;
	var $post_parent;
	var $guid;
	var $menu_order;
	var $post_type;
	var $post_mime_type;
	var $comment_count;

	/**
	 * @param array $coupon_data  any custom data registered by the coupon addon
	 * @since 0.4.0
	*/
	var $coupon_data = array();

	/**
	 * Constructor. Loads post data and coupon data
	 *
	 * @since 0.4.0
	 * @param mixed $post  wp post id or post object. optional.
	 * @return void
	*/
	function IT_Exchange_Coupon( $post=false ) {

		// If not an object, try to grab the WP object
		if ( ! is_object( $post ) )
			$post = get_post( (int) $post );

		// Ensure that $post is a WP_Post object
		if ( is_object( $post ) && 'WP_Post' != get_class( $post ) )
			$post = false;

		// Ensure this is a coupon post type
		if ( 'it_exchange_coupon' != get_post_type( $post ) )
			$post = false;

		// Return a WP Error if we don't have the $post object by this point
		if ( ! $post )
			return new WP_Error( 'it-exchange-coupon-not-a-wp-post', __( 'The IT_Exchange_Coupon class must have a WP post object or ID passed to its constructor', 'it-l10n-ithemes-exchange' ) );

		// Grab the $post object vars and populate this objects vars
		foreach( (array) get_object_vars( $post ) as $var => $value ) {
			$this->$var = $value;
		}

		// Set additional properties
		$additional_properties = apply_filters( 'it_exchange_coupon_additional_data', array(), $post );
		foreach( $additional_properties as $key => $value ) {
			$this->$key = $value;
		}
	}
}
