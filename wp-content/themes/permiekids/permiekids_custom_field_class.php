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
		$this->_tag_map['avatar'] = 'avatar';
		$this->_tag_map['four'] = 'four';
		$this->_tag_map['contact_information'] = 'contact_information';
	}
 


	function avatar( $options=array() ) {
		$defaults = array(
			'size' => 200,
		);
		$options = ITUtility::merge_defaults( $options, $defaults );
		$field_name = $field_id;
		$user_id = get_current_user_id();
 		$field_value = get_user_meta($user_id, 'custom_avatar');
		return '<div class="the_avatar_image"><img src="'. $field_value[0] . '"></div><br /><input id="upload_image_button" class="button" type="button" value="Edit your Profile Photo" />' ;
	}

	function four( $options=array() ) {
		return '<span class="for_facts">Four Facts</span>' ;
	}

	function contact_information( $options=array() ) {
		return '<span class="contact_information">Contact Information</span><br /><span class="ci_description">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</span><br />' ;
	}
				
	function motto( $options=array() ) {
				
		$defaults      = array(
			'format'      => 'html',
			'label'  => __( 'Your Personal Motto', 'it-l10n-ithemes-exchange' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );	
		$field_id = 'motto';
		$field_name = $field_id;
		$user_id = get_current_user_id();
 		$field_value = get_user_meta($user_id, $field_id);
		
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
				$output .= '<br /><span class="motto-description">This is a short description of what your quote is for and why you should pick something awesome</span><br />';
				$output .= '<textarea rows="4" cols="50" id="' . $field_id . '" name="' . $field_name . '">' . $field_value[0] . '</textarea>';
 
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
 		$user_id = get_current_user_id();
 		$field_value = get_user_meta($user_id, $field_id);
		
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
				$output .= '<input type="text" id="' . $field_id . '" name="' . $field_name . '" value="' . $field_value[0] . '" />';
 
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
		$user_id = get_current_user_id();
 		$field_value = get_user_meta($user_id, $field_id);
		 
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
				$output .= '<input type="text" id="' . $field_id . '" name="' . $field_name . '" value="' . $field_value[0] . '" />';
 
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
		$user_id = get_current_user_id();
 		$field_value = get_user_meta($user_id, $field_id);
		 
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
				$output .= '<input type="text" id="' . $field_id . '" name="' . $field_name . '" value="' . $field_value[0] . '" />';
 
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
		$user_id = get_current_user_id();
 		$field_value = get_user_meta($user_id, $field_id);
		 
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
				$output .= '<input type="text" id="' . $field_id . '" name="' . $field_name . '" value="' . $field_value[0] . '" />';
 
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
		$user_id = get_current_user_id();
 		$field_value = get_user_meta($user_id, $field_id);
		 
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
				$output .= '<input type="text" id="' . $field_id . '" name="' . $field_name . '" value="'. $field_value[0] .'" />';
 
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
 		$user_id = get_current_user_id();
 		$field_value = get_user_meta($user_id, $field_id);
		
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
				$output .= '<input type="text" id="' . $field_id . '" name="' . $field_name . '" value="' . $field_value[0] . '" />';
 
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
		$user_id = get_current_user_id();
 		$field_value = get_user_meta($user_id, $field_id);
		 
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
				$output .= '<input type="text" id="' . $field_id . '" name="' . $field_name . '" value="' . $field_value[0] . '" />';
 
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
		$user_id = get_current_user_id();
 		$field_value = get_user_meta($user_id, $field_id);
		 
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
				$output .= '<input type="text" id="' . $field_id . '" name="' . $field_name . '" value="' . $field_value[0] . '" />';
 
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
 		$user_id = get_current_user_id();
 		$field_value = get_user_meta($user_id, $field_id);
		
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
				$output .='<br /><span class="waywo_description">This is a short description of what your quote is for and why you should pick something awesome</span><br />';
				$output .= '<textarea rows="4" cols="50" id="' . $field_id . '" name="' . $field_name . '">' . $field_value[0] . '</textarea>';
 
		}
 
		return $output;
	}			
}

?>