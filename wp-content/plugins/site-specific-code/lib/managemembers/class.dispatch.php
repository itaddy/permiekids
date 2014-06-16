<?php

/**
 *
 * @package LDMW
 * @subpackage Manage Members
 * @since 1.0
 */
class LDMW_ManageMembers_Dispatch {
	/**
	 * Hold the page hook.
	 *
	 * @var bool|string
	 */
	private $hook;

	/**
	 * Holds the current view we are on.
	 *
	 * @var string
	 */
	private $view;

	/**
	 * Add hooks and add page.
	 */
	public function __construct() {
		$this->view = isset( $_GET['view'] ) && in_array( $_GET['view'], array( 'list', 'import', 'export' ) ) ? $_GET['view'] : "list";
		add_action( 'admin_menu', array( $this, 'register_page' ) );
		$this->download_csv();
	}

	/**
	 * Register the admin page.
	 */
	public function register_page() {
		$this->hook = add_menu_page( "Members", "Members", "edit_users", 'ldmw-manage-members', array( $this, 'dispatch' ) );
	}

	/**
	 * Dispatch the request.
	 */
	public function dispatch() {
		$view = ucfirst( $this->view );
		$class_name = "LDMW_ManageMembers_Controller_{$view}";

		/**
		 * @var $controller LDMW_ManageMembers_Controller
		 */
		$controller = new $class_name( new LDMW_ManageMembers_Model_Users );
		$controller->serve();
	}

	/**
	 * Process the CSV download.
	 */
	public function download_csv() {
		if ( is_admin() && isset( $_GET['export'] ) && isset( $_GET['page'] ) && $_GET['page'] == 'ldmw-manage-members' ) {
			$controller = new LDMW_ManageMembers_Controller_Export( new LDMW_ManageMembers_Model_Users() );
			$controller->serve();
		}
	}
}