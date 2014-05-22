<?php
/**
 * Store class for THEME API
 *
 * @since 0.4.0
*/

class IT_Theme_API_Store implements IT_Theme_API {

	/**
	 * API context
	 * @var string $_context
	 * @since 0.4.0
	*/
	private $_context = 'store';

	/**
	 * Maps api tags to methods
	 * @var array $_tag_map
	 * @since 0.4.0
	*/
	public $_tag_map = array(
		'products' => 'products',
	);

	/**
	 * Constructor
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function IT_Theme_API_Store() {
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
	 * This loops through the products GLOBAL and updates the product global.
	 *
	 * It return false when it reaches the last product
	 * If the has flag has been passed, it just returns a boolean
	 *
	 * @since 0.4.0
	 * @return string
	*/
	function products( $options=array() ) {
		// Return boolean if has flag was set
		if ( $options['has'] )
			return count( it_exchange_get_products() ) > 0 ;

		// If we made it here, we're doing a loop of products for the current query.
		// This will init/reset the products global and loop through them. the /api/theme/product.php file will handle individual products.
		if ( empty( $GLOBALS['it_exchange']['products'] ) ) {
			$settings = it_exchange_get_option( 'settings_general' );
			$GLOBALS['it_exchange']['products'] = it_exchange_get_products( apply_filters( 'it_exchange_store_get_products_args',  array( 'posts_per_page' => -1, 'order' => $settings['store-product-order'], 'orderby' => $settings['store-product-order-by'] ) ) );
			$GLOBALS['it_exchange']['product'] = reset( $GLOBALS['it_exchange']['products'] );
			return true;
		} else {
			if ( next( $GLOBALS['it_exchange']['products'] ) ) {
				$GLOBALS['it_exchange']['product'] = current( $GLOBALS['it_exchange']['products'] );
				return true;
			} else {
				$GLOBALS['it_exchange']['products'] = array();
				end( $GLOBALS['it_exchange']['products'] );
				$GLOBALS['it_exchange']['product'] = false;
				return false;
			}
		}
	}
}
