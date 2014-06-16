<?php

/**
 *
 * @package LDMW
 * @subpackage Users
 * @since 1.0
 */
class LDMW_Users_Dispatch {
	/**
	 * @var int
	 */
	private $user_id;

	/**
	 * Add necessary hooks and filters.
	 */
	public function __construct() {
		$this->user_id = isset( $_GET['user_id'] ) ? $_GET['user_id'] : get_current_user_id();

		add_action( 'it_exchange_print_user_edit_page_tab_links', array( $this, 'render_committees_tab' ), 15 );
		add_action( 'it_exchange_print_user_edit_page_tab_links', array( $this, 'render_federal_council_tab' ), 15 );
		add_action( 'it_exchange_print_user_edit_page_tab_links', array( $this, 'render_membership_tab' ), 15 );
		add_action( 'it_exchange_print_user_edit_page_tab_links', array( $this, 'render_crm_tab' ), 15 );
		add_action( 'it_exchange_print_user_edit_page_content', array( $this, 'dispatch' ) );
	}

	/**
	 * Render the committees tab.
	 *
	 * @param $current_tab string
	 */
	public function render_committees_tab( $current_tab ) {
		if ( ! user_can( $this->user_id, LDMW_Users_Base::$committee_role_slug ) )
			return;

		$active = ( 'committee' === $current_tab ) ? 'nav-tab-active' : '';
		?>
		<a class="nav-tab <?php echo $active; ?>" href="<?php echo add_query_arg( 'tab', 'committee' ); ?>#it-exchange-member-options"><?php _e( 'Committees' ); ?></a><?php
	}

	/**
	 * Render the committees tab.
	 *
	 * @param $current_tab string
	 */
	public function render_federal_council_tab( $current_tab ) {
		if ( ! user_can( $this->user_id, LDMW_Users_Base::$federal_council_role_slug ) )
			return;

		$active = ( 'federalcouncil' === $current_tab ) ? 'nav-tab-active' : '';
		?>
		<a class="nav-tab <?php echo $active; ?>" href="<?php echo add_query_arg( 'tab', 'federalcouncil' ); ?>#it-exchange-member-options"><?php _e( 'Federal Council' ); ?></a><?php
	}

	/**
	 * Render the miscellaneous tab.
	 *
	 * @param $current_tab string
	 */
	public function render_membership_tab( $current_tab ) {
		if ( ! user_can( $this->user_id, LDMW_Users_Base::$member_role_slug ) )
			return;

		$active = ( 'membership' === $current_tab ) ? 'nav-tab-active' : '';
		?>
		<a class="nav-tab <?php echo $active; ?>" href="<?php echo add_query_arg( 'tab', 'membership' ); ?>#it-exchange-member-options"><?php _e( 'Membership' ); ?></a><?php
	}

	/**
	 * Render the miscellaneous tab.
	 *
	 * @param $current_tab string
	 */
	public function render_crm_tab( $current_tab ) {
		$active = ( 'crm' === $current_tab ) ? 'nav-tab-active' : '';
		?>
		<a class="nav-tab <?php echo $active; ?>" href="<?php echo add_query_arg( 'tab', 'crm' ); ?>#it-exchange-member-options"><?php _e( 'CRM' ); ?></a><?php
	}

	/**
	 * Dispatch the request and render.
	 *
	 * @param $current_tab string
	 */
	public function dispatch( $current_tab ) {
		$class_name = "LDMW_Users_{$current_tab}_View";

		if ( ! class_exists( $class_name ) )
			return;

		/**
		 * @var $class LDMW_Users_View
		 */
		$class = new $class_name;
		$class->render();
	}

}