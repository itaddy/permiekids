<?php

/**
 *
 * @package    LDMW
 * @subpackage Users
 * @since      1.0
 */
abstract class LDMW_Users_View {
	/**
	 * @var int
	 */
	protected $user_id;

	/**
	 * Necessary hooks, filters, and set up data.
	 *
	 * @param $user_id int|null
	 */
	public function __construct( $user_id = null ) {
		if ( $user_id != null ) {
			$this->user_id = $user_id;
		}
		else {
			$this->user_id = isset( $_GET['user_id'] ) ? $_GET['user_id'] : get_current_user_id();
		}
	}

	/**
	 * Render the tab's content.
	 *
	 * @return void
	 */
	abstract public function render();

	/**
	 * Can the current user edit this page.
	 *
	 * @return bool
	 */
	protected function can_edit_page() {
		return user_can( $this->user_id, LDMW_Users_Base::$member_role_slug ) && current_user_can( 'edit_users' );
	}
}