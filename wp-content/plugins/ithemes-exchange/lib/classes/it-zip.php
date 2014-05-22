<?php

/*
Written by Chris Jean for iThemes.com
Version 1.0.4

Version History
	1.0.0 - 2010-12-14
		Initial Test Version
	1.0.1 - 2011-03-01
		Fixed "Call to undefined method ITZip::add_dir()" error
	1.0.2 - 2013-05-21 - Chris Jean
		Removed assign by reference.
	1.0.3 - 2013-06-25 - Chris Jean
		Changed static function declarations to "public static".
	1.0.4 - 2013-06-25 - Chris Jean
		Changed unzip() function declaration to "public static".
*/


if ( ! class_exists( 'ITZip' ) ) {
	class ITZip {
		var $_path = '';
		var $_zip = '';
		var $_args = array();
		
		
		function ITZip( $args = array() ) {
			if ( is_string( $args ) && is_file( $args ) )
				$args = array( 'file' => $args );
			
			$default_args = array(
				'delete_on_shutdown'  => false,
				'message_callback'    => null,
				'file'                => null,
				'force_compatibility' => false,
			);
			
			$this->_args = array_merge( $default_args, $args );
			
			
			it_classes_load( 'it-file-utility.php' );
			
			$path = ITFileUtility::create_writable_directory( array( 'name' => 'deleteme-ithemes-zip-temp', 'random' => true ) );
			
			if ( is_wp_error( $path ) )
				$this->_error = $path;
			else
				$this->_path = $path;
			
			$this->_cleanup_old_temp_directories();
			
			register_shutdown_function( array( &$this, '__destruct' ) );
		}
		
		function __destruct() {
			$this->cleanup();
		}
		
		function add_content( $content, $dest ) {
			if ( empty( $dest ) || ! is_string( $dest ) )
				return false;
			
			$content = maybe_serialize( $content );
			
			$result = ITFileUtility::write( "{$this->_path}/$dest", $content );
			
			if ( false === $result )
				return false;
			return true;
		}
		
		function add_file( $file, $dest = '' ) {
			if ( empty( $this->_path ) )
				return false;
			
			if ( is_dir( $file ) )
				return $this->add_directory( $file, $dest );
			
			if ( ! is_file( $file ) )
				return false;
			
			$dest = $this->_normalize_dest( $file, $dest );
			$dest = "{$this->_path}/$dest";
			
			if ( ! is_dir( dirname( $dest ) ) && ( false === ITFileUtility::mkdir( dirname( $dest ) ) ) )
				return false;
			
			return copy( $file, $dest );
		}
		
		function add_directory( $dir, $dest = '' ) {
			if ( empty( $this->_path ) )
				return false;
			
			if ( is_file( $dir ) )
				return $this->add_file( $dir, $dest );
			
			if ( ! is_dir( $dir ) )
				return false;
			
			it_classes_load( 'it-file-utility.php' );
			
			$dest = $this->_normalize_dest( $dir, $dest );
			
			if ( ! is_dir( $dest ) && ( false === ITFileUtility::mkdir( dirname( $dest ) ) ) )
				return false;
			
			$files = array_merge( glob( "$dir/*" ), glob( "$dir/.*" ) );
			
			foreach ( (array) $files as $file ) {
				if ( in_array( basename( $file ), array( '.', '..' ) ) )
					continue;
				
				if ( is_dir( $file ) )
					$this->add_directory( $file, "$dest/" );
				else if ( is_file( $file ) )
					$this->add_file( $file, "$dest/" );
			}
			
			return true;
		}
		
		function get_file_listing() {
			if ( empty( $this->_path ) )
				return false;
			return ITFileUtility::get_file_listing( $this->_path );
		}
		
		function cleanup() {
			if ( ! empty( $this->_path ) && is_dir( $this->_path ) )
				ITFileUtility::delete_directory( $this->_path );
			$this->_path = '';
			
			if ( true === $this->_args['delete_on_shutdown'] ) {
				if ( ! empty( $this->_zip ) && is_file( $this->_zip ) )
					@unlink( $this->_zip );
				
				$this->_zip = '';
			}
			
			
			$old_temp_directories = ITFileUtility::locate_file( 'deleteme-ithemes-zip-temp-*' );
			
			if ( ! is_wp_error( $old_temp_directories ) ) {
				foreach ( (array) $old_temp_directories as $directory ) {
					$stats = stat( $directory );
					
					if ( ( time() - 3600 ) > $stats['atime'] )
						ITFileUtility::delete_directory( $directory );
				}
			}
		}
		
		function _normalize_dest( $file, $dest ) {
			$file_pathinfo = pathinfo( $file );
			
			$dest = preg_replace( '|^/|', '', $dest );
			
			if ( empty( $dest ) )
				$dest = $file_pathinfo['basename'];
			else if ( preg_match( '|/$|', $dest ) || is_dir( "{$this->_path}/$dest" ) ) {
				$dest = preg_replace( '|/$|', '', $dest );
				$dest .= "/{$file_pathinfo['basename']}";
			}
			
			return $dest;
		}
		
		function _message( $message ) {
			if ( ! is_null( $this->_args['message_callback'] ) && is_callable( $this->_args['message_callback'] ) )
				call_user_func( $this->_args['message_callback'], $message );
		}
		
		function create_zip( $args = array() ) {
			if ( empty( $this->_path ) )
				return false;
			
			if ( is_string( $args ) )
				$args = array( 'name' => $args );
			if ( ! is_array( $args ) )
				$args = array();
			
			$default_args = array(
				'file'                => null,
				'name'                => 'temp',
				'extension'           => '.zip',
				'disable_compression' => false,
				'append_zip'          => false,
				'paths'               => array(),
			);
			$args = array_merge( $default_args, $args );
			
			
			if ( ! empty( $args['file'] ) && is_file( $args['file'] ) && ! is_writable( $args['file'] ) )
				return new WP_Error( 'cannot_overwrite_existing_file', 'The requested zip file path to be used exists and cannot be overridden' );
			
			if ( ! empty( $args['file'] ) ) {
				if ( ! ITFileUtility::is_file_writable( $args['file'] ) )
					return new WP_Error( 'create_zip_file_not_writable', 'Requested zip file name is not writable' );
				
				$file = $args['file'];
			}
			else {
				$file = ITFileUtility::create_writable_file( array( 'name' => $args['name'], 'extension' => $args['extension'], 'custom_search_paths' => $args['paths'] ) );
				
				if ( is_wp_error( $file ) )
					return $file;
			}
			
			
			$result = false;
			
			if ( true !== $this->_args['force_compatibility'] )
				$result = $this->_create_native_zip( $file, $args );
			if ( true !== $result )
				$result = $this->_create_compatibility_zip( $file, $args );
			
			if ( is_wp_error( $result ) )
				return $result;
			else if ( true !== $result )
				return new WP_Error( 'unknown_zip_failure', 'Zip file creation failed for an unknown reason' );
			
			
			if ( empty( $args['file'] ) )
				$this->_zip = $file;
			
			return $file;
		}
		
		function _create_native_zip( $file, $args ) {
			if ( file_exists( ABSPATH . 'zip.exe' ) )
				$command = ABSPATH . 'zip.exe';
			else
				$command = 'zip -q';
			
			if ( true === $args['disable_compression'] )
				$command .= ' -0';
			
			if ( file_exists( $file ) ) {
				if ( true === $args['append_zip'] )
					$command .= ' -g';
				else
					@unlink( $file );
			}
			
			$command .= " -r \"$file\" . -i \"*\"";
			
			
			$result = $this->_try_native_zip_command( $command, $file );
			
			if ( true !== $result )
				$result = $this->_try_native_zip_command( "/usr/bin/$command", $file );
			
			return $result;
		}
		
		function _try_native_zip_command( $command, $file ) {
			$original_cwd = getcwd();
			
			chdir( $this->_path );
			@exec( $command, $exec_return_a, $exec_return_b);
			chdir( $original_cwd );
			
			
			if ( ( ! file_exists( $file ) ) || ( $exec_return_b == '-1' ) ) {
				if ( file_exists( $file ) )
					@unlink( $file );
				
				return false;
			}
			
			return true;
		}
		
		function _create_compatibility_zip( $file, $args ) {
			if ( file_exists( $file ) && ( true !== $args['append_zip'] ) )
				@unlink( $file );
			
			
			require_once( ABSPATH . 'wp-admin/includes/class-pclzip.php' );
			
			$archive = new PclZip( $file );
			
			
			$command_arguments = array( $this->_path, PCLZIP_OPT_REMOVE_PATH, $this->_path );
			
			if ( true === $args['disable_compression'] )
				$command_arguments[] = PCLZIP_OPT_NO_COMPRESSION;
			
			if ( file_exists( $file ) )
				$result = call_user_func_array( array( &$archive, 'add' ), $command_arguments );
			else
				$result = call_user_func_array( array( &$archive, 'create' ), $command_arguments );
			
			
			if ( 0 == $result )
				return false;
			
			return true;
		}
		
		public static function unzip( $file, $path, $args = array() ) {
			if ( ! is_file( $file ) )
				return new WP_Error( 'it_zip_load_no_file', 'Unable to find the requested file to be unzipped.' );
			if ( ! is_writable( $path ) )
				return new WP_Error( 'it_zip_load_no_writable_path', 'Unable to write to destination path for zip file content.' );
			
			
			$default_args = array(
				'force_compatibility'	=> false,
			);
			$args = array_merge( $default_args, $args );
			
			
			if ( true !== $args['force_compatibility'] )
				$result = ITZip::_load_native_zip( $file, $path, $args );
			if ( true !== $result )
				$result = ITZip::_load_compatibility_zip( $file, $path, $args );
			
			if ( is_wp_error( $result ) )
				return $result;
			else if ( true !== $result )
				return new WP_Error( 'unknown_zip_failure', 'Zip file loading failed for an unknown reason' );
			
			return true;
		}
		
		public static function _load_native_zip( $file, $path, $args ) {
			return false;
		}
		
		public static function _load_compatibility_zip( $file, $path, $args ) {
			require_once( ABSPATH . 'wp-admin/includes/class-pclzip.php' );
			
			$archive = new PclZip( $file );
			
			
			$command_arguments = array( PCLZIP_OPT_PATH, $path );
			
			$result = call_user_func_array( array( &$archive, 'extract' ), $command_arguments );
			
			
			if ( 0 == $result )
				return false;
			
			return true;
		}
		
		function get_file_data( $zip_file, $file_name ) {
			require_once( ABSPATH . 'wp-admin/includes/class-pclzip.php' );
			
			$archive = new PclZip( $zip_file );
			
			
			$command_arguments = array(
				PCLZIP_OPT_BY_NAME,
				$file_name,
				PCLZIP_OPT_EXTRACT_AS_STRING,
			);
			
			$result = call_user_func_array( array( &$archive, 'extract' ), $command_arguments );
			
			
			if ( 0 == $result )
				return false;
			
			return $result[0]['content'];
		}
		
		function _cleanup_old_temp_directories() {
		}
	}
}
