<?php

/*
Written by Chris Jean for iThemes.com
Version 1.0.0

Version History
	0.0.1 - 2012-07-01 - Chris Jean
		Initial development version
	0.0.2 - 2012-07-06 - Chris Jean
		Added handler for add_help_sidebar() error
	0.0.3 - 2012-07-06 - Chris Jean
		Added handler for builder_register_module_style() error
	0.0.4 - 2012-07-06 - Chris Jean
		Streamlined message creation code
		Added handler for maximum execution time errors
	1.0.0 - 2012-07-17 - Chris Jean
		Release ready
*/


class IT_Classes_Fatal_Error_Parser {
	var $data = array();
	var $descriptions = array();
	var $solutions = array();
	var $message = '';
	
	var $solutions_complete = false;
	
	
	function IT_Classes_Fatal_Error_Parser( $error ) {
		$this->add_data( $error, 'error_' );
		
		$this->parse();
	}
	
	function add_data( $data, $prefix = '' ) {
		foreach ( $data as $key => $val )
			$this->data["{$prefix}$key"] = $val;
	}
	
	function parse() {
		$this->add_error_type_details();
		$this->add_source_data();
		$this->add_source_details();
		$this->add_software_details();
		$this->add_other_software_details();
		
		$this->build_descriptions();
		
		if ( ! $this->solutions_complete )
			$this->build_solutions();
		
		$this->generate_message();
	}
	
	function get_message() {
		return $this->message;
	}
	
	function generate_message() {
		$message = '';
		
		
		$message .= $this->get_message_block( $this->descriptions );
		$message .= $this->get_message_block( array( $this->solutions ), 'Solutions' );
		
		$message .= $this->get_message_table( $this->data['software_details'], 'Error Source Details' );
		$message .= $this->get_message_table( $this->data['other_software_details'], 'Other Software Details' );
		
		$error_data = array(
			'Type'             => "{$this->data['error_name']} ({$this->data['error_type']})",
			'Message'          => $this->data['error_message'],
			'File'             => $this->data['error_file'],
			'Line'             => $this->data['error_line'],
			'Type Description' => $this->data['error_description'],
		);
		$message .= $this->get_message_table( $error_data, 'Full Error Details' );
		
		
		$this->message = $message;
	}
	
	function get_message_block( $data, $header = '', $depth = 0 ) {
		if ( empty( $data ) )
			return '';
		
		
		$message = '';
		
		if ( ! empty( $header ) )
			$message .=  "<h2>$header</h2>\n";
		
		
		if ( is_string( $data ) ) {
			if ( $depth > 1 )
				$message .= "<li>$data</li>\n";
			else
				$message .= "<p>$data</p>\n";
		}
		else if ( is_array( $data ) ) {
			if ( $depth > 0 )
				$message .= "<ul>\n";
			
			foreach ( $data as $item )
				$message .= $this->get_message_block( $item, '', $depth + 1 );
			
			if ( $depth > 0 )
				$message .= "</ul>\n";
		}
		
		return $message;
	}
	
	function get_message_table( $data, $header = '' ) {
		if ( empty( $data ) || ! is_array( $data ) )
			return '';
		
		
		$message = '';
		
		if ( ! empty( $header ) )
			$message .=  "<h2>$header</h2>\n";
		
		
		$message .= "<table>\n";
		
		foreach ( $data as $var => $val )
			$message .= "<tr><th scope='row'>$var</th><td><code>$val</code></td></tr>\n";
		
		$message .= "</table>\n";
		
		
		return $message;
	}
	
	function add_error_type_details() {
		$error_types = array(
			E_ERROR         => array(
				'E_ERROR',
				'Fatal Error',
				'This type of error indicates that PHP cannot continue to run the code. Typical causes of this type of error are code bugs that have typos, missing or incomplete files (such as a file that was only partially uploaded), and the code using more memory than it is allowed.',
			),
			E_PARSE         => array(
				'E_PARSE',
				'Parse Error',
				'This type of error indicates that the PHP code is invalid and is typically referred to as a syntax error. It basically means that the code was written incorrectly and is preventing PHP from being able to read the code properly.',
			),
			E_CORE_ERROR    => array(
				'E_CORE_ERROR',
				'Core Fatal Error',
				'This type of error indicates a fatal error occurred as PHP started up. This typically a faulty PHP configuration on the server.',
			),
			E_COMPILE_ERROR => array(
				'E_COMPILE_ERROR',
				'Zend Compile-Time Error',
				'This type of error indicates that the PHP code is violating a restriction enforced by PHP. An example of such a violation is trying to use a reserved word for a class or function name.',
			),
			E_USER_ERROR    => array(
				'E_USER_ERROR',
				'Code-Generated Fatal Error via trigger_error()',
				'Code running on the site purposefully triggered an error. This typically indicates that some unrecoverable problem happened in the code that could only be properly handled by forcing an error so that the code would no longer run.',
			),
			E_STRICT        => array(
				'E_STRICT',
				'Strict Fatal Error',
				'Strict errors only occur when E_STRICT <a href="http://php.net/manual/en/function.error-reporting.php">error reporting</a> is enabled. These types of errors indicate that the best-practices in PHP development are not followed.',
			),
		);
		
		if ( ! isset( $error_types[$this->data['error_type']] ) )
			return false;
		
		
		$details = array(
			'code'        => $this->data['error_type'],
			'type'        => $error_types[$this->data['error_type']][0],
			'name'        => $error_types[$this->data['error_type']][1],
			'description' => $error_types[$this->data['error_type']][2],
		);
		
		$this->add_data( $details, 'error_' );
	}
	
	function add_description( $description ) {
		if ( ! empty( $description ) )
			$this->descriptions[] = $description;
	}
	
	function add_solution( $solution ) {
		if ( ! empty( $solution ) )
			$this->solutions[] = $solution;
	}
	
	function build_descriptions() {
		$this->add_source_specific_descriptions();
		$this->add_basic_error_message_descriptions();
	}
	
	function add_basic_error_message_descriptions() {
		$match = false;
		
		
		if ( preg_match( '/Maximum execution time of (\d+)( seconds?)/', $this->data['error_message'], $match ) ) {
			$max = $match[1];
			
			
			$this->add_description( "Your server is set to limit PHP execution to a maximum of <code>{$match[1]}</code>{$match[2]}. The code running on your site is exceeding this time limit." );
			$this->add_description( 'This can be caused by a number of different situations:' );
			
			
			$causes = array(
				'Code that requires more time than the server will allow.',
				'Code that attempts to contact a remote server but is unable to do so.',
				'The server could be overloaded, causing each page request to take too long to process.',
			);
			
			$this->add_description( $causes );
			
			
			if ( is_callable( 'sys_getloadavg' ) ) {
				$load = sys_getloadavg();
				$load[0] = 4;
				
				if ( $load[0] > 10 ) {
					$this->add_description( "The load on your server is <code>{$load[0]}</code>. This is extremely high and indicates that your server is overloaded." );
					$this->add_solution( 'Contact your hosting provider for help in finding out why the load is so high.' );
				}
				else if ( $load[0] > 3 ) {
					$this->add_description( "The load on your server is <code>{$load[0]}</code>. Depending on the server hardware, this could be a very high load and could indicate performance issues due to the server being overloaded." );
					$this->add_solution( 'Contact your hosting provider for help in finding out why the load is so high.' );
				}
				else if ( $load[0] > 1 ) {
					$this->add_description( "The load on your server is <code>{$load[0]}</code>. Depending on the server hardware, this could be a high load and could indicate performance issues due to the server being overloaded." );
					$this->add_solution( 'Contact your hosting provider for help in finding out if there are any server issues.' );
				}
				else {
					$this->add_description( "The load on your server is <code>{$load[0]}</code>. Your server should not be overloaded. This means that the code simply took more time than allowed to process." );
				}
			}
			else {
				$this->add_solution( 'If all of your site\'s page requests are failing, your server may be overloaded. Contact your hosting provider for help in determining if the server is overloaded and what can be done to fix the issue.' );
			}
			
			
			if ( '0' == $max ) {
				$this->add_description( "The <code><a href='http://www.php.net/manual/en/info.configuration.php#ini.max-execution-time'>max_execution_time</a></code> is set to <code>$max</code>. This should allow an unlimited amount of time for script execution." );
				$this->add_solution( 'Contact your hosting provider and ask why setting the <code><a href="http://www.php.net/manual/en/info.configuration.php#ini.max-execution-time">max_execution_time</a></code> to <code>0</code> isn\'t allowing for unlimited execution time.' );
			}
			else if ( $max <= 10 ) {
				$this->add_description( "The <code><a href='http://www.php.net/manual/en/info.configuration.php#ini.max-execution-time'>max_execution_time</a></code> is set to <code>$max</code>. This is very low (the default is <code>30</code>) and when combined with complex code and/or a slower server, could easily cause page load failures." );
				$this->add_solution( 'Contact your hosting provider and ask about increasing the <code><a href="http://www.php.net/manual/en/info.configuration.php#ini.max-execution-time">max_execution_time</a></code> on your site to at least <code>30</code>.' );
			}
			else {
				$this->add_description( "The <code><a href='http://www.php.net/manual/en/info.configuration.php#ini.max-execution-time'>max_execution_time</a></code> is set to <code>$max</code>. This should be more than enough time to process most page requests." );
				$this->add_solution( 'If a complex task is running on your site, it needs more time to finish succesfully. Contact your hosting provider and ask about temporarily increasing the <code><a href="http://www.php.net/manual/en/info.configuration.php#ini.max-execution-time">max_execution_time</a></code> on your site so that the process can finish.' );
				
				$message = 'If this is a normal page request, too much time is being used to render the page.';
				
				if ( 'WordPress core' == $this->data['source_type'] )
					$this->add_solution( "$message Create a support request on the <a href='http://wordpress.org/support/'>official WordPress support forum</a> and post these error details to get help with fixing this issue." );
				else if ( 'theme' == $this->data['source_type'] )
					$this->add_solution( "$message Contact the {$this->data['source_name']} {$this->data['source_type']} author to find a solution to the problem. A temporary solution is to switch to another theme so that site functionality is restored." );
				else if ( preg_match( '/plugin/i', $this->data['source_type'] ) )
					$this->add_solution( "$message Contact the {$this->data['source_name']} {$this->data['source_type']} author to find a solution to the problem. A temporary solution is to disable the plugin so that site functionality is restored." );
			}
			
			
			
			$this->solutions_complete = true;
			
			return;
		}
		
		if ( preg_match( '/Call to undefined method WP_Screen::add_help_sidebar/', $this->data['error_message'] ) ) {
			$this->add_description( "The <code>WP_Screen::add_help_sidebar()</code> function existed in the development version of WordPress 3.3. Before the release of WordPress 3.3, it was renamed to <code>WP_Screen::set_help_sidebar()</code>." );
			
			if ( preg_match( '|builder-core/admin-functions\.php|', $this->data['error_file'] ) )
				$this->add_solution( "Upgrading the Builder core theme to a current version will fix this issue." );
			
			$this->add_solution( "Changing the function name in the code from <code>add_help_sidebar()</code> to <code>set_help_sidebar()</code> will fix this issue." );
			
			
			return;
		}
		
		if ( preg_match( '/Call to undefined function builder_register_module_style/', $this->data['error_message'] ) ) {
			$builder_data = array();
			
			if ( ! empty( $this->data['source_parent_data'] ) && ! empty( $this->data['source_parent_data']['name'] ) && ( 'Builder' == $this->data['source_parent_data']['name'] ) )
				$builder_data = $this->data['source_parent_data'];
			else if ( ! empty( $this->data['source_data']['name'] ) && ( 'Builder' == $this->data['source_data']['name'] ) )
				$builder_data = $this->data['source_data'];
			
			if ( ! empty( $builder_data ) ) {
				if ( ! empty( $builder_data['version'] ) && version_compare( $builder_data['version'], '2.7.0', '<' ) ) {
					$this->add_description( "The <code>builder_register_module_style()</code> function was added in Builder version 2.7.0. This site is running Builder version {$builder_data['version']} which does not have this feature." );
					
					$this->add_solution( "Upgrading the Builder core theme to a current version will fix this issue." );
				}
				else {
					$this->add_description( "The <code>builder_register_module_style()</code> function must be called after the <code>it_libraries_loaded</code> action in order for it to function properly. <a href='http://ithemes.com/codex/page/Builder_Features#Custom_Module_Styles'>This tutorial</a> has information on how to properly add Alternate Module Styles." );
					
					$this->add_solution( "Read <a href='http://ithemes.com/codex/page/Builder_Features#Custom_Module_Styles'>this tutorial</a> for information on how to properly add Alternate Module Styles and make the necessary modifications to your code." );
				}
			}
			else {
				$this->add_description( "The <code>builder_register_module_style()</code> is provided by the <a href='http://ithemes.com/purchase/builder-theme/'>Builder theme</a>. Your site does not appear to be running the Builder theme." );
				
				$this->add_solution( "Switch to the Builder theme or remove the customizations that call the <code>builder_register_module_style()</code> function." );
			}
			
			return;
		}
		
		if ( preg_match( '/Cannot redeclare ([^\(]+)/', $this->data['error_message'], $match ) ) {
			$this->add_description( "The code tried to create a function that already existed (<code>{$match[1]}()</code>). This error typically occurs when the PHP file that defines the function is loaded more than once, which could indicate a bug in the code or that some modification is incorrectly trying to load the file again. This type of error can sometimes indicate a plugin conflict which can occur if two plugins try to use the same function name." );
			
			return;
		}
		
		if ( preg_match( '/Call to undefined (function|method) ([^\(]+)/', $this->data['error_message'], $match ) ) {
			$this->add_description( "The code tried to run a function that doesn't exist (<code>{$match[2]}()</code>). This type of error is typically caused by a simple typo (<code>is_hom()</code> rather than <code>is_home()</code>), by calling a function provided by a plugin when that plugin is not activated, by calling a function before it is ready (such as calling a function before the code that creates it has run), or by using a function that no longer exists (the function may have been removed from WordPress core or the plugin/theme that previously supplied it)." );
			
			return;
		}
		
		if ( preg_match( '/Class \'([^\']+)\' not found/', $this->data['error_message'], $match ) ) {
			$this->add_description( "The code tried to use a PHP class that doesn't exist (<code>{$match[1]}</code>). This type of error is typically caused by a simple typo (<code>WP_error</code> rather than <code>WP_Error</code>), by trying to use a class that is provided by a plugin when that plugin is not activated, by trying to use a class before it is ready (such as trying to use a class before the code that creates it has run), or by trying to use a class that no longer exists (the class may have been removed from WordPress core or the plugin/theme that previously supplied it)." );
			
			return;
		}
		
		if ( preg_match( '/([^\(]+)\(\)( \[[^\]]+\])?: Failed opening required \'([^\']+)\'/', $this->data['error_message'], $match ) ) {
			if ( in_array( $this->data['source_type'], array( 'theme', 'plugin' ) ) )
				$update = " Try uploading the {$this->data['source_type']} again to see if that fixes the problem.";
			else if ( 'WordPress core' == $this->data['source_type'] )
				$update = " If the missing file is part of WordPress, you may have to <a href=\"http://codex.wordpress.org/Updating_WordPress#Manual_Update\">manually update the WordPress files</a>.";
			
			$this->add_description( "A required file (<code>{$match[3]}</code>) was unable to be loaded since it does not exist. This error typically indicates that the code has a typo (<code>{$match[1]}('hom.php')</code> rather than <code>{$match[1]}('home.php')</code>) or that the file is actually missing.$update" );
			
			return;
		}
		
		if ( preg_match( '/syntax error, unexpected \$end/', $this->data['error_message'] ) ) {
			$this->add_description( "The file indicated in the error message is unable to be processed as a valid PHP file. This can be due to a bug such as a missing close curly brace (<code>}</code>) or because the file was not completely uploaded." );
			
			return;
		}
		
		if ( preg_match( '/syntax error/', $this->data['error_message'] ) ) {
			$this->add_description( "This type error is called a \"syntax error.\" A syntax error means that the format of the code is invalid. Examples of syntax errors: a missing semicolon after a line of code, having mismatched parentheses, forgetting to put a dollar sign (<code>$</code>) in front of a variable's name, and forgetting an operator &mdash; such as a plus sign (<code>+</code>) &mdash; between two variables. There are many other possible causes of syntax errors, the preceding are just a few examples." );
			
			return;
		}
		
		if ( preg_match( '/Call-time pass-by-reference has been removed/', $this->data['error_message'] ) ) {
			$this->add_description( "This error is due to your server running PHP version <code>" . phpversion() . "</code>. Starting in 5.4.0, PHP no longer permits passing function arguments by reference. The {$this->data['source_type']} code is violating this rule and is causing the code to fail." );
			
			$this->add_solution( "If you are able, switching your PHP version to 5.2.4+ or 5.3.0+ will allow your site to function properly again." );
			$this->add_solution( "Contact the {$this->data['source_type']} author and notify them of the issue. It could be possible to have the problem solved quickly." );
		}
	}
	
	function add_source_specific_descriptions() {
		if ( 'WordPress core' == $this->data['source_type'] ) {
			$this->add_description( 'The WordPress code created an error that caused PHP execution to fail. This typically happens when the WordPress code has been modified.' );
		}
		else {
			$this->add_description( "The {$this->data['details_name']} {$this->data['source_type']} code created an error that caused PHP execution to fail." );
			
			if ( 'must-use plugin' == $this->data['source_type'] )
				$this->add_description( "<strong>Note:</strong> Must-use plugins are not the same as regular plugins. Must-use plugins exist in a different directory than the regular plugins, and must-use plugins do not require activation since they always run on every page load." );
			else if ( 'drop-in plugin' == $this->data['source_type'] )
				$this->add_description( "<strong>Note:</strong> Drop-in plugins are not the same as regular plugins. Drop-in plugins exist in the <kbd>wp-content</kbd> directory, and drop-in plugins do not require activation since they always run when needed (such as some drop-in plugins only running in multisite installations)." );
		}
	}
	
	function build_solutions() {
		$solutions = array();
		
		
		if ( 'theme' == $this->data['source_type'] ) {
			$theme_solutions = array(
				'modification',
				'out_of_date',
				'plugin_conflict',
				'reupload',
			);
			
			if ( ! empty( $this->data['source_parent_data'] ) )
				$theme_solutions[] = 'parent_theme_conflict';
			
			$solutions = array_merge( $solutions, $theme_solutions );
		}
		else if ( in_array( $this->data['source_type'], array( 'plugin', 'must-use plugin', 'drop-in plugin' ) ) ) {
			$plugin_solutions = array( 'modification', 'out_of_date', 'plugin_conflict_for_plugins', 'reupload' );
			
			$solutions = array_merge( $solutions, $plugin_solutions );
		}
		else if ( 'sunrise.php' == $this->data['source_type'] ) {
			$sunrise_solutions = array( 'modification', 'plugin_conflict' );
			
			$solutions = array_merge( $solutions, $sunrise_solutions );
		}
		else if ( 'WordPress core' == $this->data['source_type'] ) {
			$wordpress_solutions = array( 'modification', 'reupload_wordpress_core' );
			
			$solutions = array_merge( $solutions, $wordpress_solutions );
		}
		
		
		$this->add_standard_solution( array_unique( $solutions ) );
	}
	
	function add_source_data() {
		if ( preg_match( '|^' . preg_quote( WP_PLUGIN_DIR, '|' ) . '/|', $this->data['error_file'] ) )
			$this->add_plugin_source_data();
		else if ( preg_match( '|^' . preg_quote( PLUGINDIR, '|' ) . '/|', $this->data['error_file'] ) )
			$this->add_plugin_source_data( PLUGINDIR );
		else if ( preg_match( '|^' . preg_quote( WPMU_PLUGIN_DIR, '|' ) . '/|', $this->data['error_file'] ) )
			$this->add_plugin_source_data( WPMU_PLUGIN_DIR, 'must-use plugin' );
		else if ( preg_match( '|^' . preg_quote( MUPLUGINDIR, '|' ) . '/|', $this->data['error_file'] ) )
			$this->add_plugin_source_data( MUPLUGIN_DIR, 'must-use plugin' );
		else if ( preg_match( '#^(' . preg_quote( ABSPATH . WPINC, '#' ) . '|'. preg_quote( ABSPATH . 'wp-admin', '#' ) . ')#', $this->data['error_file'] ) )
			$this->add_wordpress_core_source_data();
		else if ( preg_match( '#^' . preg_quote( WP_CONTENT_DIR, '#' ) . '/[^/]+\.php$#', $this->data['error_file'] ) )
			$this->add_plugin_source_data( WP_CONTENT_DIR, 'drop-in plugin' );
		else
			$this->add_theme_source_data();
		
		if ( empty( $this->data['source_type'] ) )
			$this->add_wordpress_core_source_data();
		
		if ( empty( $this->data['source_type'] ) ) {
			$source = array(
				'type' => 'Unknown',
				'path' => $this->data['error_file'],
				'file' => basename( $this->data['error_file'] ),
			);
			
			$this->add_data( $source, 'source_' );
		}
		
		if ( empty( $this->data['source_pretty_type'] ) )
			$this->data['source_pretty_type'] = ucwords( $this->data['source_type'] );
	}
	
	function add_wordpress_core_source_data() {
		$source = array(
			'type'        => 'WordPress core',
			'pretty_type' => 'WordPress Core',
			'path'        => ABSPATH,
			'file'        => preg_replace( '/^' . preg_quote( ABSPATH, '/' ) . '/', '', $this->data['error_file'] ),
		);
		
		$this->add_data( $source, 'source_' );
	}
	
	function add_theme_source_data() {
		$source = false;
		
		
		if ( ! empty( $GLOBALS['wp_theme_directories'] ) )
			$directories = (array) $GLOBALS['wp_theme_directories'];
		else if ( function_exists( 'get_theme_root' ) )
			$directories = array( get_theme_root() );
		else
			$directories = array( WP_CONTENT_DIR . '/themes' );
		
		foreach ( $directories as $directory ) {
			if ( preg_match( '|^' . preg_quote( $directory, '|' ) . '/|', $this->data['error_file'] ) )
				break;
			
			$directory = null;
		}
		
		if ( is_null( $directory ) )
			return false;
		
		
		$source = array(
			'type' => 'theme',
			'path' => preg_replace( '/^(' . preg_quote( $directory . DIRECTORY_SEPARATOR, '/' ) . "[^" . preg_quote( DIRECTORY_SEPARATOR, '/' )  . ']+).*/', '$1', $this->data['error_file'] ),
		);
		$source['slug'] = basename( $source['path'] );
		$source['file'] = preg_replace( '/^' . preg_quote( $source['path'] . DIRECTORY_SEPARATOR, '/' ) . '/', '', $this->data['error_file'] );
		
		$source['data'] = $this->get_file_data( "{$source['path']}/style.css", $this->get_theme_headers() );
		
		if ( ! empty( $source['data']['template'] ) ) {
			foreach ( $directories as $directory ) {
				if ( file_exists( "$directory/{$source['data']['template']}/style.css" ) )
					break;
				
				$directory = null;
			}
			
			if ( ! is_null( $directory ) )
				$source['parent_data'] = $this->get_file_data( "$directory/{$source['data']['template']}/style.css", $this->get_theme_headers() );
		}
		
		$this->add_data( $source, 'source_' );
	}
	
	function add_plugin_source_data( $base_path = WP_PLUGIN_DIR, $type = 'plugin' ) {
		$source = array();
		
		$source['type'] = $type;
		$source['path'] = preg_replace( '/^(' . preg_quote( $base_path . DIRECTORY_SEPARATOR, '/' ) . "[^" . preg_quote( DIRECTORY_SEPARATOR, '/' )  . ']+).*/', '$1', $this->data['error_file'] );
		$source['slug'] = basename( $source['path'] );
		
		if ( is_file( $source['path'] ) ) {
			$source['file'] = basename( $source['path'] );
			$source['plugin_type'] = 'file';
		}
		else {
			$source['file'] = preg_replace( '/^' . preg_quote( $source['path'] . DIRECTORY_SEPARATOR, '/' ) . '/', '', $this->data['error_file'] );
			$source['plugin_type'] = 'directory';
		}
		
		
		if ( is_dir( $source['path'] ) && ( false !== ( $dir = @opendir( $source['path'] ) ) ) ) {
			while ( false !== ( $file = readdir( $dir ) ) ) {
				if ( '.' == substr( $file, 0, 1 ) )
					continue;
				
				if ( '.php' == substr( $file, -4 ) ) {
					$data = $this->get_file_data( "{$source['path']}/$file", $this->get_plugin_headers() );
					
					if ( ! empty( $data['name'] ) ) {
						$source['data'] = $data;
						break;
					}
				}
			}
			
			closedir( $dir );
		}
		
		$this->add_data( $source, 'source_' );
	}
	
	function add_standard_solution( $types ) {
		if ( ! isset( $this->standard_solutions ) ) {
			$this->standard_solutions = array(
				'modification'                => sprintf( 'Invalid code modifications can cause this problem. If you have made any modifications to the %1$s, remove them and try to load the site again.', $this->data['source_type'] ),
				'out_of_date'                 => sprintf( 'It is possible that this %1$s\'s code is out of date and that an upgrade is available. Check with the %2$s %1$s\'s author%3$s to see if an upgrade is available.', $this->data['source_type'], $this->get_details( 'name' ), $this->get_details( 'author', ' (', ')' ) ),
				'plugin_conflict'             => 'There may be a conflict with a plugin running on the site. Try upgrading all the plugins on the site. A plugin conflict can be ruled out by deactivating all the active plugins on the site and checking to see if the error still occurs.',
				'plugin_conflict_for_plugins' => 'There may be a conflict with another plugin running on the site. Try upgrading the other plugins on the site. A plugin conflict can be ruled out by deactivating all the other active plugins on the site and checking to see if the error still occurs.',
				'reupload'                    => sprintf( 'The %1$s %2$s may not have been fully uploaded. Uploading the %2$s again could fix the issue. <strong>Important:</strong> If you do this, you will lose any modifications made to the %2$s.', $this->data['details_name'], $this->data['source_type'] ),
				'reupload_wordpress_core'     => sprintf( 'The %1$s %2$s may not have been fully uploaded. This can happen with bad or incomplete upgrades. Uploading the %2$s files again could fix the issue. You can find details on how to manually update WordPress\'s files in <a href="http://codex.wordpress.org/Updating_WordPress#Manual_Update">these instructions</a>.', $this->data['details_name'], $this->data['source_type'] ),
			);
			
			if ( ! empty( $this->data['source_parent_data'] ) ) {
				$this->standard_solutions['parent_theme_conflict'] = sprintf( 'The theme that triggered the error has a parent theme (%1$s version %2$s). The parent theme may be out of date. Check with the %1$s theme\'s author%3$s to see if an upgrade is available. If the parent theme was recently upgraded, a change in the parent theme may cause any modifications present in the child theme to no longer function. Remove your child theme modifications and load the site again to see if this resolves the error.', $this->get_details( 'parent_name' ), $this->get_details( 'parent_version' ), $this->get_details( 'parent_author' ) );
			}
		}
		
		foreach ( (array) $types as $type ) {
			if ( ! empty( $this->standard_solutions[$type] ) )
				$this->add_solution( $this->standard_solutions[$type] );
		}
	}
	
	function add_software_details() {
		$details = array();
		
		
		$details['Type'] = ucfirst( $this->data['source_type'] );
		
		$details_vars = array(
			'details_name'    => 'Name',
			'details_version' => 'Version',
			'details_author'  => 'Author',
		);
		
		foreach ( $details_vars as $var => $name ) {
			if ( ! empty( $this->data[$var] ) )
				$details[$name] = $this->data[$var];
		}
		
		if ( ! empty( $this->data['source_path'] ) ) {
			$details['Path'] = $this->data['source_path'];
			
			if ( empty( $this->data['source_plugin_type'] ) || ( 'file' != $this->data['source_plugin_type'] ) )
				$details['File'] = $this->data['source_file'];
		}
		
		
		$this->add_data( array( 'software_details' => $details ) );
	}
	
	function add_other_software_details() {
		$details = array();
		
		
		$details_vars = array(
			'details_parent_name'    => 'Parent Theme Name',
			'details_parent_author'  => 'Parent Theme Author',
			'details_parent_version' => 'Parent Theme Version',
		);
		
		foreach ( $details_vars as $var => $name ) {
			if ( ! empty( $this->data[$var] ) )
				$details[$name] = $this->data[$var];
		}
		
		$details['WordPress Version'] = $GLOBALS['wp_version'];
		$details['PHP Version'] = phpversion();
		
		
		$this->add_data( array( 'other_software_details' => $details ) );
	}
	
	function get_details( $type, $prefix = '', $suffix = '' ) {
		if ( empty( $this->details[$type] ) )
			return '';
		
		return "$prefix{$this->details[$type]}$suffix";
	}
	
	function add_source_details() {
		$details = array(
			'name'           => '',
			'author'         => '',
			'version'        => '',
			'parent_name'    => '',
			'parent_author'  => '',
			'parent_version' => '',
		);
		
		$types = array(
			'data'        => '',
			'parent_data' => 'parent_',
		);
		
		
		foreach ( $types as $type => $prefix ) {
			if ( ! empty( $this->data["source_$type"] ) ) {
				if ( ! empty( $this->data["source_$type"]['name'] ) ) {
					if ( ! empty( $this->data["source_$type"]['uri'] ) )
						$details[$prefix . 'name'] = "<a href='{$this->data["source_$type"]['uri']}'>{$this->data["source_$type"]['name']}</a>";
					else
						$details[$prefix . 'name'] = $this->data["source_$type"]['name'];
				}
				else if ( ( 'data' != $type ) && ! empty( $this->data['source_slug'] ) ) {
					$details[$prefix . 'name'] = $this->data['source_slug'];
				}
				
				if ( ! empty( $this->data["source_$type"]['author'] ) ) {
					if ( ! empty( $this->data["source_$type"]['author_uri'] ) )
						$details[$prefix . 'author'] = "<a href='{$this->data["source_$type"]['author_uri']}'>{$this->data["source_$type"]['author']}</a>";
					else
						$details[$prefix . 'author'] = $this->data["source_$type"]['author'];
				}
				
				if ( ! empty( $this->data["source_$type"]['version'] ) ) {
					$details[$prefix . 'version'] = $this->data["source_$type"]['version'];
				}
			}
		}
		
		
		$this->add_data( $details, 'details_' );
	}
	
	function get_theme_headers() {
		$headers = array(
			'name'        => 'Theme Name',
			'version'     => 'Version',
			'uri'         => 'Theme URI',
			'description' => 'Description',
			'author'      => 'Author',
			'author_uri'  => 'Author URI',
			'text_domain' => 'Text Domain',
			'domain_path' => 'Domain Path',
			'template'    => 'Template',
			'status'      => 'Status',
			'tags'        => 'Tags',
		);
		
		return $headers;
	}
	
	function get_plugin_headers() {
		$headers = array(
			'name'        => 'Plugin Name',
			'version'     => 'Version',
			'uri'         => 'Plugin URI',
			'description' => 'Description',
			'author'      => 'Author',
			'author_uri'  => 'Author URI',
			'text_domain' => 'Text Domain',
			'domain_path' => 'Domain Path',
			'network'     => 'Network',
		);
		
		return $headers;
	}
	
	// Modified from the WP core get_file_data function from WP 3.5-alpha
	function get_file_data( $file, $headers, $context = '' ) {
		if ( ! is_file( $file ) || ! is_readable( $file ) )
			return array();
		
		$fp = fopen( $file, 'r' );
		$file_data = fread( $fp, 8192 );
		fclose( $fp );
		
		$file_data = str_replace( "\r", "\n", $file_data );
		
		foreach ( $headers as $field => $regex ) {
			if ( preg_match( '/^[ \t\/*#@]*' . preg_quote( $regex, '/' ) . ':(.*)$/mi', $file_data, $match ) && $match[1] )
				$headers[$field] = trim( preg_replace( "/\s*(?:\*\/|\?>).*/", '', $match[1] ) );
			else
				$headers[$field] = '';
		}
		
		return $headers;
	}
}
