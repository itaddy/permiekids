<?php
/**
 * This file holds the class for an iThemes Exchange Product
 *
 * @package IT_Exchange
 * @since 0.3.2
*/

/**
 * Merges a WP Post with iThemes Exchange Product data
 *
 * @since 0.3.2
*/
class IT_Exchange_Product {

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
	 * @param string $product_type The product type for this product
	 * @since 0.3.2
	*/
	var $product_type;

	/**
	 * @param array $product_supports features that this product supports along with defaults
	 * @since 0.3.3
	*/
	var $product_supports;

	/**
	 * @param array $product_data  any custom data registered by the product-type for this product
	 * @since 0.3.2
	*/
	var $product_data = array();

	/**
	 * Constructor. Loads post data and product data
	 *
	 * @since 0.3.2
	 * @param mixed $post  wp post id or post object. optional.
	 * @return void
	*/
	function IT_Exchange_Product( $post=false ) {

		// If not an object, try to grab the WP object
		if ( ! is_object( $post ) )
			$post = get_post( (int) $post );

		// Ensure that $post is a WP_Post object
		if ( is_object( $post ) && 'WP_Post' != get_class( $post ) )
			$post = false;

		// Ensure this is a product post type
		if ( 'it_exchange_prod' != get_post_type( $post ) )
			$post = false;

		// Return a WP Error if we don't have the $post object by this point
		if ( ! $post )
			return new WP_Error( 'it-exchange-product-not-a-wp-post', __( 'The IT_Exchange_Product class must have a WP post object or ID passed to its constructor', 'it-l10n-ithemes-exchange' ) );

		// Grab the $post object vars and populate this objects vars
		foreach( (array) get_object_vars( $post ) as $var => $value ) {
			$this->$var = $value;
		}

		// Set the product type
		$this->set_product_type();

		// Register filters to set values for supported features based on componant type
		add_filter( 'it_exchange_set_product_data_for_post_meta_componant', array( $this, 'set_feature_value_for_post_meta_componant' ), 10, 3 );

		// Set the product data
		if ( did_action( 'init' ) )
			$this->set_product_supports_and_data();
		else
			add_action( 'init', array( $this, 'set_product_supports_and_data' ) );


		// Set supports for new and edit screens
		if ( did_action( 'admin_init' ) )
			$this->set_add_edit_screen_supports();
		else
			add_action( 'admin_init', array( $this, 'set_add_edit_screen_supports' ) );

	}

	/**
	 * Tack transaction_id to post_meta for product
	 *
	 * @since 0.4.0
	 *
	 * @param object $cart_object data passed to the transaction_method from the cart
	 * @param integer $transaction_id id of the transaction
	 * @return void
	*/
	function add_transaction_to_product( $transaction_id ) {
		add_post_meta( $this->ID, '_it_exchange_transaction_id', $transaction_id );
	}

	/**
	 * Sets the product_type property.
	 *
	 * If the custom value is already set, it uses that.
	 * If the custom value is not set and we're on post-add.php, check for a URL param
	 *
	 * @since 0.3.2
	*/
	function set_product_type() {
		global $pagenow;
		if ( ! $product_type = get_post_meta( $this->ID, '_it_exchange_product_type', true ) ) {
			if ( is_admin() && 'post-new.php' == $pagenow && ! empty( $_GET['it-exchange-product-type'] ) )
				$product_type = $_GET['it-exchange-product-type'];
		}
		$this->product_type = $product_type;
	}

	/**
	 * Sets the product_data property from appropriate product-type options and assoicated post_meta
	 *
	 * @ since 0.3.2
	 * @return void
	*/
	function set_product_supports_and_data() {
		// Get product-type options
		if ( $product_type_options = it_exchange_get_product_type_options( $this->product_type ) ) {
			if ( ! empty( $product_type_options['supports'] ) ) {
				foreach( $product_type_options['supports'] as $feature => $params ) {
					// Set the product_supports array
					$this->product_supports[$feature] = $params;

					// Set the product data via a filter.
					$value = apply_filters( 'it_exchange_set_product_data_for_' . $params['componant'] . '_componant', false, $this->ID, $params );

					// Set to default if it exists
					$default = empty( $product_type_options['supports'][$feature]['default'] ) ? false : $product_type_options['supports'][$feature]['default'];
					if ( empty( $value ) )
						$this->product_data[$params['key']] = $default;
					else
						$this->product_data[$params['key']] = $value;
				}
			}
		}
	}

	/**
	 * Sets supported feature values for post_meta componant
	 *
	 * @since 0.3.7
	 * @param string existing value
	 * @param integer product id
	 * @param array params for supports array registered with the add-on
	 * @return mixed value of post_meta
	*/
	function set_feature_value_for_post_meta_componant( $existing, $product, $params ) {

		// Return if someone else beat us to it.
		if ( ! empty( $existing ) )
			return $existing;

		// Set product_data to post_meta value or feature devault
		if ( $value = get_post_meta( $product, $params['key'], true ) )
			return $value;

		return false;
	}

    /**
     * Sets the supports array for the post_type.
     *
     * @since 0.3.3
    */
    function set_add_edit_screen_supports() {
		global $pagenow;
        $supports = array(
            'title', 'editor', 'author', 'thumbnail', 'excerpt', 'trackbacks', 
            'custom-fields', 'comments', 'revisions', 'post-formats', 'page-attributes'
        );

        // If is_admin and is post-new.php or post.php, only register supports for current product-type
        if ( 'post-new.php' != $pagenow && 'post.php' != $pagenow )
			return; // Don't remove any if not on post-new / or post.php

		if ( $addon = it_exchange_get_addon( $this->product_type ) ) {
			// Remove any supports args that the product add-on does not want.
			foreach( $supports as $option ) {
			
				// Map Core WP post_type supports to our addon names
				if ( 'title' == $option ) {
					$exchange_product_feature = 'title';
				} else if ( 'editor' == $option ) {
					$exchange_product_feature = 'extended-description';
				} else if ( 'author' == $option ) {
					$exchange_product_feature = 'author';
				} else if ( 'thumbnail' == $option ) {
					$exchange_product_feature = 'featured-image';
				} else if ( 'excerpt' == $option ) {
					$exchange_product_feature = 'wp-excerpt';
				} else if ( 'trackbacks' == $option ) {
					$exchange_product_feature = 'wp-trackbacks';
				} else if ( 'custom-fields' == $option ) {
					$exchange_product_feature = 'wp-custom-fields';
				} else if ( 'comments' == $option ) {
					$exchange_product_feature = 'wp-comments';
				} else if ( 'revisions' == $option ) {
					$exchange_product_feature = 'wp-revisions';
				} else if ( 'post-formats' == $option ) {
					$exchange_product_feature = 'wp-post-formats';
				} else {
					$exchange_product_feature = $option;
				}

                if ( ! it_exchange_product_type_supports_feature( $this->product_type, $exchange_product_feature ) ) {
					remove_post_type_support( 'it_exchange_prod', $option );
				}
            }
        }
    }
}
