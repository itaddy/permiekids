<?php
/**
 * Registration class for THEME API
 *
 * @since 0.4.0
*/

class IT_Theme_API_Registration implements IT_Theme_API {

	/**
	 * API context
	 * @var string $_context
	 * @since 0.4.0
	*/
	private $_context = 'registration';

	/**
	 * Current customer being viewed
	 * @var string $_customer
	 * @since 0.4.0
	*/
	private $_customer = '';

	/**
	 * Maps api tags to methods
	 * @var array $_tag_map
	 * @since 0.4.0
	*/
	public $_tag_map = array(
		'isenabled'        => 'is_enabled',
		'formopen'         => 'form_open',
		'firstname'        => 'firstname',
		'lastname'         => 'lastname',
		'username'         => 'username',
		'email'            => 'email',
		'password1'        => 'password1',
		'password2'        => 'password2',
		'save'             => 'save',
		'cancel'           => 'cancel',
		'formclose'        => 'form_close',
		'disabledmessage'  => 'disabled_message',
	);

	/**
	 * Constructor
	 *
	 * @since 0.4.0
	 * @todo get working for admins looking at other users profiles
	 * @return void
	*/
	function IT_Theme_API_Registration() {
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
	 * Checks if registration is enabled or disabled
	 * (enabled by default unless using WordPress setting)
	 *
	 * @since 0.4.0
	 * @return string
	*/
	function is_enabled( $options=array() ) {

		$settings = it_exchange_get_option( 'settings_general' );

		if ( 'wp' === $settings['site-registration'] && !get_option('users_can_register') )
		 	return false;

		return true;

	}

	/**
	 * Outputs the profile page start of form
	 *
	 * @since 0.4.0
	 * @return string
	*/
	function form_open( $options=array() ) {
		$defaults = array(
			'class'  => false,
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$default_class = it_exchange_in_superwidget() ? 'it-exchange-sw-register' : 'it-exchange-register';
		$class= empty( $options['class'] ) ? $default_class : $default_class . ' ' . esc_attr( $options['class'] );
		$output = '<form class="' . $class . '" action="" method="post" >';
		return $output;
	}

	/**
	 * Outputs the login's username data
	 *
	 * @since 0.4.0
	 * @return string
	*/
	function username( $options=array() ) {
		$defaults      = array(
			'format' => 'html',
			'label'  => __( 'Username', 'it-l10n-ithemes-exchange' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$field_id = 'user_login';
		$field_name = $field_id;

		switch( $options['format'] ) {

			case 'field-id':
				$output = $field_id;

			case 'field-name':
				$output = $field_name;

			case 'label':
				$output = esc_attr( $options['label'] );

			case 'html':
			default:
				$output = '<label for="' . $field_id. '">' . esc_attr( $options['label'] ) . '<span class="it-exchange-required-star">*</span></label>';
				$output .= '<input type="text" id="' . $field_id. '" name="' . $field_name. '" value="" />';

		}

		return $output;
	}

	/**
	 * Outputs the customer's first name data
	 *
	 * @since 0.4.0
	 * @return string
	*/
	function firstname( $options=array() ) {
		$defaults      = array(
			'format' => 'html',
			'label'  => __( 'First Name', 'it-l10n-ithemes-exchange' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$field_id = 'first_name';
		$field_name = $field_id;

		switch( $options['format'] ) {

			case 'field-id':
				$output = $field_id;
				break;
			case 'field-name':
				$output = $field_name;
				break;
			case 'label':
				$output = esc_attr( $options['label'] );
				break;
			case 'html':
			default:
				$output = '<label for="' . $field_id . '">' . esc_attr( $options['label'] ) . '</label>';
				$output .= '<input type="text" id="' . $field_id . '" name="' . $field_name . '" value="" />';

		}

		return $output;
	}

	/**
	 * Outputs the customer's last name data
	 *
	 * @since 0.4.0
	 * @return string
	*/
	function lastname( $options=array() ) {
		$defaults      = array(
			'format'      => 'html',
			'label'  => __( 'Last Name', 'it-l10n-ithemes-exchange' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$field_id = 'last_name';
		$field_name = $field_id;

		switch( $options['format'] ) {

			case 'field-id':
				$output = $field_id;

			case 'field-name':
				$output = $field_name;

			case 'label':
				$output = $options['label'];

			case 'html':
			default:
				$output = '<label for="' . $field_id . '">' . $options['label'] . '</label>';
				$output .= '<input type="text" id="' . $field_id . '" name="' . $field_name . '" value="" />';

		}

		return $output;
	}

	/**
	 * Outputs the customer's email data
	 *
	 * @since 0.4.0
	 * @return string
	*/
	function email( $options=array() ) {
		$defaults      = array(
			'format'      => 'html',
			'label'  => __( 'Email', 'it-l10n-ithemes-exchange' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$field_id = 'email';
		$field_name = $field_id;

		switch( $options['format'] ) {

			case 'field-id':
				$output = $field_id;

			case 'field-name':
				$output = $field_name;

			case 'label':
				$output = esc_attr( $options['label'] );

			case 'html':
			default:
				$output = '<label for="' . $field_id . '">' . esc_attr( $options['label'] ) . '<span class="it-exchange-required-star">*</span></label>';
				$output .= '<input type="text" id="' . $field_id . '" name="' . $field_name . '" value="" />';

		}

		return $output;
	}

	/**
	 * Outputs the customer's password(1) input data
	 *
	 * @since 0.4.0
	 * @return string
	*/
	function password1( $options=array() ) {
		$defaults      = array(
			'format'      => 'html',
			'label'  => __( 'Password', 'it-l10n-ithemes-exchange' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$field_id = 'pass1';
		$field_name = $field_id;

		switch( $options['format'] ) {

			case 'field-id':
				$output = $field_id;

			case 'field-name':
				$output = $field_name;

			case 'label':
				$output = esc_attr( $options['label'] );

			case 'html':
			default:
				$output = '<label for="' . $field_id. '">' . esc_attr( $options['label'] ) . '<span class="it-exchange-required-star">*</span></label>';
				$output .= '<input type="password" id="' . $field_id . '" name="' . $field_name. '" value="" />';

		}

		return $output;
	}

	/**
	 * Outputs the customer's password(2) input data
	 *
	 * @since 0.4.0
	 * @return string
	*/
	function password2( $options=array() ) {
		$defaults      = array(
			'format' => 'html',
			'label'  => __( 'Confirm Password', 'it-l10n-ithemes-exchange' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$field_id = 'pass2';
		$field_name = $field_id;

		switch( $options['format'] ) {

			case 'field-id':
				$output = $field_id;

			case 'field-name':
				$output = $field_name;

			case 'label':
				$output = esc_attr( $options['label'] );

			case 'html':
			default:
				$output = '<label for="' . $field_id . '">' . $options['label'] . '<span class="it-exchange-required-star">*</span></label>';
				$output .= '<input type="password" id="' . $field_id . '" name="' . $field_name. '" value="" />';

		}

		return $output;
	}

	/**
	 * Outputs the profile page save button
	 *
	 * @since 0.4.0
	 * @return string
	*/
	function save( $options=array() ) {
		$defaults      = array(
			'format' => 'html',
			'label'  =>  __( 'Register', 'it-l10n-ithemes-exchange' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$field_id = 'it-exchange-register-customer';
		$field_name = $field_id;

		switch( $options['format'] ) {

			case 'field-id':
				$output = $field_id;

			case 'field-name':
				$output = $field_name;

			case 'label':
				$output = esc_attr( $options['label'] );

			case 'html':
			default:
				$output = '<input type="submit" id="' . $field_id . '" name="' . $field_name . '" value="' . esc_attr( $options['label'] ) . '" />';

		}
		return $output;
	}

	/**
	 * Outputs the registration page cancel button
	 *
	 * @since 0.4.6
	 * @return string
	*/
	function cancel( $options=array() ) {
		$defaults = array(
			'format' => 'html',
			'label'  =>  __( 'Cancel', 'it-l10n-ithemes-exchange' ),
			'class'  => false,
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$field_id = 'it-exchange-cancel-register-customer';
		$field_name = $field_id;
		$class = empty( $options['class'] ) ? 'it-exchange-sw-cancel-register-link' : 'it-exchange-sw-cancel-register-link ' . $options['class'];

		switch( $options['format'] ) {

			case 'url':
				$output = it_exchange_get_page_url( 'login' );

			case 'label':
				$output = esc_attr( $options['label'] );

			case 'html':
			default:
				$output = '<a class="' . esc_attr( $class ) . '" href="' . it_exchange_get_page_url( 'login' ) . '">' .esc_attr( $options['label'] ) . '</a>';

		}
		return $output;
	}

	/**
	 * Outputs the profile page end of form
	 *
	 * @since 0.4.0
	 * @return string
	*/
	function form_close( $options=array() ) {
		return '</form>';
	}

	/**
	 * Outputs the profile page registration disabled message
	 *
	 * @since 0.4.0
	 * @return string
	*/
	function disabled_message( $options=array() ) {
		$defaults      = array(
			'format' => 'html',
			'label'  =>  __( 'Registration Disabled', 'it-l10n-ithemes-exchange' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		switch( $options['format'] ) {

			case 'label':
				$output = esc_attr( $options['label'] );

			case 'html':
			default:
				$output = '<h1>' . esc_attr( $options['label'] ) . '</h1>';

		}

		return $output;

	}
}
