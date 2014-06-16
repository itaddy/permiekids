<?php

/**
 * @package LDMW
 * @subpackage Gravity Forms/Fields
 * @since 1.0
 *
 * @link http://wpsmith.net/2011/plugins/how-to-create-a-custom-form-field-in-gravity-forms-with-a-terms-of-service-form-field-example/
 * @original_author Travis Smith
 */
class LDMW_Gravity_Fields_Type {
	const type = "aas_types";

	/**
	 * Add necessary hooks and filters.
	 */
	public function __construct() {
		add_filter( 'gform_add_field_buttons', array( $this, 'add_field' ) );
		add_filter( 'gform_field_type_title', array( $this, 'add_title' ) );
		add_action( "gform_field_input", array( $this, "field_input" ), 10, 5 );
		add_action( "gform_editor_js", array( $this, "editor_js" ) );
		add_action( "gform_field_css_class", array( $this, "custom_class" ), 10, 3 );
	}

	/**
	 * Add a custom field button to the advanced to the field editor
	 *
	 * @param $field_groups array
	 *
	 * @return array
	 */
	function add_field( $field_groups ) {
		foreach ( $field_groups as &$group ) {
			if ( $group["name"] == "advanced_fields" ) { // to add to the Advanced Fields
				$group["fields"][] = array(
				  "class"   => "button",
				  "value"   => __( "AAS App. Type" ),
				  "onclick" => "StartAddField('" . self::type . "');"
				);
				break;
			}
		}

		return $field_groups;
	}

	/**
	 * Adds title to GF custom field
	 *
	 * @param $type string
	 *
	 * @return string|void
	 */
	function add_title( $type ) {
		if ( $type == self::type )
			return __( 'AAS Application Types' );
	}

	/**
	 * Adds the input area to the external site
	 *
	 * @param $input
	 * @param $field
	 * @param $value
	 * @param $lead_id
	 * @param $form_id
	 *
	 * @return string
	 */
	function field_input( $input, $field, $value, $lead_id, $form_id ) {

		if ( $field["type"] == self::type ) {
			$max_chars = "";
			if ( ! IS_ADMIN && ! empty( $field["maxLength"] ) && is_numeric( $field["maxLength"] ) )
				$max_chars = self::get_counter_script( $form_id, $field_id, $field["maxLength"] );

			$input_name = $form_id . '_' . $field["id"];
			$tabindex = GFCommon::get_tabindex();
			$css = isset( $field['cssClass'] ) ? $field['cssClass'] : '';
			ob_start();
			?>
			<div class="ginput_container">
			<select name="input_<?php echo $field['id']; ?>" id="<?php echo self::type . "-" . $field['id']; ?>" class="select gform_<?php echo self::type; ?> <?php echo esc_attr( $css ); ?> <?php echo $field['size']; ?>" <?php echo $tabindex; ?>>
			        <?php foreach ( LDMW_Application_Util::get_application_types() as $slug => $type ) : ?>
				        <option value="<?php echo $slug; ?>" <?php if ( $field['allowsPrepopulate'] && ! empty( $field['inputName'] ) && isset( $_GET[$field['inputName']] ) ) {
					        selected( $_GET[$field['inputName']], $slug );
				        } ?>><?php echo $type; ?></option>
			        <?php endforeach; ?>
		        </select>
			</div><?php echo $max_chars; ?>
			<?php
			return ob_get_clean();
		}

		return $input;
	}

	/**
	 * Add settings that this field supports.
	 */
	function editor_js() {
		?>
		<script type='text/javascript'>

	    jQuery( document ).ready( function ( $ ) {
		    fieldSettings["<?php echo self::type ?>"] = ".label_setting, .description_setting, .admin_label_setting, .size_setting, .error_message_setting, .css_class_setting, .visibility_setting, .prepopulate_field_setting";
	    } );

	</script><?php
	}

	/**
	 * Add custom class to the field <li>
	 *
	 * @param $classes
	 * @param $field
	 * @param $form
	 *
	 * @return string
	 */
	function custom_class( $classes, $field, $form ) {
		if ( $field["type"] == self::type ) {
			$classes .= " gform_" . self::type;
		}

		return $classes;
	}
}