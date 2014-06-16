<?php
/**
 *
 * @package Notify
 * @subpackage Email/Template
 * @since 0.4
 */

if ( ! class_exists( 'IBD_Notify_Email_Abstract' ) ) :

	/**
	 * Class IBD_Notify_Email_Abstract
	 */
	abstract class IBD_Notify_Email_Abstract implements Serializable {
		/**
		 * @var array of $variable_to_replace => $text_to_replace_it_with
		 */
		protected $variables = array();
		/**
		 * @var string the template
		 */
		protected $template;

		/**
		 * @var string the text after it has been translated
		 */
		protected $translated_text;

		/**
		 * @var string the location of the template file.
		 */
		protected static $template_file;

		/**
		 * @param array $variables
		 *
		 * @throws InvalidArgumentException
		 */
		public function __construct( array $variables = array() ) {
			$this->variables = $variables;
			static::$template_file = $this->get_template_path();
			$this->template = $this->get_template();
		}

		/**
		 * Replace all the variables in the template with the supplied replacements
		 *
		 * @return void
		 */
		protected function translate_variables() {
			$translated_text = $this->template;
			foreach ( $this->variables as $variable => $replacement ) {
				$translated_text = str_replace( '{{' . $variable . '}}', $replacement, $translated_text );
			}

			$this->translated_text = $translated_text;
		}

		/**
		 * Get the translated version of the text
		 *
		 * @return string
		 */
		public function get_html() {
			$this->translate_variables();

			return $this->translated_text;
		}

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

		/**
		 * Get the php template.
		 *
		 * @return string
		 */
		protected function get_template() {
			ob_start();
			include_once static::$template_file;
			$template = ob_get_contents();
			ob_end_clean();

			return $template;
		}

		/**
		 * Serialize this object. Remove the translated text.
		 *
		 * @return string
		 */
		public function serialize() {
			$vars = get_object_vars( $this );
			unset( $vars['translated_text'] );

			return serialize( $vars );
		}

		/**
		 * Unserialize this object. Add back the translated text.
		 *
		 * @param string $serialized
		 */
		public function unserialize( $serialized ) {
			$unserialized = unserialize( $serialized );
			$this->variables = $unserialized['variables'];

			static::$template_file = $this->get_template_path();
			$this->template = $this->get_template();
			$this->translate_variables();
		}
	}

endif;