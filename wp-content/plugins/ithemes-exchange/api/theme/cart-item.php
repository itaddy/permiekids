<?php
/**
 * Cart Item class for THEME API
 *
 * @since 0.4.0
*/

class IT_Theme_API_Cart_Item implements IT_Theme_API {

	/**
	 * API context
	 * @var string $_context
	 * @since 0.4.0
	*/
	private $_context = 'cart-item';

	/**
	 * The current cart item
	 * @var array
	 * @since 0.4.0
	*/
	public $_cart_item = false;

	/**
	 * Maps api tags to methods
	 * @var array $_tag_map
	 * @since 0.4.0
	*/
	public $_tag_map = array(
		'title'            => 'title',
		'remove'           => 'remove',
		'quantity'         => 'quantity',
		'price'            => 'price',
		'subtotal'         => 'sub_total',
		'purchasequantity' => 'supports_purchase_quantity',
		'permalink'        => 'permalink',
		'featuredimage'    => 'featured_image',
		'images'           => 'product_images',
	);

	/**
	 * Constructor
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function IT_Theme_API_Cart_Item() {
		$this->_cart_item = empty( $GLOBALS['it_exchange']['cart-item'] ) ? false : $GLOBALS['it_exchange']['cart-item'];
	}

	/**
	 * Returns the context. Also helps to confirm we are an iThemes Exchange theme API class
	 *
	 * @since 0.4.0
	 *
	 * @return string
	*/
	function get_api_context() {
		return $this->_context;
	}

	/**
	 * Returns the remove from cart element / var based on format option
	 *
	 * @since 0.4.0
	 *
	*/
	function remove( $options=array() ) {

		// Set options
		$defaults      = array(
			'before' => '',
			'after'  => '',
			'format' => 'html',
			'class'  => false,
			'label'  => __( '&times;', 'it-l10n-ithemes-exchange' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		// Force link in SuperWidget
		$options['format'] = it_exchange_in_superwidget() ? 'link' : $options['format'];

		$var_key = it_exchange_get_field_name( 'remove_product_from_cart' );
		$var_value = $this->_cart_item['product_cart_id'];
		$core_class = ( it_exchange_is_multi_item_cart_allowed() && it_exchange_get_cart_products_count() > 1 ) ? 'remove-cart-item' : 'it-exchange-empty-cart';
		$class = empty( $options['class'] ) ? $core_class : $core_class . ' ' . esc_attr( $options['class'] );

		switch ( $options['format'] ) {
			case 'var_key' :
				$output = $var_key;
				break;
			case 'var_value' :
				$output = $var_value;
				break;
			case 'checkbox' :
				$output = $options['before'] . '<input type="checkbox" name="' . esc_attr( $var_key ) . '[]" value="' . esc_attr( $var_value ) . '" class="' . $class . '" />' . $options['label'] . $options['after'];
				break;
			case 'link' :
			default :
				$data = it_exchange_in_superwidget() ? 'data-cart-product-id="' . esc_attr( $var_value ) . '" ' : '';
				$nonce_var = apply_filters( 'it_exchange_remove_product_from_cart_nonce_var', '_wpnonce' );
				$session_id = it_exchange_get_session_id();
				$url = it_exchange_clean_query_args();
				$url = add_query_arg( $var_key, $var_value, $url );
				$url = add_query_arg( $nonce_var, wp_create_nonce( 'it-exchange-cart-action-' . $session_id ), $url );
				$output = $options['before'] . '<a href="' . $url . '" ' . $data . 'class="' . $class . '" >' . esc_attr( $options['label'] ) . '</a>' . $options['after'];
			break;
		}
		return $output;
	}

	/**
	 * Returns the title element / var based on format option
	 *
	 * @since 0.4.0
	 *
	*/
	function title( $options=array() ) {
		// Set options
		$defaults = array(
			'before' => '',
			'after'  => '',
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		return $options['before'] . it_exchange_get_cart_product_title( $this->_cart_item ) . $options['after'];
	}

	/**
	 * Returns the quantity element / var based on format option
	 *
	 * @since 0.4.0
	 *
	*/
	function quantity( $options=array() ) {
		// Set options
		$defaults = array(
			'before' => '',
			'after'  => '',
			'format' => 'text-field',
			'class'  => 'product-cart-quantity',
			'label'  => '',
		);
		$options = ITUtility::merge_defaults( $options, $defaults );
		$var_key = it_exchange_get_field_name( 'product_purchase_quantity' );
		$var_value = it_exchange_get_cart_product_quantity( $this->_cart_item );
		$max_quantity = it_exchange_get_product_feature( $this->_cart_item['product_id'], 'purchase-quantity' );

		if ( it_exchange_product_supports_feature( $this->_cart_item['product_id'], 'inventory' ) ) {

			$inventory = (int)it_exchange_get_product_feature( $this->_cart_item['product_id'], 'inventory' );

			if ( $inventory && (int) $max_quantity > 0 && (int) $max_quantity > $inventory )
				$max_quantity = $inventory;

		}

		if ( (int) $max_quantity > 0 && $var_value > $max_quantity )
			$var_value = $max_quantity;

		switch ( $options['format'] ) {
			case 'var_key' :
				$output = $var_key;
				break;
			case 'var_value' :
				$output = $var_value;
				break;
			case 'text-field' :
			default :
				$output  = $options['before'];
				if ( it_exchange_product_supports_feature( $this->_cart_item['product_id'], 'purchase-quantity' ) ) {
					$max = ! empty( $max_quantity ) ? 'max="' . $max_quantity . '"' : '';
					$output .= '<input type="number" min="1" ' . $max . ' data-cart-product-id="' . esc_attr( $this->_cart_item['product_cart_id'] ) . '" name="' . esc_attr( $var_key ) . '[' . esc_attr( $this->_cart_item['product_cart_id'] ) . ']" value="' . esc_attr( $var_value ) . '" class="' . esc_attr( $options['class'] ) . '" />';
				} else {
					$output .= '1';
					$output .= '<input type="hidden" name="' . esc_attr( $var_key ) . '[' . esc_attr( $this->_cart_item['product_cart_id'] ) . ']" value="' . esc_attr( $var_value ) . '" class="' . esc_attr( $options['class'] ) . '" />';
				}
				$output .= $options['after'];
				break;
			break;
		}

		return $output;
	}

	/**
	 * Returns the price element / var based on format option
	 *
	 * @since 0.4.0
	 *
	*/
	function price( $options=array() ) {
		return it_exchange_get_cart_product_base_price( $this->_cart_item );
	}

	/**
	 * Returns the subtotal for the cart item (price * quantity)
	 *
	 * @since 0.4.0
	 *
	 * @return string
	*/
	function sub_total( $options=array() ) {
		return it_exchange_get_cart_product_subtotal( $this->_cart_item );
	}

	/**
	 * Returns boolean. Does this cart item support a purchase quantity
	 *
	 * @since 0.4.0
	 *
	*/
	function supports_purchase_quantity( $options=array() ) {
		return it_exchange_product_supports_feature( $this->_cart_item['product_id'], 'purchase-quantity' );
	}

	/**
	 * Returns URL for cart item
	 *
	 * @since 0.4.4
	 *
	*/
	function permalink( $options=array() ) {
		return get_permalink( $this->_cart_item['product_id'] );
	}

	/**
	 * The product's product images
	 *
	 * @since 0.4.12
	 *
	 * @return string
	*/
	function product_images( $options=array() ) {

		// Get the real product item or return empty
		if ( ! $product_id = empty( $this->_cart_item['product_id'] ) ? false : $this->_cart_item['product_id'] )
			return false;

		// Return boolean if has flag was set
		if ( $options['supports'] )
			return it_exchange_product_supports_feature( $product_id, 'product-images' );

		// Return boolean if has flag was set
		if ( $options['has'] )
			return it_exchange_product_has_feature( $product_id, 'product-images' );

		if ( it_exchange_product_supports_feature( $product_id, 'product-images' )
				&& it_exchange_product_has_feature( $product_id, 'product-images' ) ) {

			$defaults = array(
				'size' => 'all'
			);

			$options = ITUtility::merge_defaults( $options, $defaults );
			$output = array();

			$image_sizes = get_intermediate_image_sizes();

			$product_images = it_exchange_get_product_feature( $product_id, 'product-images' );

			foreach( $product_images as $image_id ) {
				foreach ( $image_sizes as $size ) {
					$images[$size] = wp_get_attachment_image_src( $image_id, $size );
				}
			}

			$images['full'] = wp_get_attachment_image_src( $image_id, 'full' );

			if ( $options['size'] == 'all' ) {
				$output = $images;
			} else {
				if ( isset( $images[ $options['size'] ] ) )
					$output = $images[ $options['size'] ];
				else if ( $options['size'] == 'full' )
					$output = $images['full'];
				else
					$output = __( 'Unregisterd image size.', 'it-l10n-ithemes-exchange' );
			}

			return $output;
		}
		return false;
	}

	/**
	 * The product's featured image
	 *
	 * @since 0.4.12
	 *
	 * @return string
	*/
	function featured_image( $options=array() ) {

		// Get the real product item or return empty
		if ( ! $product_id = empty( $this->_cart_item['product_id'] ) ? false : $this->_cart_item['product_id'] )
			return false;

		// Return boolean if has flag was set
		if ( $options['supports'] )
			return it_exchange_product_supports_feature( $product_id, 'product-images' );

		// Return boolean if has flag was set
		if ( $options['has'] )
			return it_exchange_product_has_feature( $product_id, 'product-images' );

		if ( it_exchange_product_supports_feature( $product_id, 'product-images' )
				&& it_exchange_product_has_feature( $product_id, 'product-images' ) ) {

			$defaults = array(
				'size' => 'thumbnail'
			);

			$options = ITUtility::merge_defaults( $options, $defaults );
			$output = array();

			$product_images = it_exchange_get_product_feature( $product_id, 'product-images' );

			$feature_image = array(
				'id'    =>  $product_images[0],
				'thumb' => wp_get_attachment_thumb_url( $product_images[0] ),
				'large' => wp_get_attachment_url( $product_images[0] )
			);

			if ( 'thumbnail' === $options['size'] )
				$img_src = $feature_image['thumb'];
			else
				$img_src = $feature_image['large'];

			ob_start();
			?>
				<div class="it-exchange-feature-image-<?php echo get_the_id(); ?> it-exchange-featured-image">
					<div class="featured-image-wrapper">
						<img alt="" src="<?php echo $img_src ?>" data-src-large="<?php echo $feature_image['large'] ?>" data-src-thumb="<?php echo $feature_image['thumb'] ?>" />
					</div>
				</div>
			<?php
			$output = ob_get_clean();

			return $output;
		}

		return false;
	}
}
