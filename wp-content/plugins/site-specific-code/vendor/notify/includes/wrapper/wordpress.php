<?php
/**
 *
 * @package Notify
 * @subpackage Wrappers
 * @since 0.1
 */

if ( ! class_exists( 'IBD_Notify_Wrapper_WordPress' ) ) :

	/**
	 * Class IBD_Notify_Wrapper_WordPress
	 */
	class IBD_Notify_Wrapper_WordPress {
		/**
		 * Constructor. Hook at init to process all the notifications
		 */
		public function __construct() {
			if ( did_action( 'init' ) )
				$this->process_notifications();
			else
				add_action( 'init', array( $this, 'process_notifications' ) );
		}

		/**
		 * Send all the notifications in the queue
		 */
		public function process_notifications() {
			if ( get_option( 'ibd_notify_doing_notifications', false ) == true )
				return;

			$notifications = IBD_Notify_Util::get_all_notifications();
			$retriever = new IBD_Notify_Retriever();

			update_option( 'ibd_notify_doing_notifications', true );

			foreach ( $notifications as $user_id => $user_notifications ) {

				if ( ! is_array( $user_notifications ) )
					continue;

				foreach ( $user_notifications as $notification ) {
					try {
						$notification = $retriever->retrieve( $user_id, $notification['id'] );
						$notification->send();
					}
					catch ( InvalidArgumentException $e ) {
						continue;
					}
				}
			}

			update_option( 'ibd_notify_doing_notifications', false );
		}
	}

endif;