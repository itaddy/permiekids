<?php

/*
Written by Chris Jean for iThemes.com
Version 1.1.0

Version History
	1.0.0 - 2010-06-29
		Release-ready
	1.1.0 - 2011-02-22
		Added functions:
			it_classes_log_init_action_call
			it_has_init_run
*/


if ( ! function_exists( 'it_classes_load' ) ) {
	function it_classes_load( $file ) {
		require_once( dirname( __FILE__ ) . "/$file" );
	}
}

if ( ! function_exists( 'it_classes_log_init_action_call' ) ) {
	function it_classes_log_init_action_call() {
		global $it_classes_init_action_called;
		
		$it_classes_init_action_called = true;
	}
	add_action( 'init', 'it_classes_log_init_action_call', -1000 );
}

if ( ! function_exists( 'it_classes_has_init_run' ) ) {
	function it_classes_has_init_run() {
		global $it_classes_init_action_called;
		
		return isset( $it_classes_init_action_called );
	}
}


require_once( dirname( __FILE__ ) . '/it-error.php' );
require_once( dirname( __FILE__ ) . '/it-utility.php' );
require_once( dirname( __FILE__ ) . '/it-core-class.php' );

if ( WP_DEBUG )
	require_once( dirname( __FILE__ ) . '/it-debug.php' );
