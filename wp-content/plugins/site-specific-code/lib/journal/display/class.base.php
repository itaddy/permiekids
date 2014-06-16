<?php

/**
 *
 * @package site-specific-code
 * @subpackage
 * @since
 */
class LDMW_Journal_Display_Base {
	/**
	 *
	 */
	public function __construct() {
		$this->handle_file_download_request();
		add_action( 'pre_get_posts', array( $this, 'remove_unneeded_posts_from_volume_archive' ) );
	}

	/**
	 * Get the URL to the place where the user can download the PDF file
	 *
	 * @param $id int ID of the article
	 *
	 * @return string
	 */
	public static function get_pdf_url_for_article( $id ) {
		$meta = get_post_meta( $id, 'article', true );
		if ( empty( $meta ) )
			return null;

		$pdf_id = $meta['pdf'];

		if ( empty( $pdf_id ) )
			return null;

		$user_id = get_current_user_id();

		$nonce = wp_create_nonce( "aas_download_file_{$pdf_id}_{$user_id}_{$id}" );

		return home_url( "?aas_download_file=$pdf_id&_nonce=$nonce&article=$id" );
	}

	/**
	 * Handle the download file request.
	 */
	public function handle_file_download_request() {
		if ( ! isset( $_GET['aas_download_file'] ) || ! isset( $_GET['_nonce'] ) || ! isset( $_GET['article'] ) )
			return;

		if ( ! wp_verify_nonce( $_GET['_nonce'], 'aas_download_file_' . $_GET['aas_download_file'] . '_' . get_current_user_id() . "_" . $_GET['article'] ) )
			$this->invalid_file( $_GET['article'] );

		$article = $_GET['article'];

		global $post;

		$post = get_post( $article );

		if ( it_exchange_membership_addon_is_content_restricted() )
			$this->invalid_file( $article );

		$id = $_GET['aas_download_file'];

		$meta = get_post_meta( $article, 'article', true );

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

		wp_redirect( get_permalink( $article ) );
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

	/**
	 * Remove uneeded posts from the volume archive.
	 *
	 * Do this by setting number of posts to 0
	 *
	 * @param $wp_query WP_Query
	 */
	public function remove_unneeded_posts_from_volume_archive( $wp_query ) {
		if ( ! $wp_query->is_main_query() )
			return;

		if ( ! isset( $wp_query->query['journal'] ) || strpos( $wp_query->query['journal'], '/' ) !== false )
			return;

		$wp_query->query_vars['numberposts'] = 0;
	}
}