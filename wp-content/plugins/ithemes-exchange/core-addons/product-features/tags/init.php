<?php
/**
 * Registers IT Exchange Product Tags
 *
 * @package iThemes Exchange
 * @since 0.4.0
 */

if ( !function_exists( 'create_it_exchange_tags' ) ) {

	/**
	 * Registers iThemes Exchange Product Tag Taxonomy
	 *
	 * @since 1.0.0
	 * @uses register_taxonomy()
	 */
	function create_it_exchange_tags() {

		$labels = array(
			'name'              => __( 'Tags', 'it-l10n-ithemes-exchange' ),
			'singular_name'     => __( 'Product Tag', 'it-l10n-ithemes-exchange' ),
			'search_items'      => __( 'Search Product Tags', 'it-l10n-ithemes-exchange' ),
			'all_items'         => __( 'All Product Tags', 'it-l10n-ithemes-exchange' ),
			'parent_item'       => __( 'Parent Product Tags', 'it-l10n-ithemes-exchange' ),
			'parent_item_colon' => __( 'Parent Product Tags:', 'it-l10n-ithemes-exchange' ),
			'edit_item'         => __( 'Edit Product Tags', 'it-l10n-ithemes-exchange' ),
			'update_item'       => __( 'Update Product Tags', 'it-l10n-ithemes-exchange' ),
			'add_new_item'      => __( 'Add New Product Tags', 'it-l10n-ithemes-exchange' ),
			'new_item_name'     => __( 'New Product Tag', 'it-l10n-ithemes-exchange' ),
		);

        // A little hackery for admin --> appearances --> menues page
        if ( is_admin() && ! empty( $GLOBALS['pagenow'] ) && 'nav-menus.php' == $GLOBALS['pagenow'] )
            $labels['name'] = __( 'Exchange Tags', 'it-l10n-ithemes-exchange' );

		register_taxonomy(
			'it_exchange_tag',
			array( 'it_exchange_prod' ),
			array(
				'hierarchical' => false,
				'labels'       => $labels,
				'show_ui'      => true,
				'query_var'    => true,
				'rewrite'      => array( 'slug' => 'product-tag' ),
			)
		);

	}
	add_action( 'init', 'create_it_exchange_tags', 0 );

}

if ( !function_exists( 'it_exchange_tags_add_menu_item' ) ) {

	/**
	 * This adds a menu item to the Exchange menu pointing to the WP All [post_type] table
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function it_exchange_tags_add_menu_item() {
		$url = "edit-tags.php?taxonomy=it_exchange_tag&amp;post_type=it_exchange_prod";
		add_submenu_page( 'it-exchange', __( 'Product Tags', 'it-l10n-ithemes-exchange' ), __( 'Product Tags', 'it-l10n-ithemes-exchange' ), 'update_plugins', $url );
	}
	add_action( 'admin_menu', 'it_exchange_tags_add_menu_item' );

}

if ( !function_exists( 'it_exchange_tags_fix_menu_parent_file' ) ) {

	/**
	 * This fixed the $parent_file variable so that the Exchange top-level menu expands when on the Product Tags page
	 *
	 * @since 0.4.11
	 *
	 * @return void
	*/
	function it_exchange_tags_fix_menu_parent_file() {
		if ( 'it_exchange_tag' == $_GET['taxonomy'] )
			$GLOBALS['parent_file'] = 'it-exchange';
	}
	add_action( 'admin_head-edit-tags.php', 'it_exchange_tags_fix_menu_parent_file' );

}

if ( !function_exists( 'it_exchange_tags_pre_get_posts' ) ) {

	/**
	 * Removes hidden products from product tag queries
	 *
	 * @since 1.7.10
	 *
	 * @return void
	*/
	function it_exchange_tags_pre_get_posts( $query ) {
	    if ( !is_admin() && is_tax( 'it_exchange_tag' ) && ! empty( $query->it_exchange_tag ) ) {
	    	$meta_query = (array) $query->meta_query;
	    	$meta_query[] = array(
	    		'key'   => '_it-exchange-visibility',
	    		'value' => 'visible',
	    	);
	    	$query->set( 'meta_query', $meta_query );
	    }
	}
	add_action( 'pre_get_posts', 'it_exchange_tags_pre_get_posts' );
	
}
