<?php
/**
 *
 * @package LDMW
 * @subpackage Conferences
 * @since 5/29
 */

new LDMW_Conference_Base();

require_once( LDMW_Plugin::$dir . "lib/conference/papers/load.php" );
require_once( LDMW_Plugin::$dir . "lib/conference/exchange/load.php" );
require_once( LDMW_Plugin::$dir . "lib/conference/tec/load.php" );
require_once( LDMW_Plugin::$dir . "lib/conference/metabox/load.php" );
require_once( LDMW_Plugin::$dir . "lib/conference/attendees/load.php" );
