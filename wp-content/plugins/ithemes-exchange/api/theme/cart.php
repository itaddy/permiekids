<?php
/**
 * Cart class for THEME API
 *
 * @since 0.4.0
*/

class IT_Theme_API_Cart implements IT_Theme_API {

	/**
	 * API context
	 * @var string $_context
	 * @since 0.4.0
	*/
	private $_context = 'cart';

	/**
	 * Maps api tags to methods
	 * @var array $_tag_map
	 * @since 0.4.0
	*/
	public $_tag_map = array(
		'cartitems'        => 'cart_items',
		'formopen'         => 'form_open',
		'noncefield'       => 'nonce_field',
		'formclose'        => 'form_close',
		'subtotal'         => 'sub_total',
		'total'            => 'total',
		'update'           => 'update_cart',
		'checkout'         => 'checkout_cart',
		'viewcart'         => 'view_cart',
		'empty'            => 'empty_cart',
		'continueshopping' => 'continue_shopping',
		'multipleitems'    => 'multiple_items',
		'itemcount'        => 'item_count',
		'focus'            => 'focus',
	);

	/**
	 * Constructor
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function IT_Theme_API_Cart() {
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
	 * This loops through the cart session products and updates the cart-item global.
	 *
	 * It return false when it reaches the last item
	 * If the has flag has been passed, it just returns a boolean
	 *
	 * @since 0.4.0
	 * @return string
	*/
	function cart_items( $options=array() ) {
		// Return boolean if has flag was set
		if ( $options['has'] )
			return count( it_exchange_get_cart_products() ) > 0 ;

		// If we made it here, we're doing a loop of products for the current cart.
		// We're accessing the SESSION directly to make looping easier.
		// This will init/reset the SESSION products and loop through them. the /api/theme/cart-item.php file will handle individual products.
		if ( empty( $GLOBALS['it_exchange']['cart-item'] ) ) {
			$GLOBALS['it_exchange']['products'] = it_exchange_get_cart_products();
			$GLOBALS['it_exchange']['cart-item'] = reset( $GLOBALS['it_exchange']['products'] );
			return true;
		} else {
			if ( next( $GLOBALS['it_exchange']['products'] ) ) {
				$GLOBALS['it_exchange']['cart-item'] = current( $GLOBALS['it_exchange']['products'] );
				return true;
			} else {
				$GLOBALS['it_exchange']['products'] = array();
				end( $GLOBALS['it_exchange']['products'] );
				$GLOBALS['it_exchange']['cart-item'] = false;
				return false;
			}
		}
	}

	/**
	 * Prints the opening form field tag for the cart
	 * @since 0.4.0
	*/
	function form_open( $options=array() ) {
		$defaults = array(
			'class' => false,
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$class = it_exchange_in_superwidget() ? 'it-exchange-sw-update-cart' : 'it-exchange-update-cart';
		$class = empty( $options['class'] ) ? $class : $class . ' ' . esc_attr( $options['class'] );
		return '<form action="" method="post" class="' . $class . '">';
	}

	/**
	 * Returns the nonce form field
	 *
	 * @since 0.4.0
	*/
	function nonce_field( $options=array() ) {
		return it_exchange_get_cart_nonce_field();
	}

	/**
	 * Prints the closing form field
	 *
	 * @since 0.4.0
	*/
	function form_close( $options=array() ) {
		$defaults = array(
			'before'        => '',
			'after'         => '',
			'include-nonce' => true,
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$output = $options['before'];
		if ( $options['include-nonce'] )
			$output .= it_exchange_get_cart_nonce_field();

		$output .= '</form>';
		$output .= $options['after'];
		return $output;
	}

	/**
	 * Returns the update cart button / varname
	 *
	 * @since 0.4.0
	*/
	function update_cart( $options=array() ) {
		$defaults = array(
			'before' => '',
			'after'  => '',
			'class'  => 'it-exchange-update-cart',
			'format' => 'button',
			'label'  => __( 'Update Cart', 'it-l10n-ithemes-exchange' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$var = it_exchange_get_field_name( 'update_cart_action' );

		switch( $options['format'] ) {
			case 'var' :
				return $var;
				break;
			case 'button' :
			default :
				$output  = $options['before'];
				$output .= '<input type="submit" class="' . esc_attr( $options['class'] ). '" name="' . esc_attr( $var ) . '" value="' . esc_attr( $options['label'] ) . '" />';
				$output .= $options['after'];
				break;
		}
		return $output;
	}

	/**
	 * Returns the checkout cart button / varname
	 *
	 * @since 0.4.0
	*/
	function checkout_cart( $options=array() ) {
		$defaults = array(
			'before' => '',
			'after'  => '',
			'class'  => false,
			'format' => 'button',
			'label'  => __( 'Checkout', 'it-l10n-ithemes-exchange' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$class = empty( $options['class'] ) ? 'it-exchange-checkout-cart' : 'it-exchange-checkout-cart ' . esc_attr( $options['class'] );
		$class = ( it_exchange_get_cart_products_count() < 2 ) ? $class : $class . ' no-sw-js';
		$var   = it_exchange_get_field_name( 'proceed_to_checkout' );

		// If we're in the superwidget, we need to use that format.
		if ( it_exchange_in_superwidget() )
			$options['format'] = 'link';

		switch( $options['format'] ) {
			case 'var' :
				return $var;
				break;
			case 'link' :
				$url = '';
				// Tack on the superwidget state if in it.
				if ( it_exchange_in_superwidget() && 2 > it_exchange_get_cart_products_count() ) {
					// Get clean URL without any exchange query args
					$url = it_exchange_clean_query_args();
					$url = add_query_arg( 'ite-sw-state', 'checkout', $url );
				} else {
					if ( it_exchange_is_multi_item_cart_allowed() )
						$url = it_exchange_get_page_url( 'checkout' );
				}

				$output  = $options['before'];
				$output .= '<a href="' . $url . '" class="' . $class . '" name="' . esc_attr( $var ) . '">' . esc_attr( $options['label'] ) . '</a>';
				$output .= $options['after'];
				break;
			case 'button' :
			default :
				$output  = $options['before'];
				$output .= '<input type="submit" class="' . $class . '" name="' . esc_attr( $var ) . '" value="' . esc_attr( $options['label'] ) . '" />';
				$output .= $options['after'];
				break;
		}
		return $output;
	}

	/**
	 * Returns the view cart button / varname
	 *
	 * @since 0.4.10
	*/
	function view_cart( $options=array() ) {
		$defaults = array(
			'before' => '',
			'after'  => '',
			'class'  => false,
			'format' => 'button',
			'label'  => __( 'View Cart', 'it-l10n-ithemes-exchange' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$class = empty( $options['class'] ) ? 'it-exchange-view-cart' : 'it-exchange-view-cart ' . esc_attr( $options['class'] );
		$class = ( it_exchange_get_cart_products_count() < 2 ) ? $class : $class . ' no-sw-js';
		$var   = it_exchange_get_field_name( 'view_cart' );

		// If we're in the superwidget, we need to use that format.
		if ( it_exchange_in_superwidget() )
			$options['format'] = 'link';

		switch( $options['format'] ) {
			case 'var' :
				return $var;
				break;
			case 'link' :
				$url = '';
				$url = it_exchange_get_page_url( 'cart' );

				$output  = $options['before'];
				$output .= '<a href="' . $url . '" class="' . $class . '" name="' . esc_attr( $var ) . '">' . esc_attr( $options['label'] ) . '</a>';
				$output .= $options['after'];
				break;
			case 'button' :
			default :
				$output  = $options['before'];
				$output .= '<input type="submit" class="' . $class . '" name="' . esc_attr( $var ) . '" value="' . esc_attr( $options['label'] ) . '" />';
				$output .= $options['after'];
				break;
		}
		return $output;
	}

	/**
	 * Returns the empty cart button / varname
	 *
	 * @since 0.4.0
	*/
	function empty_cart( $options=array() ) {
		$defaults = array(
			'before' => '',
			'after'  => '',
			'class'  => 'it-exchange-empty-cart',
			'format' => 'button',
			'title'  => 'Empty Cart',
			'label'  => __( 'Empty Cart', 'it-l10n-ithemes-exchange' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$var = it_exchange_get_field_name( 'empty_cart' );
		$nonce_var   = apply_filters( 'it_exchange_cart_action_nonce_var', '_wpnonce' );

		switch( $options['format'] ) {
			case 'var' :
				return $var;
				break;
			case 'link' :
				// Get clean url without any exchange query args
				$url = it_exchange_clean_query_args();
				$url = add_query_arg( $var, 1, $url );
				$url = add_query_arg( $nonce_var, wp_create_nonce( 'it-exchange-cart-action-' . it_exchange_get_session_id() ), $url );
				$output  = $options['before'];
				$output .= '<a href="' . $url . '" class="' . esc_attr( $options['class'] ) . '" title="' . esc_attr( $options['title'] ) . '">' . esc_attr( $options['label'] ) . '</a>';
				$output .= $options['after'];
				break;
			case 'button' :
			default :
				$output  = $options['before'];
				$output .= '<input type="submit" class="' . esc_attr( $options['class'] ). '" name="' . esc_attr( $var ) . '" value="' . esc_attr( $options['label'] ) . '" />';
				$output .= $options['after'];
				break;
		}
		return $output;
	}

	/**
	 * Returns the continue Shopping button/link/var
	 *
	 * @since 1.8.1
	 *
	 * @param array $options
	 * @return mixed
	*/
	function continue_shopping( $options=array() ) {
		$defaults = array(
			'before' => '',
			'after'  => '',
			'class'  => 'it-exchange-continue-shopping',
			'format' => 'button',
			'title'  => __( 'Continue Shopping', 'it-l10n-ithemes-exchange' ),
			'label'  => __( 'Continue Shopping', 'it-l10n-ithemes-exchange' ),
		);
		$options = wp_parse_args( $options, $defaults );

		if ( ! empty( $options['has'] ) ) {
			if ( ! it_exchange_is_multi_item_cart_allowed() )
				return false;

			if ( 'disabled' == it_exchange_get_page_type('store') )
				return false;

			$multi_item_cart_settings = it_exchange_get_option( 'addon_multi_item_cart', true );
			if ( ! isset( $multi_item_cart_settings['show-continue-shopping-button'] ) )
				return true;

			return ! empty( $multi_item_cart_settings['show-continue-shopping-button'] );
		}

		$var = it_exchange_get_field_name( 'continue_shopping' );
		$nonce_var   = apply_filters( 'it_exchange_cart_action_nonce_var', '_wpnonce' );

		switch( $options['format'] ) {
			case 'var' :
				return $var;
				break;
			case 'link' :
				// Get clean url without any exchange query args
				$url = it_exchange_clean_query_args();
				$url = add_query_arg( $var, 1, $url );
				$url = add_query_arg( $nonce_var, wp_create_nonce( 'it-exchange-cart-action-' . it_exchange_get_session_id() ), $url );
				$output  = $options['before'];
				$output .= '<a href="' . $url . '" class="' . esc_attr( $options['class'] ) . '" title="' . esc_attr( $options['title'] ) . '">' . esc_attr( $options['label'] ) . '</a>';
				$output .= $options['after'];
				break;
			case 'button' :
			default :
				$output  = $options['before'];
				$output .= '<input type="submit" class="' . esc_attr( $options['class'] ). '" name="' . esc_attr( $var ) . '" value="' . esc_attr( $options['label'] ) . '" />';
				$output .= $options['after'];
				break;
		}
		return $output;
	}

	/**
	 * Returns the subtotal of the cart
	 *
	 * @since 0.4.0
	 *
	 * @param array $options
	 * @return string
	*/
	function sub_total( $options=array() ) {
		$defaults = array(
			'format' => true,
		);
		$options = ITUtility::merge_defaults( $options, $defaults );
		if ( 'false' === $options['format'] )
			$options['format'] = false;

		return it_exchange_get_cart_subtotal( $options['format'] );
	}

	/**
	 * Returns the total for the cart
	 *
	 * @since 0.4.0
	 *
	 * @param array $options
	 * @return string
	*/
	function total( $options=array() ) {
		$defaults = array(
			'format' => true,
		);
		$options = ITUtility::merge_defaults( $options, $defaults );
		if ( 'false' === $options['format'] )
			$options['format'] = false;

		return it_exchange_get_cart_total( $options['format'] );
	}

	/**
	 * Does the cart support multiple items?
	 *
	 * @since 0.4.0
	 *
	 * @param array $options
	 * @return boolean
	*/
	function multiple_items( $options=array() ) {
		return it_exchange_is_multi_item_cart_allowed();
	}

	/**
	 * Returns the number of items in the cart
	 *
	 * @since 0.4.0
	 *
	 * @return integer
	*/
	function item_count() {
		return it_exchange_get_cart_products_count();
	}

	/**
	 * Return the current focus if indicated
	 *
	 * @since 0.4.0
	 *
	 * @return string
	*/
	function focus( $options=array() ) {
		$defaults = array(
			'type'  => false,
		);
		$options = ITUtility::merge_defaults( $options, $defaults );
		$focus_key = it_exchange_get_field_name( 'sw_cart_focus' );

		// Get the focus from REQUEST
		$focus = empty( $_REQUEST[$focus_key] ) ? false : $_REQUEST[$focus_key];

		// Return the focus if option is false and focus is set
		if ( empty( $options['type'] ) && ! empty( $focus ) )
			return $focus;

		// Return false if $focus is false or if $options['type'] is false
		if ( ! $options['type'] || ! $focus )
			return false;

		// return boolean if focus == type
		return $focus == $options['type'];
	}
}
