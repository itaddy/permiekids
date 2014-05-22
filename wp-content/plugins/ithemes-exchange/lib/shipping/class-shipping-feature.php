<?php
/**
 * Extend this class to create new shipping features to use with your shipping add-on
*/

abstract class IT_Exchange_Shipping_Feature {

	/**
	 * @var string $slug the identifying key of the shipping feature
	*/
	var $slug;

	/**
	 * @var object $product the current product object
	*/
	var $product  = false;

	/**
	 * @var boolean $available is this shipping feature available to the current product
	*/
	var $available = false;

	/**
	 * @var boolean $enabled is this shipping feature enabled for the current product
	*/
	var $enabled  = false;

	/**
	 * @var object $values the stored values for the current product
	*/
	var $values   = false;

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

		// Set the product
		$this->set_product( $product_id );

		add_action( 'it_exchange_update_shipping_features_on_product_save', array( $this, 'update_on_product_save' ) );

		// Set the availability of this feature to this product
		$this->set_availability();

		// Set value of the enabled property for this product
		$this->set_enabled();

		// Set the shipping feature values for the current product
		$this->set_values();
	}

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
	 * Is this feature available to this product
	 *
	 *
	 * The shipping feature is required to extend override this method in the extended class.
	 * It needs to then determine if the shipping feature
	 * @since 1.4.0
	 *
	 * @return void
	*/
	abstract function set_availability();

	/**
	 * Is this feature enabled for this product
	 *
	 * @since 1.4.0
	 *
	 * @return void
	*/
	abstract function set_enabled();

	/**
	 * Neds to set the values property.
	 *
	 * @since 1.4.0
	*/
	abstract function set_values();

	abstract function update_on_product_save();

	abstract function update_value( $new_value );

	/**
	 * Prints the shipping box on the add/edit product page
	 *
	 * Relies on methods provided by extending classes
	 * If the shipping feature isn't availabe to this product, it is hidden
	 *
	 * @since 1.4.0
	 *
	 * @return void
	*/
	function print_add_edit_feature_box() {
		?>
		<div class="shipping-feature <?php esc_attr_e( $this->slug );  echo empty( $this->available ) ? ' hidden' : ''; ?>">
			<?php $this->print_add_edit_feature_box_interior(); ?>
		</div>
		<?php
	}
}
