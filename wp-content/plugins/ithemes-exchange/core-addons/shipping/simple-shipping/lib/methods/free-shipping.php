<?php
/**
 * Registers the Shipping Methods we need for Exchange Simple Shipping add-on
 *
 * @since 1.4.0
 *
 * @return void
*/
function it_exchange_addon_simple_shipping_register_free_shipping_method() {
	// Exchange Free Shipping Method
	it_exchange_register_shipping_method( 'exchange-free-shipping', 'IT_Exchange_Simple_Shipping_Free_Method' );
}
add_action( 'it_exchange_enabled_addons_loaded', 'it_exchange_addon_simple_shipping_register_free_shipping_method' );

class IT_Exchange_Simple_Shipping_Free_Method extends IT_Exchange_Shipping_Method {

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
		$this->slug = 'exchange-free-shipping';
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
		$this->label = empty( $settings['free-shipping-label'] ) ? __( 'Free Shipping', 'it-l10n-ithemes-exchange' ) : $settings['free-shipping-label'];
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
		$this->enabled = ! empty( $options['enable-free-shipping'] );
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
		$settings = array(
			array(
				'type'  => 'heading',
				'label' => __( 'Free Shipping', 'it-l10n-ithemes-exchange' ),
				'slug'  => 'free-shipping-heading',
			),
			array(
				'type'    => 'yes_no_drop_down',
				'label'   => __( 'Enable Free Shipping?', 'it-l10n-ithemes-exchange' ),
				'slug'    => 'enable-free-shipping',
				'tooltip' => __( 'Do you want free shipping available to your customers as a shipping option?', 'it-l10n-ithemes-exchange' ),
				'default' => 1,
			),
			array(
				'type'    => 'text_box',
				'label'   => __( 'Shipping Label', 'it-l10n-ithemes-exchange' ),
				'slug'    => 'free-shipping-label',
				'tooltip' => __( 'This changes the title of this Shipping Method for your customers', 'it-l10n-ithemes-exchange' ),
				'default' => __( 'Free Shipping (3-5 days)', 'it-l10n-ithemes-exchange' ),
			),
		);

		foreach ( $settings as $setting ) {
			$this->add_setting( $setting );
		}
	}

	function get_shipping_cost_for_product( $cart_product ) {
		return 0;
	}
}
