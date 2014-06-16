<?php

/**
 *
 * @package Journal
 * @subpackage Admin
 * @since 5/7
 */
class Fieldmanager_AuthorField extends Fieldmanager_TextField {
	/**
	 * @var string
	 */
	public $template;

	/**
	 * @var int
	 */
	public $author_id = 0;

	/**
	 * Call parent constructor
	 *
	 * @param string|array $label
	 * @param array $options
	 */
	public function __construct( $label = '', $options = array() ) {
		parent::__construct( $label, $options );
		$this->template = fieldmanager_get_template( 'textfield' );
	}

	/**
	 * Override the default form element.
	 *
	 * We are only displaying our author field,
	 * when a post is created.
	 *
	 * @param array $value
	 *
	 * @return string
	 */
	public function form_element( $value = array() ) {
		$screen = get_current_screen();

		if ( $screen->action != 'add' ) {
			$this->description .= "<br>Changing this value does not update the author's name, instead it creates a new author. " .
			  "To change the author's name for all articles by him/her please edit that user";
		}

		return parent::form_element( $value );
	}

	/**
	 * Take the regular presave value.
	 *
	 * Then take our author value and create a username from it.
	 *
	 * @param mixed $value
	 * @param array $current_value
	 *
	 * @return string sanitized
	 */
	public function presave( $value, $current_value = array() ) {
		if ( empty( $value ) )
			return $current_value;

		$sanitized = parent::presave( $value, $current_value );

		$author = get_user_by( 'login', $sanitized );

		// we haven't set the author for this post yet
		if ( false === $author ) {
			/**
			 * @var $author_id int|WP_Error
			 */
			$author_id = wp_insert_user( array(
				'user_login' => $sanitized,
				'user_pass'  => wp_generate_password(),
				'role'       => 'author'
			  )
			);

			if ( is_wp_error( $author_id ) ) {
				$this->_failed_validation( $author_id->get_error_message() );
			}
		}
		else {
			$author_id = $author->ID;
		}

		$this->author_id = $author_id;

		/**
		 * @var $wpdb wpdb
		 */
		global $wpdb;

		$wpdb->update( $wpdb->posts, array( 'post_author' => $author_id ), array( 'ID' => $this->data_id ) );

		return $sanitized;
	}
}