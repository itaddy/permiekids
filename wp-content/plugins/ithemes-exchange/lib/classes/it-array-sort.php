<?php

/*
Written by Chris Jean for iThemes.com
Version 1.1.0

Version History
	1.0.0 - 2009-08-14 - Chris Jean
		Release-ready
	1.0.1 - 2009-11-18 - Chris Jean
		Fixed array index warnings
	2.0.0 - 2011-02-22 - Chris Jean
		Complete rewrite
*/


if ( ! class_exists( 'ITArraySort' ) ) {
	class ITArraySort {
		var $_array = array();
		var $_index = '';
		var $_args = array();
		
		function ITArraySort( $array, $index, $args = array() ) {
			$this->_array = $array;
			$this->_index = $index;
			
			$default_args = array(
				'sort_direction' => 'asc',     // asc, desc
				'sort_type'      => 'string',  // string, numeric
				'case_sensitive' => false,
			);
			$this->_args = array_merge( $default_args, $args );
			
			it_classes_load( 'it-utility.php' );
		}
		
		function get_sorted_array() {
			if ( ! is_array( $this->_array ) )
				return null;
			
			$key = key( $this->_array );
			
			if ( ! isset( $this->_array[$key] ) || is_null( ITUtility::get_array_value( $this->_array[$key], $this->_index ) ) )
				return null;
			
			uksort( $this->_array, array( &$this, '_sorter' ) );
			
			return $this->_array;
		}
		
		function _sorter( $a, $b ) {
			$a = ITUtility::get_array_value( $this->_array[$a], $this->_index );
			$b = ITUtility::get_array_value( $this->_array[$b], $this->_index );
			
			if ( 'numeric' === $this->_args['sort_type'] ) {
				if ( $a == $b )
					return 0;
				if ( 'asc' === $this->_args['sort_direction'] )
					return ( $a > $b ) ? -1 : 1;
				return ( $a > $b ) ? 1 : -1;
			}
			
			if ( 'desc' === $this->_args['sort_direction'] ) {
				if ( true === $this->_args['case_sensitive'] )
					return strnatcmp( $b, $a );
				return strnatcasecmp( $b, $a );
			}
			
			if ( true === $this->_args['case_sensitive'] )
				return strnatcmp( $a, $b );
			return strnatcasecmp( $a, $b );
		}
	}
}
