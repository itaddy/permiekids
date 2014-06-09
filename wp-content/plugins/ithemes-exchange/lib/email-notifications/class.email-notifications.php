<?php
/**
 * Contains the class or the email notifications object
 * @since 0.4.0
 * @package IT_Exchange
*/

/**
 * The IT_Exchange_Email_Notifications class is for sending out email notification using wp_mail()
 *
 * @since 0.4.0
*/
class IT_Exchange_Email_Notifications {

	public $transaction_id;
	public $customer_id;
	public $user;

	/**
	 * Constructor. Sets up the class
	 *
	 * @since 0.4.0
	*/
	function IT_Exchange_Email_Notifications() {
		add_action( 'it_exchange_send_email_notification', array( $this, 'it_exchange_send_email_notification' ), 20, 3 );

		// Send emails on successfull transaction
		add_action( 'it_exchange_add_transaction_success', array( $this, 'send_purchase_emails' ), 20 );

		// Send emails when admin requests a resend
		add_action( 'admin_init', array( $this, 'handle_resend_confirmation_email_requests' ) );

		// Resends email notifications when status is changed from one that's not cleared for delivery to one that is cleared
		add_action( 'it_exchange_update_transaction_status', array( $this, 'resend_if_transaction_status_gets_cleared_for_delivery' ), 10, 3 );

		add_shortcode( 'it_exchange_email', array( $this, 'ithemes_exchange_email_notification_shortcode' ) );

	}

	function it_exchange_send_email_notification( $customer_id, $subject, $content ) {

		$this->transaction_id = apply_filters( 'it_exchange_send_email_notification_transaction_id', false );
		$this->customer_id    = $customer_id;
		$this->user           = it_exchange_get_customer( $customer_id );

		$settings = it_exchange_get_option( 'settings_email' );

		// Edge case where sale is made before admin visits email settings.
		if ( empty( $settings['receipt-email-name'] ) && ! isset( $IT_Exchange_Admin ) ) {
			global $IT_Exchange;
			include_once( dirname( dirname( __FILE__ ) ) . '/admin/class.admin.php' );
			add_filter( 'it_storage_get_defaults_exchange_settings_email', array( 'IT_Exchange_Admin', 'set_email_settings_defaults' ) );
			$settings = it_exchange_get_option( 'settings_email', true );
		}

		$headers[] = 'From: ' . $settings['receipt-email-name'] . ' <' . $settings['receipt-email-address'] . '>';
		$headers[] = 'MIME-Version: 1.0';
		$headers[] = 'Content-Type: text/html';
		$headers[] = 'charset=utf-8';

		$subject = do_shortcode( $subject );
		$body    = apply_filters( 'it_exchange_send_email_notification_body', $content );
		$body    = $this->body_header() . '<div>' . wpautop( do_shortcode( $body ) ) . '</div>' . $this->body_footer();

		$headers = apply_filters( 'it_exchange_send_email_notification_headers', $headers );

		wp_mail( $this->user->data->user_email, strip_tags( $subject ), $body, $headers );

	}

	/**
	 * Listens for the resend email request and passes along to send_purchase_emails
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function handle_resend_confirmation_email_requests() {
		// Abort if not requested
		if ( empty( $_GET[ 'it-exchange-customer-transaction-action' ] ) || $_GET[ 'it-exchange-customer-transaction-action' ] != 'resend' )
			return;

		// Abort if no transaction or invalid transaction was passed
		$transaction = it_exchange_get_transaction( $_GET['id'] );
		if ( empty( $transaction->ID ) ) {
			it_exchange_add_message( 'error', __( 'Invalid transaction. Confirmation email not sent.', 'it-l10n-ithemes-exchange' ) );
			$url = remove_query_arg( array( 'it-exchange-customer-transaction-action', '_wpnonce' ) );
			it_exchange_redirect( $url, 'admin-confirmation-email-resend-failed' );
			die();
		}

		// Abort if nonce is bad
		$nonce = empty( $_GET['_wpnonce'] ) ? false : $_GET['_wpnonce'];
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'it-exchange-resend-confirmation-' . $transaction->ID ) ) {
			it_exchange_add_message( 'error', __( 'Confirmation Email not sent. Please try again.', 'it-l10n-ithemes-exchange' ) );
			$url = remove_query_arg( array( 'it-exchange-customer-transaction-action', '_wpnonce' ) );
			it_exchange_redirect( $url, 'admin-confirmation-email-resend-failed' );
			die();
		}

		// Abort if user doesn't have permission
		if ( ! current_user_can( 'administrator' ) ) {
			it_exchange_add_message( 'error', __( 'You do not have permission to resend confirmation emails.', 'it-l10n-ithemes-exchange' ) );
			$url = remove_query_arg( array( 'it-exchange-customer-transaction-action', '_wpnonce' ) );
			it_exchange_redirect( $url, 'admin-confirmation-email-resend-failed' );
			die();
		}

		// Resend w/o admin notification
		$this->send_purchase_emails( $transaction, false );
		it_exchange_add_message( 'notice', __( 'Confirmation email resent', 'it-l10n-ithemes-exchange' ) );
		$url = remove_query_arg( array( 'it-exchange-customer-transaction-action', '_wpnonce' ) );
		it_exchange_redirect( $url, 'admin-confirmation-email-resend-success' );
		die();
	}

	/**
	 * Process the transaction and send appropriate emails
	 *
	 * @since 0.4.0
	 *
	 * @param mixed $transaction ID or object
	 * @param int $customer_id The customer ID
	 * @return void
	*/
	function send_purchase_emails( $transaction, $send_admin_email=true ) {

		$transaction = it_exchange_get_transaction( $transaction );
		if ( empty( $transaction->ID ) )
			return;

		$this->transaction_id = $transaction->ID;
		$this->customer_id    = it_exchange_get_transaction_customer_id( $this->transaction_id );
		$this->user           = it_exchange_get_customer( $this->customer_id );

		$settings = it_exchange_get_option( 'settings_email' );

		// Edge case where sale is made before admin visits email settings.
		if ( empty( $settings['receipt-email-name'] ) && ! isset( $IT_Exchange_Admin ) ) {
			global $IT_Exchange;
			include_once( dirname( dirname( __FILE__ ) ) . '/admin/class.admin.php' );
			add_filter( 'it_storage_get_defaults_exchange_settings_email', array( 'IT_Exchange_Admin', 'set_email_settings_defaults' ) );
			$settings = it_exchange_get_option( 'settings_email', true );
		}

		// Sets Temporary GLOBAL information
		$GLOBALS['it_exchange']['email-confirmation-data'] = array( $transaction, $this );

		$headers[] = 'From: ' . $settings['receipt-email-name'] . ' <' . $settings['receipt-email-address'] . '>';
		$headers[] = 'MIME-Version: 1.0';
		$headers[] = 'Content-Type: text/html';
		$headers[] = 'charset=utf-8';

		$subject = do_shortcode( $settings['receipt-email-subject'] );
		$body    = apply_filters( 'send_purchase_emails_body', $settings['receipt-email-template'], $transaction );
		$body    = apply_filters( 'send_purchase_emails_body_' . it_exchange_get_transaction_method( $transaction->ID ), $body, $transaction );
		$body    = $this->body_header() . '<div>' . wpautop( do_shortcode( $body ) ) . '</div>' . $this->body_footer();

		// Filters
		$to           = empty( $this->user->data->user_email ) ? false : $this->user->data->user_email;
		$to           = apply_filters( 'it_exchange_send_purchase_emails_to', $to, $transaction, $settings, $this );
		$subject      = apply_filters( 'it_exchange_send_purchase_emails_subject', $subject, $transaction, $settings, $this );
		$body         = apply_filters( 'it_exchange_send_purchase_emails_body', $body, $transaction, $settings, $this );
		$headers      = apply_filters( 'it_exchange_send_purchase_emails_headers', $headers, $transaction, $settings, $this );
		$attachments  = apply_filters( 'it_exchange_send_purchase_emails_attachments', array(), $transaction, $settings, $this );

		wp_mail( $to, strip_tags( $subject ), $body, $headers, $attachments );

		// Send admin notification if param is true and email is provided in settings
		if ( $send_admin_email && ! empty( $settings['notification-email-address'] ) ) {

			$subject = do_shortcode( $settings['admin-email-subject'] );
			$body    = apply_filters( 'send_admin_emails_body', $settings['admin-email-template'], $transaction );
			$body    = apply_filters( 'send_admin_emails_body_' . it_exchange_get_transaction_method( $transaction->ID ), $body, $transaction );
			$body    = $this->body_header() . wpautop( do_shortcode( $body ) ) . $this->body_footer();

			$emails = explode( ',', $settings['notification-email-address'] );

			foreach ( $emails as $email ) {
				wp_mail( trim( $email ), strip_tags( $subject ), $body, $headers );
			}

		}

		// Clear temp data
		if ( isset( $GLOBALS['it_exchange']['email-confirmation-data'] ) )
			unset( $GLOBALS['it_exchange']['email-confirmation-data'] );

	}

	/**
	 * Returns Email HTML header
	 *
	 * @since 0.4.0
	 *
	 * @return string HTML header
	*/
	function body_header() {
		$data = empty( $GLOBALS['it_exchange']['email-confirmation-data'] ) ? false : $GLOBALS['it_exchange']['email-confirmation-data'];
		ob_start();
		?>
			<html>
			<head>
				<meta http-equiv="Content-type" content="text/html; charset=utf-8">
			</head>
			<body>
		<?php

		$output = ob_get_clean();

		return apply_filters( 'it_exchange_email_notification_body_header', $output, $data );

	}

	/**
	 * Returns Email HTML footer
	 *
	 * @since 0.4.0
	 *
	 * @return string HTML footer
	*/
	function body_footer() {
		$data = empty( $GLOBALS['it_exchange']['email-confirmation-data'] ) ? false : $GLOBALS['it_exchange']['email-confirmation-data'];
		ob_start();
		?>
			</body>
		</html>
		<?php

		$output = ob_get_clean();

		return apply_filters( 'it_exchange_email_notification_body_footer', $output, $data );

	}

	/**
	 * Get available template tags
	 * Array of tags (key) and callback functions (value)
	 *
	 * @since 0.4.0
	 *
	 * @return array available replacement template tags
	*/
	function get_shortcode_functions() {

		$data = empty( $GLOBALS['it_exchange']['email-confirmation-data'] ) ? false : $GLOBALS['it_exchange']['email-confirmation-data'];
		//Key = replacement tag
		//Value = callback function
		$shortcode_functions = array(
			'download_list'  => 'it_exchange_replace_download_list_tag',
			'name'           => 'it_exchange_replace_name_tag',
			'fullname'       => 'it_exchange_replace_fullname_tag',
			'username'       => 'it_exchange_replace_username_tag',
			'order_table'    => 'it_exchange_replace_order_table_tag',
			'purchase_date'  => 'it_exchange_replace_purchase_date_tag',
			'total'          => 'it_exchange_replace_total_tag',
			'payment_id'     => 'it_exchange_replace_payment_id_tag',
			'receipt_id'     => 'it_exchange_replace_receipt_id_tag',
			'payment_method' => 'it_exchange_replace_payment_method_tag',
			'sitename'       => 'it_exchange_replace_sitename_tag',
			'receipt_link'   => 'it_exchange_replace_receipt_link_tag',
			'login_link'     => 'it_exchange_replace_login_link_tag',
			'account_link'   => 'it_exchange_replace_account_link_tag',
		);

		return apply_filters( 'it_exchange_email_notification_shortcode_functions', $shortcode_functions, $data );

	}

	/**
	 * This shortcode is intended to print an email arguments for email templates
	 *
	 * @since 0.4.0
	 * @param array $atts attributess passed from WP Shortcode API
	 * @param string $content data passed from WP Shortcode API
	 * @return string html for the 'Add to Shopping Cart' HTML
	*/
	function ithemes_exchange_email_notification_shortcode( $atts, $content='' ) {
		$data = empty( $GLOBALS['it_exchange']['email-confirmation-data'] ) ? false : $GLOBALS['it_exchange']['email-confirmation-data'];
		$supported_pairs = array(
			'show'    => '',
			'options' => '',
		);
		// Merge supported_pairs with passed attributes
		extract( shortcode_atts( $supported_pairs, $atts ) );

		$shortcode_functions = $this->get_shortcode_functions();

		$return = false;

		if ( !empty( $shortcode_functions[$show] ) ) {
			if ( is_callable( array( $this, $shortcode_functions[$show] ) ) )
				$return = call_user_func( array( $this, $shortcode_functions[$show] ), $this, explode( ',', $options ) );
			else if ( is_callable( $shortcode_functions[$show] ) )
				$return = call_user_func( $shortcode_functions[$show], $this, explode( ',', $options ) );
		}

		return apply_filters( 'it_exchange_email_notification_shortcode_' . $atts['show'], $return, $atts, $content, $data );
	}

	/**
	 * Replacement Tag
	 *
	 * @since 0.4.0
	 *
	 * @param object $args of IT_Exchange_Email_Notifications
	 * @return string Replaced value
	*/
	function it_exchange_replace_download_list_tag( $args, $options = NULL ) {
		$status_notice = '';
		ob_start();
		// Grab products attached to transaction
		$transaction_products = it_exchange_get_transaction_products( $args->transaction_id );

		// Grab all hashes attached to transaction
		$hashes   = it_exchange_get_transaction_download_hash_index( $args->transaction_id );
		if ( !empty( $hashes ) ) {
		?>
			<div style="border-top: 1px solid #EEE">
				<?php foreach ( $transaction_products as $transaction_product ) : ?>
					<?php
						$product_id = $transaction_product['product_id'];
						$db_product = it_exchange_get_product( $transaction_product );
					?>
					<?php if ( $product_downloads = it_exchange_get_product_feature( $transaction_product['product_id'], 'downloads' ) ) : $downloads_exist_for_transaction = true; ?>
						<?php if ( ! it_exchange_transaction_is_cleared_for_delivery( $args->transaction_id ) ) : ?>
							<?php
							/* Status notice is blank by default and printed here, in the email if downloads are available.
							 * If downloads are not available for this transaction (tested in loop below), this echo of the status notice won't be printed.
							 * But we know that downloads will be available if the status changes so we set print the message instead of the files.
							 * If no files exist for the transaction, then there is no need to print this message even if status is pending
							 * Clear as mud.
							*/
							$status_notice = '<p>' . __( 'The status for this transaction does not grant access to downloadable files. Once the transaction is updated to an approved status, you will receive a follow-up email with your download links.', 'it-l10n-ithemes-exchange' ) . '</p>';
							$status_notice = '<h3>' . __( 'Available Downloads', 'it-l10n-ithemes-exchange' ) . '</h3>' . $status_notice;
							?>
						<?php else : ?>
							<h4><?php esc_attr_e( it_exchange_get_transaction_product_feature( $transaction_product, 'title' ) ); ?></h4>
							<?php $count = it_exchange_get_transaction_product_feature( $transaction_product, 'count' ); ?>
							<?php if ( $count > 1 && apply_filters( 'it_exchange_print_downlods_page_link_in_email', true, $this->transaction_id ) ) : ?>
								<?php $downloads_url = it_exchange_get_page_url( 'downloads' ); ?>
								<p><?php printf( __( 'You have purchased %d unique download link(s) for each file available with this product.%s%sEach link has its own download limits and you can view the details on your %sdownloads%s page.', 'it-l10n-ithemes-exchange' ), $count, '<br />', '<br />', '<a href="' . $downloads_url . '">', '</a>' ); ?></p>
							<?php endif; ?>
							<?php foreach( $product_downloads as $download_id => $download_data ) : ?>
								<?php $hashes_for_product_transaction = it_exchange_get_download_hashes_for_transaction_product( $args->transaction_id, $transaction_product, $download_id ); ?>
								<?php $hashes_found = ( ! empty( $hashes_found ) || ! empty( $hashes_for_product_transaction ) ); // If someone purchases a product prior to downloads existing, they dont' get hashes/downloads ?>
								<h5><?php esc_attr_e( get_the_title( $download_id ) ); ?></h5>
								<ul class="download-hashes">
									<?php foreach( (array) $hashes_for_product_transaction as $hash ) : ?>
										<?php
										$hash_data      = it_exchange_get_download_data_from_hash( $hash );
										$download_limit = ( 'unlimited' == $hash_data['download_limit'] ) ? __( 'Unlimited', 'it-l10n-ithemes-exchange' ) : $hash_data['download_limit'];
										$downloads      = empty( $hash_data['downloads'] ) ? (int) 0 : absint( $hash_data['downloads'] );
										?>
										<li>
											<a href="<?php echo site_url() . '?it-exchange-download=' . $hash; ?>"><?php _e( 'Download link', 'it-l10n-ithemes-exchange' ); ?></a> <span style="font-family: Monaco, monospace;font-size:12px;color:#AAA;">(<?php esc_attr_e( $hash ); ?>)</span>
										</li>
									<?php endforeach; ?>
								</ul>
							<?php endforeach; ?>
						<?php endif; ?>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		<?php
		}

		if ( empty( $downloads_exist_for_transaction ) || empty( $hashes_found ) ) {
			echo $status_notice;
			return ob_get_clean();
		} else {
			return ob_get_clean();
		}
	}

	/**
	 * Replacement Tag
	 *
	 * @since 0.4.0
	 *
	 * @param object $args of IT_Exchange_Email_Notifications
	 * @return string Replaced value
	*/
	function it_exchange_replace_name_tag( $args, $options = NULL ) {
		$name = '';
		if ( !empty( $this->user->data->first_name ) ) {
			$name = $this->user->data->first_name;
		} else if ( ! empty( $this->user->data->display_name ) ) {
			$name = $this->user->data->display_name;
		} else if ( ! empty( $GLOBALS['it_exchange']['email-confirmation-data'][0]->customer_id ) && is_email( $GLOBALS['it_exchange']['email-confirmation-data'][0]->customer_id ) ) {
			// Guest Chekcout
			$name = $GLOBALS['it_exchange']['email-confirmation-data'][0]->customer_id;
		}
		return $name;
	}

	/**
	 * Replacement Tag
	 *
	 * @since 0.4.0
	 *
	 * @param object $args of IT_Exchange_Email_Notifications
	 * @return string Replaced value
	*/
	function it_exchange_replace_fullname_tag( $args, $options = NULL ) {
		$fullname = '';

		if ( ! empty( $this->user->data->first_name ) && ! empty( $this->user->data->last_name ) ) {
			$fullname = $this->user->data->first_name . ' ' . $this->user->data->last_name;
		} else if ( ! empty( $this->user->data->display_name ) ) {
			$fullname = $this->user->data->display_name;
		}

		return $fullname;
	}

	/**
	 * Replacement Tag
	 *
	 * @since 0.4.0
	 *
	 * @param object $args of IT_Exchange_Email_Notifications
	 * @return string Replaced value
	*/
	function it_exchange_replace_username_tag( $args, $options = NULL ) {
		return empty( $this->user->data->user_login ) ? '' : $this->user->data->user_login;
	}

	/**
	 * Replacement Tag
	 *
	 * @since 0.4.0
	 *
	 * @param object $args of IT_Exchange_Email_Notifications
	 * @return string Replaced value
	*/
	function it_exchange_replace_order_table_tag( $args, $options = NULL ) {

		$purchase_messages_heading  = '<h3>' . __( 'Important Information', 'it-l10n-ithemes-exchange' ). '</h3>';
		$purchase_messages          = '';
		$purchase_message_on        = false;

		if ( in_array( 'purchase_message', $options ) )
			$purchase_message_on = true;

		ob_start();
		?>
			<table style="text-align: left; background: #FBFBFB; margin-bottom: 1.5em;border:1px solid #DDD;border-collapse: collapse;">
				<thead style="background:#F3F3F3;">
					<tr>
						<th style="padding: 10px;border:1px solid #DDD;"><?php _e( 'Product', 'it-l10n-ithemes-exchange' ); ?></th>
						<th style="padding: 10px;border:1px solid #DDD;"><?php _e( 'Quantity', 'it-l10n-ithemes-exchange' ); ?></th>
						<th style="padding: 10px;border:1px solid #DDD;"><?php _e( 'Total Price', 'it-l10n-ithemes-exchange' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( $products = it_exchange_get_transaction_products( $this->transaction_id ) ) : ?>
						<?php foreach ( $products as $product ) : ?>
							<tr>
								<td style="padding: 10px;border:1px solid #DDD;">
								<?php echo apply_filters( 'it_exchange_email_notification_order_table_product_name', it_exchange_get_transaction_product_feature( $product, 'product_name' ), $product ); ?>
                                </td>
								<td style="padding: 10px;border:1px solid #DDD;"><?php echo apply_filters( 'it_exchange_email_notification_order_table_product_count', it_exchange_get_transaction_product_feature( $product, 'count' ), $product ); ?></td>
								<td style="padding: 10px;border:1px solid #DDD;"><?php echo apply_filters( 'it_exchange_email_notification_order_table_product_subtotal', it_exchange_format_price( it_exchange_get_transaction_product_feature( $product, 'product_subtotal' ), $product ) ); ?></td>
							</tr>

							<?php
							// Generate Purchase Messages
							if ( $purchase_message_on && it_exchange_product_has_feature( $product['product_id'], 'purchase-message' ) ) {
								$purchase_messages .= '<h4>' . esc_attr( it_exchange_get_transaction_product_feature( $product, 'product_name' ) ) . '</h4>';
								$purchase_messages .= '<p>' . it_exchange_get_product_feature( $product['product_id'], 'purchase-message' ) . '</p>';
								$purchase_messages = apply_filters( 'it_exchange_email_notification_order_table_purchase_message', $purchase_messages, $product );
							}
							?>

						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
				<tfoot style="background:#F3F3F3;">
					<tr>
						<td colspan="2" style="padding: 10px;border:1px solid #DDD;"><?php _e( 'Total', 'it-l10n-ithemes-exchange' ); ?></td>
						<td style="padding: 10px;border:1px solid #DDD;"><?php echo it_exchange_get_transaction_total( $this->transaction_id, true ) ?></td>
					</tr>
				</tfoot>
			</table>
		<?php

		$table = ob_get_clean();
		$table .= empty( $purchase_messages ) ? '' : $purchase_messages_heading . $purchase_messages;

		return $table;
	}

	/**
	 * Replacement Tag
	 *
	 * @since 0.4.0
	 *
	 * @param object $args of IT_Exchange_Email_Notifications
	 * @return string Replaced value
	*/
	function it_exchange_replace_purchase_date_tag( $args, $options = NULL ) {
		return it_exchange_get_transaction_date( $this->transaction_id );
	}

	/**
	 * Replacement Tag
	 *
	 * @since 0.4.0
	 *
	 * @param object $args of IT_Exchange_Email_Notifications
	 * @return string Replaced value
	*/
	function it_exchange_replace_total_tag( $args, $options = NULL ) {
		return it_exchange_get_transaction_total( $this->transaction_id, true );
	}

	/**
	 * Replacement Tag
	 *
	 * @since 0.4.0
	 *
	 * @param object $args of IT_Exchange_Email_Notifications
	 * @return string Replaced value
	*/
	function it_exchange_replace_payment_id_tag( $args, $options = NULL ) {
		return it_exchange_get_gateway_id_for_transaction( $this->transaction_id );
	}

	/**
	 * Replacement Tag
	 *
	 * @since 0.4.0
	 *
	 * @param object $args of IT_Exchange_Email_Notifications
	 * @return string Replaced value
	*/
	function it_exchange_replace_receipt_id_tag( $args, $options = NULL ) {
		return it_exchange_get_transaction_order_number( $this->transaction_id );
	}

	/**
	 * Replacement Tag
	 *
	 * @since 0.4.0
	 *
	 * @param object $args of IT_Exchange_Email_Notifications
	 * @return string Replaced value
	*/
	function it_exchange_replace_payment_method_tag( $args, $options = NULL ) {
		return it_exchange_get_transaction_method( $this->transaction_id );
	}

	/**
	 * Replacement Tag
	 *
	 * @since 0.4.0
	 *
	 * @param object $args of IT_Exchange_Email_Notifications
	 * @return string Replaced value
	*/
	function it_exchange_replace_sitename_tag( $args, $options = NULL ) {
		return wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	}

	/**
	 * Replacement Tag
	 *
	 * @since 0.4.0
	 *
	 * @param object $args of IT_Exchange_Email_Notifications
	 * @return string Replaced value
	*/
	function it_exchange_replace_receipt_link_tag( $args, $options = NULL ) {
		return it_exchange_get_transaction_confirmation_url( $this->transaction_id );
	}

	/**
	 * Replacement Tag
	 *
	 * @since 1.0.2
	 *
	 * @param object $args of IT_Exchange_Email_Notifications
	 * @return string Replaced value
	*/
	function it_exchange_replace_login_link_tag( $args, $options = NULL ) {
		return it_exchange_get_page_url( 'login' );
	}

	/**
	 * Replacement Tag
	 *
	 * @since 1.4.0
	 *
	 * @param object $args of IT_Exchange_Email_Notifications
	 * @return string Replaced value
	*/
	function it_exchange_replace_account_link_tag( $args, $options = NULL ) {
		return it_exchange_get_page_url( 'account' );
	}

	/**
	 * Resends the email to the customer if the transaction status was changed from not cleared for delivery to cleared.
	 *
	 * @since 0.4.11
	 *
	 * @param object $transaction the transaction object
	 * @param string $old_status the status it was just changed from
	 * @param boolean $old_status_cleared was the old status cleared for delivery?
	 * @return void
	*/
	function resend_if_transaction_status_gets_cleared_for_delivery( $transaction, $old_status, $old_status_cleared ) {
		// Using ->ID here so that get_transaction forces a reload and doesn't use the old object with the old status
		$new_status = it_exchange_get_transaction_status( $transaction->ID );
		$new_status_cleared = it_exchange_transaction_is_cleared_for_delivery( $transaction->ID );

		if ( ( $new_status != $old_status ) && ! $old_status_cleared && $new_status_cleared )
			$this->send_purchase_emails( $transaction, false );
	}
}
$IT_Exchange_Email_Notifications = new IT_Exchange_Email_Notifications();
