<?php

/**
 *
 * @package LDMW
 * @subpackage Library
 * @since 1.0
 */
class LDMW_Util {
	/**
	 * Convert a php date format string to a date picker format for jQuery
	 *
	 * @link http://snipplr.com/view/41329/
	 *
	 * @param $dateString
	 *
	 * @return mixed
	 */
	public static function dateStringToDatepickerFormat( $dateString ) {
		$pattern = array(

			//day
		  'd', //day of the month
		  'j', //3 letter name of the day
		  'l', //full name of the day
		  'z', //day of the year

			//month
		  'F', //Month name full
		  'M', //Month name short
		  'n', //numeric month no leading zeros
		  'm', //numeric month leading zeros

			//year
		  'Y', //full numeric year
		  'y' //numeric year: 2 digit
		);
		$replace = array(
		  'dd', 'd', 'DD', 'o',
		  'MM', 'M', 'm', 'mm',
		  'yy', 'y'
		);
		foreach ( $pattern as &$p ) {
			$p = '/' . $p . '/';
		}

		return preg_replace( $pattern, $replace, $dateString );
	}

	/**
	 * @return array
	 */
	public static function get_pages_list() {
		$pages = get_pages();
		$list = array();

		foreach ( $pages as $page ) {
			$list[$page->ID] = $page->post_title;
		}

		return $list;
	}

	/**
	 * Get a list of user ID -> user name
	 *
	 * @param string|null $role
	 *
	 * @return array
	 */
	public static function get_users_list( $role = null ) {
		$args = array();

		$key = isset( $role ) ? $role : 'all';

		if ( false === ( $list = get_transient( "ldmw_options_users_list_$key" ) ) ) {
			if ( ! is_null( $role ) )
				$args['role'] = $role;

			$query = new WP_User_Query( $args );

			$list = array();

			/**
			 * @var $user WP_User
			 */
			foreach ( $query->get_results() as $user ) {
				$list[$user->ID] = $user->user_login;
			}

			set_transient( "ldmw_options_users_list_$key", $list, 86400 );
		}

		return $list;
	}

}