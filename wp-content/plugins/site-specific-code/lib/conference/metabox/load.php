<?php
/**
 *
 * @package Conference
 * @subpackage Metabox
 * @since 6/2
 */

add_filter( 'query_vars',
  function ( $vars ) {
	  $vars[] = 'ldmw_conference_id';
	  $vars[] = 'ldmw_conference_view';

	  return $vars;
  }
);

new LDMW_Conference_Metabox_Page( 'Submit Paper', 'paper', TribeEvents::POSTTYPE );
new LDMW_Conference_Metabox_Page( "Submit Abstract", 'abstract' );
new LDMW_Conference_Metabox_Page( "Shortcourses" );
new LDMW_Conference_Metabox_Page( "Speakers" );
new LDMW_Conference_Metabox_Page( "Program" );
new LDMW_Conference_Metabox_Page( "President's Prize", "prize" );
new LDMW_Conference_Metabox_Page( "Organising, Technical & International Advisory Committees", 'committees' );
new LDMW_Conference_Metabox_Page( "Travel Information", 'travel' );
new LDMW_Conference_Metabox_Page( "Exhibitor Information", 'exhibit' );
new LDMW_Conference_Metabox_Page( "Sponsors" );
new LDMW_Conference_Metabox_Page( "Contact" );
