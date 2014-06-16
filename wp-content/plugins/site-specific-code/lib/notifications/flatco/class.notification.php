<?php
/**
 *
 * @package LDMW
 * @subpackage Notifications
 * @since 1.0
 */

if ( ! class_exists( 'LDMW_Notifications_Flatco_Notification' ) ) :

	/**
	 * Class LDMW_Notifications_Flatco_Notification
	 */
	class LDMW_Notifications_Flatco_Notification extends IBD_Notify_Admin_Notification {
		/**
		 * Info is blue with "i" icon.
		 *
		 * Warning is orange alert with triangle warning sign icon.
		 *
		 * Success is green with checkmark icon.
		 *
		 * @var string info|success
		 */
		private $class = "info";

		/**
		 * Function to be called to check if we can send the notification
		 *
		 * @var callable
		 */
		private $can_send_function = '__return_true';

		/**
		 * Should setup all the properties, but should not save(),
		 * the client must call the save() method. Do not assume that all
		 * arguments are specified in the args array. Check if they are set
		 * using isset and provide defaults for all values. Also do not assume
		 * that the id value is populated, use uniqid() to generate it if it
		 * does not exist.
		 *
		 * @param int $user_id
		 * @param string $title
		 * @param string $message
		 * @param array $args
		 */
		public function __construct( $user_id, $title, $message, $args = array() ) {
			if ( isset( $args['class'] ) && in_array( $args['class'], array( 'info', 'success', 'warning' ) ) )
				$this->class = $args['class'];

			if ( isset( $args['can_send_function'] ) && is_callable( $args['can_send_function'] ) )
				$this->can_send_function = $args['can_send_function'];

			parent::__construct( $user_id, $title, $message, $args );
		}

		/**
		 * Send this notification by attaching it to an action,
		 * that is located in the theme.
		 *
		 * @return bool
		 */
		public function send() {
			if ( get_current_user_id() == $this->user_id && call_user_func( $this->can_send_function, $this ) ) {
				add_action( 'ibd_notify_flatco_notification', array( $this, 'display' ) );

				return true;
			}

			return false;
		}

		/**
		 * Display the notification.
		 */
		public function display() {
			$this->notify( true );
			switch ( $this->class ) {
				case 'info' :
					$icon_class = "info";
					break;
				case 'success' :
					$icon_class = "ok";
					break;
				case 'warning' :
				default :
					$icon_class = "warning";
			};
			?>
			<div class="alert <?php if ( $this->class != 'warning' ) echo "alert-" . $this->class; ?>">
				<button type="button" class="close" data-dismiss="alert">×</button>
				<strong><?php echo $this->title; ?>:</strong> <?php echo $this->message; ?>
				<i class="icon-<?php echo $icon_class; ?>-sign"></i>
			</div>
			<?php

			do_action( 'ldmw_flatco_notification_sent_' . $this->id, $this );
		}

		/**
		 * Set up our data to be prepped to send.
		 */
		protected function setup_notification_data() {
			$this->notification_data = array(
			  'id'                => $this->id,
			  'user_id'           => $this->user_id,
			  'title'             => $this->title,
			  'message'           => $this->message,
			  'class'             => $this->class,
			  'handler'           => get_called_class(),
			  'can_send_function' => $this->can_send_function
			);
		}

	}

endif;