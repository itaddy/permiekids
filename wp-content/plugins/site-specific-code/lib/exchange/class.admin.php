<?php

/**
 *
 * @package LDMW
 * @subpackage Exchange
 * @since 1.0
 */
class LDMW_Exchange_Admin {
	/**
	 *
	 */
	public function __construct() {
		add_action( 'restrict_manage_posts', array( $this, 'add_products_filter' ) );
		add_action( 'pre_get_posts', array( $this, 'filter_products' ) );
	}

	/**
	 * Add a filter to the IT_Exchange products list table.
	 */
	public function add_products_filter() {
		$screen = get_current_screen();

		if ( $screen->post_type != 'it_exchange_prod' )
			return;

		$product_type_addons = it_exchange_get_enabled_addons( array( 'category' => 'product-type' ) );

		?>
		<select id="ldmw-product-type" name="product_type_filter">
				<option value="-1">Show all product types</option>
			<?php foreach ( $product_type_addons as $product_type ) : ?>
				<option value="<?php echo $product_type['slug']; ?>" <?php selected( $product_type['slug'], isset( $_GET['product_type_filter'] ) ) ? $_GET['product_type_filter'] : true; ?>><?php echo $product_type['name']; ?></option>
			<?php endforeach; ?>
		</select>
	<?php
	}

	/**
	 * Filter the posts query to remove IT_Exchange's product types and insert our own.
	 *
	 * @param $query WP_Query
	 */
	public function filter_products( $query ) {
		if ( ! function_exists( 'get_current_screen' ) )
			return;

		$screen = get_current_screen();

		if ( ! is_a( $screen, 'WP_Screen' ) )
			return;

		if ( $screen->post_type != 'it_exchange_prod' || empty( $_GET['product_type_filter'] ) || $_GET['product_type_filter'] == - 1 ) {
			return;
		}

		$query->query_vars['meta_query'][0]['value'] = array( $_GET['product_type_filter'] );
	}
}