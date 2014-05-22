<?php
/**
 * Coupons class for THEME API
 *
 * @since 0.4.0
*/

class IT_Theme_API_Coupons implements IT_Theme_API {

	/**
	 * API context
	 * @var string $_context
	 * @since 0.4.0
	*/
	private $_context = 'coupons';

	/**
	 * Maps api tags to methods
	 * @var array $_tag_map
	 * @since 0.4.0
	*/
	var $_tag_map = array(
		'supported'      => 'is_supported',
		'applied'        => 'applied',
		'accepting'      => 'accepting',
		'apply'          => 'apply',
		'code'           => 'code',
		'discount'       => 'discount',
		'discountlabel'  => 'discount_label',
		'totaldiscount'  => 'total_discount',
		'remove'         => 'remove',
		'discountmethod' => 'discount_method',
	);

	/**
	 * Current coupon in iThemes Exchange Global
	 * @var object $coupon
	 * @since 0.4.0
	*/
	private $coupon;

	/**
	 * Constructor
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function IT_Theme_API_Coupons() {
		// Set the current global coupon as a property
		$this->coupon = empty( $GLOBALS['it_exchange']['coupon'] ) ? false : it_exchange_get_coupon( $GLOBALS['it_exchange']['coupon']['id'] );
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
	 * Are we supporting coupons?
	 *
	 * @since 0.4.0
	 *
	 * @param array $options
	 * @return boolean
	*/
	function is_supported( $options=array() ) {
			$defaults = array(
				'type' => false,
			);
			$options = ITUtility::merge_defaults( $options, $defaults );

			// Return false if no type option is set
			if ( ! $options['type'] )
				return false;

			// Return false if no coupons add-on is enabled
			if ( ! (boolean) it_exchange_get_enabled_addons( array( 'category' => 'coupons' ) ) )
				return false;

			// Ask addon if it supports this type of coupon. Default is false
			return it_exchange_supports_coupon_type( $options['type'] );
	}

	/**
	 * Do we have any coupons currently applied
	 *
	 * We rely on the add-on to give us the answer. Should check for type asked.
	 *
	 * @since 0.4.0
	 *
	 * @param array $options
	 * @return boolean
	*/
	function applied( $options=array() ) {
		$defaults = array(
			'type' => false,
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		// Return false if no type option is set
		if ( ! $options['type'] )
			return false;

		// Return false if no coupons add-on is enabled
		if ( ! (boolean) it_exchange_get_enabled_addons( array( 'category' => 'coupons' ) ) )
			return false;

		// Do we have any applied coupons
		if ( ! empty( $options['has'] ) )
			return (boolean) it_exchange_get_applied_coupons( $options['type'] );

		// If we made it here, we're doing a loop of applied coupons
		// This will init/reset the applied_coupons global and loop through them.
		if ( empty( $GLOBALS['it_exchange']['applied_' . $options['type'] . '_coupons'] ) ) {
			if ( ! $coupons = it_exchange_get_applied_coupons( $options['type'] ) )
				return false;
			$GLOBALS['it_exchange']['applied_' . $options['type'] . '_coupons'] = $coupons;
			$GLOBALS['it_exchange']['coupon'] = reset( $GLOBALS['it_exchange']['applied_' . $options['type'] . '_coupons'] );
			return true;
		} else {
			if ( next( $GLOBALS['it_exchange']['applied_' . $options['type'] . '_coupons'] ) ) {
				$GLOBALS['it_exchange']['coupon'] = current( $GLOBALS['it_exchange']['applied_' . $options['type'] . '_coupons'] );
				return true;
			} else {
				$GLOBALS['it_exchange']['applied_' . $options['type'] . '_coupons'] = array();
				end( $GLOBALS['it_exchange']['applied_' . $options['type'] . '_coupons'] );
				$GLOBALS['it_exchange']['coupon'] = false;
				return false;
			}
		}
	}

	/**
	 * Are we accepting new coupons?
	 *
	 * @since 0.4.0
	 *
	 * @param array $options
	 * @return boolean
	*/
	function accepting( $options=array() ) {
			$defaults = array(
				'type' => false,
			);
			$options = ITUtility::merge_defaults( $options, $defaults );

			// Return false if no type option is set
			if ( ! $options['type'] )
				return false;

			// Return false if no coupons add-on is enabled
			if ( ! (boolean) it_exchange_get_enabled_addons( array( 'category' => 'coupons' ) ) )
				return false;

			// Ask addon if it is accepting any more of the specified type of coupon
			return it_exchange_accepting_coupon_type( $options['type'] );
	}

	/**
	 * Returns the field for applying a coupon
	 *
	 * Can also return the field var
	 *
	 * @since 0.4.0
	 *
	 * @param array $otpoins
	 * @return string
	*/
	function apply( $options=array() ) {
			$defaults = array(
				'type'        => false,
				'format'      => 'form-field',
				'class'       => 'apply-coupon',
				'placeholder' => __( 'Coupon Code', 'it-l10n-ithemes-exchange' ),
				'before'      => '',
				'after'       => '',
			);
			$options = ITUtility::merge_defaults( $options, $defaults );

			// Return empty string if no type option is set
			if ( ! $options['type'] )
				return '';

			// Return empty string if no coupons add-on is enabled
			if ( ! (boolean) it_exchange_get_enabled_addons( array( 'category' => 'coupons' ) ) )
				return '';

			// Return empty string if no var is found
			if ( ! $var = it_exchange_get_field_name( 'apply_coupon' ) )
				return '';

			$var .= '-' . $options['type'];

			// Return var if requested format
			if ( 'var_name' == $options['format'] )
				return $var;

			// Return the field
			$field = it_exchange_get_coupon_type_apply_field( $options['type'], array( 'class' => $options['class'], 'placeholder' => $options['placeholder'] ) );

			if ( $field )
				return $options['before'] . $field . $options['after'];
			else
				return '';
	}

	function code( $options=array() ) {
		return $this->coupon->code;
	}

	function discount( $options=array() ) {
		$amount_number = it_exchange_convert_from_database_number( $this->coupon->amount_number );

		return _x( '-', 'LION', 'negative character for amount of money in coupons' ) . it_exchange_basic_coupons_get_total_discount_for_cart();
	}

	/**
	 * Returns the coupon discount label
	 *
	 * ie: $10.00 or 10%
	 *
	 * @since 0.4.0
	 *
	 * @param array $options optional
	 * @return string
	*/
	function discount_label( $options=array() ) {
		$defaults = array(
			'before' => '',
			'after'  => '',
		);
		$options = ITUtility::merge_defaults( $options, $defaults );
		if ( $label = it_exchange_get_coupon_discount_label( $this->coupon->ID ) )
			return $options['before'] . $label . $options['after'];

		return '';
	}

	function total_discount( $options=array() ) {
		$defaults = array(
			'type' => 'cart',
		);
		$options = ITUtility::merge_defaults( $options, $defaults );
		return it_exchange_get_total_coupons_discount( $options['type'] );
	}

	/**
	 * Returns the remove coupon var, link, checkbox. Default is link
	 *
	 * @since 0.4.0
	*/
	function remove( $options ) {

		$defaults = array(
			'before' => '',
			'after'  => '',
			'class'  => 'remove-coupon',
			'type'   => false,
			'format' => 'link',
			'label'  => '&times;',
		);

		$options = ITUtility::merge_defaults( $options, $defaults );

		// Return empty string if no type option is set
		if ( ! $options['type'] )
			return '';

		// Return empty string if no var is found
		if ( ! $var = it_exchange_get_field_name( 'remove_coupon' ) )
			return '';

		$var .= '-' . $options['type'];

		// Return var if requested format
		if ( 'var_name' == $options['format'] )
			return $var;

		// Return the requested type
		$return = it_exchange_get_remove_coupon_html( $options['type'], $this->coupon->code, array( 'format' => $options['format'], 'class' => $options['class'], 'label' => $options['label'], 'code' => $this->coupon->code ) );

		if ( $return )
			return $options['before'] . $return . $options['after'];
		else
			return '';
	}

	/**
	 * Returns boolean value if we have a coupon or not
	 *
	 * @since 0.4.0
	 *
	 * @return boolean
	*/
	function found( $options=array() ) {
		return (boolean) $this->coupon;
	}

	/**
	 * The coupon title
	 *
	 * @since 0.4.0
	 * @return string
	*/
	function title( $options=array() ) {

		// Return boolean if has flag was set
		if ( $options['has'] )
			return ! empty( $this->coupon->name );

		if ( ! empty( $this->coupon->name ) ) {

			$result   = '';
			$title    = $this->coupon->name;
			$defaults = array(
				'before' => '<h1 class="coupon-title">',
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
	 * The coupon Limit
	 *
	 * @since 0.4.0
	 * @return string
	*/
	function limit( $options=array() ) {

		// Return boolean if has flag was set
		if ( $options['has'] )
			return ! empty( $this->coupon->limit );

		if ( ! empty( $this->coupon->limit ) ) {

			$result   = '';
			$limit    = $this->coupon->limit;
			$defaults = array(
				'before' => '<span class="coupon-limit">',
				'after'  => '</span>',
				'format' => 'raw',
			);

			$options = ITUtility::merge_defaults( $options, $defaults );

			if ( 'html' == $options['format'] )
				$result .= $options['before'];

			$result .= $limit;

			if ( 'html' == $options['format'] )
				$result .= $options['after'];

			return $result;
		}
		return false;
	}

	/**
	 * The coupon expiration
	 *
	 * @since 0.4.0
	 * @return string
	*/
	function expiration( $options=array() ) {

		// Return boolean if has flag was set
		if ( $options['has'] )
			return ! empty( $this->coupon->expiration );

		if ( ! empty( $this->coupon->expiration ) ) {

			$result     = '';
			$expiration = $this->coupon->expiration;
			$defaults   = array(
				'before' => '<span class="coupon-expiration">',
				'after'  => '</span>',
				'format' => 'raw',
			);

			$options = ITUtility::merge_defaults( $options, $defaults );

			if ( 'html' == $options['format'] )
				$result .= $options['before'];

			$result .= $expiration;

			if ( 'html' == $options['format'] )
				$result .= $options['after'];

			return $result;
		}
		return false;
	}

	/**
	 * The coupon Discount Method if the coupon addon provides one
	 *
	 * @since 0.4.0
	 * @return string
	*/
	function discount_method( $options=array() ) {

		$method = it_exchange_get_coupon_discount_method( $this->coupon->ID );

		// Return boolean if has flag was set
		if ( $options['has'] )
			return ! empty( $method );

		if ( ! empty( $method ) ) {

			$result     = '';
			$defaults   = array(
				'before' => '<span class="coupon-method">',
				'after'  => '</span>',
				'format' => 'raw',
			);

			$options = ITUtility::merge_defaults( $options, $defaults );

			if ( 'html' == $options['format'] )
				$result .= $options['before'];

			$result .= $method;

			if ( 'html' == $options['format'] )
				$result .= $options['after'];

			return $result;
		}
		return false;
	}
}
