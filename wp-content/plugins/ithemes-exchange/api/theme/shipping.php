<?php
/**
 * Shipping class for THEME API
 *
 * @since 1.4.0
*/

class IT_Theme_API_Shipping implements IT_Theme_API {

	/**
	 * API context
	 * @var string $_context
	 * @since 1.4.0
	*/
	private $_context = 'shipping';

	/**
	 * Current customer Shipping Address
	 * @var string $_shipping_address
	 * @since 1.4.0
	*/
	private $_shipping_address = '';

	/**
	 * Maps api tags to methods
	 * @var array $_tag_map
	 * @since 1.4.0
	*/
	public $_tag_map = array(
		'firstname'   => 'first_name',
		'lastname'    => 'last_name',
		'companyname' => 'company_name',
		'address1'    => 'address1',
		'address2'    => 'address2',
		'city'        => 'city',
		'state'       => 'state',
		'zip'         => 'zip',
		'country'     => 'country',
		'email'       => 'email',
		'phone'       => 'phone',
		'submit'      => 'submit',
		'cancel'      => 'cancel',
	);

	/**
	 * Constructor
	 *
	 * @since 1.4.0
	 * @return void
	*/
	function IT_Theme_API_Shipping() {
		$this->_shipping_address = it_exchange_get_cart_shipping_address();
	}

	/**
	 * Returns the context. Also helps to confirm we are an iThemes Exchange theme API class
	 *
	 * @since 1.4.0
	 *
	 * @return string
	*/
	function get_api_context() {
		return $this->_context;
	}

	/**
	 * Outputs the shipping address first name data
	 *
	 * @since 1.4.0
	 * @return string
	*/
	function first_name( $options=array() ) {
		$defaults      = array(
			'format'   => 'html',
			'label'    => __( 'First Name', 'it-l10n-ithemes-exchange' ),
			'required' => true,
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$options['field_id']   = 'it-exchange-shipping-address-first-name';
		$options['field_name'] = 'it-exchange-shipping-address-first-name';
		$options['value']      = empty( $this->_shipping_address['first-name'] ) ? '' : $this->_shipping_address['first-name'];

		return $this->get_fields( $options );
	}

	/**
	 * Outputs the shipping address last name data
	 *
	 * @since 1.4.0
	 * @return string
	*/
	function last_name( $options=array() ) {
		$defaults      = array(
			'format'   => 'html',
			'label'    => __( 'Last Name', 'it-l10n-ithemes-exchange' ),
			'required' => true,
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$options['field_id']   = 'it-exchange-shipping-address-last-name';
		$options['field_name'] = 'it-exchange-shipping-address-last-name';
		$options['value']      = empty( $this->_shipping_address['last-name'] ) ? '' : $this->_shipping_address['last-name'];

		return $this->get_fields( $options );
	}

	/**
	 * Outputs the shipping address compnay name data
	 *
	 * @since 1.4.0
	 * @return string
	*/
	function company_name( $options=array() ) {
		$defaults      = array(
			'format'   => 'html',
			'label'    => __( 'Company Name', 'it-l10n-ithemes-exchange' ),
			'required' => false,
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$options['field_id']   = 'it-exchange-shipping-address-company-name';
		$options['field_name'] = 'it-exchange-shipping-address-company-name';
		$options['value']      = empty( $this->_shipping_address['company-name'] ) ? '' : $this->_shipping_address['company-name'];

		return $this->get_fields( $options );
	}

	/**
	 * Outputs the shipping address address 1 data
	 *
	 * @since 1.4.0
	 * @return string
	*/
	function address1( $options=array() ) {
		$defaults      = array(
			'format'   => 'html',
			'label'    => __( 'Address', 'it-l10n-ithemes-exchange' ),
			'required' => true,
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$options['field_id']   = 'it-exchange-shipping-address-address1';
		$options['field_name'] = 'it-exchange-shipping-address-address1';
		$options['value']      = empty( $this->_shipping_address['address1'] ) ? '' : $this->_shipping_address['address1'];

		return $this->get_fields( $options );
	}

	/**
	 * Outputs the shipping address address 2data
	 *
	 * @since 1.4.0
	 * @return string
	*/
	function address2( $options=array() ) {
		$defaults      = array(
			'format'   => 'html',
			'label'    => __( 'Address 2', 'it-l10n-ithemes-exchange' ),
			'required' => false,
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$options['field_id']   = 'it-exchange-shipping-address-address2';
		$options['field_name'] = 'it-exchange-shipping-address-address2';
		$options['value']      = empty( $this->_shipping_address['address2'] ) ? '' : $this->_shipping_address['address2'];

		return $this->get_fields( $options );
	}

	/**
	 * Outputs the shipping address city data
	 *
	 * @since 1.4.0
	 * @return string
	*/
	function city( $options=array() ) {
		$defaults      = array(
			'format'   => 'html',
			'label'    => __( 'City', 'it-l10n-ithemes-exchange' ),
			'required' => true,
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$options['field_id']   = 'it-exchange-shipping-address-city';
		$options['field_name'] = 'it-exchange-shipping-address-city';
		$options['value']      = empty( $this->_shipping_address['city'] ) ? '' : $this->_shipping_address['city'];

		return $this->get_fields( $options );
	}

	/**
	 * Outputs the shipping address zip data
	 *
	 * @since 1.4.0
	 * @return string
	*/
	function zip( $options=array() ) {
		$defaults      = array(
			'format'   => 'html',
			'label'    => __( 'Zip Code', 'it-l10n-ithemes-exchange' ),
			'required' => true,
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$options['field_id']   = 'it-exchange-shipping-address-zip';
		$options['field_name'] = 'it-exchange-shipping-address-zip';
		$options['value']      = empty( $this->_shipping_address['zip'] ) ? '' : $this->_shipping_address['zip'];

		return $this->get_fields( $options );
	}

	/**
	 * Outputs the shipping address country data
	 *
	 * @since 1.4.0
	 * @return string
	*/
	function country( $options=array() ) {
		$defaults      = array(
			'format'   => 'html',
			'label'    => __( 'Country', 'it-l10n-ithemes-exchange' ),
			'required' => true,
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$options['field_id']   = 'it-exchange-shipping-address-country';
		$options['field_name'] = 'it-exchange-shipping-address-country';
		$options['value']      = empty( $this->_shipping_address['country'] ) ? '' : $this->_shipping_address['country'];

		// Update value if doing ajax
		$options['value'] = empty( $_POST['ite_base_country_ajax'] ) ? $options['value'] : $_POST['ite_base_country_ajax'];

		$countries = it_exchange_get_data_set( 'countries' );

		$current_value = empty( $options['value'] ) ? '' : esc_attr( $options['value'] );

		$field  = '<select id="' . esc_attr( $options['field_id'] ) . '" name="' . esc_attr( $options['field_name'] ) . '">';
		$field .= '<option value=""></option>';
		foreach( $countries as $key => $value ) {
			$alternatives = esc_attr( $key );
			if ( 'US' == $key )
				$alternatives .= ' US us usa USA u';
			$field .= '<option value="' . esc_attr( $key ) . '" ' . selected( $key, $current_value, false ) . ' data-alternative-spellings="' . $alternatives . '">' . esc_html( $value ) . '</option>';
		}
		$field .= '</select>';

		switch( $options['format'] ) {
			case 'field-id' :
				$output = $options['field_id'];
				break;
			case 'field-name':
				$output = $options['field_name'];
				break;
			case 'label':
				$output = $options['label'];
				break;
			case 'field':
				$output = $field;
				break;
			case 'value':
				$output = $current_value;
				break;
			case 'html':
			default:
				$output  = '<label for="' . esc_attr( $options['field_id'] ) . '">' . $options['label'];
				if ( $options['required'] )
					$output .= '<span class="it-exchange-required-star">&#42;</span>';
				$output .= '</label>';
				$output .= $field;
		}
		return $output;
	}

	/**
	 * Outputs the shipping address state data
	 *
	 * @since 1.4.0
	 * @return string
	*/
	function state( $options=array() ) {

		// Default state value for normal page load
		$shipping_value = empty( $this->_shipping_address['state'] ) ? '' : $this->_shipping_address['state'];
		$default_value = empty( $_POST['it-exchange-shipping-address-state'] ) ? $shipping_value : $_POST['it-exchange-shipping-address-state'];

		$defaults      = array(
			'format'     => 'html',
			'label'      => __( 'State', 'it-l10n-ithemes-exchange' ),
			'value'      => $default_value,
			'field-type' => false,
			'required'   => true,
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		// Update value if doing ajax
		$options['value'] = empty( $_POST['ite_base_state_ajax'] ) ? $options['value'] : $_POST['ite_base_state_ajax'];

		$options['field_id']   = 'it-exchange-shipping-address-state';
		$options['field_name'] = 'it-exchange-shipping-address-state';
		$options['value']      = empty( $this->_shipping_address['state'] ) ? '' : $this->_shipping_address['state'];

		$states = it_exchange_get_data_set( 'states', array( 'country' => it_exchange( 'shipping', 'get-country', array( 'format' => 'value' ) ) ) );

		$current_value = empty( $options['value'] ) ? '' : esc_attr( $options['value'] );

		$field = '';
		if ( ! empty( $states ) && is_array( $states ) && 'text' != $options['field-type'] ) {
			$field .= '<select id="' . esc_attr( $options['field_id'] ) . '" name="' . esc_attr( $options['field_name'] ) . '">';
			$field .= '<option value=""></option>';
			foreach( (array) $states as $key => $value ) {
				$alternatives = esc_attr( $key );
				$field .= '<option value="' . esc_attr( $key ) . '" ' . selected( $key, $current_value, false ) . ' data-alternative-spellings="' . $alternatives . '">' . esc_html( $value ) . '</option>';
			}
			$field .= '</select>';
		} else {
			$text_options = $options;
			$text_options['format']    = 'field';
			$field .= $this->get_fields( $text_options );
		}

		switch( $options['format'] ) {
			case 'field-id' :
				$output = $options['field_id'];
				break;
			case 'field-name':
				$output = $options['field_name'];
				break;
			case 'label':
				$output = $options['label'];
				break;
			case 'field':
				$output = $field;
				break;
			case 'value':
				$output = $current_value;
				break;
			case 'html':
			default:
				$output  = '<label for="' . esc_attr( $options['field_id'] ) . '">' . $options['label'];
				if ( $options['required'] )
					$output .= '<span class="it-exchange-required-star">&#42;</span>';
				$output .= '</label>';
				$output .= $field;
		}
		return $output;
	}

	/**

	/**
	 * Outputs the shipping address email data
	 *
	 * @since 1.4.0
	 * @return string
	*/
	function email( $options=array() ) {
		$defaults      = array(
			'format' => 'html',
			'label'  => __( 'Email', 'it-l10n-ithemes-exchange' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$options['field_id']   = 'it-exchange-shipping-address-email';
		$options['field_name'] = 'it-exchange-shipping-address-email';
		$options['value']      = empty( $this->_shipping_address['email'] ) ? '' : $this->_shipping_address['email'];

		return $this->get_fields( $options );
	}

	/**
	 * Outputs the shipping address phone data
	 *
	 * @since 1.4.0
	 * @return string
	*/
	function phone( $options=array() ) {
		$defaults      = array(
			'format' => 'html',
			'label'  => __( 'Phone', 'it-l10n-ithemes-exchange' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$options['field_id']   = 'it-exchange-shipping-address-phone';
		$options['field_name'] = 'it-exchange-shipping-address-phone';
		$options['value']      = empty( $this->_shipping_address['phone'] ) ? '' : $this->_shipping_address['phone'];

		return $this->get_fields( $options );
	}

	/**
	 * Outputs the shipping address submit button
	 *
	 * @since 1.4.0
	 * @return string
	*/
	function submit( $options=array() ) {
		$defaults      = array(
			'format' => 'html',
			'label'  => __( 'Submit', 'it-l10n-ithemes-exchange' ),
			'name'   => '',
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$options['field_id']   = 'it-exchange-shipping-address-submit';

		return $output = '<input type="submit" id="' . esc_attr( $options['field_id'] ) . '" name="' . esc_attr( $options['name'] ) . '" value="'. esc_attr( $options['label'] ) .'" />';
	}

	/**
	 * Outputs the shipping address phone data
	 *
	 * @since 1.4.0
	 * @return string
	*/
	function cancel( $options=array() ) {
		$defaults      = array(
			'format' => 'html',
			'label'  => __( 'Cancel', 'it-l10n-ithemes-exchange' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		return '<a class="it-exchange-shipping-address-requirement-cancel" href="' . it_exchange_get_page_url( 'checkout' ) . '">' . $options['label'] . '</a>';
	}

	/**
	 * Gets the HTML is the desired format
	 *
	 * @since 1.4.0
	 *
	 * @param array $options
	 * @return mixed
	*/
	function get_fields( $options ) {

		$value = empty( $options['value'] ) ? '' : esc_attr( $options['value'] );
		$class = empty( $options['class'] ) ? '' : esc_attr( $options['class'] );
		$options['required'] = ! empty( $options['required'] );

		switch( $options['format'] ) {

			case 'field-id' :
				$output = $options['field_id'];
				break;
			case 'field-name':
				$output = $options['field_name'];
				break;
			case 'label':
				$output = $options['label'];
				if ( $options['required'] )
					$output .= '<span class="it-exchange-required-star">&#42;</span>';
				break;
			case 'field':
				$output = '<input type="text" class="' . $class . '" id="' . esc_attr( $options['field_id'] ) . '" name="' . esc_attr( $options['field_name'] ) . '" value="' . $value . '" />';
				break;
			case 'value':
				$output = $value;
				break;
			case 'html':
			default:
				$output  = empty( $options['label'] ) ? '' : '<label for="' . esc_attr( $options['field_id'] ) . '">' . $options['label'];
				if ( $options['required'] )
					$output .= '&nbsp;<span class="it-exchange-required-star">&#42;</span>';
				$output .= '</label>';
				$output .= '<input type="text" class="' . $class . '" id="' . esc_attr( $options['field_id'] ) . '" name="' . esc_attr( $options['field_name'] ) . '" value="' . $value . '" />';
		}

		return $output;
	}
}
