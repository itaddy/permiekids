<?php

/**
 *
 * @package Conferences
 * @subpackage Attendees
 * @since 6/2
 */
class LDMW_Conference_Attendees_Dispatch {
	/**
	 * Hold the page hook.
	 *
	 * @var bool|string
	 */
	private $hook;

	/**
	 * Conference transaction ID
	 *
	 * @var int
	 */
	protected $conference_id;

	/**
	 * Add hooks and add page.
	 */
	public function __construct() {
		$this->conference_id = urldecode($_GET['id']);
		add_action( 'admin_menu', array( $this, 'register_page' ) );
		$this->download_csv();
	}

	/**
	 * Register the admin page.
	 */
	public function register_page() {
		$this->hook = add_submenu_page( 'edit.php?post_type=tribe_events', "Attendees", "Attendees", "edit_users", 'ldmw-conference-attendees', array( $this, 'dispatch' ) );
	}

	/**
	 * Dispatch the request.
	 */
	public function dispatch() {
		/**
		 * @var $controller LDMW_ManageMembers_Controller
		 */
		$controller = new LDMW_Conference_Attendees_Controller( new LDMW_Conference_Attendees_Model( $this->conference_id ), $this->conference_id );
		$controller->serve();
	}

	/**
	 * Process the CSV download.
	 */
	public function download_csv() {
		if ( is_admin() && isset( $_GET['export'] ) && isset( $_GET['page'] ) && $_GET['page'] == 'ldmw-conference-attendees' ) {
			$controller = new LDMW_Conference_Attendees_Export( new LDMW_Conference_Attendees_Model( $this->conference_id ), $this->conference_id );
			$controller->serve();
		}
	}
}