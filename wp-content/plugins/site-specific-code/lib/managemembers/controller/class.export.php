<?php

/**
 *
 * @package LDMW
 * @subpackage Manage Members
 * @since 1.0
 */
class LDMW_ManageMembers_Controller_Export extends LDMW_ManageMembers_Controller_List {
	/**
	 * @var bool
	 */
	protected $export = false;

	/**
	 * Constructor.
	 *
	 * @param $model LDMW_ManageMembers_Model_Users
	 */
	public function __construct( $model ) {
		parent::__construct( $model );

		if ( is_admin() && isset( $_GET['export'] ) && current_user_can( 'list_users' ) ) {
			$this->export = true;
		}
	}

	/**
	 * Serve the page request.
	 *
	 * Override the method to use a new view.
	 */
	public function serve() {
		$data = array();

		foreach ( $this->users as $user ) {
			$data[] = $this->prepare_user( $user );
		}

		$view = new LDMW_ManageMembers_View_Export( $data );
		$this->export === true ? $view->render_csv() : $view->render();
	}
}