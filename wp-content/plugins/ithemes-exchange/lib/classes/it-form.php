<?php

/*
Written by Chris Jean for iThemes.com
Version 2.6.0

Version History
	2.0.0 - 2011-02-22 - Chris Jean
		Complete rewrite
	2.1.0 - 2011-07-01 - Chris Jean
		Added optgroup support for select inputs
	2.2.0 - 2011-07-06 - Chris Jean
		Added get_option function
	2.3.0 - 2011-12-05 - Chris Jean
		Updated the start_form function to handle forms on core
			admin pages better
	2.4.0 - 2012-09-24 - Chris Jean
		Updated end_form to clean up variables to be ready for a new call to start_form.
		Updated end_form to automatically add render_clean hiddens when needed.
		Fixed bug in checkbox checked logic.
	2.4.1 - 2013-01-25 - Chris Jean
		Updated get_post_data to use '' rather than null for empty values. This avoids bugs when arrays are merged as a
			null value will be overridden.
	2.4.2 - 2013-02-15 - Chris Jean
		Added esc_value_attr function to better escape value attributes.
		Updated all value attribute sections to use the esc_value_attr function.
	2.5.0 - 2013-03-05 - Chris Jean
		Added get_password and add_password functions.
	2.5.1 - 2013-06-25 - Chris Jean
		Changed static function declarations to "public static".
		Rewrote $file calculation in start_form() in order to avoid strict standards warning in PHP 5.5.0.
	2.6.0 - 2013-11-25 - Chris Jean
		Added the option for drop downs to have dividers by using __optgroup_\d+ indexes in the options array.

Notes:
	Need to fix $this->_var support or handle used_inputs better
*/


if ( ! class_exists( 'ITForm' ) ) {
	it_classes_load( 'it-utility.php' );
	
	class ITForm {
		var $_used_inputs = array();
		var $_options = array();
		var $_args = array();
		var $_var = '';
		var $_used_inputs_printed = false;
		var $_input_group = '';
		var $_input_group_stack = array();
		
		
		function ITForm( $options = array(), $args = array() ) {
			if ( is_bool( $args ) )
				$args = array( 'compact_used_inputs' => $args );
			
			$default_args = array(
				'compact_used_inputs' => false,
				'prefix'              => '',
				'widget_instance'     => null,
			);
			$args = array_merge( $default_args, $args );
			
			if ( ! empty( $args['defaults'] ) && is_array( $args['defaults'] ) ) {
				foreach ( (array) $args['defaults'] as $var => $val )
					$options["default_option_$var"] = $val;
			}
			
			$this->_args =& $args;
			$this->_options =& $options;
		}
		
		
		public static function get_post_data( $compact_used_inputs = false ) {
			$data = array();
			
			if ( ! empty( $_POST['used-inputs'] ) ) {
				if ( isset( $_POST['__it-form-compact-used-inputs'] ) && ( '1' === $_POST['__it-form-compact-used-inputs'] ) && preg_match_all( '/([^{},]*){([^}]+)}/', $_POST['used-inputs'], $matches, PREG_SET_ORDER ) ) {
					foreach ( (array) $matches as $match ) {
						foreach ( (array) explode( ',', $match[2] ) as $name ) {
							$var = "{$match[1]}$name";
							$val = ITUtility::get_array_value( $_POST, $var );
							
							if ( null === $val )
								$val = '';
							
							if ( ! empty( $_POST['__it-form-prefix'] ) )
								$var = preg_replace( "|^{$_POST['__it-form-prefix']}-|", '', $var );
							
							ITUtility::add_array_value( $data, $var, $val );
						}
					}
				}
				else {
					foreach ( (array) explode( ',', $_POST['used-inputs'] ) as $var ) {
						$val = ITUtility::get_array_value( $_POST, $var );
						
						if ( null === $val )
							$val = '';
						
						if ( ! empty( $_POST['__it-form-prefix'] ) )
							$var = preg_replace( "|^{$_POST['__it-form-prefix']}-|", '', $var );
						
						ITUtility::add_array_value( $data, $var, $val );
					}
				}
			}
			else {
				$skip = array( '_wpnonce', '_wp_http_referer', 'used-inputs', '__it-form-prefix' );
				
				foreach ( (array) $_POST as $var => $val ) {
					if ( in_array( $var, $skip ) )
						continue;
					
					$data[$var] = $val;
				}
			}
			
			return stripslashes_deep( $data );
		}
		
		public static function parse_values( $values = array(), $args = array() ) {
			$new_values = array();
			
			foreach ( (array) $values as $var => $val ) {
				if ( ! empty( $args['prefix'] ) ) {
					if ( preg_match( "/^{$args['prefix']}-(.+)/", $var, $matches ) )
						$var = $matches[1];
					else
						continue;
				}
				
				$new_values[$var] = $val;
			}
			
			return $new_values;
		}
		
		
		function start_form( $options = array(), $nonce_var = null ) {
			if ( isset( $_REQUEST['page'] ) ) {
				list ( $location, $query ) = explode( '?', $_SERVER['REQUEST_URI'] );
				$file = basename( $location );
				
				if ( 'admin.php' == $file )
					$default_action = "$location?page={$_REQUEST['page']}";
				else if ( ( 'edit.php' == $file ) && isset( $_REQUEST['post_type'] ) )
					$default_action = "$location?post_type={$_REQUEST['post_type']}&page={$_REQUEST['page']}";
			}
			
			if ( ! isset( $default_action ) )
				$default_action = $_SERVER['REQUEST_URI'];
			
			
			$defaults = array(
				'id'      => 'posts-filter',
				'enctype' => 'multipart/form-data',
				'method'  => 'post',
				'action'  => $default_action,
			);
			
			$options = array_merge( $defaults, $options );
			
			echo '<form';
			
			foreach ( (array) $options as $var => $val ) {
				if ( ! is_array( $val ) ) {
					$val = str_replace( '"', '&quot;', $val );
					echo " $var=\"$val\"";
				}
			}
			
			echo ">\n";
			
			if ( false !== $nonce_var )
				ITForm::add_nonce( $nonce_var );
		}
		
		function end_form() {
			if ( ! empty( $_REQUEST['render_clean'] ) )
				$this->add_hidden_no_save( 'render_clean', $_REQUEST['render_clean'], true );
			if ( ! empty( $this->_args['prefix'] ) )
				$this->add_hidden_no_save( '__it-form-prefix', $this->_args['prefix'], true );
			if ( false === $this->_used_inputs_printed )
				$this->add_used_inputs();
			
			$this->_used_inputs_printed = false;
			$this->_used_inputs = array();
			$this->_input_group = '';
			$this->_input_group_stack = array();
			
			echo "</form>\n";
		}
		
		function set_input_group() {
			$this->remove_input_group( true );
			
			$args = func_get_args();
			
			call_user_func_array( array( &$this, 'add_input_group' ), $args );
		}
		
		function add_input_group() {
			$args = func_get_args();
			
			$this->_input_group = '';
			
			if ( is_array( $args[0] ) )
				$args = $args[0];
			
			if ( ( 1 === count( $args ) ) && ! is_string( $args[0] ) && ! is_numeric( $args[0] ) )
				$args = array();
			
			$this->_input_group_stack = array_merge( $this->_input_group_stack, $args );
			
			$this->_generate_input_group();
		}
		
		function remove_input_group( $remove_all = false ) {
			if ( true === $remove_all )
				$this->_input_group_stack = array();
			else
				array_pop( $this->_input_group_stack );
			
			$this->_generate_input_group();
		}
		
		function push_input_groups() {
			if ( ! isset( $this->_input_group_stack_cache ) || ! is_array( $this->_input_group_stack_cache ) )
				$this->_input_group_stack_cache = array();
			
			array_push( $this->_input_group_stack_cache, $this->_input_group_stack );
		}
		
		function pop_input_groups() {
			if ( ! is_array( $this->_input_group_stack_cache ) || empty( $this->_input_group_stack_cache ) )
				return
			
			$this->_input_group_stack = array_pop( $this->_input_group_stack_cache );
			
			$this->_generate_input_group();
		}
		
		function _generate_input_group() {
			$args = $this->_input_group_stack;
			
			$this->_input_group = '';
			
			if ( ! empty( $args ) ) {
				$this->_input_group = $args[0];
				
				for ( $x = 1; $x < count( $args ); $x++ ) {
					if ( ! is_array( $args[$x] ) && ! is_object( $args[$x] ) )
						$this->_input_group .= '[' . ( (string) $args[$x] ) . ']';
				}
			}
		}
		
		function get_option( $name ) {
			if ( isset( $this->_options[$name] ) )
				return $this->_options[$name];
			return null;
		}
		
		function set_option( $name, $value ) {
			$this->_options[$name] = $value;
		}
		
		function set_options( $options = array() ) {
			$this->_options = $options;
		}
		
		function clear_options() {
			$this->_options = array();
		}
		
		function push_options() {
			if ( ! isset( $this->_options_cache ) || ! is_array( $this->_options_cache ) )
				$this->_options_cache = array();
			
			array_push( $this->_options_cache, $this->_options );
		}
		
		function pop_options() {
			if ( ! is_array( $this->_options_cache ) || empty( $this->_options_cache ) )
				return;
			
			$this->_options = array_pop( $this->_options_cache );
		}
		
		public static function add_nonce( $name = null ) {
			wp_nonce_field( $name );
		}
		
		public static function check_nonce( $name = null ) {
			check_admin_referer( $name );
		}
		
		function new_form() {
			$this->_used_inputs = array();
			$this->_used_inputs_printed = false;
		}
		
		function get_submit( $var, $options = array(), $override_value = true ) {
			if ( ! is_array( $options ) )
				$options = array( 'value' => $options );
			
			$options['type'] = 'submit';
			$options['name'] = $var;
			$options['class'] = ( empty( $options['class'] ) ) ? 'button-primary' : $options['class'];
			
			return $this->_get_simple_input( $var, $options, $override_value );
		}
		
		function add_submit( $var, $options = array(), $override_value = true ) {
			echo $this->get_submit( $var, $options, $override_value );
		}
		
		function get_button( $var, $options = array(), $override_value = true ) {
			if ( ! is_array( $options ) )
				$options = array( 'value' => $options );
			
			$options['type'] = 'button';
			$options['name'] = $var;
			
			return $this->_get_simple_input( $var, $options, $override_value );
		}
		
		function add_button( $var, $options = array(), $override_value = true ) {
			echo $this->get_button( $var, $options, $override_value );
		}
		
		function get_close_thickbox_button( $var, $options = array(), $override_value = true ) {
			if ( ! is_array( $options ) )
				$options = array( 'value' => $options );
			
			$options['type'] = 'button';
			$options['name'] = $var;
			$options['onclick'] = 'close_thickbox();';
			
			return $this->_get_simple_input( $var, $options, $override_value );
		}
		
		function add_close_thickbox_button( $var, $options = array(), $override_value = true ) {
			echo $this->get_close_thickbox_button( $var, $options, $override_value );
		}
		
		function get_text_box( $var, $options = array(), $override_value = false ) {
			if ( ! is_array( $options ) )
				$options = array( 'value' => $options );
			
			$options['type'] = 'text';
			
			return $this->_get_simple_input( $var, $options, $override_value );
		}
		
		function add_text_box( $var, $options = array(), $override_value = false ) {
			echo $this->get_text_box( $var, $options, $override_value );
		}
		
		function get_text_area( $var, $options = array(), $override_value = false ) {
			if ( ! is_array( $options ) )
				$options = array( 'value' => $options );
			
			$options['type'] = 'textarea';
			
			return $this->_get_simple_input( $var, $options, $override_value );
		}
		
		function add_text_area( $var, $options = array(), $override_value = false ) {
			echo $this->get_text_area( $var, $options, $override_value );
		}
		
		function get_password( $var, $options = array(), $override_value = false ) {
			if ( ! is_array( $options ) )
				$options = array( 'value' => $options );
			
			$options['type'] = 'password'; 
			return $this->_get_simple_input( $var, $options, $override_value );
		}
		
		function add_password( $var, $options = array(), $override_value = false ) {
			echo $this->get_password( $var, $options, $override_value );
		}
		
		function get_file_upload( $var, $options = array(), $override_value = false ) {
			if ( ! is_array( $options ) )
				$options = array( 'value' => $options );
			
			$options['type'] = 'file';
			$options['name'] = $var;
			
			return $this->_get_simple_input( $var, $options, $override_value );
		}
		
		function add_file_upload( $var, $options = array(), $override_value = false ) {
			echo $this->get_file_upload( $var, $options, $override_value );
		}
		
		function get_check_box( $var, $options = array(), $override_value = false ) {
			if ( ! is_array( $options ) )
				$options = array( 'value' => $options );
			
			$options['type'] = 'checkbox';
			
			if ( empty( $options['value'] ) )
				$options['value'] = '1';
			
			return $this->_get_simple_input( $var, $options, $override_value );
		}
		
		function add_check_box( $var, $options = array(), $override_value = false ) {
			echo $this->get_check_box( $var, $options, $override_value );
		}
		
		function get_multi_check_box( $var, $options = array(), $override_value = false ) {
			if ( ! is_array( $options ) )
				$options = array( 'value' => $options );
			
			if ( empty( $options['id'] ) )
				$options['id'] = "$var-{$options['value']}";
			if ( empty( $options['class'] ) )
				$options['class'] = $var;
			
			$options['type'] = 'checkbox';
			$var = $var . '[]';
			
			return $this->_get_simple_input( $var, $options, $override_value );
		}
		
		function add_multi_check_box( $var, $options = array(), $override_value = false ) {
			echo $this->get_multi_check_box( $var, $options, $override_value );
		}
		
		function get_radio( $var, $options = array(), $override_value = false ) {
			if ( ! is_array( $options ) )
				$options = array( 'value' => $options );
			
			$options['type'] = 'radio';
			$options['append_val_to_id'] = true;
			
			return $this->_get_simple_input( $var, $options, $override_value );
		}
		
		function add_radio( $var, $options = array(), $override_value = false ) {
			echo $this->get_radio( $var, $options, $override_value );
		}
		
		function get_yes_no_drop_down( $var, $options = array(), $override_value = false ) {
			if ( ! is_array( $options ) )
				$options = array();
			
			$options['value'] = array( '1' => 'Yes', '' => 'No' );
			
			return $this->get_drop_down( $var, $options, $override_value );
		}
		
		function add_yes_no_drop_down( $var, $options = array(), $override_value = false ) {
			echo $this->get_yes_no_drop_down( $var, $options, $override_value );
		}
		
		function get_drop_down( $var, $options = array(), $override_value = false ) {
			if ( ! is_array( $options ) )
				$options = array();
			else if ( ! isset( $options['value'] ) || ! is_array( $options['value'] ) )
				$options = array( 'value' => $options );
			
			$options['type'] = 'dropdown';
			
			return $this->_get_simple_input( $var, $options, $override_value );
		}
		
		function add_drop_down( $var, $options = array(), $override_value = false ) {
			echo $this->get_drop_down( $var, $options, $override_value );
		}
		
		function get_credit_card_month_drop_down( $var, $options = array(), $override_value = false ) {
			$options['value'][''] = 'Month';
			
			for ( $month = 1; $month <= 12; $month++ ) {
				$month = sprintf( '%02d', $month );
				$options['value'][$month] = $month;
			}
			
			return $this->get_drop_down( $var, $options, $override_value );
		}
		
		function add_credit_card_month_drop_down( $var, $options = array(), $override_value = false ) {
			echo $this->get_credit_card_month_drop_down( $var, $options, $override_value );
		}
		
		function get_credit_card_year_drop_down( $var, $options = array(), $override_value = false ) {
			$options['value'][''] = 'Year';
			
			$cur_year = date( 'Y' );
			
			for ( $year = $cur_year; $year <= $cur_year + 10; $year++ )
				$options['value'][$year] = $year;
			
			return $this->get_drop_down( $var, $options, $override_value );
		}
		
		function add_credit_card_year_drop_down( $var, $options = array(), $override_value = false ) {
			echo $this->get_credit_card_year_drop_down( $var, $options, $override_value );
		}
		
		function get_hidden( $var, $options = array(), $override_value = false ) {
			if ( ! is_array( $options ) )
				$options = array( 'value' => $options );
			
			$options['type'] = 'hidden';
			
			return $this->_get_simple_input( $var, $options, $override_value );
		}
		
		function add_hidden( $var, $options = array(), $override_value = false ) {
			echo $this->get_hidden( $var, $options, $override_value );
		}
		
		function get_hidden_no_save( $var, $options = array(), $override_value = true ) {
			if ( ! is_array( $options ) )
				$options = array( 'value' => $options );
			
			$options['name'] = $var;
			
			return $this->get_hidden( $var, $options, $override_value );
		}
		
		function add_hidden_no_save( $var, $options = array(), $override_value = true ) {
			echo $this->get_hidden_no_save( $var, $options, $override_value );
		}
		
		function get_hiddens( $data, $options = array() ) {
			$inputs = '';
			
			foreach ( (array) $data as $var => $val ) {
				if ( ! is_array( $val ) )
					$inputs .= $this->get_hidden( $var, array_merge( $options, array( 'value' => $val ) ), true );
				else {
					$this->add_input_group( $var );
					
					$inputs .= $this->get_hiddens( $val, $options );
					
					$this->remove_input_group();
				}
			}
			
			return $inputs;
		}
		
		function add_hiddens( $data, $options = array() ) {
			echo $this->get_hiddens( $data, $options );
		}
		
		function get_default_hidden( $var ) {
			$options = array();
			$options['value'] = $this->defaults[$var];
			
			$var = "default_option_$var";
			
			return $this->_get_simple_input( $var, $options );
		}
		
		function add_default_hidden( $var ) {
			echo $this->get_default_hidden( $var );
		}
		
		function get_used_inputs() {
			$input_groups = array();
			
			if ( true === $this->_args['compact_used_inputs'] ) {
				foreach ( (array) $this->_used_inputs as $input_group => $names )
					$input_groups[] = "$input_group{" . implode( ',', $names ) . "}";
			}
			else {
				foreach ( (array) $this->_used_inputs as $input_group => $names ) {
					foreach ( (array) $names as $name )
						$input_groups[] = "$input_group$name";
				}
			}
			
			$options['type'] = 'hidden';
			$options['value'] = implode( ',', $input_groups );
			$options['name'] = 'used-inputs';
			
			
			$this->push_input_groups();
			$this->remove_input_group( true );
			
			$input = $this->_get_simple_input( 'used-inputs', $options, true );
			
			if ( true === $this->_args['compact_used_inputs'] )
				$input .= $this->get_hidden_no_save( '__it-form-compact-used-inputs', '1' );
			
			$this->pop_input_groups();
			
			
			$this->_used_inputs_printed = true;
			
			return $input;
		}
		
		function add_used_inputs() {
			echo $this->get_used_inputs();
		}
		
		function _get_simple_input( $var, $options, $override_value ) {
			if ( empty( $options['type'] ) )
				return "<!-- _get_simple_input called without a type option set. -->\n";
			
			$scrublist['textarea']['value'] = true;
			$scrublist['file']['value'] = true;
			$scrublist['dropdown']['value'] = true;
			$scrublist['dropdown']['type'] = true;
			
			$defaults = array();
			
			if ( isset( $this->_args['widget_instance'] ) && @method_exists( $this->_args['widget_instance'], 'get_field_name' ) )
				$defaults['name'] = $this->_args['widget_instance']->get_field_name( $var );
			else if ( empty( $this->_args['prefix'] ) )
				$defaults['name'] = $var;
			else
				$defaults['name'] = "{$this->_args['prefix']}-$var";
			
			$input_group_name = $defaults['name'];
			
			$var = str_replace( '[]', '', $var );
			
			$clean_var = trim( preg_replace( '/[^a-z0-9_]+/i', '-', $var ), '-' );
			
			if ( ! empty( $this->_input_group ) ) {
				if ( false === strpos( $defaults['name'], '[' ) )
					$defaults['name'] = "[{$defaults['name']}]";
				else
					$defaults['name'] = preg_replace( '/^([^\[]+)\[/', '[$1][', $defaults['name'] );
				
				$input_group_name = $defaults['name'];
				
				$defaults['name'] = "{$this->_input_group}{$defaults['name']}";
				
				$clean_var = trim( preg_replace( '/[^a-z0-9_]+/i', '-', $defaults['name'] ), '-' );
			}
			
			if ( isset( $this->_args['widget_instance'] ) && @method_exists( $this->_args['widget_instance'], 'get_field_id' ) )
				$defaults['id'] = $this->_args['widget_instance']->get_field_id( $var );
			else
				$defaults['id'] = $clean_var;
			
			if ( ! empty( $options['append_val_to_id'] ) && ( true === $options['append_val_to_id'] ) && ! empty( $options['value'] ) ) {
				unset( $options['append_val_to_id'] );
				$defaults['id'] .= '-' . trim( preg_replace( '/[^a-z0-9_]+/i', '-', $options['value'] ), '-' );
			}
			
			$options = ITUtility::merge_defaults( $options, $defaults );
			
			if ( ( false === $override_value ) && isset( $this->_options[$var] ) ) {
				if ( in_array( $options['type'], array( 'checkbox', 'radio' ) ) ) {
					if ( ( is_array( $this->_options[$var] ) && in_array( $options['value'], $this->_options[$var] ) ) || ( ! is_array( $this->_options[$var] ) && ( (string) $this->_options[$var] === (string) $options['value'] ) ) )
						$options['checked'] = 'checked';
				}
				else if ( 'dropdown' !== $options['type'] )
					$options['value'] = $this->_options[$var];
			}
			
			if ( ( ( ! empty( $this->_args['prefix'] ) && ( preg_match( "|^{$this->_args['prefix']}-|", $options['name'] ) ) ) || ( empty( $this->_args['prefix'] ) ) ) ) {
				if ( ! isset( $this->_used_inputs[$this->_input_group] ) || ! in_array( $input_group_name, $this->_used_inputs[$this->_input_group] ) )
					$this->_used_inputs[$this->_input_group][] = $input_group_name;
			}
			
			
			$attributes = '';
			
			if ( false !== $options ) {
				foreach ( (array) $options as $name => $val ) {
					if ( ! is_array( $val ) && ( ! isset( $scrublist[$options['type']][$name] ) || ( true !== $scrublist[$options['type']][$name] ) ) ) {
						if ( 'value' == $name )
							$val = ITForm::esc_value_attr( $val );
						else if ( ! in_array( $options['type'], array( 'submit', 'button' ) ) )
							$val = esc_attr( $val );
						
						$attributes .= "$name=\"$val\" ";
					}
				}
			}
			
			$retval = '';
			
			if ( 'textarea' === $options['type'] ) {
				if ( ! isset( $options['value'] ) )
					$options['value'] = '';
				
				$retval = "<textarea $attributes >" . ITForm::esc_value_attr( $options['value'] ) . '</textarea>';
			}
			else if ( 'dropdown' === $options['type'] ) {
				$retval = "<select $attributes>\n";
				
				if ( isset( $options['value'] ) && is_array( $options['value'] ) ) {
					foreach ( (array) $options['value'] as $val => $name ) {
						if ( is_array( $name ) ) {
							$options = $name;
							
							if ( preg_match( '/^__optgroup_\d+$/', $val ) ) {
								$retval .= "<optgroup class='it-classes-optgroup-separator'>\n";
							} else {
								$retval .= "<optgroup label='" . esc_attr( $val ) . "'>\n";
							}
							
							foreach ( (array) $options as $val => $name ) {
								$selected = ( isset( $this->_options[$var] ) && ( (string) $this->_options[$var] === (string) $val ) ) ? ' selected="selected"' : '';
								$retval .= "<option value=\"" . ITForm::esc_value_attr( $val ) . "\"$selected>$name</option>\n";
							}
							
							$retval .= "</optgroup>\n";
						}
						else {
							$selected = ( isset( $this->_options[$var] ) && ( (string) $this->_options[$var] === (string) $val ) ) ? ' selected="selected"' : '';
							$retval .= "<option value=\"" . ITForm::esc_value_attr( $val ) . "\"$selected>$name</option>\n";
						}
					}
				}
				
				$retval .= "</select>\n";
			}
			else {
				$retval = '<input ' . $attributes . '/>';
			}
			
			return $retval;
		}
		
		public static function esc_value_attr( $text ) {
			$text = wp_check_invalid_utf8( $text );
			$text = htmlspecialchars( htmlspecialchars_decode( htmlspecialchars_decode( $text ) ), ENT_QUOTES );
			
			return $text;
		}
	}
}
