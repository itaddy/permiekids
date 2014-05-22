<?php
/**
 * Adds our Ghost pages as options in the nav menu admin page
 *
 * @since 0.4.0
 * @package IT_Exchange
*/

/**
 * IT_Exchange_Nav_Menu_Meta_Box class
 *
 * @since 0.4.0
*/
class IT_Exchange_Nav_Menu_Meta_Box {

	function IT_Exchange_Nav_Menu_Meta_Box() {
		if ( ! is_admin() )
			return;

		add_action( 'admin_init', array( $this, 'register_meta_box' ) );
		add_filter( 'wp_setup_nav_menu_item', array( $this, 'setup_menu_item' ), 10, 2 );
		add_filter( 'get_user_option_metaboxhidden_nav-menus', array( $this, 'show_exchange_menu' ), 10, 3 );
	}

	/**
	 * Registers the meta box for the nav page
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function register_meta_box() {
		add_meta_box( 'it-exchange-pages', __( 'iThemes Exchange' ), array( $this, 'print_meta_box' ), 'nav-menus', 'side', 'default' );
	}

	/**
	 * Prints the meta box
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function print_meta_box( $object ) {
		global $nav_menu_selected_id;
		$pages = it_exchange_get_pages();

		$terms = array();
		foreach( $pages as $page => $data ) {

			if ( in_array( $page, array( 'transaction', 'product', 'confirmation', 'logout' ) ) )
				continue;

			$page_slug = it_exchange_get_page_slug( $page );
			$page_name = it_exchange_get_page_name( $page );

			if ( ! $page_slug || ! $page_name )
				continue;

			$object          = new stdClass();
			$object->type    = 'it-exchange-ghost-page';
			$object->name    = $page_name;
			$object->setting = $page;
			$object->slug    = $page_slug;
			$terms[] = $object;
		}

		$walker = new Walker_Nav_Menu_Checklist( false );
		$removed_args = array(
			'action',
			'customlink-tab',
			'edit-menu-item',
			'menu-item',
			'page-tab',
			'_wpnonce',
		);
		?>
		<div id="taxonomy-it-exchange-pages" class="taxonomydiv">
			<ul id="taxonomy-it-exchange-pages-tabs" class="taxonomy-tabs add-menu-item-tabs">
				<li class="tabs">
					<a class="nav-tab-link" data-type="tabs-panel-it-exchange-pages-all" href="<?php if ( $nav_menu_selected_id ) echo esc_url(add_query_arg( 'it-exchange-pages' . '-tab', 'all', remove_query_arg( $removed_args ) ) ); ?>#tabs-panel-it-exchange-pages-all">
						<?php _e( 'View All' ); ?>
					</a>
				</li>
			</ul><!-- .taxonomy-tabs -->

			<div id="tabs-panel-it-exchange-pages-all" class="tabs-panel tabs-panel-view-all tabs-panel-active">
				<ul id="it-exchange-pageschecklist" data-wp-lists="list:it-exchange-pages" class="categorychecklist form-no-clear">
					<?php
					$args['walker'] = $walker;
					echo walk_nav_menu_tree( array_map('wp_setup_nav_menu_item', $terms), 0, (object) $args );
					?>
				</ul>
			</div><!-- /.tabs-panel -->

			<p class="button-controls">
				<span class="list-controls">
					<a href="<?php
						echo esc_url(add_query_arg(
							array(
								'it-exchange-pages-tab' => 'all',
								'selectall' => 1,
							),
							remove_query_arg( $removed_args )
						));
					?>#taxonomy-it-exchange-pages" class="select-all"><?php _e('Select All'); ?></a>
				</span>

				<span class="add-to-menu">
					<input type="submit"<?php wp_nav_menu_disabled_check( $nav_menu_selected_id ); ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e( __( 'Add to Menu' ) ); ?>" name="add-taxonomy-menu-item" id="<?php esc_attr_e( 'submit-taxonomy-it-exchange-pages' ); ?>" />
					<span class="spinner"></span>
				</span>
			</p>
		</div>
		<?php
	}

	/**
	 * Sets up the menu item for the walker
	 *
	 * @since 0.4.0
	 * @return object
	*/
	function setup_menu_item( $menu_item ) {
		$type = empty( $menu_item->type ) ? false : $menu_item->type;
		if ( 'it-exchange-ghost-page' != $type )
			return $menu_item;

		$menu_item->ID = $menu_item->slug;
		$menu_item->db_id = 0;
		$menu_item->menu_item_parent = 0;
		$menu_item->object_id = $menu_item->slug;
		$menu_item->post_parent = (int) 0;
		$menu_item->type = 'custom';

		$menu_item->object = $menu_item->name;
		$menu_item->type_label = $menu_item->name;

		$menu_item->title = $menu_item->name;
		$menu_item->url = $this->get_url( $menu_item->setting );
		$menu_item->target = '';
		$menu_item->attr_title = '';
		$menu_item->description = 'test';
		$menu_item->classes = array();
		$menu_item->xfn = 'it-exchange-' . esc_attr( $menu_item->setting );

		return $menu_item;
	}

	/**
	 * Creates a guid based on the current view
	 *
	 * @since 0.4.0
	 *
	 * @param string $setting setting page
	 * @return string
	*/
	function get_url( $setting ) {
		return it_exchange_get_page_url( $setting, true );
	}

	/**
	 * Unhides the exchange nav items
	 *
	 * @since 0.4.16
	 *
	 * @param mixed $result the result about to be passed back to WP
	 *
	*/
	function show_exchange_menu( $result, $option, $user ) {
		// Get user ID from user object
		$user_id = empty( $user->ID ) ? false : $user->ID;

		// If false, that means the user hasn't had usermeta updated before, so were going to whitelist core defaults, plus ours.
		if ( false === $result ) {
			$result = array(
				'nav-menu-theme-locations',
				'add-page',
				'add-custom-links',
				'add-category',
				'it-exchange-pages',
			);

			$initial_meta_boxes = $result;
			$hidden_meta_boxes = array();
			foreach ( array_keys($GLOBALS['wp_meta_boxes']['nav-menus']) as $context ) {
				foreach ( array_keys($GLOBALS['wp_meta_boxes']['nav-menus'][$context]) as $priority ) {
					foreach ( $GLOBALS['wp_meta_boxes']['nav-menus'][$context][$priority] as $box ) {
						if ( in_array( $box['id'], $initial_meta_boxes ) ) {
							unset( $box['id'] );
						} else {
							$hidden_meta_boxes[] = $box['id'];
						}
					}
				}
			}
			if ( ! empty( $user_id ) )
				update_user_meta( $user_id, 'metaboxhidden_nav-menus', $hidden_meta_boxes, true );

		}
		return $result;
	}
}
$IT_Exchange_Nav_Menu_Meta_Box = new IT_Exchange_Nav_Menu_Meta_Box();
