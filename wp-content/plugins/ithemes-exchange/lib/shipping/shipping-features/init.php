<?php
/**
 * Registers core shipping features
 *
*/
// Available Shipping Methods
include_once( dirname( __FILE__ ) . '/core-available-shipping-methods.php' );
it_exchange_register_shipping_feature( 'core-available-shipping-methods', 'IT_Exchange_Core_Shipping_Feature_Available_Shipping_Methods' );

// Core From Address
include_once( dirname( __FILE__ ) . '/core-from-address.php' );
it_exchange_register_shipping_feature( 'core-from-address', 'IT_Exchange_Core_Shipping_Feature_From_Address' );

// Weight and Dimensions
include_once( dirname( __FILE__ ) . '/core-weight-dimensions.php' );
it_exchange_register_shipping_feature( 'core-weight-dimensions', 'IT_Exchange_Core_Shipping_Feature_Weight_Dimensions' );
