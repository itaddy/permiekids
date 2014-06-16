<?php

/**
 *
 * @package LDMW
 * @subpackage Gravity Forms
 * @since 1.0
 */
class LDMW_Gravity_Base {

	/**
	 * Add necessary hooks.
	 */
	public function __construct() {
		add_action( "gform_after_submission_" . LDMW_Gravity_Util::get_selected_application_form_id(), array( $this, 'save_submitted_application_form' ), 9999, 2 );
	}

	public function add_js_to_application_form() {
		global $IT_Exchange;
		wp_enqueue_script( 'ldmw-gravity-form', LDMW_Plugin::$url . "/lib/gravity/assets/js/form.js", array( 'jquery' ) );
		wp_enqueue_script( 'jquery-select-to-autocomplete', $IT_Exchange->_plugin_url . '/lib/assets/js/jquery.select-to-autocomplete.min.js', array( 'jquery', 'jquery-ui-autocomplete' ) );
		wp_enqueue_style( 'it-exchange-autocomplete-style', $IT_Exchange->_plugin_url . '/lib/assets/styles/autocomplete.css' );
	}

	/**
	 * Save the submitted form.
	 *
	 * @param $entry array
	 * @param $form array
	 */
	public function save_submitted_application_form( $entry, $form ) {
		$processed_entry = $this->process_lead( $entry, $form );
		LDMW_Gravity_Util::add_application_entry( $processed_entry );
		update_user_meta( $processed_entry['user_id'], 'ldmw_membership_application', $processed_entry['entry_id'] );
	}

	/**
	 * Save the communications preference form.
	 *
	 * @param $entry array
	 * @param $form array
	 */
	public function save_submitted_communication_preference_form( $entry, $form ) {
		$result = array();

		$processed_entry = $this->process_lead( $entry, $form );

		$fields = $processed_entry['fields'];
		$fields = $fields[0];
		$prefs = $fields['inputs'];

		foreach ( $prefs as $pref ) {
			$result[$pref['label']] = ! empty( $pref['value'] );
		}

		update_user_meta( $processed_entry['user_id'], 'ldmw_communication_preferences', $result );
	}

	/**
	 * Process the lead.
	 *
	 * Build an array of labels and values.
	 *
	 * @param $entry
	 * @param $form
	 *
	 * @return array
	 */
	protected function process_lead( $entry, $form ) {
		$result = array();
		$result['time'] = $entry['date_created'];
		$result['user_id'] = get_current_user_id();
		$result['form_id'] = $entry['form_id'];
		$result['entry_id'] = $entry['id'];
		$result['files'] = array();
		foreach ( $form['fields'] as $field ) {

			if ( $field['type'] == 'fileupload' ) {
				if ( $field['multipleFiles'] == 1 ) {
					$files = $entry[$field['id']];

					$files = ltrim( $files, '[' ); // remove first [
					$files = rtrim( $files, ']' ); // remove last ]

					if ( empty( $files ) )
						continue;

					$files = explode( ",", $files ); // it is a comma separated list of URLs

					foreach ( $files as $file ) {
						$url = urldecode( stripslashes( $file ) );
						$name = explode( '/', parse_url( $url, PHP_URL_PATH ) );
						$name = end( $name );
						$name = sanitize_text_field( $name );

						$pathinfo = pathinfo( $name );
						$name = str_replace( array( "-", "_" ), " ", $pathinfo['filename'] );

						$result['files'][] = array(
						  'label' => $name,
						  'value' => $url
						);
					}

				}
				else {
					if ( ! empty( $entry[$field['id']] ) ) {
						$result['files'][] = array(
						  "label" => $field['label'],
						  "value" => $entry[$field['id']]
						);
					}
				}
				continue;
			}

			if ( $field['type'] == LDMW_Gravity_Fields_Division::type ) {
				$result['division'] = $entry[$field['id']];
				continue;
			}

			if ( $field['type'] == LDMW_Gravity_Fields_Grade::type ) {
				$result['grade'] = $entry[$field['id']];
				continue;
			}

			if ( $field['type'] == LDMW_Gravity_Fields_Type::type ) {
				$result['application_type'] = $entry[$field['id']];
				continue;
			}

			if ( trim( strtolower( $field['label'] ) ) == "confirmation" ) {
				$current_field = array(
				  'label' => 'Confirmation',
				  'value' => 'Yes'
				);
				$result['fields'][] = $current_field;
				continue;
			}

			$current_field = array();
			$current_field['label'] = $field['label'];

			if ( ! empty( $field['inputs'] ) ) {
				foreach ( $field['inputs'] as $input ) {
					$current_field['inputs'][] = array(
					  'label' => $input['label'],
					  'value' => $entry[strval( $input['id'] )]
					);
				}
			}
			else {
				$current_field['value'] = $entry[$field['id']];
			}

			$result['fields'][] = $current_field;
		}

		return $result;
	}

}