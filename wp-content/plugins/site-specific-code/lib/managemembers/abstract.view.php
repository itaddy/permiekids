<?php

/**
 *
 * @package LDMW
 * @subpackage Manage Members
 * @since 1.0
 */
abstract class LDMW_ManageMembers_View {

	/**
	 * @var array|WP_User[]
	 */
	protected $users = array();

	/**
	 * @param $users array
	 */
	public function __construct( $users ) {
		$this->users = $users;
	}

	/**
	 * Render the page.
	 *
	 * @return void
	 */
	abstract public function render();
}