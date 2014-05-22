<?php
/**
 * This is an abstract class mean to be extended by product features
 *
 * @since 1.7.27
 * @package IT_Exchange
*/
abstract class IT_Exchange_Product_Feature_Abstract {

	/**
	 * Constructor. Registers hooks
	 *
	 * @since 1.7.27
	 *
	 * @return void
	*/
	function IT_Exchange_Product_Feature_Abstract( $args=array() ) {

		// Merge Defaults
		$defaults = array(
			'slug'                           => false,
			'description'                    => false,
			'product_types'                  => 'all',
			'has_admin_metaboxes'            => true,
			'update_on_save_product'         => true,
			'use_core_product_feature_hooks' => true,
			'register_feature_on_init'       => true,
			'metabox_title'                  => false,
			'metabox_context'                => 'normal',
			'metabox_priority'               => 'default',
			'metabox_args'                   => null,
		);
		$options = wp_parse_args( $args, $defaults );

		// Don't go any further if we don't have a slug
		if ( empty( $options['slug'] ) ) {
			it_exchange_add_message( 'error', __( 'Coding Error: IT_Exchange_Product_Feature_Abstract extended without a slug value', 'it-l10n-ithemes-exchange' ) );
			return false;
		}

		// Set properties
		$this->slug             = $options['slug'];
		$this->description      = $options['description'];
		$this->product_types    = $options['product_types'];
		$this->metabox_title    = empty( $options['metabox_title'] ) ? ucwords( $this->slug ) : $options['metabox_title'];
		$this->metabox_context  = $options['metabox_context'];
		$this->metabox_priority = $options['metabox_priority'];
		$this->metabox_args     = $options['metabox_args'];

		do_action( 'it_exchange_product_feature_abstract_begin_construct-' . $this->slug, $this );

		// Load actions for admin only
		if ( is_admin() ) {
			if ( $options['has_admin_metaboxes'] ) {
				add_action( 'load-post-new.php', array( $this, 'init_feature_metaboxes' ) );
				add_action( 'load-post.php', array( $this, 'init_feature_metaboxes' ) );
			}

			if ( $options['update_on_save_product'] )
				add_action( 'it_exchange_save_product', array( $this, 'save_feature_on_product_save' ) );
		}

		// Register the product feature on init
		if ( $options['register_feature_on_init'] )
			add_action( 'it_exchange_enabled_addons_loaded', array( $this, 'add_feature_support_to_product_types' ) );

		// Register the core methods
		if ( $options['use_core_product_feature_hooks'] ) {
			add_action( 'it_exchange_update_product_feature_' . $this->slug, array( $this, 'save_feature' ), 9, 3 );
			add_filter( 'it_exchange_get_product_feature_' . $this->slug, array( $this, 'get_feature' ), 9, 3 );
			add_filter( 'it_exchange_product_has_feature_' . $this->slug, array( $this, 'product_has_feature') , 9, 3 );
			add_filter( 'it_exchange_product_supports_feature_' . $this->slug, array( $this, 'product_supports_feature') , 9, 3 );
		}

		// Action run on construct
		do_action( 'it_exchange_product_feature_abstract_end_construct-' . $this->slug, $this );
	}

	/**
	 * Register the product feature and add it to enabled product-type addons
	 *
	 * @since 1.7.27
	*/
	function add_feature_support_to_product_types() {

		// Register the product feature
		it_exchange_register_product_feature( $this->slug, $this->description );

		// Add it to all enabled product-type addons
		$product_types = ( 'all' == $this->product_types ) ? array_keys( it_exchange_get_enabled_addons( array( 'category' => 'product-type' ) ) ) : (array) $this->product_types;
		foreach( $product_types as $product_type ) {
			it_exchange_add_feature_support_to_product_type( $this->slug, $product_type );
		}
	}

	/**
	 * Register's the metabox for any product type that supports the feature
	 *
	 * @since 1.7.27
	 * @return void
	*/
	function init_feature_metaboxes() {

		global $post;

		if ( isset( $_REQUEST['post_type'] ) ) {
			$post_type = $_REQUEST['post_type'];
		} else {
			if ( isset( $_REQUEST['post'] ) )
				$post_id = (int) $_REQUEST['post'];
			elseif ( isset( $_REQUEST['post_ID'] ) )
				$post_id = (int) $_REQUEST['post_ID'];
			else
				$post_id = 0;

			if ( $post_id )
				$post = get_post( $post_id );

			if ( isset( $post ) && ! empty( $post ) )
				$post_type = $post->post_type;
		}

		if ( ! empty( $_REQUEST['it-exchange-product-type'] ) )
			$product_type = $_REQUEST['it-exchange-product-type'];
		else
			$product_type = it_exchange_get_product_type( $post );

		if ( ! empty( $post_type ) && 'it_exchange_prod' === $post_type ) {
			if ( ! empty( $product_type ) &&  it_exchange_product_type_supports_feature( $product_type, $this->slug ) )
				add_action( 'it_exchange_product_metabox_callback_' . $product_type, array( $this, 'register_metabox' ) );
		}

	}

	/**
	 * Registers the feature metabox for a specific product type
	 *
	 * Hooked to it_exchange_product_metabox_callback_[product-type] where product type supports the feature
	 *
	 * @since 1.7.27
	 * @return void
	*/
	function register_metabox() {
		add_meta_box( 'it-exchange-product-' . $this->slug, $this->metabox_title, array( $this, 'print_metabox' ), 'it_exchange_prod', $this->metabox_context, $this->metabox_priority, $this->metabox_args );
	}

	/**
	 * This echos the feature metabox.
	 *
	 * @since 1.7.27
	 * @return void
	*/
	abstract function print_metabox( $post );

	/**
	 * This saves the value
	 *
	 * @since 1.7.27
	 *
	 * @param object $post wp post object
	 * @return void
	*/
	abstract function save_feature_on_product_save();

	/**
	 * This updates the feature for a product
	 *
	 * @since 1.7.27
	 * @param integer $product_id the product id
	 * @param mixed $new_value the new value
	 * @return bolean
	*/
	abstract function save_feature( $product_id, $new_value, $options=array() );

	/**
	 * Return the product's features
	 *
	 * @since 1.7.27
	 *
	 * @param mixed $existing the values passed in by the WP Filter API. Ignored here.
	 * @param integer product_id the WordPress post ID
	 * @return string product feature
	*/
	abstract function get_feature( $existing, $product_id, $options=array() );

	/**
	 * Does the product have the feature?
	 *
	 * @since 1.7.27
	 * @param mixed $result Not used by core
	 * @param integer $product_id
	 * @return boolean
	*/
	abstract function product_has_feature( $result, $product_id, $options=array() );

	/**
	 * Does the product support this feature?
	 *
	 * This is different than if it has the feature, a product can
	 * support a feature but might not have the feature set.
	 *
	 * @since 1.7.27
	 * @param mixed $result Not used by core
	 * @param integer $product_id
	 * @return boolean
	*/
	abstract function product_supports_feature( $result, $product_id, $options=array() );
}
