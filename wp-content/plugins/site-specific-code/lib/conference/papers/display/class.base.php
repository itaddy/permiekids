<?php

/**
 *
 * @package Conferences
 * @subpackage Papers/Display
 * @since 5/29
 */
class LDMW_Conference_Papers_Display_Base {
	/**
	 *
	 */
	public function __construct() {
		$this->handle_file_download_request();
		add_filter( 'template_include', array( $this, 'fix_template' ) );
	}

	/**
	 * @param $template string
	 *
	 * @return string
	 */
	public function fix_template( $template ) {
		global $wp_query;

		if ( !$wp_query->is_main_query() )
			return $template;

		if ( $wp_query->get( 'post_type' ) != LDMW_Conference_Papers_Admin_CPT::$slug )
			return $template;

		if ( $wp_query->get( LDMW_Conference_Papers_Admin_CPT::$slug ) != '' )
			return $template;

		$template = get_archive_template();

		return $template;
	}

	/**
	 * Get the URL to the place where the user can download the PDF file
	 *
	 * @param $id int ID of the article
	 *
	 * @return string
	 */
	public static function get_pdf_url_for_paper( $id ) {
		$meta = get_post_meta( $id, 'cpaper', true );
		if ( empty( $meta ) )
			return null;

		$pdf_id = $meta['pdf'];

		if ( empty( $pdf_id ) )
			return null;

		$user_id = get_current_user_id();

		$nonce = wp_create_nonce( "aas_download_paper_{$pdf_id}_{$user_id}_{$id}" );

		return home_url( "?aas_download_paper=$pdf_id&_nonce=$nonce&article=$id" );
	}

	/**
	 * Handle the download file request.
	 */
	public function handle_file_download_request() {
		if ( !isset( $_GET['aas_download_paper'] ) || !isset( $_GET['_nonce'] ) || !isset( $_GET['article'] ) )
			return;

		if ( !wp_verify_nonce( $_GET['_nonce'], 'aas_download_paper_' . $_GET['aas_download_paper'] . '_' . get_current_user_id() . "_" . $_GET['article'] ) )
			$this->invalid_file( $_GET['article'] );

		$article = $_GET['article'];

		global $post;

		$post = get_post( $article );

		if ( it_exchange_membership_addon_is_content_restricted() )
			$this->invalid_file( $article );

		$id = $_GET['aas_download_paper'];

		$meta = get_post_meta( $article, 'cpaper', true );

		if ( $meta['pdf'] != $id )
			$this->invalid_file( $article );

		$file = get_attached_file( $id );

		$this->serve_file( $file );
	}

	/**
	 * Serve invalid file page
	 *
	 * @param $article int
	 */
	protected function invalid_file( $article ) {
		$notification = new LDMW_Notifications_Flatco_Notification( get_current_user_id(), 'AAS', 'Invalid Download Link' );
		$notification->save();

		$redirect = get_permalink( $article );

		if ( !$redirect )
			$redirect = home_url();

		wp_redirect( $redirect );
		exit;
	}

	/**
	 * Serve the PDF
	 *
	 * @param $file string
	 */
	protected function serve_file( $file ) {
		$filename = basename( $file ); /* Note: Always use .pdf at the end. */

		header( 'Content-type: application/pdf' );
		header( 'Content-Disposition: inline; filename="' . $filename . '"' );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Content-Length: ' . filesize( $file ) );
		header( 'Accept-Ranges: bytes' );

		@readfile( $file );
	}
}