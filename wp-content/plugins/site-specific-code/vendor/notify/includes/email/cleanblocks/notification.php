<?php
/**
 *
 * @package Notify
 * @subpackage Email/Template
 * @since 0.4
 */

if ( ! class_exists( 'IBD_Notify_Email_CleanBlocks_Notification' ) ) :

	/**
	 * Class IBD_Notify_Email_CleanBlocks_Notification
	 *
	 * Variables for this template:
	 *
	 * email_title      => the <title> of the email
	 * header_image     => url to the header image
	 * main_title       => the main title of the email under the header image
	 * main_content     => the main content of the email under the header image, should be the $message
	 * left_image       => url to the left image column
	 * left_content     => the left column content
	 * right_image      => url to the left image column
	 * right_content    => the left column content
	 * footer_content   => the footer content
	 */
	class IBD_Notify_Email_CleanBlocks_Notification extends IBD_Notify_Email_Abstract {
		/**
		 * @var string override the template file
		 */
		static $template_file;

		/**
		 * Get the path to the template.
		 *
		 * @return string
		 *
		 * @throws InvalidArgumentException
		 */
		protected function get_template_path() {
			$dir = dirname( __FILE__ ) . "/";

			return $dir . "template.php";
		}
	}

endif;