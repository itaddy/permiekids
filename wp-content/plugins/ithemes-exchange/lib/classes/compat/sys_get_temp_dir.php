<?php

/*
Compatibility function for sys_get_temp_dir
Written by Chris Jean for iThemes.com
Version 1.0.1

Version History
	1.0.0 - 2011-10-05 - Chris Jean
		Release ready
	1.0.1 - 2013-05-22 - Chris Jean
		Wrapped in function_exists check to ensure that the file passes lint checking.
*/


if ( ! function_exists( 'sys_get_temp_dir' ) ) {
	function sys_get_temp_dir() {
		if ( $temp = getenv('TMP') )
			return $temp;
		if ( $temp = getenv('TEMP') )
			return $temp;
		if ( $temp = getenv('TMPDIR') )
			return $temp;
		
		$temp = tempnam( dirname( __FILE__ ), '' );
		if ( file_exists( $temp ) ) {
			unlink( $temp );
			return dirname( $temp );
		}
		return null;
	}
}
