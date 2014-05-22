<?php

/**
 * Generates and returns the Duplicate Product URL
 *
 * @since 1.1.2
 *
 * @params array $actions Current post row actions
 * @params object $post Current post object
 * @return array
*/
function it_exchange_duplicate_product_addon_get_duplicating_url( $post ) {
	$product_type = it_exchange_get_product_type( $post );

	$args = array(
		'post_type'                        => 'it_exchange_prod',
		'it-exchange-product-type'         => $product_type,
		'it-exchange-duplicate-product-id' => $post->ID,
	);

	return add_query_arg( $args, admin_url( 'post-new.php' ) );
}

/**
 * Add Duplicate action from IT Exchange Post Types
 *
 * @since 1.1.2
 *
 * @params array $actions Current post row actions
 * @params object $post Current post object
 * @return array
*/
function it_exchange_duplicate_product_addon_add_duplicate_product_function( $actions, $post ) {
	if ( in_array( $post->post_type, array( 'it_exchange_prod' ) ) ) {

		$url = it_exchange_duplicate_product_addon_get_duplicating_url( $post );

		$actions['it_exchange_duplicate'] =  '<a class="duplicate_product_it_exchange_product" id="' . $post->ID . '" title="' . __( 'Duplicate Exchange Products', 'it-l10n-ithemes-exchange' ) . '" href="' . $url . '">Duplicate</a>';

	}

	return $actions;
}
add_filter( 'post_row_actions', 'it_exchange_duplicate_product_addon_add_duplicate_product_function', 10, 2 );

/**
 * Copies previous product content to new product
 *
 * @since 1.1.2
 *
 * @params string $post_content Current WordPress default post content
 * @params object $post Current post object
 * @return array
*/
function it_exchange_duplicate_product_addon_default_product_content( $post_content, $post ) {

	if ( !empty( $_REQUEST['it-exchange-duplicate-product-id'] )
		&& $duplicate_product_post_id = $_REQUEST['it-exchange-duplicate-product-id'] ) {

		$duplicate_product_post = get_post( $duplicate_product_post_id );

		$post_content = $duplicate_product_post->post_content;

	}

	return $post_content;

}
add_filter( 'default_content', 'it_exchange_duplicate_product_addon_default_product_content', 10, 2 );

/**
 * Copies previous product title to new product
 *
 * @since 1.1.2
 *
 * @params string $post_content Current WordPress default post title
 * @params object $post Current post object
 * @return array
*/
function it_exchange_duplicate_product_addon_default_product_title( $post_title, $post ) {

	if ( !empty( $_REQUEST['it-exchange-duplicate-product-id'] )
		&& $duplicate_product_post_id = $_REQUEST['it-exchange-duplicate-product-id'] ) {

		$duplicate_product_post = get_post( $duplicate_product_post_id );

		$post_title = $duplicate_product_post->post_title . ' - ' . __( 'copy', 'it-l10n-ithemes-exchange' );

	}

	return $post_title;

}
add_filter( 'default_title', 'it_exchange_duplicate_product_addon_default_product_title', 10, 2 );

/**
 * Copies previous product content to new excerpt (not really used in Exchange)
 *
 * @since 1.1.2
 *
 * @params string $post_content Current WordPress default post excerpt
 * @params object $post Current post object
 * @return array
*/
function it_exchange_duplicate_product_addon_default_product_excerpt( $post_excerpt, $post ) {

	if ( !empty( $_REQUEST['it-exchange-duplicate-product-id'] )
		&& $duplicate_product_post_id = $_REQUEST['it-exchange-duplicate-product-id'] ) {

		$duplicate_product_post = get_post( $duplicate_product_post_id );

		$post_excerpt = $duplicate_product_post->post_excerpt;

	}

	return $post_excerpt;

}
add_filter( 'default_excerpt', 'it_exchange_duplicate_product_addon_default_product_excerpt', 10, 2 );

/**
 * Copies previous product meta to new product
 *
 * @since 1.1.2
 *
 * @params string $post_type Current WordPress default post type (ignored)
 * @params object $post Current post object
 * @return array
*/
function it_exchange_duplicate_product_addon_default_product_meta( $post_type, $post ) {

	if ( !empty( $_REQUEST['it-exchange-duplicate-product-id'] )
		&& $duplicate_product_post_id = $_REQUEST['it-exchange-duplicate-product-id'] ) {

		$duplicate_product_post_meta = get_post_meta( $duplicate_product_post_id );

		foreach ( $duplicate_product_post_meta as $key => $values ) {

			foreach ( $values as $value ) {

				//We do not want to copy ALL of the post meta, some of it is specific to transaction history, etc.
				if ( in_array( $key, apply_filters( 'it_exchange_duplicate_product_addon_default_product_meta_invalid_keys', array( '_edit_lock', '_edit_last', '_it_exchange_transaction_id' ) ) ) )
					continue;

				$value = maybe_unserialize( $value );

				add_post_meta( $post->ID, $key, $value );

				//Other add-ons might need to perform some extra actions with this new post meta (e.g. Membership)
				do_action( 'it_exchange_duplicate_product_addon_add_product_meta', $post, $key, $value );

			}

		}

		do_action( 'it_exchange_duplicate_product_addon_default_product_meta', $post, $duplicate_product_post_id );

	}

}
add_action( 'add_meta_boxes', 'it_exchange_duplicate_product_addon_default_product_meta', 10, 2 );