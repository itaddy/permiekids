<?php
/**
 * Register our Simple Shipping Provider
 *
 * @since 1.4.0
 *
 * @return void
*/
function it_exchange_addon_simple_shipping_register_shipping_provider() {
    $options = array(
        'label'            => __( 'Simple Shipping', 'it-l10n-ithemes-exchange' ),
        'shipping-methods' => array(
            'exchange-flat-rate-shipping',
            'exchange-free-shipping',
        ),
    );
    it_exchange_register_shipping_provider( 'simple-shipping', $options );
}
add_filter( 'it_exchange_enabled_addons_loaded', 'it_exchange_addon_simple_shipping_register_shipping_provider' );
