<?php

/**
 *
 * @package Conferences
 * @subpackage Metabox
 * @since 6/2
 */
class LDMW_Conference_Metabox_Page {

	/**
	 * Title of the metabox and page
	 *
	 * @var string
	 */
	protected $title;

	/**
	 * Page slug and url
	 *
	 * @var string
	 */
	protected $slug;

	/**
	 * Post types this metabox should show up on
	 *
	 * @var array
	 */
	protected $post_types;

	/**
	 * @var string
	 */
	protected $rewrite_base = '^conferences/([^/]+)/';

	/**
	 * @var string
	 */
	protected $matches_base = 'index.php?ldmw_conference_id=$matches[1]';

	/**
	 * @var WP_Post
	 */
	protected $conference;

	/**
	 * Setup a new FieldManager metabox
	 * that translates to a page
	 *
	 * @param $title string
	 * @param $slug string
	 * @param $post_types string|array
	 */
	public function __construct( $title, $slug = null, $post_types = null ) {
		$this->title = $title;

		if ( empty( $slug ) )
			$slug = urlencode( strtolower( $title ) );

		$this->slug = (string) $slug;

		if ( empty( $post_types ) )
			$post_types = TribeEvents::POSTTYPE;

		if ( !is_array( $post_types ) )
			$post_types = array( $post_types );

		$this->post_types = $post_types;

		$this->register_metabox();
		add_filter( 'fm_element_markup_start', array( $this, 'add_media_buttons' ), 10, 2 );

		$this->register_rewrites();
		add_filter( 'query_vars', array( $this, 'add_query_var' ) );
		add_filter( 'template_include', array( $this, 'fetch_template' ) );
	}

	/**
	 * Register the rich text area metabox
	 */
	protected function register_metabox() {
		$fm = new Fieldmanager_RichTextArea( array(
			'label' => $this->title,
			'name'  => $this->slug
		  )
		);

		$fm->add_meta_box( $this->title, $this->post_types, 'normal', 'high' );
	}

	/**
	 * @param $out string
	 * @param $fm Fieldmanager_Field
	 *
	 * @return string
	 */
	public function add_media_buttons( $out, $fm ) {
		if ( $fm->name != $this->slug )
			return $out;

		ob_start();
		echo '<div id="wp-' . $fm->get_element_id() . '-editor-tools" class="wp-editor-tools hide-if-no-js">';

		if ( !function_exists( 'media_buttons' ) )
			include( ABSPATH . 'wp-admin/includes/media.php' );

		echo '<div id="wp-' . $fm->get_element_id() . '-media-buttons" class="wp-media-buttons">';

		/**
		 * Fires after the default media button(s) are displayed.
		 *
		 * @since 2.5.0
		 *
		 * @param string $editor_id Unique editor identifier, e.g. 'content'.
		 */
		do_action( 'media_buttons', $fm->get_element_id() );
		echo "</div>";
		echo "</div>\n";

		$out .= ob_get_clean();

		return $out;
	}

	/**
	 * Register rewrite rules with the /slug/
	 */
	protected function register_rewrites() {
		$rewrite = "{$this->rewrite_base}{$this->slug}/?";
		$matches = "{$this->matches_base}&ldmw_conference_view={$this->slug}";
		add_rewrite_rule( $rewrite, $matches, 'top' );
	}

	/**
	 * Add our custom query var to WP
	 *
	 * @param $vars array
	 *
	 * @return array
	 */
	public function add_query_var( $vars ) {
		$vars[] = $this->slug;

		return $vars;
	}

	/**
	 * Load the proper template we want
	 *
	 * @param $existing array
	 *
	 * @return array
	 */
	public function fetch_template( $existing ) {
		if ( "" == get_query_var( 'ldmw_conference_id' ) )
			return $existing;

		if ( $this->slug != get_query_var( 'ldmw_conference_view' ) )
			return $existing;

		$conference_slug = get_query_var( 'ldmw_conference_id' );

		$posts = get_posts( array( 'name' => $conference_slug, 'post_type' => $this->post_types ) );

		if ( empty( $posts ) )
			return get_404_template();

		$this->conference = $posts[0];

		$this->modify_wp_query();

		return get_page_template();
	}

	/**
	 * Modify WP Query so WP doesn't get confused as to what to load
	 *
	 * Taken from iThemes Exchange Casper class
	 */
	protected function modify_wp_query() {
		$GLOBALS['wp_query']->posts_per_page = 1;
		$GLOBALS['wp_query']->nopaging = true;
		$GLOBALS['wp_query']->post_count = 1;

		// If we don't have a post, load an empty one
		if ( empty( $GLOBALS['wp_query']->post ) )
			$GLOBALS['wp_query']->post = new WP_Post( new stdClass() );

		$GLOBALS['wp_query']->post->ID = 0;
		$GLOBALS['wp_query']->post->post_date = current_time( 'mysql' );
		$GLOBALS['wp_query']->post->post_date_gmt = current_time( 'mysql', 1 );
		$GLOBALS['wp_query']->post->post_content = $this->get_content();
		$GLOBALS['wp_query']->post->post_title = $this->get_title();
		$GLOBALS['wp_query']->post->post_excerpt = '';
		$GLOBALS['wp_query']->post->post_status = 'publish';
		$GLOBALS['wp_query']->post->comment_status = false;
		$GLOBALS['wp_query']->post->ping_status = false;
		$GLOBALS['wp_query']->post->post_password = '';
		$GLOBALS['wp_query']->post->post_name = 'ldmw-conference-view' . $this->slug;
		$GLOBALS['wp_query']->post->to_ping = '';
		$GLOBALS['wp_query']->post->pinged = '';
		$GLOBALS['wp_query']->post->post_modified = $GLOBALS['wp_query']->post->post_date;
		$GLOBALS['wp_query']->post->post_modified_gmt = $GLOBALS['wp_query']->post->post_date_gmt;
		$GLOBALS['wp_query']->post->post_content_filtered = '';
		$GLOBALS['wp_query']->post->post_parent = 0;
		$GLOBALS['wp_query']->post->guid = $this->get_guid();
		$GLOBALS['wp_query']->post->menu_order = 0;
		$GLOBALS['wp_query']->post->post_type = 'page';
		$GLOBALS['wp_query']->post->post_mime_type = '';
		$GLOBALS['wp_query']->post->comment_count = 0;
		$GLOBALS['wp_query']->post->filter = 'raw';
		$GLOBALS['wp_query']->queried_object = $GLOBALS['wp_query']->post;

		$GLOBALS['wp_query']->posts = array( $GLOBALS['wp_query']->post );
		$GLOBALS['wp_query']->found_posts = 1;
		$GLOBALS['wp_query']->is_single = false; //false -- so comments_template() doesn't add comments
		$GLOBALS['wp_query']->is_preview = false;
		$GLOBALS['wp_query']->is_page = false; //false -- so comments_template() doesn't add comments
		$GLOBALS['wp_query']->is_archive = false;
		$GLOBALS['wp_query']->is_date = false;
		$GLOBALS['wp_query']->is_year = false;
		$GLOBALS['wp_query']->is_month = false;
		$GLOBALS['wp_query']->is_day = false;
		$GLOBALS['wp_query']->is_time = false;
		$GLOBALS['wp_query']->is_author = false;
		$GLOBALS['wp_query']->is_category = false;
		$GLOBALS['wp_query']->is_tag = false;
		$GLOBALS['wp_query']->is_tax = false;
		$GLOBALS['wp_query']->is_search = false;
		$GLOBALS['wp_query']->is_feed = false;
		$GLOBALS['wp_query']->is_comment_feed = false;
		$GLOBALS['wp_query']->is_trackback = false;
		$GLOBALS['wp_query']->is_home = false;
		$GLOBALS['wp_query']->is_404 = false;
		$GLOBALS['wp_query']->is_comments_popup = false;
		$GLOBALS['wp_query']->is_paged = false;
		$GLOBALS['wp_query']->is_admin = false;
		$GLOBALS['wp_query']->is_attachment = false;
		$GLOBALS['wp_query']->is_singular = false;
		$GLOBALS['wp_query']->is_posts_page = false;
		$GLOBALS['wp_query']->is_post_type_archive = false;

		$GLOBALS['wp_query']->conference = $this->conference;
	}

	/**
	 * Get the content for this fake page
	 *
	 * @return string
	 */
	protected function get_content() {
		return get_post_meta( $this->conference->ID, $this->slug, true );
	}

	/**
	 * Get the title of the page as registered
	 *
	 * @return string
	 */
	protected function get_title() {
		return $this->title;
	}

	/**
	 * Get the permalink for this page
	 *
	 * @return string
	 */
	protected function get_guid() {
		$base = trailingslashit( get_permalink( $this->conference->ID ) ) . $this->slug;

		return trailingslashit( $base );

	}
}