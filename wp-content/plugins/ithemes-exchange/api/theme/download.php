<?php
/**
 * Download class for THEME API
 *
 * @since 0.4.0
*/

class IT_Theme_API_Download implements IT_Theme_API {

	/**
	 * API context
	 * @var string $_context
	 * @since 0.4.0
	*/
	private $_context = 'download';

	/**
	 * Maps api tags to methods
	 * @var array $_tag_map
	 * @since 0.4.0
	*/
	var $_tag_map = array(
		'found'      => 'found',
		'name'       => 'title',
		'title'      => 'title',
		'limit'      => 'limit',
		'expiration' => 'expiration',
	);

	/**
	 * Current download in iThemes Exchange Global
	 * @var object $download
	 * @since 0.4.0
	*/
	private $download;

	/**
	 * Constructor
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function IT_Theme_API_Download() {
		// Set the current global download as a property
		$this->download = empty( $GLOBALS['it_exchange']['download'] ) ? false : $GLOBALS['it_exchange']['download'];
	}

	/**
	 * Returns the context. Also helps to confirm we are an iThemes Exchange theme API class
	 *
	 * @since 0.4.0
	 *
	 * @return string
	*/
	function get_api_context() {
		return $this->_context;
	}

	/**
	 * Returns boolean value if we have a download or not
	 *
	 * @since 0.4.0
	 *
	 * @return boolean
	*/
	function found( $options=array() ) {
		return (boolean) $this->download;
	}

	/**
	 * The download title
	 *
	 * @since 0.4.0
	 * @return string
	*/
	function title( $options=array() ) {

		// Return boolean if has flag was set
		if ( $options['has'] )
			return ! empty( $this->download['name'] );

		if ( ! empty( $this->download['name'] ) ) {

			$result   = '';
			$title    = $this->download['name'];
			$defaults = array(
				'before' => '<h1 class="download-title">',
				'after'  => '</h1>',
				'format' => 'raw',
			);

			$options = ITUtility::merge_defaults( $options, $defaults );

			if ( 'html' == $options['format'] )
				$result .= $options['before'];

			$result .= $title;

			if ( 'html' == $options['format'] )
				$result .= $options['after'];

			return $result;
		}
		return false;
	}

	/**
	 * The download Limit
	 *
	 * @since 0.4.0
	 * @return string
	*/
	function limit( $options=array() ) {

		// Return boolean if has flag was set
		if ( $options['has'] )
			return ! empty( $this->download['limit'] );

		$result   = '';
		$limit    = $this->download['download_limit'];

		$defaults = array(
			'before' => '<span class="download-limit">',
			'after'  => '</span>',
			'format' => 'raw',
			'unlimited-label' => __( 'Unlimited', 'it-l10n-ithemes-exchange' ),
		);

		$options = ITUtility::merge_defaults( $options, $defaults );

		if ( empty( $limit ) )
			$limit = $options['unlimited-label'];

		if ( 'html' == $options['format'] )
			$result .= $options['before'];

		$result .= $limit;

		if ( 'html' == $options['format'] )
			$result .= $options['after'];

		return $result;
	}

	/**
	 * The download expiration
	 *
	 * @since 0.4.0
	 * @return string
	*/
	function expiration( $options=array() ) {

		// Return boolean if has flag was set
		if ( $options['has'] )
			return ! empty( $this->download['expiration'] );

		$result     = '';
		$expiration = $this->download['expires'];
		$defaults   = array(
			'before'              => '<span class="download-expiration">',
			'after'               => '</span>',
			'format'              => 'raw',
			'never-expires-label' => __( 'Never expires', 'it-l10n-ithemes-exchange' ),
			'template'            => __( '%d %s after purchase', 'it-l10n-ithemes-exchange' ),
		);

		$options = ITUtility::merge_defaults( $options, $defaults );

		if ( empty( $expiration ) )
			$expiration = $options['never-expires-label'];
		else
			$expiration = sprintf( $options['template'], $this->download['expire_int'], $this->download['expire_units'] );

		if ( 'html' == $options['format'] )
			$result .= $options['before'];

		$result .= $expiration;

		if ( 'html' == $options['format'] )
			$result .= $options['after'];

		return $result;
	}
}
