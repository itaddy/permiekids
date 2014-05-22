<?php
/**
 * This class will build a form for an admin settings page.
 *
 * It can be extended as needed or simply invoked by calling it_exchange_print_admin_settings_form()
 * and passing the correct options to the constructor.
 * By default, it will look for POST data and attempt to save. If you need to do this on your own, you can disable
 * the save functionality by passing save-on-load => false
 *
 * @since 1.3.1
*/
class IT_Exchange_Admin_Settings_Form {

	var $prefix             = false;
	var $form_fields        = array();
	var $form_options       = array();
	var $field_values       = array();
	var $button_options     = array();
	var $saved_settings     = array();
	var $country_states_js  = false;

	/**
	 * Constructor Sets up the object
	 *
	 * @since 1.3.1
	 *
	 * @return void
	*/
	function IT_Exchange_Admin_Settings_Form( $args ) {

		// Default Settings
		$defaults = array(
			'prefix'  => false,
			'form-fields'  => array(),
			'form-options' => array(
				'id'      => false,
				'enctype' => false,
				'action'  => false,
			),
			'button-options' => array(
				'save-button-label' => __( 'Save Changes', 'it-l10n-ithemes-exchange' ),
				'save-button-class' => 'button button-primary',
			),
			'country-states-js' => false,
			'save-on-load'      => true,
		);

		// Merge defaults
		$options = ITUtility::merge_defaults( $args, $defaults );

		// If no prefix or form fields, return false
		if ( empty( $options['prefix'] ) || empty( $options['form-fields'] ) )
			return false;

		// Set prefix and form fields
		$this->prefix      = $options['prefix'];
		$this->form_fields = $options['form-fields'];

		// Update settings if form was submitted
		if ( ! empty( $options['save-on-load'] ) )
			$this->save_settings();

		// Set form options
		$this->form_options   = $options['form-options'];
		$this->button_options = $options['button-options'];

		// Set form options
		$this->set_form_options( $options['form-options'] );

		// Set form fields
		$this->set_form_fields( $options['form-fields'] );

		// Loads settings saved previously
		$this->load_settings();

		// Do we want to include the country states JS?
		$this->set_country_states_js( $options['country-states-js'] );
	}

	/**
	 * Checks the default form options and sets them if empty
	 *
	 * @since 1.3.1
	 *
	 * @param  array $form_options the options for the HTML form tag
	 * @return void
	*/
	function set_form_options( $options ) {

		// Validate Options
		$options['id']      = empty( $options['id'] ) ? 'it-exchange-' . $this->prefix : $options['id'];
		$options['action']  = empty( $options['action'] ) ? '' : $options['action'];
		$options['enctype'] = empty( $options['enctype'] ) ? '' : $options['enctype'];

		// Update property
		$this->form_options = $options;
	}

	/**
	 * Sets the form fields property
	 *
	 * @since 1.3.1
	 *
	 * @return void
	*/
	function set_form_fields( $fields ) {
		// Update property
		$this->form_fields = $fields;
	}

	/**
	 * Grabs existing settings and loads them in the object property
	 *
	 * @since 1.3.1
	 *
	 * @return void
	*/
	function load_settings() {
		add_filter( 'it_storage_get_defaults_exchange_' . $this->prefix, array( $this, 'get_default_settings' ) );
		$settings = it_exchange_get_option( $this->prefix, true );
		$this->settings = apply_filters( 'it_exchange_load_admin_form_settings_for_' . $this->prefix, $settings );
	}

	/**
	 * Gives the default settings to the ITStorage API
	 *
	 * @since 1.3.1
	 *
	 * @param  array $options
	 * @return array
	*/
	function get_default_settings( $options ) {
		foreach( (array) $this->form_fields as $field ) {
			$options[$field['slug']] = empty( $field['default'] ) ? '' : $field['default'];
		}
		return $options;
	}

	/**
	 * Print the form
	 *
	 * @since 1.3.1
	 *
	 * @return void
	*/
	function print_form() {
		$this->print_messages();
		$this->set_field_values();
		$this->init_form();
		$this->start_form();
		$this->print_fields();
		$this->print_actions();
		$this->end_form();
	}

	/**
	 * Sets form field values for this page load
	 *
	 * Uses POST data, Previously saved settings, Defaults
	 *
	 * @since 1.3.1
	 *
	 * @return void
	*/
	function set_field_values() {
		$this->field_values  = ! it_exchange_has_messages( 'error' ) ? $this->settings : ITForm::get_post_data();
	}

	/**
	 * Init the form
	 *
	 * @return void
	*/
	function init_form() {
		// Init the form
		$this->form = new ITForm( $this->field_values, array( 'prefix' => $this->prefix ) );
	}

	/**
	 * Start the form
	 *
	 * Prints the opening form HTML tag
	 *
	 * @since 1.3.1
	 *
	 * @return void
	*/
	function start_form() {
		$this->form->start_form( $this->form_options, $this->prefix );
	}

	/**
	 * Prints the messages if they are present
	 *
	 * @since 1.3.1
	 *
	 * @return void
	*/
	function print_messages() {
		// Print errors if they exist
		if ( it_exchange_has_messages( 'error' ) ) {
			foreach( it_exchange_get_messages( 'error' ) as $message ) {
				ITUtility::show_error_message( $message );
			}
		}

		// Print notices if they exist
		if ( it_exchange_has_messages( 'notice' ) ) {
			foreach( it_exchange_get_messages( 'notice' ) as $message ) {
				ITUtility::show_status_message( $message );
			}
		}
	}

	/**
	 * Prints the form fields
	 *
	 * @since 1.3.1
	 *
	 * @return void
	*/
	function print_fields() {
		?>
		<table class="form-table">
			<?php do_action( 'it_exchange_' . $this->prefix . '_top' ); ?>
			<?php
			foreach( $this->form_fields as $row => $field ) {
				$field['options'] = empty( $field['options'] ) ? array() : $field['options'];
				if ( 'heading' == $field['type'] ) {
					$this->print_heading_row( $field );
				} else {
					$form_method = 'add_' . $field['type'];
					if ( is_callable( array( $this->form, $form_method ) ) )
						$this->print_setting_row( $field, $form_method );
					else
						$this->print_uncallable_method_row( $field );
				}
			}
			// Add a hidden field to identify this form
			$this->form->add_hidden( 'it-exchange-saving-settings', true );
			?>
			<?php do_action( 'it_exchange_' . $this->prefix . '_bottom' ); ?>
		</table>
		<?php

		// Include Country State JS if needed
		if ( is_array( $this->country_states_js ) )
			$this->print_country_states_js();
	}

	/**
	 * Prints the form actions
	 *
	 * @since 1.3.1
	 *
	 * @return void
	*/
	function print_actions() {
		?>
		<p class="submit">
			<input type="submit" value="<?php esc_attr_e( $this->button_options['save-button-label'] ); ?>" class="<?php esc_attr_e( $this->button_options['save-button-class'] ); ?>" />
		</p>
		<?php
	}

	/**
	 * Prints the close of the form
	 *
	 * @since 1.3.1
	 *
	 * @return void
	*/
	function end_form() {
		$this->form->end_form();
	}

	function print_heading_row( $heading ) {
		?>
		<tr valign="top">
			<th scope="row"><strong><?php echo $heading['label']; ?></strong></th>
			<td></td>
		</tr>
		<?php
	}

	/**
	 * Prints a table row with the setting
	 *
	 * @since 1.3.1
	 *
	 * @return void
	*/
	function print_setting_row( $setting, $form_method ) {
		?>
		<tr valign="top" id="<?php esc_attr_e( $setting['slug'] ); ?>-table-row">
			<th scope="row" id="<?php esc_attr_e( $setting['slug'] ); ?>-table-row-head"><label for="<?php esc_attr_e( $setting['slug'] ); ?>"><?php echo $setting['label']; ?>
				<?php
				if ( ! empty( $setting['tooltip'] ) ) {
					echo '<span class="tip" title="' . esc_attr( $setting['tooltip'] ) . '">i</span>';
				}
				?>
				</label>
			</th>
			<td id="<?php esc_attr_e( $setting['slug'] ); ?>-wrapper">
				<?php echo empty( $setting['before'] ) ? '' : $setting['before']; ?>
				<?php $this->form->$form_method( $setting['slug'], $setting['options'] ); ?>
				<?php echo empty( $setting['after'] ) ? '' : $setting['after']; ?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Prints a warning if the setting has an uncallable method
	 *
	 * @since 1.3.1
	 *
	 * @return void
	*/
	function print_uncallable_method_row( $setting ) {
		?>
		<tr valign="top">
			<th scope="row" class="error"><strong><?php _e( 'Coding Error!', 'it-l10n-ithemes-exchange' ); ?></strong></th>
			<td id="<?php esc_attr_e( $setting['slug'] ); ?>-wrapper"><?php printf( __( 'The setting for %s has an incorrect type argument. No such method exists in the ITForm class', 'it-l10n-ithemes-exchange' ), $setting['slug'] ); ?></td>
		</tr>
		<?php
	}

	/**
	 * Saves the settings via ITStorage
	 *
	 * @since 1.3.1
	 *
	 * @return void
	*/
	function save_settings() {
		// Abandon if not processing
		if ( empty( $_POST['_wpnonce'] ) || empty( $_POST[$this->prefix . '-it-exchange-saving-settings'] ) )
			return;

		// Log error if nonce wasn't set
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], $this->prefix ) ) {
			it_exchange_add_message( 'error', __( 'Invalid security token. Please try again', 'it-l10n-ithemes-exchange' ) );
			return;
		}

		$values = ITForm::get_post_data();
		unset( $values['it-exchange-saving-settings'] );

		$values = apply_filters( 'it_exchange_save_admin_form_settings_for_' . $this->prefix, $values );

		it_exchange_save_option( $this->prefix, $values );
		it_exchange_add_message( 'notice', __( 'Settings updated', 'it-l10n-ithemes-exchange' ) );
	}

	/**
	 * Set the country state js property
	 *
	 * @since 1.3.1
	 *
	 * @param  array $args args needed to pass to the jQuery plugin
	 * @return void
	*/
	function set_country_states_js( $args ) {

		// Return false if we're missing any required vars
		if (
			empty( $args['country-id'] ) ||
			empty( $args['states-id'] ) ||
			empty( $args['states-wrapper'] )
		){
			$this->country_states_js = false;
		} else {
			$this->country_states_js = $args;
			$url = ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/assets/js/country-states-sync.js' );
			wp_enqueue_script( 'it-exchange-country-states-sync', $url, array( 'jquery' ), false, true );
		}
	}

	/**
	 * Prints the JS that binds the country state sync JS to the country field
	 *
	 * @since 1.3.1
	 *
	 * @return void
	*/
	function print_country_states_js() {
		$country_id     = empty( $this->country_states_js['country-id'] ) ? '' : $this->country_states_js['country-id'];
		$states_id      = empty( $this->country_states_js['states-id'] ) ? '' : $this->country_states_js['states-id'];
		$states_wrapper = empty( $this->country_states_js['states-wrapper'] ) ? '' : $this->country_states_js['states-wrapper'];
		$template_part  = empty( $this->country_states_js['template-part'] ) ? '' : $this->country_states_js['template-part'];
		?>
		<script type="text/javascript">
			var itExchangeAjaxCountryStatesAjaxURL = '<?php echo esc_js( trailingslashit( get_site_url() ) ); ?>';
			jQuery(function(){
				jQuery('#<?php echo esc_js( $country_id ); ?>').itCountryStatesSync(
					{
						stateWrapper: '<?php echo esc_js( $states_wrapper ); ?>',
						stateFieldID: '<?php echo esc_js( $states_id ); ?>',
						adminPrefix:  '<?php echo esc_js( $this->prefix ); ?>'
					}
				).trigger('change');
			});
		</script>
		<?php
	}
}
