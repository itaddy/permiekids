<?php
/**
 * Creates the post type for Coupons
 *
 * @package IT_Exchange
 * @since 0.4.0
*/

/**
 * Registers the it_exchange_coupon post type
 *
 * @since 0.4.0
*/
class IT_Exchange_Coupon_Post_Type {

	/**
	 * Class Constructor
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function IT_Exchange_Coupon_Post_Type() {
		$this->init();
		add_action( 'save_post', array( $this, 'save_coupon' ) );
	}

	function init() {
		$this->post_type = 'it_exchange_coupon';
		$labels    = array(
			'name'          => __( 'Coupons', 'it-l10n-ithemes-exchange' ),
			'singular_name' => __( 'Coupon', 'it-l10n-ithemes-exchange' ),
			'edit_item'     => __( 'Edit Coupon', 'it-l10n-ithemes-exchange' ),
		);

		// We're not going to add this to the menu. Individual add-ons will need to do that.
		$this->options = array(
			'labels'               => $labels,
			'description'          => __( 'An iThemes Exchange Post Type for storing all Coupons in the system', 'it-l10n-ithemes-exchange' ),
			'public'               => false,
			'show_ui'              => true,
			'show_in_nav_menus'    => false,
			'show_in_menu'         => false,
			'show_in_admin_bar'    => false,
			'hierarchical'         => false,
			'register_meta_box_cb' => array( $this, 'meta_box_callback' ),
			'supports'             => array( // We'll register them all but the core add-on for basic coupons isn't using WP add/edit screen.
				'title',
				'editor',
				'author',
				'thumbnail',
				'excerpt',
				'trackbacks',
				'custom-fields',
				'comments',
				'revisions',
				'post-formats',
			), // We're going to build our own add / edit screen
			'capabilities'         => array(
				'edit_posts'        => 'edit_posts',
				'edit_others_posts' => 'edit_others_posts',
				'publish_posts'     => 'publish_posts',
			),
			'map_meta_cap'         => true,
			'capability_type'      => 'post',
		);

		add_action( 'init', array( $this, 'register_the_post_type' ) );
	}

	/**
	 * Actually registers the post type
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function register_the_post_type() {
		register_post_type( $this->post_type, $this->options );
	}

	/**
	 * Callback hook for coupon post type admin views
	 *
	 * @since 0.4.0
	 * @uses it_exchange_get_enabled_add_ons()
	 * @return void
	*/
	function meta_box_callback( $post ) {
		$coupon = it_exchange_get_coupon( $post );

		// Do action for any product type
		do_action( 'it_exchange_coupon_metabox_callback', $coupon );
	}

	/**
	 * Provides specific hooks for when iThemes Exchange coupons are saved.
	 *
	 * This method is hooked to save_post. It provides hooks for add-on developers
	 * that will only be called when the post being saved is an iThemes Exchange coupon.
	 * It provides the following 4 hooks:
	 * - it_exchange_save_coupon_unvalidated // Runs every time an iThemes Exchange coupon is saved.
	 * - it_exchange_save_coupon             // Runs every time an iThemes Exchange coupon is saved if not an autosave and if user has permission to save post
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function save_coupon( $post ) {

		// Exit if not it_exchange_prod post_type
		if ( ! 'it_exchange_coupon' == get_post_type( $post ) )
			return;

		// These hooks fire off any time a it_exchange_coupon post is saved w/o validations
		do_action( 'it_exchange_save_coupon_unvalidated', $post );

		// Fire off actions with validations that most instances need to use.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return;

		if ( ! current_user_can( 'edit_post', $post ) )
			return;

		// This is called any time save_post hook
		do_action( 'it_exchange_save_coupon', $post );
	}
}
$IT_Exchange_Coupon_Post_Type = new IT_Exchange_Coupon_Post_Type();
