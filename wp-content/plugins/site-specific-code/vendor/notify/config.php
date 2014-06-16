<?php
/**
 *
 * @package Notify
 * @subpackage
 * @since 0.1
 */

if ( ! defined( 'IBD_NOTIFY_URL' ) )
	define( 'IBD_NOTIFY_URL', LDMW_Plugin::$url . "/vendor/notify/" );

if ( ! defined( 'IBD_NOTIFY_PATH' ) )
	define( 'IBD_NOTIFY_PATH', LDMW_Plugin::$dir . "/vendor/notify/" );

if ( ! defined( "IBD_NOTIFY_DATABASE_CLASS" ) )
	define( 'IBD_NOTIFY_DATABASE_CLASS', 'IBD_Notify_Database_WordPress' );