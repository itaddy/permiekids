<?php
/**
 * This file prints the content added to the user-edit.php WordPress page
 *
 * @since 0.4.0
 * @package IT_Exchange
*/
?>
<div id="profile-page" class="wrap">

	<?php
		if ( empty( $_REQUEST['user_id'] ) )
			$user_id = get_current_user_id();
		else
			$user_id = $_REQUEST['user_id'];

		$user_object = get_userdata( $user_id );
	?>

	<?php ITUtility::screen_icon( 'it-exchange' ); ?>

	<h2>
		<?php echo $user_object->display_name; ?>
		<a href="<?php echo esc_url( add_query_arg( 'wp_http_referer', urlencode( stripslashes_deep( $_SERVER['REQUEST_URI'] ) ), get_edit_user_link( $user_object->ID ) ) ); ?>" class="edit-user add-new-h2"><?php echo esc_html_x( 'Edit User', 'it-l10n-ithemes-exchange' ); ?></a>
	</h2>
	<?php
	// Show update messages
	if ( $notices = it_exchange_get_messages( 'notice' ) ) {
		foreach( $notices as $notice ) {
			ITUtility::show_status_message( $notice );
		}
		it_exchange_clear_messages( 'notice' );
	}
	// Show errror messages
	if ( $errors = it_exchange_get_messages( 'error' ) ) {
		foreach( $errors as $error ) {
			ITUtility::show_error_message( $error );
		}
		it_exchange_clear_messages( 'error' );
	}

	// Print tabs
	$this->print_user_edit_page_tabs();
	do_action( 'it_exchange_user_edit_page_top' );

	$tab = ! empty( $_REQUEST['tab'] ) ? $_REQUEST['tab'] : 'products';
	$user_id = empty( $user_id ) ? get_current_user_id() : $user_id;

	switch ( $tab ) {
		case 'transactions':
			include( 'admin-user-transactions.php' );
			break;
		case 'info':
			include( 'admin-user-info.php' );
			break;
		case 'products':
			include( 'admin-user-products.php' );
			break;
		default :
			do_action( 'it_exchange_print_user_edit_page_content', $tab );
			break;
	}

	?>

</div>
