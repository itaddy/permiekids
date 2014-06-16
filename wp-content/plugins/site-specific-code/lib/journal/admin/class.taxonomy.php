<?php

/**
 *
 * @package Journal
 * @subpackage Admin
 * @since 5/3
 */
class LDMW_Journal_Admin_Taxonomy {
	/**
	 * @var string
	 */
	public static $slug = 'journal';

	/**
	 * Kick off all of our taxonomy code
	 */
	public function __construct() {
		$this->register_taxonomy();
		add_action( self::$slug . '_add_form_fields', array( $this, 'add_form_fields' ) );
		add_filter( 'taxonomy_parent_dropdown_args', array( $this, 'modify_taxonomy_dropdown' ), 10, 2 );
		add_action( self::$slug . "_edit_form_fields", array( $this, 'add_edit_form_fields' ) );
		add_action( 'created_' . self::$slug, array( $this, 'save_taxonomy_metadata' ), 10, 2 );
		add_action( 'edited_' . self::$slug, array( $this, 'save_taxonomy_metadata' ), 10, 2 );
	}

	/**
	 * Register our journal taxonomy
	 */
	public function register_taxonomy() {
		$labels = array(
		  'name'                       => 'Volumes',
		  'menu_name'                  => 'Volumes',
		  'singular_name'              => 'Volume',
		  'search_items'               => 'Search Issues and Volumes',
		  'add_new_item'               => 'Add New Volume or Issue',
		  'all_items'                  => 'All Volumes',
		  'parent_item'                => 'Volume',
		  'parent_item_colon'          => 'Volume:',
		  'edit_item'                  => 'Edit Issue or Volume',
		  'view_item'                  => 'View Issue or Volume',
		  'new_item_name'              => 'New Issue or Volume',
		  'separate_items_with_commas' => 'Separate issues and volumes with commas',
		  'add_or_remove_items'        => 'Add or remove issues or volumes',
		  'choose_from_most_used'      => 'Choose from most used issues and volumes',
		  'not_found'                  => 'No issues or volumes found.'
		);

		$args = array(
		  'labels'       => $labels,
		  'slug'         => 'journal',
		  'hierarchical' => true,
		  'rewrite'      => array(
			'with_front'   => false,
			'slug'         => 'journals',
			'hierarchical' => true,
		  )
		);

		register_taxonomy( self::$slug, LDMW_Journal_Admin_CPT::$slug, $args );
	}

	/**
	 * Only show the volumes in the taxonomy parent dropdown
	 *
	 * @param $dropdown_args array
	 * @param $taxonomy string
	 *
	 * @return array
	 */
	public function modify_taxonomy_dropdown( $dropdown_args, $taxonomy ) {
		if ( $taxonomy != self::$slug )
			return $dropdown_args;

		$dropdown_args['depth'] = 1;

		return $dropdown_args;
	}

	/**
	 * Add custom form fields to taxonomy
	 */
	public function add_form_fields() {
		wp_enqueue_style( 'ldmw-admin', LDMW_Plugin::$url . "assets/css/admin.css", array(), "1.0" );
		wp_enqueue_script( 'ldmw-admin', LDMW_Plugin::$url . "assets/js/admin.js", array( 'jquery-ui-datepicker', 'jquery' ), 1.0 );
		?>
		<div class="form-field">
			<label for="ldmw_taxonomy_name">Issue Title</label>
			<input type="text" id="ldmw_taxonomy_name" name="ldmw_taxonomy_name">
			<p>The issue's name as would appear on the cover.</p>
	    </div>

		<div class="form-field">
			<label for="ldmw_taxonomy_publish_date">Publish Date</label>
			<input type="text" class="datepicker" data-date-format="<?php echo LDMW_Util::dateStringToDatepickerFormat( get_option( 'date_format', 'm/d/Y' ) ); ?>" id="ldmw_taxonomy_publish_date" name="ldmw_taxonomy_publish_date">
			<p>What date was this volume or issue published.</p>
		</div>

	<?php
	}

	/**
	 * Add our custom fields on the edit screen
	 *
	 * @param $term object
	 */
	public function add_edit_form_fields( $term ) {
		wp_enqueue_style( 'ldmw-admin', LDMW_Plugin::$url . "assets/css/admin.css", array(), "1.0" );
		wp_enqueue_script( 'ldmw-admin', LDMW_Plugin::$url . "assets/js/admin.js", array( 'jquery-ui-datepicker', 'jquery' ), 1.0 );

		$publish_date = get_term_meta( $term->term_taxonomy_id, '_ldmw_publish_date', true );
		if ( ! empty( $publish_date ) )
			$publish_date = ( new DateTime( "@$publish_date" ) )->format( get_option( 'date_format', 'm/d/Y' ) );
		?>

		<?php if ( ! empty( $term->parent ) ) : ?>
			<tr>
				<th><label for="ldmw_taxonomy_name">Issue Title</label></th>
				<td>
					<input type="text" id="ldmw_taxonomy_name" name="ldmw_taxonomy_name" value="<?php echo esc_attr( get_term_meta( $term->term_taxonomy_id, '_ldmw_name', true ) ); ?>">
					<p>The issue's name as would appear on the cover.</p>
				</td>
		    </tr>
		<?php endif; ?>

		<tr>
			<th><label for="ldmw_taxonomy_publish_date">Publish Date</label></th>
			<td>
				<input type="text" class="datepicker" id="ldmw_taxonomy_publish_date" name="ldmw_taxonomy_publish_date" data-date-format="<?php echo LDMW_Util::dateStringToDatepickerFormat( get_option( 'date_format', 'm/d/Y' ) ); ?>" value="<?php echo esc_attr( $publish_date ); ?>">
				<p>What date was this volume or issue published.</p>
			</td>
	    </tr>
	<?php
	}

	/**
	 * Save our custom taxonomy metadata
	 *
	 * @param $term_id int
	 * @param $tt_id int
	 */
	public function save_taxonomy_metadata( $term_id, $tt_id ) {
		if ( isset( $_POST['ldmw_taxonomy_name'] ) ) {
			update_term_meta( $tt_id, '_ldmw_name', sanitize_text_field( $_POST['ldmw_taxonomy_name'] ) );
		}

		if ( isset( $_POST['ldmw_taxonomy_publish_date'] ) ) {
			/*
			 * Check and sanitize the date
			 */
			$publish_date = $_POST['ldmw_taxonomy_publish_date'];

			// Get the user's option set in WP General Settings
			$wp_date_format = get_option( 'date_format', 'm/d/Y' );

			// strtotime requires formats starting with day to be separated by - and month separated by /
			if ( 'd' == substr( $wp_date_format, 0, 1 ) )
				$publish_date = str_replace( '/', '-', $publish_date );

			// Transfer to epoch
			if ( $epoch = strtotime( $publish_date ) ) {

				// Returns an array with values of each date segment
				$date = date_parse( $publish_date );

				// Confirms we have a legitimate date
				if ( checkdate( $date['month'], $date['day'], $date['year'] ) )
					update_term_meta( $tt_id, '_ldmw_publish_date', $epoch );
			}
		}
	}
}