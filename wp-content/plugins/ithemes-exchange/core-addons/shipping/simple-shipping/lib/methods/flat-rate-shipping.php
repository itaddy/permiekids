<?php
/**
 * Registers the Shipping Methods we need for Exchange Simple Shipping add-on
 *
 * @since 1.4.0
 *
 * @return void
*/
function it_exchange_addon_simple_shipping_register_flat_rate_shipping_method() {
	// Exchange Flat Rate Shipping Method
	it_exchange_register_shipping_method( 'exchange-flat-rate-shipping', 'IT_Exchange_Simple_Shipping_Flat_Rate_Method' );
}
add_action( 'it_exchange_enabled_addons_loaded', 'it_exchange_addon_simple_shipping_register_flat_rate_shipping_method' );

/**
 * Register exchange flat rate cost shipping feature
 *
*/
function it_exchange_addon_simple_shipping_register_flat_rate_shipping_features() {
	it_exchange_register_shipping_feature( 'exchange-flat-rate-shipping-cost', 'IT_Exchange_Simple_Shipping_Flat_Rate_Shipping_Cost' );
}
add_action( 'it_exchange_enabled_addons_loaded', 'it_exchange_addon_simple_shipping_register_flat_rate_shipping_features' );

class IT_Exchange_Simple_Shipping_Flat_Rate_Method extends IT_Exchange_Shipping_Method {

	/**
	 * Class constructor. Needed to call parent constructor
	 *
	 * @since 1.4.0
	 *
	 * @param integer $product_id optional product id for current product
	 * @return void
	*/
	function __construct( $product_id=false ) {
		parent::__construct( $product_id );
	}

	/**
	 * Sets the identifying slug for this shipping method
	 *
	 * @since 1.4.0
	 *
	 * @return void
	*/
	function set_slug() {
		$this->slug = 'exchange-flat-rate-shipping';
	}

	/**
	 * Sets the label for this shipping method
	 *
	 * @since 1.4.0
	 *
	 * @return void
	*/
	function set_label() {
		$settings = it_exchange_get_option( 'simple-shipping' );
		$this->label = empty( $settings['flat-rate-shipping-label'] ) ? __( 'Flat Rate Shipping', 'it-l10n-ithemes-exchange' ) : $settings['flat-rate-shipping-label'];
	}

	/**
	 * Sets the Shipping Features that this method uses.
	 *
	 * @since 1.4.0
	 *
	 * @return void
	*/
	function set_features() {
		$this->shipping_features = array(
			'exchange-flat-rate-shipping-cost',
		);
	}

	/**
	 * Determines if this shipping method is enabled and sets the property value
	 *
	 * @since 1.4.0
	 *
	 * @return void
	*/
	function set_enabled() {
		$break_cache   = is_admin() && ! empty( $_POST );
		$options       = it_exchange_get_option( 'simple-shipping', $break_cache );
		$this->enabled = ! empty( $options['enable-flat-rate-shipping'] );
	}

	/**
	 * Determines if this shipping method is available to the product and sets the property value
	 *
	 * @since 1.4.0
	 *
	 * @return void
	*/
	function set_availability() {
		$this->available = $this->enabled;
	}

	/**
	 * Define any setting fields that you want this method to include on the Provider settings page
	 *
	 * @since 1.4.0
	 *
	 * @return void
	*/
	function set_settings() {
		$general_settings = it_exchange_get_option( 'settings_general' );
		$currency         = it_exchange_get_currency_symbol( $general_settings['default-currency'] );

		$settings = array(
			array(
				'type'  => 'heading',
				'label' => __( 'Flat Rate Shipping', 'it-l10n-ithemes-exchange' ),
				'slug'  => 'flat-rate-shipping-heading',
			),
			array(
				'type'    => 'yes_no_drop_down',
				'label'   => __( 'Enable Flat Rate Shipping?', 'it-l10n-ithemes-exchange' ),
				'slug'    => 'enable-flat-rate-shipping',
				'tooltip' => __( 'Do you want flat rate shipping available to your customers as a shipping option?', 'it-l10n-ithemes-exchange' ),
				'default' => 1,
			),
			array(
				'type'    => 'text_box',
				'label'   => __( 'Shipping Label', 'it-l10n-ithemes-exchange' ),
				'slug'    => 'flat-rate-shipping-label',
				'tooltip' => __( 'This changes the title of this Shipping Method for your customers', 'it-l10n-ithemes-exchange' ),
				'default' => __( 'Standard Shipping (3-5 days)', 'it-l10n-ithemes-exchange' ),
			),
			array(
				'type'    => 'text_box',
				'label'   => __( 'Default Shipping Amount', 'it-l10n-ithemes-exchange' ),
				'slug'    => 'flat-rate-shipping-amount',
				'tooltip' => __( 'The default shipping amount for new products. This can be overridden by individual products.', 'it-l10n-ithemes-exchange' ),
				'default' => it_exchange_format_price( 5 ),
				'options' => array(
					'data-symbol'              => esc_attr( $currency ),
					'data-symbol-position'     => esc_attr( $general_settings['currency-symbol-position'] ),
					'data-thousands-separator' => esc_attr( $general_settings['currency-thousands-separator'] ),
					'data-decimals-separator'  => esc_attr( $general_settings['currency-decimals-separator'] ),
				),
			),
		);

		foreach ( $settings as $setting ) {
			$this->add_setting( $setting );
		}
	}

	function get_shipping_cost_for_product( $cart_product ) {
		$count = empty( $cart_product['count'] ) ? 1 : $cart_product['count'];
		$cost = it_exchange_get_shipping_feature_for_product( 'exchange-flat-rate-shipping-cost', $this->product->ID );
		$cost = empty( $cost->cost ) ? 0 : $cost->cost;
		$cost = it_exchange_convert_from_database_number( it_exchange_convert_to_database_number( html_entity_decode( $cost, ENT_COMPAT, 'UTF-8' ) ) );
		return $cost * $count;
	}
}

/**
 * This is the class for our exchange flat rate shipping feature
 *
 * @since 1.4.0
*/
class IT_Exchange_Simple_Shipping_Flat_Rate_Shipping_Cost extends IT_Exchange_Shipping_Feature {

	var $slug = 'exchange-flat-rate-shipping-cost';

	/**
	 * Constructor
	*/
	function __construct( $product_id=false ) {
		parent::__construct( $product_id );
	}

	/**
	 * Sets the availability
	*/
	function set_availability() {
		$this->available = true;
	}

	function set_enabled() {
		$this->enabled = true;
	}

	/**
	 * Sets the values
	*/
	function set_values() {

		// Init values object as standard class
		$values = new stdClass();

		// Grab default value
		$defaults     = it_exchange_get_option( 'simple-shipping' );
		$default_cost = $defaults['flat-rate-shipping-amount'];

		// Post meta
		$post_amount  = get_post_meta( $this->product->ID, '_it_exchange_shipping_flat-rate-shipping-default-amount', true );

		// Set value
		$values->cost = empty( $post_amount ) ? $default_cost : it_exchange_format_price( it_exchange_convert_from_database_number( $post_amount ) );
		$this->values = $values;
	}

	/**
	 * Save the values
	 *
	 * Saves the values when the add/edit product screen is saved
	*/
	function update_on_product_save() {
		if ( ! empty( $_POST['it-exchange-flat-rate-shipping-cost'] ) )
			$this->update_value( $_POST['it-exchange-flat-rate-shipping-cost'] );
	}

	/**
	 * Updates the value to the passed paramater
	 *
	*/
	function update_value( $new_value ) {
		update_post_meta( $this->product->ID, '_it_exchange_shipping_flat-rate-shipping-default-amount', it_exchange_convert_to_database_number( $new_value ) );
	}

	/**
	 * Prints the interior of the feature box in the add/edit product view
	*/
	function print_add_edit_feature_box_interior() {
		$settings = it_exchange_get_option( 'settings_general' );
		$currency = it_exchange_get_currency_symbol( $settings['default-currency'] );
		?>
		<div class="it-exchange-flat-rate-shipping-cost">
			<label for="it-exchange-flat-rate-shipping-cost"><?php _e( 'Flat Rate Shipping Cost', 'it-l10n-ithemes-exchange' ); ?> <span class="tip" title="<?php _e( 'Shipping costs for this product. Multiplied by quantity purchased.', 'it-l10n-ithemes-exchange' ); ?>">i</span></label>
			<input type="text" data-symbol="<?php esc_attr_e( $currency ); ?>" data-symbol-position="<?php esc_attr_e( $settings['currency-symbol-position'] ); ?>" data-thousands-separator="<?php esc_attr_e( $settings['currency-thousands-separator'] ); ?>" data-decimals-separator="<?php esc_attr_e( $settings['currency-decimals-separator'] ); ?>" id="it-exchange-flat-rate-shipping-cost" name="it-exchange-flat-rate-shipping-cost" class="input-money-small" value="<?php esc_attr_e( $this->values->cost ); ?>"/>
		</div>
		<?php
	}
}
