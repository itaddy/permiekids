<?php
/**
 * Creates the post type for Products
 *
 * @package IT_Exchange
 * @since 0.3.0
*/

/**
 * Registers the it_exchange_prod post type
 *
 * @since 0.3.0
*/
class IT_Exchange_Product_Post_Type {

	/**
	 * Class Constructor
	 *
	 * @since 0.3.0
	 * @return void
	*/
	function IT_Exchange_Product_Post_Type() {
		$this->init();

		add_action( 'template_redirect', array( $this, 'load_product' ) );
		add_action( 'save_post_it_exchange_prod', array( $this, 'save_product' ) );
		add_action( 'admin_init', array( $this, 'set_add_new_item_label' ) );
		add_action( 'admin_init', array( $this, 'set_edit_item_label' ) );
		add_action( 'it_exchange_save_product_unvalidated', array( $this, 'set_initial_post_product_type' ) );
		add_action( 'admin_head-edit.php', array( $this, 'modify_post_new_file' ) );
		add_action( 'admin_head-post.php', array( $this, 'modify_post_new_file' ) );
		add_filter( 'manage_edit-it_exchange_prod_columns', array( $this, 'it_exchange_product_columns' ), 999 );
		add_filter( 'manage_edit-it_exchange_prod_sortable_columns', array( $this, 'it_exchange_product_sortable_columns' ) );
		add_filter( 'manage_it_exchange_prod_posts_custom_column', array( $this, 'it_exchange_prod_posts_custom_column_info' ) );
		add_filter( 'request', array( $this, 'modify_wp_query_request_on_edit_php' ) );
		add_filter( 'wp_insert_post_empty_content', array( $this, 'wp_insert_post_empty_content' ), 20, 2 );
		add_filter( 'post_updated_messages', array( $this, 'product_updated_messages' ) );
		add_action( 'it_exchange_add_edit_product_screen_layout_setup', array( $this, 'replace_core_slug_metabox' ) );
		add_action( 'it_exchange_save_wizard_settings', array( $this, 'create_sample_product' ) );

		if ( is_admin() && !empty( $_REQUEST['post_type'] ) && 'it_exchange_prod' === $_REQUEST['post_type'] )
			add_action( 'pre_get_posts', array( $this, 'remove_disabled_product_types_from_admin_list' ) );
	}
	
	/**
	 * Load IT Exchange Product if looking at a single product page
	 *
	 * @since 0.4.4
	*/
	function load_product() {
		if ( ! is_admin() ) {
			if ( is_singular( 'it_exchange_prod' ) ) {
				global $post;
				$GLOBALS['it_exchange']['product'] = it_exchange_get_product( $post );
			}
		}
	}

	/**
	 * Removed disabled product-types from the list
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function remove_disabled_product_types_from_admin_list( $query ) {
		$post_type = $query->get( 'post_type' );
		if ( is_admin() && ! empty( $post_type ) && 'it_exchange_prod' == $post_type ) {

			// Preserve existing meta_query
			$meta_query = $query->get( 'meta_query' );

			// Add ours to existing
			$meta_query[] = array(
				'key'   => '_it_exchange_product_type',
				'value' => array_keys( it_exchange_get_enabled_addons( array( 'category' => 'product-type' ) ) ),
			);
			$query->set( 'meta_query', $meta_query );
		}
	}

	/**
	 * Allows empty title/description/excerpt products to be published to WP
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function wp_insert_post_empty_content( $maybe_empty, $postarr ) {

		if ( !empty( $postarr['action'] ) && 'editpost' === $postarr['action']
			&& !empty( $postarr['post_type'] ) && 'it_exchange_prod' === $postarr['post_type'] ) {

			return false;

		}

		return $maybe_empty;

	}

	/**
	 * Sets up the object
	 *
	 * @since 0.3.0
	 *
	 * @return void
	*/
	function init() {
		$this->post_type = 'it_exchange_prod';
		$labels    = array(
			'name'          => __( 'Products', 'it-l10n-ithemes-exchange' ),
			'singular_name' => __( 'Product', 'it-l10n-ithemes-exchange' ),
			'edit_item'     => __( 'Edit Product', 'it-l10n-ithemes-exchange' ),
			'view_item'     => __( 'View Product', 'it-l10n-ithemes-exchange' ),
		);
		$this->options = array(
			'labels' => $labels,
			'description' => __( 'An iThemes Exchange Post Type for storing all Products in the system', 'it-l10n-ithemes-exchange' ),
			'public'      => true,
			'show_ui'     => true,
			'show_in_nav_menus' => true,
			'show_in_menu'      => false, // We will be adding it manually with various labels based on available product-type add-ons
			'show_in_admin_bar' => true,
			'hierarchical'      => false,
			'supports'          => array( // Support everything including page-attributes for add-on flexibility
				'title', 'editor', 'author', 'thumbnail', 'excerpt', 'trackbacks', 
				'custom-fields', 'comments', 'revisions', 'post-formats', 'page-attributes'
				//If you edit this, edit it in set_add_edit_screen_supports()
				//in lib/products/class.product.php
			),
			'register_meta_box_cb' => array( $this, 'meta_box_callback' ),
			'rewrite' => array(
				'slug'       => 'product',
				'with_front' => false,
			),
		);

		// We need to register in a different order during admin to catch updating the permalinks.
		if ( is_admin() ) {

			// Normal Priorities
			$priorities['set_rewrite_slug']   = 9;
			$priorities['register_post_type'] = 10;

			// Special priorities for saving the wizard
			if ( ! empty( $_GET['page'] ) && 'it-exchange-setup' == $_GET['page'] ) {
				$priorities['set_rewrite_slug']   = 8;
				$priorities['register_post_type'] = 8;
			}

			add_action( 'admin_init', array( $this, 'set_rewrite_slug' ), $priorities['set_rewrite_slug'] );
			add_action( 'admin_init', array( $this, 'register_the_post_type' ), $priorities['register_post_type'] );
		}
	
		add_action( 'init', array( $this, 'set_rewrite_slug' ), 9 );
		add_action( 'init', array( $this, 'register_the_post_type' ) );
	}

	/**
	 * Set rewrite rules according to settings
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function set_rewrite_slug() {
		if ( ! $slug = it_exchange_get_page_slug( 'product', true ) )
			return;

		$this->options['rewrite']['slug'] = $slug;
		$this->options['query_var'] = $slug;
	}

	/**
	 * Actually registers the post type
	 *
	 * @since 0.3.0
	 * @return void
	*/
	function register_the_post_type() {
		register_post_type( $this->post_type, $this->options );
		it_exchange_flush_rewrite_rules();
	}

	/**
	 * Call Back hook for product post type admin views
	 *
	 * @since 0.3.0
	 * @uses it_exchange_get_enabled_add_ons()
	 * @return void
	*/
	function meta_box_callback( $post ) {
		$product = it_exchange_get_product( $post );

		// Add action for current product type
		if ( $product_types = it_exchange_get_enabled_addons( array( 'category' => array( 'product-type' ) ) ) ) {
			foreach( $product_types as $addon_slug => $params ) {
				if ( $addon_slug == $product->product_type )
					do_action( 'it_exchange_product_metabox_callback_' . $addon_slug, $product );
			}
		}

		remove_meta_box( 'submitdiv', __( 'Publish' ), 'post_submit_meta_box', null, 'it_exchange_advanced', 'core' );
		add_meta_box( 'submitdiv', __( 'Publish' ), array( $this, 'post_submit_meta_box' ), 'it_exchange_prod', 'it_exchange_side', 'high' );

		// Do action for any product type
		do_action( 'it_exchange_product_metabox_callback', $product );
	}

	function post_submit_meta_box( $post ) {

		global $action;

		$post_type = $post->post_type;
		$post_type_object = get_post_type_object($post_type);
		$can_publish = current_user_can($post_type_object->cap->publish_posts);

		// Grab the iThemes Exchange Product object from the WP $post object
		$product = it_exchange_get_product( $post );

		// Set the value of the visibility for this product
		$product_visibility = get_post_meta( $post->ID, '_it-exchange-visibility', true );
		$product_visibility = apply_filters( 'it_exchange_add_ediit_product_visibility', $product_visibility, $post->ID );
		?>
        <div id="submitpost" class="it-exchange-submit-box">
			<?php do_action('post_submitbox_start'); ?>
			<div style="display:none;">
				<?php submit_button( __( 'Save' ), 'button', 'save' ); ?>
			</div>
			<div class="publishing-actions">
				<div id="save-action">
					<?php if ( 'publish' != $post->post_status && 'future' != $post->post_status && 'pending' != $post->post_status ) : ?>
						<input <?php if ( 'private' == $post->post_status ) { ?>style="display:none"<?php } ?> type="submit" name="save" id="save-post" value="<?php esc_attr_e( 'Save Draft' ); ?>" class="button button-large" />
					<?php elseif ( 'pending' == $post->post_status && $can_publish ) : ?>
						<input type="submit" name="save" id="save-post" value="<?php esc_attr_e( 'Save as Pending' ); ?>" class="button button-large" />
					<?php endif; ?>
					<span class="spinner"></span>
				</div>
				<?php if ( $post_type_object->public ) : ?>
					<div id="preview-action">
						<?php
							if ( 'publish' == $post->post_status ) {
								$preview_link = esc_url( apply_filters( 'it_exchange_view_product_button_link', get_permalink( $post->ID ), $post ) );
								$preview_button = apply_filters( 'it_exchange_view_product_button_label', __( 'View Product', 'it-l10n-ithemes-exchange' ), $post );
								$preview_id = 'post-view';
							} else {
								$preview_link = set_url_scheme( get_permalink( $post->ID ) );
								$preview_link = esc_url( apply_filters( 'it_exchange_preview_product_button_link', apply_filters( 'preview_post_link', add_query_arg( 'preview', 'true', $preview_link ) ), $post ) );
								$preview_button = apply_filters( 'it_exchange_preview_product_button_label', __( 'Preview Product', 'it-l10n-ithemes-exchange' ), $post );
								$preview_id = 'post-preview';
							}
						?>
						<a class="preview button button-large" href="<?php echo $preview_link; ?>" target="wp-preview" id="<?php echo $preview_id; ?>"><?php echo $preview_button; ?></a>
						<input type="hidden" name="wp-preview" id="wp-preview" value="" />
					</div>
				<?php endif; ?>
				<div id="publishing-action">
					<span class="spinner"></span>
					<?php if ( ! in_array( $post->post_status, array( 'publish', 'future', 'private' ) ) || 0 == $post->ID ) : ?>
						<?php if ( $can_publish ) : ?>
							<?php if ( !empty($post->post_date_gmt) && time() < strtotime( $post->post_date_gmt . ' +0000' ) ) : ?>
								<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Schedule' ) ?>" />
								<?php submit_button( __( 'Schedule' ), 'primary button-large', 'publish', false, array( 'accesskey' => 'p' ) ); ?>
							<?php else : ?>
								<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Publish') ?>" />
								<?php submit_button( __( 'Publish' ), 'primary button-large', 'publish', false, array( 'accesskey' => 'p' ) ); ?>
							<?php endif; ?>
						<?php else : ?>
							<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Submit for Review') ?>" />
							<?php submit_button( __( 'Submit for Review' ), 'primary button-large', 'publish', false, array( 'accesskey' => 'p' ) ); ?>
						<?php endif; ?>
					<?php else : ?>
						<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Update' ) ?>" />
						<input name="save" type="submit" class="button button-primary button-large" id="publish" accesskey="p" value="<?php esc_attr_e( 'Update' ) ?>" />
					<?php endif; ?>
				</div>
			</div>
			<div class="modifying-actions">
				<div id="advanced-action">
					<a class="advanced-status-option-link advanced-hidden" href data-hidden="<?php _e( 'Show Advanced', 'it-l10n-ithemes-exchange' ); ?>" data-visible="<?php _e( 'Hide Advanced', 'it-l10n-ithemes-exchange' ); ?>"><?php _e( 'Show Advanced', 'it-l10n-ithemes-exchange' ); ?></a>
				</div>
				<div id="delete-action">
					<?php if ( current_user_can( "delete_post", $post->ID ) ) : ?>
						<?php
							if ( ! EMPTY_TRASH_DAYS )
								$delete_text = __( 'Delete Permanently' );
							else
								$delete_text = __('Move to Trash');
						?>
						<a class="submitdelete deletion" href="<?php echo get_delete_post_link( $post->ID ); ?>"><?php echo $delete_text; ?></a>
					<?php endif; ?>
				</div>
			</div>
			<div class="advanced-actions hide-if-js">
				<div id="misc-publishing-actions">
					<div class="misc-pub-section">
						<label for="post_status"><?php _e( 'Status:' ) ?></label>
						<span id="post-status-display">
							<?php
								switch ( $post->post_status ) {
									case 'private':
										_e('Privately Published');
										break;
									case 'publish':
										_e('Published');
										break;
									case 'future':
										_e('Scheduled');
										break;
									case 'pending':
										_e('Pending Review');
										break;
									case 'draft':
									case 'auto-draft':
										_e('Draft');
										break;
								}
							?>
						</span>
						<?php if ( 'publish' == $post->post_status || 'private' == $post->post_status || $can_publish ) : ?>
							<a href="#post_status" <?php if ( 'private' == $post->post_status ) { ?>style="display:none;" <?php } ?>class="edit-post-status hide-if-no-js"><?php _e( 'Edit', 'it-l10n-ithemes-exchange' ) ?></a>
							<div id="post-status-select" class="hide-if-js">
								<input type="hidden" name="hidden_post_status" id="hidden_post_status" value="<?php echo esc_attr( ('auto-draft' == $post->post_status ) ? 'draft' : $post->post_status); ?>" />
								<select name='post_status' id='post_status'>
									<?php if ( 'publish' == $post->post_status ) : ?>
										<option<?php selected( $post->post_status, 'publish' ); ?> value='publish'><?php _e( 'Published', 'it-l10n-ithemes-exchange' ); ?></option>
									<?php endif; ?>
										<option<?php selected( $post->post_status, 'pending' ); ?> value='pending'><?php _e( 'Pending Review', 'it-l10n-ithemes-exchange' ); ?></option>
									<?php if ( 'auto-draft' == $post->post_status ) : ?>
										<option<?php selected( $post->post_status, 'auto-draft' ); ?> value='draft'><?php _e( 'Draft', 'it-l10n-ithemes-exchange' ); ?></option>
									<?php else : ?>
										<option<?php selected( $post->post_status, 'draft' ); ?> value='draft'><?php _e('Draft') ?></option>
									<?php endif; ?>
								</select>
								 <a href="#post_status" class="save-post-status hide-if-no-js button"><?php _e('OK'); ?></a>
								 <a href="#post_status" class="cancel-post-status hide-if-no-js"><?php _e('Cancel'); ?></a>
							 </div>
						<?php endif; ?>
					</div>

					<div class="misc-pub-section">
						<label for="visibility"><?php _e( 'Visibility:', 'it-l10n-ithemes-exchange' ) ?></label>
						<span id="product-visibility-display">
							<?php
								switch ( $product_visibility ) {
									case 'hidden':
										_e( 'Hide from Store', 'it-l10n-ithemes-exchange' );
										break;
									case 'visible':
									default:
										_e( 'Show in Store', 'it-l10n-ithemes-exchange' );
										break;
								}
							?>
						</span>
						<?php if ( 'visible' == $product_visibility || 'hidden' == $product_visibility || $can_publish ) : ?>
							<a href="#product_visibility" class="edit-product-visibility hide-if-no-js"><?php _e('Edit') ?></a>
							<div id="product-visibility-select" class="hide-if-js">
								<input type="hidden" name="hidden_it-exchange-visibility" id="hidden_it-exchange-visibility" value="<?php echo esc_attr( ('hidden' == $post->post_status ) ? 'hidden' : $product_visibility); ?>" />
								<select name='it-exchange-visibility' id='it-exchange-visibility'>
										<option<?php selected( $product_visibility, 'visible' ); ?> value='visible'><?php _e( 'Show in Store', 'it-l10n-ithemes-exchange' ) ?></option>
										<option<?php selected( $product_visibility, 'hidden' ); ?> value='hidden'><?php _e( 'Hide from Store', 'it-l10n-ithemes-exchange' ) ?></option>
								</select>
								<a href="#product_visibility" class="save-product_visibility hide-if-no-js button"><?php _e('OK'); ?></a>
								<a href="#product_visibility" class="cancel-product_visibility hide-if-no-js"><?php _e('Cancel'); ?></a>
							</div>
						<?php endif; ?>
					</div>

					<?php
						if ( 'private' == $post->post_status ) {
							$post->post_password = '';
							$visibility = 'private';
							$visibility_trans = __( 'Private' );
						} elseif ( !empty( $post->post_password ) ) {
							$visibility = 'password';
							$visibility_trans = __('Password protected');
						} elseif ( $post_type == 'post' && is_sticky( $post->ID ) ) {
							$visibility = 'public';
							$visibility_trans = __('Public, Sticky');
						} else {
							$visibility = 'public';
							$visibility_trans = __('Public');
						}
					?>
					<input type="hidden" name="hidden_post_visibility" id="hidden-post-visibility" value="<?php echo esc_attr( $visibility ); ?>" />

					<?php do_action('post_submitbox_misc_actions'); ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Generates the Add New Product Label for a new Product
	 *
	 * @since 0.3.0
	 * @return string $label Label for add new product page.
	*/
	function set_add_new_item_label() {
		global $pagenow, $wp_post_types;
		if ( $pagenow != 'post-new.php' || empty( $_GET['post_type'] ) || 'it_exchange_prod' != $_GET['post_type'] )
			return apply_filters( 'it_exchange_add_new_product_label', __( 'Add New Product', 'it-l10n-ithemes-exchange' ) );

		if ( empty( $wp_post_types['it_exchange_prod'] ) )
			return;

		$product_add_ons = it_exchange_get_enabled_addons( array( 'category' => array( 'product-type' ) ) );
		$product = array();

		// Isolate the product type
		if ( 1 == count( $product_add_ons ) ) {
			$product = reset( $product_add_ons );
		} else {
			$product_type = it_exchange_get_product_type();
			if ( ! empty( $product_type ) && ! empty( $product_add_ons[$product_type] ) )
				$product = $product_add_ons[$product_type];
			else
				$product['options']['labels']['singular_name'] = 'Product';

		}
		$singular = empty( $product['options']['labels']['singular_name'] ) ? $product['name'] : $product['options']['labels']['singular_name'];
		$label = apply_filters( 'it_exchange_add_new_product_label_' . $product['slug'], __( 'Add New ', 'it-l10n-ithemes-exchange' ) . $singular );
		$wp_post_types['it_exchange_prod']->labels->add_new_item = $label;
	}

	/**
	 * Generates the Edit Product Label for a new Product
	 *
	 * Post types have to be registered earlier than we know that type of post is being edited
	 * so this function inserts the correct label into the $wp_post_types global after post type is registered
	 *
	 * @since 0.3.1
	 * @return string $label Label for edit product page.
	*/
	function set_edit_item_label() {
		global $pagenow, $wp_post_types;
		$post = empty( $_GET['post'] ) ? false : get_post( $_GET['post'] );

		if ( ! is_admin() || $pagenow != 'post.php' || ! $post )
			return;

		if ( empty( $wp_post_types['it_exchange_prod'] ) )
			return;

		if ( 'it_exchange_prod' != get_post_type( $post ) )
			return;

		$product_type = it_exchange_get_product_type( $post );

		$product_add_ons = it_exchange_get_enabled_addons( array( 'category' => array( 'product-type' ) ) );
		$product = array();
		if ( 1 == count( $product_add_ons ) ) {
			$product = reset( $product_add_ons );
		} else {
			if ( ! empty( $product_type ) && ! empty( $product_add_ons[$product_type] ) ) {
				$product = $product_add_ons[$product_type];
			} else {
				$product['slug'] = '';
				$product['options']['labels']['singular_name'] = 'Product';
			}
		}

		$singular = empty( $product['options']['labels']['singular_name'] ) ? $product['name'] : $product['options']['labels']['singular_name'];
		$label = apply_filters( 'it_exchange_edit_product_label_' . $product['slug'], __( 'Edit ', 'it-l10n-ithemes-exchange' ) . $singular );
		$wp_post_types['it_exchange_prod']->labels->edit_item = $label;
	}

	/**
	 * Provides specific hooks for when iThemes Exchange products are saved.
	 *
	 * This method is hooked to save_post. It provides hooks for add-on developers
	 * that will only be called when the post being saved is an iThemes Exchange product.
	 * It provides the following 4 hooks:
	 * - it_exchange_save_product_unvalidated                // Runs every time an iThemes Exchange product is saved.
	 * - it_exchange_save_product_unavalidate-[product-type] // Runs every time a specific iThemes Exchange product type is saved.
	 * - it_exchange_save_product                            // Runs every time an iThemes Exchange product is saved if not an autosave and if user has permission to save post
	 * - it_exchange_save_product-[product-type]             // Runs every time a specific iThemes Exchange product-type is saved if not an autosave and if user has permission to save post
	 *
	 * @since 0.3.1
	 * @param int $post_id WordPress Post ID
	 * @return void
	*/
	function save_product( $post_id ) {

		// Exit if not it_exchange_prod post_type
		if ( ! 'it_exchange_prod' === get_post_type( $post_id ) )
			return;

		// Fire off actions with validations that most instances need to use.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return;

		if ( ! current_user_can( 'edit_post', $post_id ) )
			return;

		if ( isset( $_POST['it-exchange-visibility'] ) )
			update_post_meta( $post_id, '_it-exchange-visibility', $_POST['it-exchange-visibility'] );

		// Grab enabled product add-ons
		$product_type_addons = it_exchange_get_enabled_addons( array( 'category' => 'product-type' ) );

		// Grab current post's product_type
		$product_type = it_exchange_get_product_type();

		// These hooks fire off any time a it_exchange_prod post is saved w/o validations
		do_action( 'it_exchange_save_product_unvalidated', $post_id );
		foreach( (array) $product_type_addons as $slug => $params ) {
			if ( $slug == $product_type ) {
				do_action( 'it_exchange_save_product_unvalidated_' . $slug, $post_id );
			}
		}

		// This is called any time save_post hook
		do_action( 'it_exchange_save_product', $post_id );
		foreach( (array) $product_type_addons as $slug => $params ) {
			if ( $slug == $product_type ) {
				do_action( 'it_exchange_save_product_' . $slug, $post_id );
			}
		}
	}

	/**
	 * Sets the post product_type on post creation
	 *
	 * @since 0.3.3
	 * @return void
	*/
	function set_initial_post_product_type( $post ) {
		global $pagenow;
		if ( $product = it_exchange_get_product( $post ) ) {
			if ( ! empty( $product->product_type ) && ! get_post_meta( $product->ID, '_it_exchange_product_type', true ) )
				update_post_meta( $product->ID, '_it_exchange_product_type', $product->product_type );
		}
	}

	/**
	 * Modifies the value of $post_new_file to change the link attached to the Add New button next to the H2 on all / edit products
	 *
	 * I'm not proud of this. Don't copy it. ^gta
	 *
	 * @since 0.3.10
	 * @return void
	*/
	function modify_post_new_file() {

		global $current_screen, $post_new_file;

		if ( 'edit-it_exchange_prod' == $current_screen->id || 'it_exchange_prod' == $current_screen->id ) {

			$product_type = it_exchange_get_product_type();

			// Hackery. The 'Add New button in the H2 isn't going to work if we have multiple product types
			$product_addons = it_exchange_get_enabled_addons( array( 'category' => 'product-type' ) );
			$hide_it = '<style type="text/css">.add-new-h2 { display:none; }</style>';
			if ( empty( $product_type ) && ( count( $product_addons ) > 1  ) ) {
				echo $hide_it;
			} else if ( empty( $product_type ) && ! it_exchange_get_products() ) {
				// If we made it here, we only have one product type, but there are no products. Won't happen that often.
				$product_addon = array_keys( $product_addons );
				$product_addon = $product_addon[0];
				$product_type = empty( $product_addon ) ? false : $product_addon;
			}

			if ( ! empty( $post_new_file) && ! empty( $product_type ) )
				$post_new_file = add_query_arg( array( 'it-exchange-product-type' => $product_type ), $post_new_file );

		}

	}

	/**
	 * Adds the product type column to the View All products table
	 *
	 * @since 0.3.3
	 * @param array $existing  exisiting columns array
	 * @return array  modified columns array
	*/
	function it_exchange_product_columns( $existing ) {
		add_filter( 'the_excerpt', array( $this, 'replace_excerpt_in_products_table_with_description' ) );
		$columns['cb']                                = '<input type="checkbox" />';
		$columns['title']                             = __( 'Title', 'it-l10n-ithemes-exchange' );
		$columns['it_exchange_product_price']         = __( 'Price', 'it-l10n-ithemes-exchange' );
		$columns['it_exchange_product_show_in_store'] = __( 'Show in Store', 'it-l10n-ithemes-exchange' );
		$columns['it_exchange_product_inventory']     = __( 'Inventory', 'it-l10n-ithemes-exchange' );
		$columns['it_exchange_product_purchases']     = __( 'Purchases', 'it-l10n-ithemes-exchange' );

		if ( 1 < count( it_exchange_get_enabled_addons( array( 'category' => 'product-type' ) ) ) )
			$columns['it_exchange_product_type']          = __( 'Product Type', 'it-l10n-ithemes-exchange' );

		return $columns;
	}

	/**
	 * Replace the excerpt with the product description (our version of excerpt) in the admin All Products table when mode is 'excerpt'
	 *
	 * @since 0.4.0
	 *
	 * @param string $excerpt existing excerpt passed in by WP filter
	 * @return string
	*/
	function replace_excerpt_in_products_table_with_description( $excerpt ) {
		global $post;
		if ( it_exchange_product_has_feature( $post->ID, 'description' ) )
			$excerpt = it_exchange_get_product_feature( $post->ID, 'description' );
		else
			$excerpt = '';
		return $excerpt;
	}

	/**
	 * Makes the product_type column added above sortable
	 *
	 * @since 0.3.3
	 * @param array $sortables  existing sortable columns
	 * @return array  modified sortable columnns
	*/
	function it_exchange_product_sortable_columns( $sortables ) {
		$sortables['it_exchange_product_price']         = 'it-exchange-product-price';
		$sortables['it_exchange_product_show_in_store'] = 'it-exchange-product-show-in-store';
		$sortables['it_exchange_product_inventory']     = 'it-exchange-product-inventory';

		//This will only show up if there are multiple product-type addons
		$sortables['it_exchange_product_type']     = 'it-exchange-product-type';

		return $sortables;
	}

	/**
	 * Adds the product_type of a product to each row of the column added above
	 *
	 * @since 0.3.3
	 * @param string $column  column title
	 * @param integer $post  post ID
	 * @return void
	*/
	function it_exchange_prod_posts_custom_column_info( $column ) {
		global $post;
		$product = it_exchange_get_product( $post );

		switch( $column ) {
			case 'it_exchange_product_price':
				esc_attr_e( it_exchange_format_price( it_exchange_get_product_feature( $post->ID, 'base-price' ) ) );
				break;
			case 'it_exchange_product_show_in_store':
				$product_visibility = get_post_meta( $post->ID, '_it-exchange-visibility', true );
				echo ucwords( $product_visibility );
				break;
			case 'it_exchange_product_inventory':
				esc_attr_e( it_exchange_get_product_feature( $post->ID, 'inventory' ) );
				break;
			case 'it_exchange_product_purchases':
				esc_attr_e( count( it_exchange_get_transactions_for_product( $post->ID, 'ids' ) ) );
				break;
			case 'it_exchange_product_type':
				esc_attr_e( it_exchange_get_product_type_name( it_exchange_get_product_type( $post ) ) );
				break;
		}
	}

	/**
	 * Modify sort of products in edit.php for custom columns
	 *
	 * @since 0.4.0
	 *
	 * @param string $request original request
	 */
	function modify_wp_query_request_on_edit_php( $request ) {
		global $hook_suffix;

		if ( 'edit.php' === $hook_suffix ) {
			if ( 'it_exchange_prod' === $request['post_type'] && isset( $request['orderby'] ) ) {
				switch( $request['orderby'] ) {
					case 'it-exchange-product-price':
						$request['orderby'] = 'meta_value_num';
						$request['meta_key'] = '_it-exchange-base-price';
						break;
					case 'it-exchange-product-show-in-store':
						$request['orderby'] = 'meta_value';
						$request['meta_key'] = '_it-exchange-visibility';
						break;
					case 'it-exchange-product-type':
						$request['orderby'] = 'meta_value';
						$request['meta_key'] = '_it_exchange_product_type';
						break;
				}
			}
		}

		return $request;
	}

	/**
	 * Modify the product updated messages
	 *
	 * @since 0.4.9
	 *
	 * @param array $messages existing messages
	 * @return array
	*/
	function product_updated_messages( $messages ) {
		global $post;
		$post_ID = $post->ID;
		$messages['it_exchange_prod'] = array(
			 1 => sprintf( __('Product updated. <a href="%s">View product</a>'), esc_url( get_permalink($post_ID) ) ),
			 2 => __('Custom field updated.'),
			 3 => __('Custom field deleted.'),
			 4 => __('Product updated.'),
			/* translators: %s: date and time of the revision */
			 5 => isset($_GET['revision']) ? sprintf( __('Product restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			 6 => sprintf( __('Product published. <a href="%s">View product</a>'), esc_url( get_permalink($post_ID) ) ),
			 7 => __('Product saved.'),
			 8 => sprintf( __('Product submitted. <a target="_blank" href="%s">Preview product</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
			 9 => sprintf( __('Product scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview product</a>'),
				// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
			10 => sprintf( __('Product draft updated. <a target="_blank" href="%s">Preview product</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) )
		);
		return $messages;
	}

	/**
	 * Unregisters core slug metabox and registers ours
	 *
	 * @since 0.4.13
	 * @return void
	*/
	function replace_core_slug_metabox() {
		remove_meta_box( 'slugdiv', 'it_exchange_prod', 'it_exchange_advanced', 'low' );
		add_meta_box( 'slugdiv', __( 'Product Slug', 'it-l10n-ithemes-exchange' ), array( $this, 'post_slug_meta_box' ), 'it_exchange_prod', 'it_exchange_advanced', 'low' );
	}
	/**
	 * Modifed version of slugmetabox with description
	 *
	 * @since 0.4.13
	 *
	 * @return void
	*/
	function post_slug_meta_box( $post ) {
		?>
		<label class="" for="post_name">
			<?php _e( 'Product Slug', 'it-l10n-ithemes-exchange' ); ?> <span class="tip" title="<?php _e( 'This is the final part of the product\'s URL. WordPress will auto-create it for you.', 'it-l10n-ithemes-exchange' ); ?>">i</span>
		</label>
		<input name="post_name" type="text" size="13" id="post_name" value="<?php echo esc_attr( apply_filters('editable_slug', $post->post_name) ); ?>" />
		<?php
	}

    /**
     * Creates a sample product when the wizard is saved.
     *
     * @since 0.1.15
     *
     * @return void
    */
    function create_sample_product() {
		$settings    = it_exchange_get_option( 'settings_general', true );
		$sample_id   = empty( $settings['sample-product-id'] ) ? false : $settings['sample-product-id'];
		// Abort if product already exists.
		if ( it_exchange_get_product( $sample_id ) )
			return;

		// Set sample product Type
		$product_types       = it_exchange_get_enabled_addons( array( 'category' => 'product-type' ) );
		$product_type        = current( $product_types );
		$sample_product_type = empty( $product_type['slug'] ) ? 'digital-downloads-product-type' : $product_type['slug'];

		$title       = __( 'My Sample Product', 'it-l10n-ithemes-exchange' );
		$price       = '1';
		$description = __( 'A great product description includes the primary benefits, not just features or technical specs to your target market and core audience. It\'s probably about 3-4 sentences, if that, selling your product as the solution for your prospective customers. To help you, answer these questions: What problem does it solve? Who does it solve it for? And how is it different than other products out there?', 'it-l10n-ithemes-exchange' );
		$extended    = __( '
			This is your extended description. Use this area for a variety of information, like:
			<ul>
				<li>FAQs</li>
				<li>Table of contents (for books) or track listings (for albums)</li>
				<li>Samples files</li>
				<li>Additional yet educational sales information</li>
				<li>And don\'t forget you could put all those other features and technical specs here!</li>
			</ul>
			You can change this on the fly too!

			<strong>Photos and Images</strong>

			You\'ll want to create bigger images, perhaps even at a 1x1 ratio and 1000pixel wide.

			Then let WordPress resize them for output on your page. It\'s best to have a featured image (one big one that really displays your product) then have supplement images (if available) that buyers can click on to see more what you offer.

			JPGs are typically best for photos. PNGs for other types of artwork.'
		, 'it-l10n-ithemes-exchange' );

		$args = array(
			'type'                 => $sample_product_type,
			'status'               => 'draft',
			'show_in_store'        => true,
			'description'          => $description,
			'title'                => $title,
			'base-price'           => $price,
            'extended-description' => $extended,
			'images-from-urls'     => array(
				ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/admin/images/sample-product-image-1.png' ) => __( 'Sample Image One', 'it-l10n-ithemes-exchange' ),
				ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/admin/images/sample-product-image-2.png' ) => __( 'Sample Image Two', 'it-l10n-ithemes-exchange' ),
				ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/admin/images/sample-product-image-3.png' ) =>  __( 'Sample Image Three', 'it-l10n-ithemes-exchange' ),
			),
		);

		// The it_exchange_add_product API method is a work in progress. We don't suggest that you use it yet.
		if ( $product_id = it_exchange_add_product( $args ) ) {
			$settings['sample-product-id'] = $product_id;
			it_exchange_save_option( 'settings_general', $settings );
		}
	}
}
$IT_Exchange_Product_Post_Type = new IT_Exchange_Product_Post_Type();
