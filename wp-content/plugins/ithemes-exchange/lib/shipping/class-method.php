<?php
/**
 * Extend this class to create new shipping method to use with your shipping add-on
*/

abstract class IT_Exchange_Shipping_Method {

	/**
	 * @var string $slug the identifying key of the shipping feature
	*/
	var $slug;

	/**
	 * @var object $product the current product object
	*/
	var $product  = false;

	/**
	 * @var boolean $enabled is this shipping feature enabled
	*/
	var $enabled = false;

	/**
	 * @var boolean $available is this shipping feature available to the current product
	*/
	var $available = false;

	/**
	 * @var array $settings the fields that will be added to the settings page of the provider
	*/
	var $settings = array();

	/**
	 * Class constructor
	 *
	 * @since 1.4.0
	 *
	 * @param  int   $product exchange product id or empty to attempt to pick up the global product
	 * @param  array $options options for the object
	 * @return void
	*/
	function __construct( $product_id=false ) {

		// Set slug
		$this->set_slug();

		// Set label
		$this->set_label();

		// Set the product
		$this->set_product( $product_id );

		// Set whether this is enabled
		$this->set_enabled();

		// Set the availability of this method to this product
		$this->set_availability();

		// Set the settings
		$this->set_settings();

		// Set the shipping features for this method
		$this->set_features();
	}

	abstract function set_slug();

	/**
	 * Sets the product if one is available
	 *
	 * @since 1.4.0
	 *
	 * @todo   I don't like this. Cory needs to refactor it. ^gta
	 * @param  int  $product exchange product id or empty to attempt to pick up the global product
	 * @return void
	*/
	function set_product( $product_id=false ) {
		$product = false;

		// If a product ID is passed, use it
		if ( $product_id  ) {
			$product = it_exchange_get_product( $product_id );
		} else {
			// Grab global $post
			global $post;

			// If post is set in REQUEST, use it.
			if ( isset( $_REQUEST['post'] ) )
				$post_id = (int) $_REQUEST['post'];
			elseif ( isset( $_REQUEST['post_ID'] ) )
				$post_id = (int) $_REQUEST['post_ID'];
			else
				$post_id = empty( $post->ID ) ? 0 : $post->ID;

			// If we have a post ID, get the post object
			if ( $post_id )
				$post = get_post( $post_id );

			// If we have a post object, grab the product
			if ( ! empty( $post ) && is_object( $post ) )
				$product = it_exchange_get_product( $post );
		}

		// Set the property
		if ( is_object( $product ) && 'IT_Exchange_Product' == get_class( $product ) )
			$this->product = $product;
		else
			$this->product = false;
	}

	/**
	 * Is this method available to this product
	 *
	 *
	 * The shipping method is required to extend override this method in the extended class.
	 * It needs to then determine if the shipping method is available
	 * @since 1.4.0
	 *
	 * @return void
	*/
	abstract function set_availability();

	abstract function set_label();

	abstract function set_settings();

	abstract function set_enabled();

	abstract function set_features();

	abstract function get_shipping_cost_for_product( $cart_product );

	function add_setting( $setting ) {
		$settings = (array) $this->settings;
		$this->settings[] = $setting;
	}
}
