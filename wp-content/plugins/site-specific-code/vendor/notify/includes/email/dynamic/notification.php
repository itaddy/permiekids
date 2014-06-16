<?php

/**
 *
 * @package Notify
 * @subpackage Email
 * @since 1.0
 */
class IBD_Notify_Email_Dynamic_Notification extends IBD_Notify_Email_Abstract {
	/**
	 * @var string
	 */
	protected $dynamic_template = "";

	/**
	 * @param array $variables
	 * @param string $dynamic_template
	 */
	public function __construct( array $variables = array(), $dynamic_template = "" ) {
		$this->dynamic_template = $dynamic_template;
		parent::__construct( $variables );
	}

	/**
	 * @return string
	 */
	protected function get_template() {
		return $this->dynamic_template;
	}

	/**
	 * Unserialize the object. Add the dynamic template text.
	 *
	 * @param string $serialized
	 */
	public function unserialize( $serialized ) {
		$unserialized = unserialize( $serialized );
		$this->dynamic_template = $unserialized['dynamic_template'];
		parent::unserialize( $serialized );
	}
}