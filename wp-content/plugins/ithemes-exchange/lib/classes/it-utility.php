<?php

/*
Written by Chris Jean for iThemes.com
Version 1.9.1

Version History
	1.7.0 - 2013-02-13 - Chris Jean
		Added get_cached_value function.
		Updated get_url_from_file and get_file_from_url to use get_cached_value function.
	1.8.0 - 2013-04-17 - Chris Jean
		Added get_self_closing_tag and print_self_closing_tag.
	1.8.1 - 2013-05-21 - Chris Jean
		Added "public static" in front of function declarations to satisfy strict standards.
	1.8.2 - 2013-06-28 - Chris Jean
		Fixed issue with get_url_from_file when finding URL for a file in a multisite uploads directory on Windows servers.
	1.8.3 - 2013-11-25 - Chris Jean
		Simplified code for get_open_tag() while also fixing instances where some characters would not be properly escaped.
	1.9.0 - 2013-12-02 - Chris Jean
		Added the screen_option() function.
	1.9.1 - 2014-01-23 - Chris Jean
		Updated fix_url() to change https to http when is_ssl() is false.
*/


if ( ! class_exists( 'ITUtility' ) ) {
	it_classes_load( 'it-error.php' );
	
	class ITUtility {
		public static function require_file_once( $file, $class = false ) {
			$file = ITUtility::verify_file( $file, 'require_once', true );
			@require_once( $file );
			ITUtility::check_class( $class, true );
		}
		
		public static function require_file( $file, $class = false ) {
			$file = ITUtility::verify_file( $file, 'require', true );
			@require( $file );
			ITUtility::check_class( $class, true );
		}
		
		public static function include_file_once( $file, $class = false ) {
			$file = ITUtility::verify_file( $file, 'include_once', false );
			@include_once( $file );
			ITUtility::check_class( $class, false );
		}
		
		public static function include_file( $file, $class = false ) {
			$file = ITUtility::verify_file( $file, 'include', false );
			@include( $file );
			ITUtility::check_class( $class, false );
		}
		
		public static function verify_file( $file, $type, $required = true ) {
			if ( file_exists( get_stylesheet_directory() . "/$file" ) )
				return get_stylesheet_directory() . "/$file";
			else if ( file_exists( get_template_directory() . "/$file" ) )
				return get_template_directory() . "/$file";
			else if ( file_exists( ABSPATH . "/$file" ) )
				return ABSPATH . "/$file";
			else if ( file_exists( dirname( __FILE__ ) . "/$file" ) )
				return dirname( __FILE__ ) . "/$file";
			else if ( ! file_exists( $file ) ) {
				if ( $required )
					ITError::fatal( "missing_file:$type:$file", 'A file necessary for the theme to public static function is missing or unable to be read by the web server.' );
				else {
					ITError::warn( "missing_file:$type:$file", 'A theme file is missing or unable to be read by the web server.' );
					return false;
				}
			}
			
			return $file;
		}
		
		public static function check_class( $class, $required = true ) {
			if ( false !== $class ) {
				if ( ! class_exists( $class ) ) {
					if ( $required )
						ITError::fatal( "missing_class:$class", 'A necessary core component of the theme is missing.' );
					else
						ITError::warn( "missing_class:$class", 'A core component of the theme is missing.' );
				}
			}
		}
		
		public static function merge_defaults( $values, $defaults, $force = false ) {
			if ( ! ITUtility::is_associative_array( $defaults ) ) {
				if ( ! isset( $values ) )
					return $defaults;
				
				if ( false === $force )
					return $values;
				
				if ( isset( $values ) || is_array( $values ) )
					return $values;
				return $defaults;
			}
			
			foreach ( (array) $defaults as $key => $val ) {
				if ( ! isset( $values[$key] ) )
					$values[$key] = null;
				
				$values[$key] = ITUtility::merge_defaults( $values[$key], $val, $force );
			}
			
			return $values;
		}
		
		public static function is_associative_array( &$array ) {
			if ( ! is_array( $array ) || empty( $array ) )
				return false;
			
			$next = 0;
			
			foreach ( $array as $k => $v )
				if ( $k !== $next++ )
					return true;
			
			return false;
		}
		
		public static function show_status_message( $message ) {
			echo "<div class=\"updated fade\"><p><strong>$message</strong></p></div>\n";
		}
		
		public static function show_error_message( $message ) {
			if ( is_wp_error( $message ) )
				$message = $message->get_error_message();
			
			if ( ! is_string( $message ) )
				return;
			
			echo "<div class=\"error\"><p><strong>$message</strong></p></div>\n";
		}
		
		public static function show_inline_status_message( $message ) {
			echo "<div class=\"updated fade inline\"><p><strong>$message</strong></p></div>\n";
		}
		
		public static function show_inline_error_message( $message ) {
			if ( is_wp_error( $message ) )
				$message = $message->get_error_message();
			
			if ( ! is_string( $message ) )
				return;
			
			echo "<div class=\"error inline\"><p><strong>$message</strong></p></div>\n";
		}
		
		public static function verify_class( $var, $class ) {
			if ( isset( $var ) && is_object( $var ) && ( strtolower( $class ) === strtolower( get_class( $var ) ) ) )
				return true;
			return false;
		}
		
		public static function get_open_tag( $tag_name, $attributes = array() ) {
			$tag = "<$tag_name";
			
			foreach ( (array) $attributes as $attribute => $values ) {
				$attr_value = esc_attr( implode( ' ', (array) $values ) );
				$tag .= " $attribute=\"$attr_value\"";
			}
			
			$tag .= '>';
			
			return $tag;
		}
		
		public static function print_open_tag( $tag_name, $attributes ) {
			echo ITUtility::get_open_tag( $tag_name, $attributes ) . "\n";
		}
		
		public static function get_self_closing_tag( $tag_name, $attributes = array() ) {
			$tag = ITUtility::get_open_tag( $tag_name, $attributes );
			$tag = preg_replace( '/>$/', ' />', $tag );
			
			return $tag;
		}
		
		public static function print_self_closing_tag( $tag_name, $attributes = array() ) {
			echo ITUtility::get_self_closing_tag( $tag_name, $attributes );
		}
		
		public static function cleanup_request_vars() {
			$_REQUEST = ITUtility::strip_slashes( $_REQUEST );
			$_POST = ITUtility::strip_slashes( $_POST );
			$_GET = ITUtility::strip_slashes( $_GET );
		}
		
		public static function strip_slashes( $var ) {
			if ( is_array( $var ) ) {
				foreach ( (array) $var as $index => $val )
					$var[$index] = ITUtility::strip_slashes( $val );
			}
			else
				$var = stripslashes( $var );
			
			return $var;
		}
		
		public static function sort_array( $array, $index, $args = array() ) {
			it_classes_load( 'it-array-sort.php' );
			
			$sorter = new ITArraySort( $array, $index, $args );
			
			return $sorter->get_sorted_array();
		}
		
		// Deprecated
		public static function sort_array_by_index( $array, $index ) {
			if ( ! is_array( $array ) )
				ITError::fatal( 'invalid_var:parameter:array', 'Invalid data was passed to ITUtility::sort_array_by_index. This indicates a code bug.' );
			
			$new_array = array();
			$indexes = array();
			
			foreach ( (array) $array as $sub_index => $sub_array )
				$indexes[$sub_index] = $sub_array[$index];
			
			asort( $indexes );
			
			foreach ( (array) $indexes as $sub_index => $sub_value )
				$new_array[] = $array[$sub_index];
			
			return $new_array;
		}
		
		public static function get_array_value( $array, $index ) {
			if ( is_string( $index ) ) {
				if ( false === strpos( $index, '[' ) )
					$index = array( $index );
				else {
					$index = rtrim( $index, '[]' );
					$index = preg_split( '/[\[\]]+/', $index );
				}
			}
			
			while ( count( $index ) > 1 ) {
				if ( isset( $array[$index[0]] ) ) {
					$array = $array[$index[0]];
					array_shift( $index );
				}
				else
					return null;
			}
			
			if ( isset( $array[$index[0]] ) )
				return $array[$index[0]];
			
			return null;
		}
		
		public static function add_array_value( &$array, $index, $val ) {
			if ( is_string( $index ) ) {
				if ( false === strpos( $index, '[' ) )
					$index = array( $index );
				else {
					$index = rtrim( $index, '[]' );
					$index = preg_split( '/[\[\]]+/', $index );
				}
			}
			
			$cur_array =& $array;
			
			while ( count( $index ) > 1 ) {
				if ( ! isset( $cur_array[$index[0]] ) || ! is_array( $cur_array[$index[0]] ) )
					$cur_array[$index[0]] = array();
				
				$cur_array =& $cur_array[$index[0]];
				array_shift( $index );
			}
			
			$cur_array[$index[0]] = $val;
		}
		
		public static function print_js_vars( $options = array() ) {
			
?>
	<script type="text/javascript">
		<?php foreach ( (array) $options as $var => $val ) : ?>
			<?php $val = str_replace( '"', '\\"', $val ); ?>
			var <?php echo $var; ?> = "<?php echo $val; ?>";
		<?php endforeach; ?>
	</script>
<?php
			
		}
		
		public static function print_js_script( $script ) {
			
?>
	<script type="text/javascript">
		<?php echo $script; ?>
	</script>
<?php
			
		}
		
		public static function add_tooltip_scripts() {
			global $it_utility_cached_url_base;
			
			if ( empty( $it_utility_cached_url_base ) )
				$it_utility_cached_url_base = ITUtility::get_url_from_file( dirname( __FILE__ ) );
			
			wp_enqueue_script( 'jquery-tooltip', $it_utility_cached_url_base . '/js/jquery.tooltip.js', array( 'jquery' ) );
			wp_enqueue_script( 'it-tooltip', $it_utility_cached_url_base . '/js/it-tooltip.js', array( 'jquery-tooltip' ) );
		}
		
		public static function add_tooltip_styles() {
			global $it_utility_cached_url_base;
			
			if ( empty( $it_utility_cached_url_base ) )
				$it_utility_cached_url_base = ITUtility::get_url_from_file( dirname( __FILE__ ) );
			
			wp_enqueue_style( 'it-tooltip', $it_utility_cached_url_base . '/css/it-tooltip.css' );
		}
		
		public static function get_tooltip( $message, $title = '', $class = '', $alt = '(?)' ) {
			global $it_utility_cached_url_base;
			
			if ( empty( $it_utility_cached_url_base ) )
				$it_utility_cached_url_base = ITUtility::get_url_from_file( dirname( __FILE__ ) );
			
			$message = esc_attr( $message );
			$title = esc_attr( $title );
			
			if ( empty( $class ) )
				$class = 'it-tooltip';
			else
				$class = "it-tooltip-$class";
			
			$tip = "<a class='$class' title='$title |:|~| $message'><img src='" . $it_utility_cached_url_base . "/images/it-tooltip.png' alt='(?)' /></a>";
			
			return $tip;
		}
		
		public static function add_tooltip( $message, $title = '', $class = '', $alt = '(?)' ) {
			echo ITUtility::get_tooltip( $message, $title, $class, $alt );
		}
		
		/* Changes http protocols to https when is_ssl() is true and https protocols to http otherwise */
		public static function fix_url( $url ) {
			if ( is_ssl() ) {
				$url = preg_replace( '|^http://|', 'https://', $url );
			} else {
				$url = preg_replace( '|^https://|', 'http://', $url );
			}
			
			return $url;
		}
		
		public static function get_random_string( $length = 10, $use_sets = array( 'lower', 'upper', 'num' ) ) {
			$sets = array(
				'lower'    => 'abcdefghijklmnopqrstuvwxyz',
				'upper'    => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
				'num'      => '0123456789',
				'basic'    => '-_,.',
				'extended' => '`~!@#$%^&*()=+[]{};:\'"<>/?\|',
			);
			
			
			if ( is_array( $length ) ) {
				if ( ! isset( $length[0] ) || ! isset( $length[1] ) )
					return '';
				
				$lower = intval( $length[0] );
				$upper = intval( $length[1] );
				
				$length = rand( $lower, $upper );
			}
			else
				$length = intval( $length );
			
			if ( $length < 1 )
				$length = 1;
			
			
			$source_string = '';
			
			if ( is_string( $use_sets ) )
				$source_string = $use_sets;
			else if ( is_array( $use_sets ) ) {
				foreach ( $use_sets as $set ) {
					if ( is_string( $set ) && isset( $sets[$set] ) )
						$source_string .= $sets[$set];
				}
			}
			else if ( true === $use_sets ) {
				foreach ( $sets as $chars )
					$source_string .= $chars;
			}
			
			if ( empty( $source_string ) )
				return false;
			
			
			$string = '';
			
			while ( strlen( $string ) < $length )
				$string .= substr( $source_string, rand( 0, strlen( $source_string ) - 1 ), 1 );
			
			
			return $string;
		}
		
		public static function pad( $depth, $pad = '    ' ) {
			$retval = '';
			
			for ( $x = 0; $x <= $depth; $x++ )
				$retval .= $pad;
			
			return $retval;
		}
		
		public static function get_url_from_file( $file, $auto_ssl = true, $prevent_recursion = false ) {
			$file = str_replace( '\\', '/', $file );
			
			$url = '';
			
			$upload_dir = ITUtility::get_cached_value( 'wp_upload_dir' );
			$upload_dir['basedir'] = str_replace( '\\', '/', $upload_dir['basedir'] );
			
			if ( is_array( $upload_dir ) && ( false === $upload_dir['error'] ) ) {
				if ( 0 === strpos( $file, $upload_dir['basedir'] ) )
					$url = str_replace( $upload_dir['basedir'], $upload_dir['baseurl'], $file );
				else if ( false !== strpos( $file, 'wp-content/uploads' ) )
					$url = $upload_dir['baseurl'] . substr( $file, strpos( $file, 'wp-content/uploads' ) + 18 );
			}
			
			if ( empty( $url ) ) {
				if ( ! isset( $GLOBALS['it_classes_cache_wp_content_dir'] ) )
					$GLOBALS['it_classes_cache_wp_content_dir'] = rtrim( str_replace( '\\', '/', WP_CONTENT_DIR ), '/' );
				if ( ! isset( $GLOBALS['it_classes_cache_abspath'] ) )
					$GLOBALS['it_classes_cache_abspath'] = rtrim( str_replace( '\\', '/', ABSPATH ), '/' );
				
				if ( 0 === strpos( $file, $GLOBALS['it_classes_cache_wp_content_dir'] ) )
					$url = WP_CONTENT_URL . str_replace( '\\', '/', preg_replace( '/^' . preg_quote( $GLOBALS['it_classes_cache_wp_content_dir'], '/' ) . '/', '', $file ) );
				else if ( 0 === strpos( $file, $GLOBALS['it_classes_cache_abspath'] ) )
					$url = get_option( 'siteurl' ) . str_replace( '\\', '/', preg_replace( '/^' . preg_quote( $GLOBALS['it_classes_cache_abspath'], '/' ) . '/', '', $file ) );
			}
			
			if ( empty( $url ) && ! $prevent_recursion )
				$url = ITUtility::get_url_from_file( realpath( $file ), $auto_ssl, true );
			
			if ( empty( $url ) )
				return '';
			
			
			if ( $auto_ssl )
				$url = ITUtility::fix_url( $url );
			
			return $url;
		}
		
		public static function get_file_from_url( $url ) {
			$url = preg_replace( '/^https/', 'http', $url );
			$url = preg_replace( '/\?.*$/', '', $url );
			
			$file = '';
			
			$upload_dir = ITUtility::get_cached_value( 'wp_upload_dir' );
			
			if ( is_array( $upload_dir ) && ( false === $upload_dir['error'] ) ) {
				if ( 0 === strpos( $url, $upload_dir['baseurl'] ) )
					$file = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $url );
				else if ( false !== strpos( $url, 'wp-content/uploads' ) )
					$file = $upload_dir['basedir'] . substr( $url, strpos( $url, 'wp-content/uploads' ) + 18 );
			}
			
			if ( empty( $file ) ) {
				if ( ! isset( $GLOBALS['it_classes_cache_wp_content_url'] ) )
					$GLOBALS['it_classes_cache_wp_content_url'] = preg_replace( '/^https/', 'http', WP_CONTENT_URL );
				if ( ! isset( $GLOBALS['it_classes_cache_siteurl'] ) )
					$GLOBALS['it_classes_cache_siteurl'] = preg_replace( '/^https/', 'http', get_option( 'siteurl' ) );
				
				if ( 0 === strpos( $url, $GLOBALS['it_classes_cache_wp_content_url'] ) )
					$file = rtrim( WP_CONTENT_DIR, '\\\/' ) . preg_replace( '/^' . preg_quote( $GLOBALS['it_classes_cache_wp_content_url'], '/' ) . '/', '', $url );
//				else if ( 0 === strpos( $url, $GLOBALS['it_classes_cache_siteurl'] . '/files' ) )
//					$file = preg_replace( '/^' . preg_quote( $GLOBALS['it_classes_cache_siteurl'] . '/files', '/' ) . '/', ABSPATH . get_option( 'upload_path' ), $url );
				else if ( 0 === strpos( $url, $GLOBALS['it_classes_cache_siteurl'] ) )
					$file = rtrim( ABSPATH, '\\\/' ) . preg_replace( '/^' . preg_quote( $GLOBALS['it_classes_cache_siteurl'], '/' ) . '/', '', $url );
			}
			
			return $file;
		}
		
		public static function get_cached_value( $function, $args = array() ) {
			if ( ! isset( $GLOBALS['it_classes_cached_values'] ) )
				$GLOBALS['it_classes_cached_values'] = array();
			
			$key = $function;
			
			if ( ! empty( $args ) )
				$key .= '-' . md5( serialize( $args ) );
			
			if ( ! isset( $GLOBALS['it_classes_cached_values'][$key] ) )
				$GLOBALS['it_classes_cached_values'][$key] = call_user_func_array( $function, $args );
			
			return $GLOBALS['it_classes_cached_values'][$key];
		}
		
		// WordPress 3.8 deprecates the screen_icon function. Replacing all instances of screen_icon with ITUtility::screen_icon allows for easy backcompat while avoiding the deprecation notices.
		public static function screen_icon() {
			if ( version_compare( $GLOBALS['wp_version'], '3.7.10', '<=' ) ) {
				screen_icon();
			}
		}
		
		
		/* Debug Functions */
		/* These functions are no part of the ITDebug class. These are retained for legacy purposes. */
		
		public static function print_r( $data, $description = '', $expand_objects = true, $max_depth = -1 ) {
			it_classes_load( 'it-debug.php' );
			
			$args = compact( 'description', 'expand_objects', 'max_depth' );
			
			ITDebug::print_r( $data, $args );
		}
		
		public static function backtrace( $description = false, $expand_objects = false, $max_depth = 3 ) {
			it_classes_load( 'it-debug.php' );
			
			$args = compact( 'description', 'expand_objects', 'max_depth' );
			$args['offset'] = 1;
			
			ITDebug::backtrace( $args );
		}
		
		public static function inspect( $data, $expand_objects = false, $max_depth = 2, $echo = true ) {
			it_classes_load( 'it-debug.php' );
			
			$args = compact( 'expand_objects', 'max_depth', 'echo' );
			
			return ITDebug::inspect( $data, $args );
		}
	}
}
