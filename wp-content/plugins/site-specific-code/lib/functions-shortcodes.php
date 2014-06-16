<?php
/**
 *
 * @package LDMW
 * @subpackage Lib
 * @since 1.0
 */

add_shortcode( 'ldmw_division', function () {
	  return LDMW_Users_Util::membership_division_slug_to_name( LDMW_Users_Util::get_membership_division( get_current_user_id() ) );
  }
);

add_shortcode( 'ldmw_grade', function () {
	  return LDMW_Users_Util::membership_grade_slug_to_name( LDMW_Users_Util::get_membership_grade( get_current_user_id() ) );
  }
);