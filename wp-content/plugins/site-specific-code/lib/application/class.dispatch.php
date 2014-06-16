<?php

/**
 *
 * @package LDMW
 * @subpackage Application Approval
 * @since 1.0
 */
class LDMW_Application_Approval_Dispatcher {
	/**
	 * Current view
	 *
	 * @var string
	 */
	private $view;
	/**
	 * Current action
	 *
	 * @var string
	 */
	private $action;

	/**
	 * Determine what action and view we are on
	 */
	public function __construct() {
		$this->view = isset( $_GET['view'] ) ? $_GET['view'] : 'list';
		$this->action = isset( $_GET['action'] ) ? $_GET['action'] : "view";
	}

	/**
	 * Dispatch the request.
	 */
	public function dispatch() {
		$param = null;

		switch ( $this->view ) {
			case "list" :
				$param = LDMW_Application_Util::parse_search( $_GET );
				break;
			case "single" :
				$entry_id = isset( $_GET['id'] ) ? $_GET['id'] : - 1;
				$param = LDMW_Gravity_Util::get_application_entry( $entry_id );
				break;
		}

		if ( $this->action == 'edit' && ! current_user_can( 'edit_application' ) )
			$this->action = 'view';

		if ( $this->action == 'view' && ! current_user_can( 'read_application' ) )
			$this->view = 'list';

		$dir = dirname( __FILE__ ) . "/{$this->view}/class.action-{$this->action}.php";
		require( $dir );

		$class_name = "LDMW_Application_Approval_" . $this->view . "_" . $this->action;
		$view = new $class_name( $param );
		$view->render();
	}

	/**
	 * Get the url to a new page based on the wanted view and action.
	 *
	 * @param string $view
	 * @param string $action
	 * @param int $entry_id
	 *
	 * @return string
	 */
	public static function get_redirect_url( $view = "list", $action = "view", $entry_id = -1 ) {
		return add_query_arg( array( "view" => $view, "action" => $action, 'id' => $entry_id ) );
	}

	/**
	 * Redirect to a new page based on the wanted view and action.
	 *
	 * @param string $view
	 * @param string $action
	 * @param int $entry_id
	 */
	public static function redirect_to( $view = "list", $action = "view", $entry_id = -1 ) {
		if ( ! headers_sent() ) {
			wp_redirect( self::get_redirect_url( $view, $action, $entry_id ) );
			die();
		}
		else {
			$dir = dirname( __FILE__ ) . "/{$view}/class.action-{$action}.php";
			require( $dir );

			$class_name = "LDMW_Application_Approval_{$view}_{$action}";

			if ( $entry_id != - 1 )
				$param = LDMW_Gravity_Util::get_application_entry( $entry_id );
			else
				$param = LDMW_Gravity_Util::get_application_entries();

			//( new $class_name( $param ) )->render();
		}
	}
}