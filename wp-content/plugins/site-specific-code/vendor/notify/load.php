<?php
/**
 *
 * @package LDMW
 * @subpackage Notify
 * @since 1.0
 */
require_once( LDMW_Plugin::$dir . "/vendor/notify/config.php" );
require_once( LDMW_Plugin::$dir . "/vendor/notify/autoload.php" );
new IBD_Notify_Wrapper_WordPress();