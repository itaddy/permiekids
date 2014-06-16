<?php

/**
 *
 * @package LDMW
 * @subpackage Manage Members
 * @since 1.0
 */
abstract class LDMW_ManageMembers_Controller {
	/**
	 * @var LDMW_ManageMembers_Model_Users
	 */
	protected $model;

	/**
	 * @param $model LDMW_ManageMembers_Model_Users
	 */
	public function __construct( $model ) {
		$this->model = $model;
	}

	/**
	 * @return mixed
	 */
	abstract public function serve();
}