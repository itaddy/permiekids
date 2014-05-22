<?php

/*
This library is an easy to use caching system that supports file caching
with a fallback to access the data in case file caching fails.

Written by Chris Jean for iThemes.com
Version 1.0.2

Version History
	1.0.0 - 2010-12-14 - Chris Jean
		Initial version
	1.0.1 - 2011-08-04 - Chris Jean
		Used ITUtility::fix_url for URLs to prevent issues with SSL sites
	1.0.2 - 2013-02-13 - Chris Jean
		Added fix for when site data migrates to a new server to automatically regenerate URL's and path locations.
*/


if ( ! class_exists( 'ITCache' ) ) {
	class ITCache {
		var $_name = '';
		var $_args = array();
		
		var $_path = '';
		var $_url = '';
		var $_version = '';
		
		var $_file_cache_enabled = true;
		var $_content = array();
		var $_saved_cache_files = array();
		var $_rebuild_types = array();
		
		
		function ITCache( $name, $args = array() ) {
			$this->_name = $name;
			
			$default_args = array(
				'base_path'         => str_replace( ' ', '-', $name ),
				'minify'            => true,
				'enable_file_cache' => true,
				'option_name'       => 'it_cache_base_paths',
				'use_default_types' => true,
			);
			
			$this->_args = array_merge( $default_args, $args );
			
			if ( true === $this->_args['use_default_types'] ) {
				$this->add_content_type( 'javascript', 'script.js', 'text/javascript', array( 'async_load' => true ) );
				$this->add_content_type( 'css', 'style.css', 'text/css' );
			}
			
			if ( true !== $this->_args['enable_file_cache'] )
				$this->_file_cache_enabled = false;
			
			
			$this->_load_path_info();
			
			
			add_action( "it_cache_render_{$name}", array( &$this, 'render' ) );
			add_action( "it_cache_rebuild_cache_{$name}", array( &$this, 'rebuild_cache' ) );
		}
		
		function render( $type ) {
			if ( ! isset( $this->_types[$type] ) )
				return;
			if ( isset( $this->_empty_types[$type] ) )
				return;
			
			$options = $this->_types[$type];
			
			$url = $this->get_url( $type );
			
			if ( false === $url ) {
				$content = $this->get_content( $type );
				
				if ( empty( $content ) )
					return;
			}
			
			if ( 'text/javascript' === $options['content_type'] ) {
				if ( false !== $url ) {
					if ( true === $this->_types[$type]['async_load'] ) {
						if ( ! isset( $this->_async_count ) ) {
							$var = 'bsl';
							$this->_async_count = 0;
						}
						else {
							$var = "bsl_{$this->_async_count}";
						}
						$this->_async_count++;
						
						echo "<script type='text/javascript'>\n";
						echo "(function() {\n";
						echo "var $var=document.createElement('script');\n";
						echo "$var.type='text/javascript';$var.async=true;$var.src='$url';\n";
						echo "var se=document.getElementsByTagName('script')[0];se.parentNode.insertBefore($var,se);\n";
						echo "})();\n";
						echo "</script>\n";
					}
					else
						echo "<script type='text/javascript' src='$url'></script>\n";
				}
				else
					echo "<script type='text/javascript'>\n$content\n</script>\n";
			}
			else if ( 'text/css' === $options['content_type'] ) {
				if ( false !== $url ) {
					$id = "{$this->_args['base_path']}-" . str_replace( '.', '-', $this->_types[$type]['file'] );
					echo "<link rel='stylesheet' id='$id' href='$url' type='text/css' media='all' />\n";
				}
				else
					echo "<style type='text/css'>\n$content\n</style>\n";
			}
			else {
				if ( ! isset( $content ) )
					$content = $this->get_content( $type );
				echo "$content\n";
			}
		}
		
		function rebuild_cache( $types = array() ) {
			if ( ! $this->_file_cache_enabled )
				return false;
			
			if ( empty( $types ) )
				$types = array_keys( $this->_types );
			else
				$types = array_intersect( (array) $types, array_keys( $this->_types ) );
			
			
			$result = true;
			
			foreach ( (array) $types as $type ) {
				if ( false === $this->_save_cache_file( $type ) )
					$result = false;
			}
			
			return $result;
		}
		
		function add_content_type( $name, $file, $content_type, $options = array() ) {
			$default_options = array(
				'file'                 => $file,
				'content_type'         => $content_type,
				'minify_function'      => null,
				'minify_function_file' => null,
				'filters'              => array(),
				'functions'            => array(),
				'async_load'           => false,
			);
			
			$options = array_merge( $default_options, $options );
			
			$this->_types[$name] = $options;
		}
		
		function remove_type( $type ) {
			unset( $this->_types[$type] );
		}
		
		function add_content_function( $types, $function ) {
			$this->_add_content_source( $types, $filter, 'function' );
		}
		
		function add_content_filter( $types, $filter ) {
			$this->_add_content_source( $types, $filter, 'filter' );
		}
		
		function get_content_types() {
			return array_keys( $this->_types );
		}
		
		function get_content( $type ) {
			if ( isset( $this->_content[$type] ) )
				return $this->_content[$type];
			
			$options = $this->_types[$type];
			
			do_action( "it_file_cache_prefilter_{$this->_name}_$type" );
			
			$content = '';
			
			if ( isset( $options['functions'] ) ) {
				foreach ( (array) $options['functions'] as $function ) {
					if ( is_callable( $function ) ) {
						if ( ! empty( $content ) )
							$content .= "\n\n";
						$content .= call_user_func( $function );
					}
				}
			}
			
			if ( isset( $options['filters'] ) ) {
				foreach ( (array) $options['filters'] as $filter ) {
					if ( ! empty( $content ) )
						$content .= "\n\n";
					$content .= apply_filters( $filter, '' );
				}
			}
			
			
			$content = apply_filters( "it_file_cache_filter_{$this->_name}_$type", $content );
			
			$minify_function = $options['minify_function'];
			
			if ( ( true === $this->_args['minify'] ) && ! empty( $minify_function ) && is_callable( $minify_function ) ) {
				$minify_function_file = $options['minify_function_file'];
				
				if ( ! empty( $minify_function_file ) && is_file( $minify_function_file ) )
					require_once( $minify_function_file );
				
				
				$content = call_user_func( $minify_function, $content );
			}
			
			$this->_content[$type] = $content;
			
			return $content;
		}
		
		function get_path( $type ) {
			if ( ! $this->_file_cache_enabled )
				return false;
			
			$path = $this->_path . '/' . $this->_types[$type]['file'];
			
			if ( ! file_exists( $path ) )
				$this->rebuild_cache( $type );
			
			if ( ! file_exists( $path ) )
				return false;
			
			return $path;
		}
		
		function get_url( $type ) {
			$path = $this->get_path( $type );
			
			if ( false === $path )
				return false;
			
			$ver = ( ! empty( $this->_versions[$type] ) ) ? "?ver={$this->_versions[$type]}" : '';
			
			return ITUtility::fix_url( "{$this->_url}/{$this->_types[$type]['file']}{$ver}" );
		}
		
		function _save_cache_file( $type ) {
			if ( ! empty( $this->_saved_cache_files[$type] ) )
				return true;
			
			it_classes_load( 'it-file-utility.php' );
			
			$content = $this->get_content( $type );
			$path = $this->_path . '/' . $this->_types[$type]['file'];
			
			$this->_increment_file_version( $type );
			
			$this->_saved_cache_files[$type] = true;
			
			if ( empty( $content ) )
				$this->_set_empty_type( $type );
			else if ( isset( $this->_empty_types[$type] ) )
				$this->_unset_empty_type( $type );
			
			return ITFileUtility::write( $path, $content );
		}
		
		function _load_path_info() {
			$old_cache_base_path = '';
			
//			delete_option( $this->_args['option_name'] );
			
			$path_info = $this->_get_path_info();
			
			if ( ! empty( $path_info['path'] ) ) {
				if ( is_writable( $path_info['path'] ) ) {
					$path_info['url'] = ITUtility::get_url_from_file( $path_info['path'] );
					
					$this->_path = $path_info['path'];
					$this->_url = $path_info['url'];
					$this->_versions = $path_info['versions'];
					$this->_empty_types = $path_info['empty_types'];
					
					return;
				}
				
				if ( is_dir( $path_info['path'] ) )
					$old_cache_path = $path_info['path'];
			}
			
			it_classes_load( 'it-file-utility.php' );
			
			$path_info = ITFileUtility::get_writable_uploads_directory( 'it-file-cache/' . $this->_args['base_path'] );
			
			if ( false === $path_info ) {
				$this->_file_cache_enabled = false;
				return;
			}
			
			$path_info['versions'] = array();
			$path_info['empty_types'] = array();
			
			$this->_update_path_info( $path_info );
			
			
			if ( ! empty( $old_cache_base_path ) && is_dir( $old_cache_base_path ) )
				ITFileUtility::copy( $old_cache_base_path . '/*', $this->_path );
		}
		
		function _increment_file_version( $type ) {
			if ( false === ( $paths = get_option( $this->_args['option_name'] ) ) )
				$paths = array();
			
			if ( ! isset( $paths[$this->_name]['versions'] ) ) {
				$paths[$this->_name]['versions'] = array();
				$this->_versions = array();
			}
			if ( ! isset( $paths[$this->_name]['versions'][$type] ) ) {
				$paths[$this->_name]['versions'][$type] = 0;
				$this->_versions[$type] = 0;
			}
			
			$paths[$this->_name]['versions'][$type]++;
			$this->_versions[$type]++;
			
			update_option( $this->_args['option_name'], $paths );
		}
		
		function _set_empty_type( $type ) {
			if ( false === ( $paths = get_option( $this->_args['option_name'] ) ) )
				$paths = array();
			
			$paths[$this->_name]['empty_types'][$type] = true;
			$this->_empty_types[$type] = true;
			
			update_option( $this->_args['option_name'], $paths );
		}
		
		function _unset_empty_type( $type ) {
			if ( false === ( $paths = get_option( $this->_args['option_name'] ) ) )
				$paths = array();
			
			unset( $paths[$this->_name]['empty_types'][$type] );
			unset( $this->_empty_types[$type] );
			
			update_option( $this->_args['option_name'], $paths );
		}
		
		function _update_path_info( $path_info ) {
			if ( false === ( $paths = get_option( $this->_args['option_name'] ) ) )
				$paths = array();
			
			$this->_path = $path_info['path'];
			$this->_url = ITUtility::get_url_from_file( $path_info['path'] );
			$this->_versions = $path_info['versions'];
			$this->_empty_types = $path_info['empty_types'];
			
			$paths[$this->_name] = $path_info;
			
			update_option( $this->_args['option_name'], $paths );
		}
		
		function _get_path_info() {
			if ( false === ( $paths = get_option( $this->_args['option_name'] ) ) )
				return false;
			if ( empty( $paths[$this->_name] ) )
				return false;
			
			return $paths[$this->_name];
		}
		
		function _add_content_source( $types, $source, $source_type ) {
			if ( empty( $types ) )
				return;
			
			$source_type .= 's';
			
			foreach ( (array) $types as $type ) {
				if ( isset( $this->_types[$type] ) ) {
					if ( ! is_array( $this->_types[$type][$source_type] ) )
						$this->_types[$type][$source_type] = array();
					
					if ( ! in_array( $source, $this->_types[$type][$source_type] ) )
						$this->_types[$type][$source_type][] = $source;
				}
			}
		}
	}
}
