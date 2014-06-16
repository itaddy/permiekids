<?php

/**
 *
 * @package LDMW
 * @subpackage Manage Members
 * @since 1.0
 */
class LDMW_ManageMembers_Base {
	/**
	 *
	 */
	public function __construct() {
		add_filter( 'set-screen-option', array( $this, 'save_members_per_page' ), 10, 3 );
		add_action( 'load-toplevel_page_ldmw-manage-members', array( $this, 'add_screen_options' ) );
	}

	/**
	 * @param $status string
	 * @param $option string
	 * @param $value string
	 *
	 * @return string|boolean
	 */
	public function save_members_per_page( $status, $option, $value ) {
		if ( 'fencer_list_table_per_page' == $option )
			return $value;

		return false;
	}

	/**
	 * Register screen options for manage members page.
	 */
	public function add_screen_options() {
		if ( ! isset( $_GET['view'] ) || $_GET['view'] == 'list' )
			add_screen_option( 'per_page', array( 'label' => "Members", 'default' => 20, 'option' => 'fencer_list_table_per_page' ) );
	}
}