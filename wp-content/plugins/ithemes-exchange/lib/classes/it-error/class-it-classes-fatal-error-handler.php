<?php

/*
Written by Chris Jean for iThemes.com
Version 1.0.1

Version History
	1.0.0 - 2012-06-29 - Chris Jean
		Split off of the 1.2.0 version of it-error.php
	1.0.1 - 2013-06-25 - Chris Jean
		Changed function declaration to "public static".
*/


class IT_Classes_Fatal_Error_Handler {
	public static function handle_error( $error ) {
		if ( ! ( $error['type'] & ( E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR ) ) )
			return;
		
		
		$message = "<h2>Error Message</h2>\n";
		$message .= "<pre>Fatal Error: {$error['message']} in {$error['file']} on line {$error['line']}</pre>\n";
		
		$message .= "<h2>Description</h2>\n";
		
		
		if ( preg_match( '/Out of memory \(allocated (\d*)/', $error['message'], $match ) || preg_match( '/Allowed memory size of (\d*)/', $error['message'], $match ) ) {
			$memory_limit = $match[1] / 1024 / 1024;
			
			
			$message .= "<p>PHP exceeded the maximum amount of memory that it can use while rendering this page. The error message indicates that a specific file is the cause of the problem, but this file isn't necessarily doing anything incorrectly. All code will consume some memory. It just so happened that the memory limit was reached while running code in the file indicated in the error message.</p>\n";
			
			
			$message .= "<p>Your site is configured to allow PHP to use a maximum of <code>$memory_limit megabytes</code> of memory. ";
			
			if ( $memory_limit <= 32 )
				$message .= "This amount of memory can be very limiting as it will only allow for a few plugins and a simple theme to run on the site before the memory is used up.";
			else if ( $memory_limit <= 64 )
				$message .= "This amount of memory should be sufficient for most sites. Some more complex plugins and themes may require a large amount of memory to run properly. However, if you are only running very simple plugins and a very simple theme, this error could indicate that there is a problem with one of them.";
			else
				$message .= "This is a large amount of memory which should only be exceeded in very rare cases. While increasing the memory limit can allow all the current code to run, disabling some of the plugins that are consuming large amounts of memory should be considered.";
			
			$message .= "</p>\n";
			
			
			$message .= "<h2>Solutions</h2>\n";
			
			$message .= "<ul>\n";
			$message .= "<li>Disabling some plugins should reduce the amount of memory used and will return site functionality. You can manually deactivate a plugin by using FTP to rename an active plugin's directory (such as renaming <code>public_html/wp-content/plugins/sample-plugin</code> to <code>public_html/wp-content/plugins/sample-plugin.bak</code>).</li>\n";
			$message .= "<li>If you need to raise PHP's maximum memory limit, contact your hosting provider for instructions on how to do this.</li>\n";
			$message .= "</ul>\n";
		}
		else {
			require_once( dirname( __FILE__ ) . '/class-it-classes-fatal-error-parser.php' );
			
			$error_parser = new IT_Classes_Fatal_Error_Parser( $error );
			
			$message .= $error_parser->get_message();
			
			
			if ( false === $message )
				return;
		}
		
		
		ITError::show_fatal_error_message( $message );
	}
}


function it_classes_fatal_error_handler_shutdown() {
	$error = error_get_last();
	
	if ( is_array( $error ) )
		IT_Classes_Fatal_Error_Handler::handle_error( $error );
}

if ( function_exists( 'error_get_last' ) )
	register_shutdown_function( 'it_classes_fatal_error_handler_shutdown' );
