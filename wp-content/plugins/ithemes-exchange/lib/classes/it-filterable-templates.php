<?php

/*
Alternate get_template_part and locate_template functions that permit
filtering of the template search paths.

Written by Chris Jean for iThemes.com
Version 1.0.0

Version History
	1.0.0 - 2011-11-04 - Chris Jean
		Initial release version
*/


if ( ! function_exists( 'it_filterable_get_template_part' ) ) {
	function it_filterable_get_template_part( $slug, $name = null ) {
		do_action( "get_template_part_{$slug}", $slug, $name );
		
		$templates = array();
		
		if ( isset( $name ) )
			$templates[] = "{$slug}-{$name}.php";
		
		$templates[] = "{$slug}.php";
		
		return it_filterable_locate_template( $templates, true, false );
	}
	
}

if ( ! function_exists( 'it_filterable_locate_template' ) ) {
	function it_filterable_locate_template( $template_names, $load = false, $require_once = true ) {
		global $it_possible_template_paths;
		
		if ( ! isset( $it_possible_template_paths ) ) {
			$it_possible_template_paths = array( get_stylesheet_directory(), get_template_directory() );
			$it_possible_template_paths = apply_filters( 'it_filter_possible_template_paths', $it_possible_template_paths );
			$it_possible_template_paths = array_unique( $it_possible_template_paths );
		}
		
		
		$located = '';
		
		foreach ( (array) $template_names as $template_name ) {
			if ( empty( $template_name ) )
				continue;
			
			foreach ( (array) $it_possible_template_paths as $path ) {
				if ( ! is_file( "$path/$template_name" ) )
					continue;
				
				$located = "$path/$template_name";
				break;
			}
			
			if ( ! empty( $located ) )
				break;
		}
		
		if ( ( true == $load ) && ! empty( $located ) )
			load_template( $located, $require_once );
		
		return $located;
	}
}
