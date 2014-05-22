<?php
/**
 * Purchase Dialog class for THEME API
 *
 * @since 1.3.0
*/

class IT_Theme_API_Purchase_Dialog implements IT_Theme_API {

	/**
	 * API context
	 * @var string $_context
	 * @since 1.3.0
	*/
	private $_context = 'purchase-dialog';

	/**
	 * The required fields for this form this.
	 * @var string $_required_fields
	 * @since 1.3.0
	*/
	private $_required_fields= '';

	/**
	 * The Transaction Method invoking this.
	 * @var string $_customer
	 * @since 1.3.0
	*/
	private $_transaction_method= '';

	/**
	 * Maps api tags to methods
	 * @var array $_tag_map
	 * @since 1.3.0
	*/
	public $_tag_map = array(
		'ccfirstname'           => 'cc_first_name',
		'cclastname'            => 'cc_last_name',
		'ccnumber'              => 'cc_number',
		'ccexpirationmonthyear' => 'cc_expiration_month_year',
		'ccexpirationmonth'     => 'cc_expiration_month',
		'ccexpirationyear'      => 'cc_expiration_year',
		'cccode'                => 'cc_code',
		'fields'                => 'fields',
	);

	/**
	 * Constructor
	 *
	 * @since 1.3.0
	 * @todo get working for admins looking at other users profiles
	 * @return void
	*/
	function IT_Theme_API_Purchase_Dialog() {
		$dialog = it_exchange_get_current_purchase_dialog();
		$this->_transaction_method = empty( $GLOBALS['it_exchange']['purchase-dialog']['transaction-method-slug'] ) ? '' : $GLOBALS['it_exchange']['purchase-dialog']['transaction-method-slug'];
		$this->_required_fields = empty( $dialog->required_cc_fields ) ? array() : $dialog->required_cc_fields;
	}

	/**
	 * Returns the context. Also helps to confirm we are an iThemes Exchange theme API class
	 *
	 * @since 1.3.0
	 *
	 * @return string
	*/
	function get_api_context() {
		return $this->_context;
	}

	/**
	 * Outputs the CC first name data
	 *
	 * @since 1.3.0
	 * @return string
	*/
	function cc_first_name( $options=array() ) {
		$defaults      = array(
			'format'      => 'html',
			'label'       => __( 'Name on Card', 'it-l10n-ithemes-exchange' ),
			'placeholder' => '',
			'required'    => (boolean) in_array( 'first-name', $this->_required_fields ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$field_id   = 'it-exchnage-purchase-dialog-cc-first-name-for-' . $this->_transaction_method;
		$field_name = 'it-exchange-purchase-dialog-cc-first-name';

		return $this->get_field( $options, $field_id, $field_name );
	}

	/**
	 * Outputs the CC last name data
	 *
	 * @since 1.3.0
	 * @return string
	*/
	function cc_last_name( $options=array() ) {
		$defaults      = array(
			'format'      => 'html',
			'label'       => __( 'Last Name on card', 'it-l10n-ithemes-exchange' ),
			'placeholder' => '',
			'required'    => (boolean) in_array( 'last-name', $this->_required_fields ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$field_id   = 'it-exchnage-purchase-dialog-cc-last-name-for-' . $this->_transaction_method;
		$field_name = 'it-exchange-purchase-dialog-cc-last-name';

		return $this->get_field( $options, $field_id, $field_name );
	}

	/**
	 * Outputs the CC Number data
	 *
	 * @since 1.3.0
	 * @return string
	*/
	function cc_number( $options=array() ) {
		$defaults      = array(
			'format'      => 'html',
			'label'       => __( 'Card Number', 'it-l10n-ithemes-exchange' ),
			'placeholder' => '',
			'required'    => (boolean) in_array( 'number', $this->_required_fields ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$field_id   = 'it-exchnage-purchase-dialog-cc-number-for-' . $this->_transaction_method;
		$field_name = 'it-exchange-purchase-dialog-cc-number';

		return $this->get_field( $options, $field_id, $field_name );
	}

	/**
	 * Outputs the CC Expiration Month / Year data
	 *
	 * @since 1.3.0
	 * @return string
	*/
	function cc_expiration_month_year( $options=array() ) {
		$defaults      = array(
			'format'      => 'html',
			'label'       => __( 'Expiration Date', 'it-l10n-ithemes-exchange' ),
			'placeholder' => '',
			'required'    => (boolean) in_array( 'expiration-month-year', $this->_required_fields ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$field_id   = 'it-exchnage-purchase-dialog-cc-expiration-month-year-for-' . $this->_transaction_method;
		$field_name = 'it-exchange-purchase-dialog-cc-expiration-month-year';

		return $this->get_field( $options, $field_id, $field_name );
	}

	/**
	 * Outputs the CC Expiration Month data
	 *
	 * @since 1.3.0
	 * @return string
	*/
	function cc_expiration_month( $options=array() ) {
		$defaults      = array(
			'format'      => 'html',
			'label'       => __( 'Expiration Month', 'it-l10n-ithemes-exchange' ),
			'placeholder' => '',
			'required'    => (boolean) in_array( 'expiration-month', $this->_required_fields ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$field_id   = 'it-exchnage-purchase-dialog-cc-expiration-month-for-' . $this->_transaction_method;
		$field_name = 'it-exchange-purchase-dialog-cc-expiration-month';

		return $this->get_field( $options, $field_id, $field_name );
	}

	/**
	 * Outputs the CC Expiration Year data
	 *
	 * @since 1.3.0
	 * @return string
	*/
	function cc_expiration_year( $options=array() ) {
		$defaults      = array(
			'format'      => 'html',
			'label'       => __( 'Expiration Year', 'it-l10n-ithemes-exchange' ),
			'placeholder' => '',
			'required'    => (boolean) in_array( 'expiration-year', $this->_required_fields ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$field_id   = 'it-exchnage-purchase-dialog-cc-expiration-year-for-' . $this->_transaction_method;
		$field_name = 'it-exchange-purchase-dialog-cc-expiration-year';

		return $this->get_field( $options, $field_id, $field_name );
	}

	/**
	 * Outputs the CC Expiration data
	 *
	 * @since 1.3.0
	 * @return string
	*/
	function cc_code( $options=array() ) {
		$defaults = array(
			'format'      => 'html',
			'label'       => __( 'CVC Code', 'it-l10n-ithemes-exchange' ),
			'placeholder' => '',
			'required'    => (boolean) in_array( 'code', $this->_required_fields ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$field_id   = 'it-exchnage-purchase-dialog-cc-code-for-' . $this->_transaction_method;
		$field_name = 'it-exchange-purchase-dialog-cc-code';

		return $this->get_field( $options, $field_id, $field_name );
	}

	/**
	 * Outputs the hidden field for fields used
	 *
	 * @since 1.3.0
	 * @return string
	*/
	function fields( $options=array() ) {
		$defaults      = array(
			'format' => 'html',
			'label'  => __( 'Fields', 'it-l10n-ithemes-exchange' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$field_id   = 'it-exchnage-purchase-dialog-cc-fields-for-' . $this->_transaction_method;
		$field_name = 'it-exchange-purchase-dialog-cc-fields';

		return $this->get_field( $options, $field_id, $field_name );
	}

	/**
	 * Returns the field data in the format requested
	 *
	 * @since 1.3.0
	 *
	 * @param array $options
	 * @param string $field_id the id of the form field
	 * @param string $field_name the name of the form field
	 * @return mixex
	*/
	function get_field( $options, $field_id, $field_name ) {

		$required = empty( $options['required'] ) ? '' : 'class="required" ';
		$output = '';

		switch( $options['format'] ) {

			case 'field-id':
				$output = $field_id;
				break;
			case 'field-name':
				$output = $field_name;
				break;
			case 'label':
				$output = $options['label'];
				break;
			case 'field':
				$output .= '<input type="text" id="' . esc_attr( $field_id ) . '" '. $required . 'placeholder="' . esc_attr( $options['placeholder'] ) . '" name="' . esc_attr( $field_name ) . '" value="" />';
				break;
			case 'html':
			default:
				$output = '<label for="' . esc_attr( $field_id ) . '">' . $options['label'] . '</label>';
				$output .= '<input type="text" id="' . esc_attr( $field_id ) . '" placeholder="' . esc_attr( $options['placeholder'] ) . '" name="' . esc_attr( $field_name ) . '" value="" />';
		}

		return $output;
	}
}
