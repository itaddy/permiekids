<?php
/**
 * Shipping Method class for THEME API
 *
 * @since 0.4.0
*/

class IT_Theme_API_Shipping_Method implements IT_Theme_API {

	/**
	 * API context
	 * @var string $_context
	 * @since 0.4.0
	*/
	private $_context = 'shipping-method';

	/**
	 * Maps api tags to methods
	 * @var array $_tag_map
	 * @since 0.4.0
	*/
	public $_tag_map = array(
		'form'    => 'form',
		'cancel'  => 'cancel',
		'submit'  => 'submit',
		'current' => 'current',
	);

	/**
	 * Constructor
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function IT_Theme_API_Shipping_Method() {
		$this->cart_methods                      = it_exchange_get_available_shipping_methods_for_cart();
		$this->cart_product_methods              = it_exchange_get_available_shipping_methods_for_cart_products();
		$this->multiple_shipping_methods_allowed = false;
		$this->current_method                    = it_exchange_get_cart_shipping_method();
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
	 * Prints the Shipping Method select form
	 *
	 * @since 1.4.0
	 *
	 * @param  array  $options
	 * @return string
	*/
	function form( $options=array() ) {
		ob_start();

		$cart_methods                      = $this->cart_methods;
		$cart_product_methods              = $this->cart_product_methods;
		$multiple_shipping_methods_allowed = $this->multiple_shipping_methods_allowed;
		$current_method                    = $this->current_method;

		if ( ( count( $cart_methods ) === 1 && count( $cart_product_methods ) === 1 ) || count( $cart_product_methods ) === 1 ) {
			$method = reset($cart_methods);
			it_exchange_update_cart_data( 'shipping-method', $method->slug );
			echo $method->label . ' (' . it_exchange_get_cart_shipping_cost() . ')';
		} else {
			?>
			<form method="post" action="">
			<select class="it-exchange-shipping-method-select" name="it-exchange-shipping-method">
			<?php
			$options = '<option value="0">' . __( 'Select a shipping method', 'it-l10n-ithemes-exchange' );
			foreach( $cart_methods as $method ) {
				$options .= '<option value="' . esc_attr( $method->slug ) . '" ' . selected( $current_method, $method->slug, false ) . '>' . $method->label . ' (' . it_exchange_get_cart_shipping_cost( $method->slug ) . ')</option>';
			}
			if ( (array) it_exchange_get_cart_products() > 1 ) {
				$cart_products_with_shipping = 0;
				foreach( (array) it_exchange_get_cart_products() as $cart_product ) {
					if ( it_exchange_product_has_feature( $cart_product['product_id'], 'shipping' ) )
						$cart_products_with_shipping++;
				}
				if ( $cart_products_with_shipping > 1 && count( $cart_product_methods ) > 1 ) {
					$multiple_shipping_methods_allowed = true;
					$options .= '<option value="multiple-methods" ' . selected( $current_method, 'multiple-methods', false ) . '>' . __( 'Use multiple shipping methods', 'it-l10n-ithemes-exchange' ) . '</option>';
				}
			}

			echo $options;
			?>
			</select>
			</form>
			<?php
		}

		if ( 'multiple-methods' == $current_method && $multiple_shipping_methods_allowed ) :
			?>
			<div class="it-exchange-itemized-checkout-methods it-exchange-clearfix">
				<?php
				foreach( (array) it_exchange_get_cart_products() as $product ) {
					if ( ! it_exchange_product_has_feature( $product['product_id'], 'shipping' ) )
						continue;

					echo '<div class="it-exchange-itemized-checkout-method">';

						echo '<span class="it-exchange-shipping-product-title">' . it_exchange_get_cart_product_title( $product ) . '</span>';
						$selected_multiple_method = it_exchange_get_multiple_shipping_method_for_cart_product( $product['product_cart_id'] );
						$enabled_shipping_methods = (array) it_exchange_get_enabled_shipping_methods_for_product( it_exchange_get_product( $product['product_id'] ) );

						if ( count( $enabled_shipping_methods ) > 1 ) {
							?>
							<select class="it-exchange-multiple-shipping-methods-select it-exchange-right" data-it-exchange-product-cart-id="<?php esc_attr_e( $product['product_cart_id'] ); ?>" name="it-exchange-shipping-method-for-<?php esc_attr_e( $product['product_cart_id'] ); ?>" >
								<?php foreach( $enabled_shipping_methods as $product_method ) : ?>
									<?php if ( empty( $product_method->slug ) ) continue; ?>
									<option value="<?php esc_attr_e( $product_method->slug ); ?>" <?php selected( $selected_multiple_method, $product_method->slug ); ?>>
										<?php echo $product_method->label; ?>
										(<?php echo it_exchange_get_shipping_method_cost_for_cart_item( $product_method->slug, $product, true ); ?>)
									</option>
								<?php endforeach; ?>
							</select><br />
							<?php
						} else {
							$product_method = reset( $enabled_shipping_methods );
							it_exchange_update_multiple_shipping_method_for_cart_product( $product['product_cart_id'], $product_method->slug );
							echo '<span class="it-exchange-right">' . $product_method->label . ' (' . it_exchange_get_shipping_method_cost_for_cart_item( $product_method->slug, $product, true ) . ')</span>';
						}

					echo '</div>';
				}
				?>
			</div>
			<?php
		endif;

		$return = ob_get_clean();
		return $return;
	}

	/**
	 * Prints the cancel button for shipping method select
	 *
	 * Only prints if we have a method
	 *
	 * @since 1.4.0
	 *
	 * @param array $options
	*/
	function cancel( $options=array() ) {

		if ( empty( $this->current_method ) )
			return '';

		$defaults = array(
			'label' => __( 'Cancel', 'it-l10n-ithemes-exchange' ),
			'class' => false,
		);

		$options = ITUtility::merge_defaults( $options, $defaults );

		$core_class = 'it-exchange-super-widget-shipping-method-cancel-action';
		$class = empty( $options['class'] ) ? $core_class : esc_attr( $options['class'] ) . ' ' . $core_class;

		$return = '<a href="" class="' . $class . '">' . esc_html( $options['label'] ). '</a>';
		return $return;
	}

	/**
	 * Prints the submit button for shipping method select
	 *
	 * @since 1.4.0
	 *
	 * @param array $options
	*/
	function submit( $options=array() ) {

		if ( empty( $this->current_method ) )
			return '';

		$defaults = array(
			'label' => __( 'Next', 'it-l10n-ithemes-exchange' ),
			'class' => false,
		);

		$options = ITUtility::merge_defaults( $options, $defaults );

		$core_class = 'it-exchange-super-widget-shipping-method-submit-action';
		$class = empty( $options['class'] ) ? $core_class : esc_attr( $options['class'] ) . ' ' . $core_class;

		$return = '<a href="" class="' . $class . '">' . esc_html( $options['label'] ). '</a>';
		return $return;
	}

	/**
	 * Returns the label for the currently selected Shipping Method
	 *
	 * @since 1.4.0
	 *
	*/
	function current( $options=array() ) {
		$method = it_exchange_get_registered_shipping_method( $this->current_method );
		return $method->label;
	}
}
