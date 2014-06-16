<?php
/**
 *
 * @package Notify
 * @subpackage Email
 * @since 0.3
 */

if ( ! class_exists( 'IBD_Notify_Email_Notification' ) ) :
	/**
	 * Class IBD_Notify_Email_Notification
	 */
	class IBD_Notify_Email_Notification extends IBD_Notify_Notification {
		/**
		 * @var string email address this notification should be sent for
		 */
		private $email;

		/**
		 * @var string|null the name this email should be addressed to
		 */
		private $to_name = null;

		/**
		 * @var array any additional headers to be sent
		 */
		private $headers = array( "Content-type" => "text/html" );

		/**
		 * @var IBD_Notify_Email_Abstract|null the template object
		 */
		private $template = null;

		/**
		 * @var array Attachments to send with the email.
		 */
		private $attachments = array();

		/**
		 * Should setup all the properties, but should not save(),
		 * the client must call the save() method. Do not assume that all
		 * arguments are specified in the args array. Check if they are set
		 * using isset and provide defaults for all values. Also do not assume
		 * that the id value is populated, use uniqid() to generate it if it
		 * does not exist.
		 *
		 * @param $user_id int
		 * @param $title string
		 * @param $message string
		 * @param $args array {
		 *
		 * @type string $email email
		 * @type string $to_name to name
		 * @type array $headers headers @see IBD_Notify_Email_Notification::headers
		 * @type IBD_Notify_Email_Abstract $template template object
		 * }
		 *
		 * @throws InvalidArgumentException
		 */
		public function __construct( $user_id, $title, $message, $args = array() ) {
			$this->user_id = $user_id;
			$this->title = $title;
			$this->message = $message;
			$user = get_user_by( 'id', $user_id );

			if ( isset( $args['email'] ) )
				$this->email = $args['email'];
			else
				$this->email = $user->user_email;

			if ( isset( $args['to_name'] ) )
				$this->to_name = $args['to_name'];

			if ( isset( $args['headers'] ) && is_array( $args['headers'] ) )
				$this->headers = array_merge( $this->headers, $args['headers'] );

			if ( isset( $args['template'] ) )
				$this->template = $args['template'];

			if ( isset( $args['attachments'] ) && is_array( $args['attachments'] ) )
				$this->attachments = $args['attachments'];

			if ( isset( $args['id'] ) )
				$this->id = $args['id'];
			else
				$this->id = uniqid();
		}

		/**
		 * Set a header value.
		 *
		 * @param $header string the header field
		 * @param $value string the header value
		 */
		public function set_header( $header, $value ) {
			$this->headers[$header] = $value;
		}

		/**
		 * Get a header value.
		 *
		 * @param $header string the header field
		 *
		 * @return string|null
		 */
		public function get_header( $header ) {
			return isset( $this->headers[$header] ) ? $this->headers[$header] : null;
		}

		/**
		 * Add an attachment to the email.
		 *
		 * @param $file string
		 */
		public function add_attachment( $file ) {
			$attachments = $this->attachments;
			$attachments[] = $file;
			$this->attachments = $attachments;
		}

		/**
		 * Take all of the headers and convert them to the proper format for wp_mail()
		 *
		 * @return string
		 */
		protected function get_headers_for_email() {
			$headers = "";

			foreach ( $this->headers as $header => $value ) {
				$headers .= $header . ": " . $value . "\n";
			}

			return $headers;
		}

		/**
		 * Make the to field for the email.
		 *
		 * @return string
		 */
		private function get_to_email() {
			if ( isset( $this->to_name ) ) {
				return $this->to_name . "<" . $this->email . ">";
			}
			else {
				return $this->email;

			}
		}

		/**
		 * Get the message content.
		 *
		 * @return string
		 */
		private function get_message() {
			if ( $this->template === null ) {
				return $this->message;
			}
			else {
				return $this->template->get_html();
			}
		}

		/**
		 * Send this notification using whatever method specified by extended class.
		 * Examples: growl, email, admin notification etc..
		 *
		 * In this case we implement it by adding an admin_notices action.
		 *
		 * @uses wp_mail()
		 *
		 * @return boolean
		 */
		public function send() {
			$response = wp_mail( $this->get_to_email(), $this->title, $this->get_message(), $this->get_headers_for_email(), $this->attachments );
			$this->notify( (bool) true );

			return $response;
		}

		/**
		 * Response when the message is processed
		 *
		 * @param boolean $complete
		 * @param array $data
		 *
		 * @return boolean
		 */
		public function notify( $complete = true, $data = array() ) {
			if ( $complete === true )
				$this->delete();

			return $complete;
		}

		/**
		 * Sets up the data to be saved to the database
		 *
		 * @return void
		 */
		protected function setup_notification_data() {
			$this->notification_data = array(
			  'id'       => $this->id,
			  'user_id'  => $this->user_id,
			  'title'    => $this->title,
			  'message'  => $this->message,
			  'email'    => $this->email,
			  'to_name'  => $this->to_name,
			  'headers'  => $this->headers,
			  'template' => $this->template,
			  'handler'  => __CLASS__
			);
		}
	}

endif;