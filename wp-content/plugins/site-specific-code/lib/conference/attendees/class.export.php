<?php

/**
 *
 * @package Conferences
 * @subpackage Attendees
 * @since 6/2
 */
class LDMW_Conference_Attendees_Export extends LDMW_Conference_Attendees_Controller {

	/**
	 * @var bool
	 */
	protected $export = false;

	/**
	 * Constructor.
	 *
	 * @param $model LDMW_ManageMembers_Model_Users
	 * @param $conference_id int
	 */
	public function __construct( $model, $conference_id ) {
		parent::__construct( $model, $conference_id );

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
		$view = new LDMW_ManageMembers_View_Export( $this->prepare_users( $this->users ) );
		$this->export === true ? $view->render_csv() : $view->render();
	}
}