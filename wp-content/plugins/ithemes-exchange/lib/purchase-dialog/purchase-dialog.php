<?php
/**
 * This file holds the IT_Exchange_Purchase_Dialog class
 *
 * @package IT_Exchange
 * @since 1.3.0
*/

/**
 * Transaction methods call or extend this class to create a
 * purchase dialog with a custom form in it.
 *
 * @since 1.3.0
*/
class IT_Exchange_Purchase_Dialog{

	/**
	 * @param string $addon_slug the slug for the addon invoking the class
	 * @since 1.3.0
	*/
	var $addon_slug = false;

	/**
	 * @param array a key => value array of form attributes like class, id, etc.
	 * @since 1.3.0
	*/
	var $form_attributes = array();

	/**
	 * @param array an array of the cc fields were using
	 * @since 1.3.0
	*/
	var $active_cc_fields = array();

	/**
	 * @param array an array of the required cc fields were using
	 * @since 1.3.0
	*/
	var $required_cc_fields = array();

	/**
	 * @param string the label used for the button that opens up the CC fields
	 * @since 1.3.0
	*/
	var $purchase_label;

	/**
	 * @param string the label used for the button that submits the CC fields
	 * @since 1.3.0
	*/
	var $submit_label;

	/**
	 * @param string the label used for the cancel link to close the CC fields
	 * @since 1.3.0
	*/
	var $cancel_label;

	/**
	 * Class Constructor
	 *
	 * @since 1.3.0
	 *
	 * @param array $options
	*/
	function IT_Exchange_Purchase_Dialog( $transaction_method_slug, $options=array() ) {

		$defaults = array(
			'form-attributes'    => array(
				'action' => it_exchange_get_page_url( 'transaction' ),
				'method' => 'post',
			),
			'required-cc-fields' => array(
				'first-name',
				'last-name',
				'number',
				'expiration-month',
				'expiration-year',
			),
			'purchase-label' => __( 'Purchase', 'it-l10n-ithemes-exchange' ),
			'submit-label'   => __( 'Complete Purchase', 'it-l10n-ithemes-exchange' ),
			'cancel-label'   => __( 'Cancel', 'it-l10n-ithemes-exchange' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$sw = it_exchange_in_superwidget() ? ' it-exchange-sw-purchase-dialog' : '';
		// Append class name
		$class_name = 'it-exchange-purchase-dialog it-exchange-purchase-dialog-' . $transaction_method_slug . $sw;
		$options['form-attributes']['class'] = empty( $options['form-attributes']['class'] ) ? $class_name : $options['form-attributes']['class'] . ' ' . $class_name;

		$this->addon_slug         = $transaction_method_slug;
		$this->form_attributes    = (array) $options['form-attributes'];
		$this->required_cc_fields = (array) $options['required-cc-fields'];
		$this->purchase_label     = $options['purchase-label'];
		$this->submit_label       = $options['submit-label'];
		$this->cancel_label       = $options['cancel-label'];
	}

	/**
	 * Returns the HTML for the button
	 *
	 * @since 1.3.0
	 *
	 * @return string HTML
	*/
	function insert_dialog() {
		//$this->enqueue_js(); // We are now doing this in lib/functions/functions.php
		$wrapper_open  = $this->get_wrapper_open();
		$form          = $this->get_purchase_form();
		$wrapper_close = $this->get_wrapper_close();
		$button        = $this->get_purchase_button();

		return $wrapper_open . $form . $wrapper_close . $button;
	}

	/**
	 * Generates the opening HTML for the wrapper div
	 *
	 * @since 1.3.0
	 *
	 * @return HTML
	*/
	function get_wrapper_open() {
		$ssl_class = is_ssl() ? ' it-exchange-is-ssl' : ' it-exchange-no-ssl';
		$html = '<div class="it-exchange-purchase-dialog it-exchange-purchase-dialog-' . esc_attr( $this->addon_slug ) . $ssl_class . '" data-addon-slug="' . esc_attr( $this->addon_slug ) . '">';
		return $html;
	}

	/**
	 * Generates the closing HTML for the wrapper div
	 *
	 * @since 1.3.0
	 *
	 * @return HTML
	*/
	function get_wrapper_close() {
		$html = '</div>';
		return $html;
	}

	/**
	 * Generates the purchase form
	 *
	 * @since 1.3.0
	 *
	 * @return HTML
	*/
	function get_purchase_form() {
		$GLOBALS['it_exchange']['purchase-dialog']['transaction-method-slug'] = $this->addon_slug;

		$form_open          = $this->get_form_open();
		$form_hidden_fields = $this->get_form_hidden_fields();
		$form_fields        = $this->get_form_fields();
		$form_actions       = $this->get_form_actions();
		$form_close         = $this->get_form_close();

		$form = $form_open . $form_hidden_fields . $form_fields . $form_actions . $form_close;

		unset( $GLOBALS['it_exchange']['purchase-dialog']['transaction-method-slug'] );
		return $form;
	}

	/**
	 * Gets the open form field
	 *
	 * @since 1.3.0
	 *
	 * @return string HTML
	*/
	function get_form_open() {
		$form_attributes = '';
		foreach( $this->form_attributes as $key => $value ) {
			$form_attributes .= $key . '="' . esc_attr( $value ) . '" ';
		}
		$form_open = '<form ' . $form_attributes . '>';

		return $form_open;
	}

	/**
	 * Get form hidden fields
	 *
	 * @since 1.3.0
	 *
	 * @return string HTML
	*/
	function get_form_hidden_fields() {
		$fields  = '<input type="hidden" name="' . esc_attr( it_exchange_get_field_name('transaction_method') ) . '" value="' . esc_attr( $this->addon_slug ) . '" />';
		$fields .= wp_nonce_field( $this->addon_slug . '-checkout', 'ite-' . $this->addon_slug . '-purchase-dialog-nonce', true, false );
		return $fields;
	}

	/**
	 * Gets the form body
	 *
	 * @since 1.3.0
	 *
	 * @return string HTML
	*/
	function get_form_fields() {
		ob_start();
		it_exchange_get_template_part( 'content', 'purchase-dialog' );
		$fields = ob_get_clean();
		return $fields;
	}

	/**
	 * Gets the form actions
	 *
	 * @since 1.3.0
	 *
	 * @return string HTML
	*/
	function get_form_actions() {
		$actions  = '<p><input type="submit" value="' . esc_attr( $this->submit_label ) . '" /><br />';
		$actions .= '<a href="#" class="it-exchange-purchase-dialog-cancel" data-addon-slug="' . esc_attr( $this->addon_slug ) . '">' . esc_html( $this->cancel_label ) . '</a></p>';
		return $actions;
	}

	/**
	 * Get form close
	 *
	 * @since 1.3.0
	 * @return string HTML
	*/
	function get_form_close() {
		$form_close = '</form>';
		return $form_close;
	}

	/**
	 * Generates the init button that calls the dialog
	 *
	 * @since 1.3.0
	 *
	 * @return string HTML
	*/
	function get_purchase_button() {
		$error_class = it_exchange_purchase_dialog_has_error( $this->addon_slug ) ? ' has-errors' : '';
		it_exchange_clear_purchase_dialog_error_flag( $this->addon_slug );
		return '<input type="submit" class="it-exchange-purchase-dialog-trigger-' . esc_attr( $this->addon_slug ) . ' it-exchange-purchase-dialog-trigger' . $error_class . '" value="' . esc_attr( $this->purchase_label ) . '" data-addon-slug="' . esc_attr( $this->addon_slug ) . '" />';
	}

	/**
	 * Enqueues the JS for purchase dialogs
	 *
	 * @since 1.3.0
	 *
	 * @return void
	*/
	function enqueue_js() {
		//$file = dirname( __FILE__ ) . '/js/exchange-purchase-dialog.js';
		//wp_enqueue_script( 'exchange-purchase-dialog', ITUtility::get_url_from_file( $file ), array( 'jquery', 'detect-credit-card-type' ), false, true );
	}

	/**
	 * Grabs the credit card fields from $_POST
	 *
	 * @since 1.3.0
	 *
	 * @return array
	*/
	function get_submitted_form_values() {
		$fields = array(
			'it-exchange-purchase-dialog-cc-first-name'		   => 'first-name',
			'it-exchange-purchase-dialog-cc-last-name'		   => 'last-name',
			'it-exchange-purchase-dialog-cc-number'            => 'number',
			'it-exchange-purchase-dialog-cc-expiration-month'  => 'expiration-month',
			'it-exchange-purchase-dialog-cc-expiration-year'   => 'expiration-year',
			'it-exchange-purchase-dialog-cc-code'              => 'code',
		);

		$cc_data = array();
		foreach( $fields as $key => $value ) {
			$cc_data[$value] = empty( $_POST[$key] ) ? false : $_POST[$key];
		}

		// Filter available in ithemes-exchange/api/purchase-dialog.php
		return $cc_data;
	}

	/**
	 * Validates the credit card fields were populated
	 *
	 * It is up to the transaction method add-on to validate if its a good/acceptible CC
	 * This only confirms that the fields exists. Use the filter at the bottom of the function
	 * to modify validation of CC data.
	 *
	 * @since 1.3.0
	 *
	 * @todo this method could use some TLC
	 * @return boolean
	*/
	function is_submitted_form_valid() {
		// Grab the values
		$values = $this->get_submitted_form_values();

		// Validate nonce
		$nonce = empty( $_POST['ite-' . $this->addon_slug . '-purchase-dialog-nonce'] ) ? false : $_POST['ite-' . $this->addon_slug . '-purchase-dialog-nonce'];
		if ( ! wp_verify_nonce( $nonce, $this->addon_slug . '-checkout' ) ) {
			it_exchange_add_message( 'error', __( 'Transaction Failed, unable to verify security token.', 'it-l10n-ithemes-exchange' ) );
			it_exchange_flag_purchase_dialog_error( $this->addon_slug );
			return false;
		}

		foreach( (array) $values as $key => $value ) {
			$invalid  = false;
			$required = in_array( $key, $this->required_cc_fields );

			// Make sure its not empty if its required
			if ( $required && empty( $value ) )
				$invalid = __( 'Please make sure all required fields have a value.', 'it-l10n-ithemes-exchange' );

			// Make sure card number, expiration, and code have a number
			if ( ! empty( $value ) && ! in_array( $key, array( 'first-name', 'last-name' ) ) && ! is_numeric( $value ) )
				$invalid = __( 'Please make sure all fields are formatted properly.', 'it-l10n-ithemes-exchange' );

			// Filter makes it possible for add-on to make something valid that would be invalid otherwise.
			$invalid = apply_filters( 'it_exchange_validate_' . $key . '_credit_cart_field', $invalid, $value, $this->addon_slug );
			if ( $invalid ) {
				it_exchange_add_message( 'error', $invalid );
				it_exchange_flag_purchase_dialog_error( $this->addon_slug );
				return false;
			}
		}

		return true;
	}
}
