<?php

/**
 *
 * @package LDMW
 * @subpackage Settings
 * @since 1.0
 */
class LDMW_Options_View {
	/**
	 * @var array hold plugin options fields
	 */
	private $options_fields = array();

	/**
	 * Holds data from options
	 *
	 * @var array
	 */
	private $options = array();

	/**
	 * Set up everything
	 */
	public function init() {

		$controller = LDMW_Options_Controller::get_instance();
		$this->options_fields = $controller->get_fields();

		if ( isset( $_POST['ldmw_options_nonce'] ) ) {
			$data = $this->get_data();
			$this->options = $data;
			$controller->save( $data );
			$this->admin_notice();
		}

		$options = LDMW_Options_Model::get_instance();
		$this->options = $options->get_options();

		wp_create_nonce( 'ldmw_options_update' );

		$this->render();
	}

	/**
	 * Grab POST data
	 */
	public function get_data() {
		if ( ! wp_verify_nonce( $_POST['ldmw_options_nonce'], 'ldmw_options_update' ) )
			wp_die( 'Permissions check failed.' );

		$values = array();

		foreach ( $this->options_fields as $key => $value ) {
			if ( isset( $_POST[$key] ) )
				$values[$key] = $_POST[$key];
			else if ( isset( $this->options_fields[$key]['default'] ) ) {
				if ( $this->options_fields[$key]['field_type'] == 'checkbox' )
					$values[$key] = false;
				else
					$values[$key] = $this->options_fields[$key]['default'];
			}

		}

		return $values;
	}

	/**
	 * Print Options Updated Notice
	 */
	public function admin_notice() {
		echo '<div class="updated"><p>' . __( 'Options Updated' ) . '</p></div>';
	}

	/**
	 * Render option fields with type text
	 *
	 * @param $option_field
	 */
	public function render_text( $option_field ) {
		?>

		<label for="<?php echo esc_attr( $option_field['slug'] ); ?>"><?php echo $option_field['label']; ?></label>
		<input type="text" id="<?php echo esc_attr( $option_field['slug'] ); ?>" name="<?php echo esc_attr( $option_field['slug'] ); ?>" value="<?php echo esc_attr( $this->options[$option_field['slug']] ); ?>"


        <?php if ( isset( $option_field['field_args'] ) ) : ?>
			<?php foreach ( $option_field['field_args'] as $attr => $value ) : ?>
				<?php echo $attr; ?>="<?php echo $value; ?>"
			<?php endforeach; ?>
		<?php endif; ?>
		  >
		<p><?php echo $option_field['description']; ?></p>

	<?php
	}

	/**
	 * Render option fields with type date
	 *
	 * @param $option_field
	 */
	public function render_date( $option_field ) {

		if ( ! empty( $option_field['options']['date-format'] ) )
			$format = $option_field['options']['date-format'];
		else
			$format = get_option( 'date_format', "m/d/y" );

		if ( ! empty( $this->options[$option_field['slug']] ) ) {
			$date = new DateTime( "@{$this->options[$option_field['slug']]}" );
			$date = $date->format( $format );
		}
		else {
			$date = "";
		}

		$jquery_format = LDMW_Util::dateStringToDatepickerFormat( $format );
		?>

		<label for="<?php echo esc_attr( $option_field['slug'] ); ?>"><?php echo $option_field['label']; ?></label>
		<input type="text" class="datepicker" id="<?php echo esc_attr( $option_field['slug'] ); ?>" name="<?php echo esc_attr( $option_field['slug'] ); ?>" data-date-format="<?php echo esc_attr( $jquery_format ); ?>" value="<?php echo esc_attr( $date ); ?>"

        <?php if ( isset( $option_field['field_args'] ) ) : ?>

			<?php foreach ( $option_field['field_args'] as $attr => $value ) : ?>
				<?php echo $attr; ?>="<?php echo $value; ?>"
			<?php endforeach; ?>

		<?php endif; ?>>

		<p><?php echo $option_field['description']; ?></p>

	<?php
	}

	/**
	 * Render options fields with type number
	 *
	 * @param $option_field
	 */
	public function render_number( $option_field ) {
		?>

		<label for="<?php echo esc_attr( $option_field['slug'] ); ?>"><?php echo $option_field['label']; ?></label>
		<input type="number" id="<?php echo esc_attr( $option_field['slug'] ); ?>" name="<?php echo esc_attr( $option_field['slug'] ); ?>" value="<?php echo esc_attr( $this->options[$option_field['slug']] ); ?>"

        <?php if ( isset( $option_field['field_args'] ) ) : ?>

			<?php foreach ( $option_field['field_args'] as $attr => $value ) : ?>
				<?php echo $attr; ?>="<?php echo $value; ?>"
			<?php endforeach; ?>

		<?php endif; ?>>

		<p><?php echo $option_field['description']; ?></p>

	<?php
	}

	/**
	 * Render options fields with type checkbox
	 *
	 * @param $option_field
	 */
	public function render_checkbox( $option_field ) {
		?>

		<label for="<?php echo esc_attr( $option_field['slug'] ); ?>"><?php echo $option_field['label']; ?></label>
		<input type="checkbox" id="<?php echo esc_attr( $option_field['slug'] ); ?>" name="<?php echo esc_attr( $option_field['slug'] ); ?>"
		       <?php echo true === (bool) $this->options[$option_field['slug']] ? 'checked="checked"' : ""; ?>

		<?php if ( isset( $option_field['field_args'] ) ) : ?>

			<?php foreach ( $option_field['field_args'] as $attr => $value ) : ?>
				<?php echo $attr; ?>="<?php echo $value; ?>"
			<?php endforeach; ?>

		<?php endif; ?>>

		<span><?php echo $option_field['description']; ?></span>

	<?php
	}

	/**
	 * Render options fields with type select.
	 *
	 * @param $option_field
	 */
	public function render_select( $option_field ) {
		?>

		<label for="<?php echo esc_attr( $option_field['slug'] ); ?>"><?php echo $option_field['label']; ?></label>
		<select id="<?php echo esc_attr( $option_field['slug'] ); ?>" name="<?php echo esc_attr( $option_field['slug'] ); ?>"

		<?php if ( isset( $option_field['field_args'] ) ) : ?>

			<?php foreach ( $option_field['field_args'] as $attr => $value ) : ?>
				<?php echo $attr; ?>="<?php echo $value; ?>"
			<?php endforeach; ?>

		<?php endif; ?>>


		<?php foreach ( $option_field['options'] as $slug => $option ) : ?>
			<option value="<?php echo $slug; ?>" <?php selected( $slug, $this->options[$option_field['slug']] ); ?>><?php echo $option; ?></option>
		<?php endforeach; ?>

		</select>

		<p><?php echo $option_field['description']; ?></p>

	<?php
	}

	/**
	 * Render options field with type editor.
	 *
	 * @param $option_field array
	 */
	public function render_editor( $option_field ) {
		?>
		<label for="<?php echo $option_field['slug']; ?>"><?php echo $option_field['label']; ?></label>
		<p><?php echo $option_field['description']; ?></p>

		<?php wp_editor( $this->options[$option_field['slug']], $option_field['slug'], $option_field['options'] ); ?>
		<br>
	<?php
	}

	/**
	 * Render options field with type description.
	 *
	 * @param $option_field array
	 */
	public function render_description( $option_field ) {
		?>

		<label><?php echo $option_field['label']; ?></label>
		<p><?php echo $option_field['description']; ?></p>

	<?php
	}

	/**
	 * Render section titles
	 *
	 * @param $option_field
	 */
	public function render_section_title( $option_field ) {
		$heading_size = isset( $option_field['heading_size'] ) ? $option_field['heading_size'] : "h3";
		echo "<$heading_size>" . $option_field['title'] . "</$heading_size>";
	}

	/**
	 * Render dividers
	 */
	public function render_hr() {
		echo '<hr class="light">';
	}

	/**
	 * Render the markup of the page
	 */
	public function render() {
		?>

		<div class="wrap">
			<h2><?php _e( "Options" ); ?></h2>

			<form id="fence-plus-options-form" action="#" method="post">

				<?php foreach ( $this->options_fields as $option_field ) : ?>

					<?php call_user_func( array( $this, 'render_' . $option_field['field_type'] ), $option_field ); ?>

				<?php endforeach; ?>

				<input type="submit" class="button button-primary" value="Update">

				<?php wp_nonce_field( 'ldmw_options_update', 'ldmw_options_nonce' ); ?>
			</form>
		</div>

	<?php
	}
}