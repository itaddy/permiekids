<?php 
if( ! class_exists( 'IT_Theme_API_Registration' ) || class_exists( 'IT_Theme_API_PermieKids_registration' ) )
	return;
 
class IT_Theme_API_PermieKids_registration extends IT_Theme_API_Registration {
 
	/**
	 * API context
	 * @var string $_context
	 * @since 0.4.0
	*/
	private $_context = 'permiekids_registration';
 
 
	/**
	 * Constructor
	 *
	 * @since 0.4.0
	 * @todo get working for admins looking at other users profiles
	 * @return void
	*/
	function __construct() {
		$this->_tag_map['motto'] = 'motto';
		$this->_tag_map['who_said_it'] = 'who_said_it';
		$this->_tag_map['location'] = 'location';
		$this->_tag_map['family'] = 'family';
		$this->_tag_map['occupation'] = 'occupation';
		$this->_tag_map['experience'] = 'experience';
		$this->_tag_map['facebook'] = 'facebook';
		$this->_tag_map['twitter'] = 'twitter';
		$this->_tag_map['linkedin'] = 'linkedin';	
		$this->_tag_map['what_are_you_working_on'] = 'what_are_you_working_on';	
	}
 
	function motto( $options=array() ) {
		$defaults      = array(
			'format'      => 'html',
			'label'  => __( 'Motto', 'it-l10n-ithemes-exchange' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );
 
		$field_id = 'motto';
		$field_name = $field_id;
 
		switch( $options['format'] ) {
 
			case 'field-id':
				$output = $field_id;
 
			case 'field-name':
				$output = $field_name;

			case 'field-value':
				$output = $field_value;
				break;
				 
			case 'label':
				$output = esc_attr( $options['label'] );
 
			case 'html':
			default:
				$output = '<label for="' . $field_id . '">' . esc_attr( $options['label'] ) . '</label>';
				$output .= '<textarea rows="4" cols="50" id="' . $field_id . '" name="' . $field_name . '">' . $field_value . '</textarea>';
 
		}
 
		return $output;
	}
 

	function who_said_it( $options=array() ) {
		$defaults      = array(
			'format'      => 'html',
			'label'  => __( 'Who said it?', 'it-l10n-ithemes-exchange' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );
 
		$field_id = 'who_said_it';
		$field_name = $field_id;
 
		switch( $options['format'] ) {
 
			case 'field-id':
				$output = $field_id;
 
			case 'field-name':
				$output = $field_name;
 
			case 'label':
				$output = esc_attr( $options['label'] );
 
 			case 'field-value':
				$output = $field_value;
				break;
				
			case 'html':
			default:
				$output = '<label for="' . $field_id . '">' . esc_attr( $options['label'] ) . '</label>';
				$output .= '<input type="text" id="' . $field_id . '" name="' . $field_name . '" value="' . $field_value . '" />';
 
		}
 
		return $output;
	}

	function location( $options=array() ) {
		$defaults      = array(
			'format'      => 'html',
			'label'  => __( 'Location', 'it-l10n-ithemes-exchange' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );
 
		$field_id = 'location';
		$field_name = $field_id;
 
		switch( $options['format'] ) {
 
			case 'field-id':
				$output = $field_id;
 
			case 'field-name':
				$output = $field_name;
 
  			case 'field-value':
				$output = $field_value;
				break;
				
			case 'label':
				$output = esc_attr( $options['label'] );
 
			case 'html':
			default:
				$output = '<label for="' . $field_id . '">' . esc_attr( $options['label'] ) . '</label>';
				$output .= '<input type="text" id="' . $field_id . '" name="' . $field_name . '" value="' . $field_value . '" />';
 
		}
 
		return $output;
	} 

	function family( $options=array() ) {
		$defaults      = array(
			'format'      => 'html',
			'label'  => __( 'Family', 'it-l10n-ithemes-exchange' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );
 
		$field_id = 'family';
		$field_name = $field_id;
 
		switch( $options['format'] ) {
 
			case 'field-id':
				$output = $field_id;
 
			case 'field-name':
				$output = $field_name;

  			case 'field-value':
				$output = $field_value;
				break;
				 
			case 'label':
				$output = esc_attr( $options['label'] );
 
			case 'html':
			default:
				$output = '<label for="' . $field_id . '">' . esc_attr( $options['label'] ) . '</label>';
				$output .= '<input type="text" id="' . $field_id . '" name="' . $field_name . '" value="' . $field_value . '" />';
 
		}
 
		return $output;
	} 	

	function occupation( $options=array() ) {
		$defaults      = array(
			'format'      => 'html',
			'label'  => __( 'Occupation', 'it-l10n-ithemes-exchange' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );
 
		$field_id = 'occupation';
		$field_name = $field_id;
 
		switch( $options['format'] ) {
 
			case 'field-id':
				$output = $field_id;
 
			case 'field-name':
				$output = $field_name;
 
			case 'label':
				$output = esc_attr( $options['label'] );
 
   			case 'field-value':
				$output = $field_value;
				break;
				
			case 'html':
			default:
				$output = '<label for="' . $field_id . '">' . esc_attr( $options['label'] ) . '</label>';
				$output .= '<input type="text" id="' . $field_id . '" name="' . $field_name . '" value="' . $field_value . '" />';
 
		}
 
		return $output;
	} 	

	function experience( $options=array() ) {
		$defaults      = array(
			'format'      => 'html',
			'label'  => __( 'Experience', 'it-l10n-ithemes-exchange' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );
 
		$field_id = 'experience';
		$field_name = $field_id;
 
		switch( $options['format'] ) {
 
			case 'field-id':
				$output = $field_id;
 
			case 'field-name':
				$output = $field_name;
 
			case 'label':
				$output = esc_attr( $options['label'] );
 
    		case 'field-value':
				$output = $field_value;
				break;
				
			case 'html':
			default:
				$output = '<label for="' . $field_id . '">' . esc_attr( $options['label'] ) . '</label>';
				$output .= '<input type="text" id="' . $field_id . '" name="' . $field_name . '" value="'. $field_value .'" />';
 
		}
 
		return $output;
	} 	

	function facebook( $options=array() ) {
		$defaults      = array(
			'format'      => 'html',
			'label'  => __( 'Facebook', 'it-l10n-ithemes-exchange' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );
 
		$field_id = 'facebook';
		$field_name = $field_id;
 
		switch( $options['format'] ) {
 
			case 'field-id':
				$output = $field_id;
 
			case 'field-name':
				$output = $field_name;
 
			case 'label':
				$output = esc_attr( $options['label'] );
 
     		case 'field-value':
				$output = $field_value;
				break;
				
			case 'html':
			default:
				$output = '<label for="' . $field_id . '">' . esc_attr( $options['label'] ) . '</label>';
				$output .= '<input type="text" id="' . $field_id . '" name="' . $field_name . '" value="' . $field_value . '" />';
 
		}
 
		return $output;
	} 	

	function twitter( $options=array() ) {
		$defaults      = array(
			'format'      => 'html',
			'label'  => __( 'Twitter', 'it-l10n-ithemes-exchange' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );
 
		$field_id = 'twitter';
		$field_name = $field_id;
 
		switch( $options['format'] ) {
 
			case 'field-id':
				$output = $field_id;
 
			case 'field-name':
				$output = $field_name;
 
			case 'label':
				$output = esc_attr( $options['label'] );
 
 			case 'field-value':
				$output = $field_value;
				break;
				
			case 'html':
			default:
				$output = '<label for="' . $field_id . '">' . esc_attr( $options['label'] ) . '</label>';
				$output .= '<input type="text" id="' . $field_id . '" name="' . $field_name . '" value="' . $field_value . '" />';
 
		}
 
		return $output;
	} 	

	function linkedin( $options=array() ) {
		$defaults      = array(
			'format'      => 'html',
			'label'  => __( 'LinkedIn', 'it-l10n-ithemes-exchange' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );
 
		$field_id = 'linkedin';
		$field_name = $field_id;
 
		switch( $options['format'] ) {
 
			case 'field-id':
				$output = $field_id;
 
			case 'field-name':
				$output = $field_name;
 
  			case 'field-value':
				$output = $field_value;
				break;
				
			case 'label':
				$output = esc_attr( $options['label'] );
 
			case 'html':
			default:
				$output = '<label for="' . $field_id . '">' . esc_attr( $options['label'] ) . '</label>';
				$output .= '<input type="text" id="' . $field_id . '" name="' . $field_name . '" value="' . $field_value . '" />';
 
		}
 
		return $output;
	} 						
	
	function what_are_you_working_on( $options=array() ) {
		$defaults      = array(
			'format'      => 'html',
			'label'  => __( 'What are you working on?', 'it-l10n-ithemes-exchange' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );
 
		$field_id = 'what_are_you_working_on';
		$field_name = $field_id;
 
		switch( $options['format'] ) {
 
			case 'field-id':
				$output = $field_id;
 
			case 'field-name':
				$output = $field_name;
 
   			case 'field-value':
				$output = $field_value;
				break;
				
			case 'label':
				$output = esc_attr( $options['label'] );
 
			case 'html':
			default:
				$output = '<label for="' . $field_id . '">' . esc_attr( $options['label'] ) . '</label>';
				$output .= '<textarea rows="4" cols="50" id="' . $field_id . '" name="' . $field_name . '">' . $field_value . '</textarea>';
 
		}
 
		return $output;
	}			
}

?>