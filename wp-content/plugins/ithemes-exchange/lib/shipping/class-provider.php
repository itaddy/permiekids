<?php
/**
 * This class setsup a provider
*/
class IT_Exchange_Shipping_Provider{

	var $slug;
	var $label;
	var $shipping_methods;
	var $provider_settings;
	var $country_states_js;
	var $has_settings_page = false;

	function IT_Exchange_Shipping_Provider( $slug, $options=array() ) {

		$defaults = array(
			'label' => false,
			'shipping-methods'  => array(),
			'country-states-js' => array(),
		);

		// Merge options with defaults
		$options = ITUtility::merge_defaults( $options, $defaults );

		// Set slug and label properties
		$this->slug  = $slug;
		$this->label = $options['label'];

		// Setup Shipping Methods if needed
		if ( ! empty( $options['shipping-methods'] ) )
			$this->setup_shipping_methods( $options['shipping-methods'] );

		// Setup settings page if needed
		if ( ! empty( $options['provider-settings'] ) )
			$this->setup_provider_settings( $options['provider-settings'] );

		// Add method settings
		$this->add_method_settings();

		// Setup country_states_js if needed
		if ( ! empty( $options['country-states-js'] ) )
			$this->setup_country_states_js( $options['country-states-js'] );

		// Default values for settings
		add_filter( 'it_storage_get_defaults_exchange_addon_shipping_' . $this->slug, array( $this, 'get_default_provider_setting_values' ) );
	}

	function get_slug() {
		return $this->slug;
	}

	function get_label() {
		return $this->label;
	}

	/**
	 * Applies an array of shipping methods to the object provider property
	 *
	 * @since 1.4.0
	 *
	 * @param  array $methods the methods array
	 * @return void
	*/
	function setup_shipping_methods( $methods=array() ) {
		if ( ! empty( $methods ) ) {
			foreach( $methods as $slug ) {
				$this->add_shipping_method( $slug );
			}
		}
	}

	/**
	 * Applies an array of provider settings to the object provider property
	 *
	 * @since 1.4.0
	 *
	 * @param  array $settings the settings array
	 * @return void
	*/
	function setup_provider_settings( $settings=array() ) {

		// Add any provider settings
		foreach( (array) $settings as $options ) {
			$this->add_provider_setting( $options );
		}
	}

	/**
	 * Applies an array of provider settings to the object provider property
	 *
	 * @since 1.4.0
	 *
	 * @param  array $options the country_states_js options
	 * @return void
	*/
	function setup_country_states_js( $options ) {
		$this->country_states_js = $options;
	}

	/**
	 * Adds a shipping method to the provider as an object property
	 *
	 * @since 1.4.0
	 *
	 * @param  string $slug    the slug for the shipping method
	 * @param  array  $options options for the shipping method
	 * @return void
	*/
	function add_shipping_method( $slug ) {
		if ( ! in_array( $slug, (array) $this->shipping_methods ) )
			$this->shipping_methods[] = $slug;
	}

	function add_method_settings() {

		// Loop through methods and add method settings
		foreach( (array) $this->shipping_methods as $method ) {
			if ( $method = it_exchange_get_registered_shipping_method( $method ) ) {
				foreach( (array) $method->settings as $setting ) {
					$this->add_provider_setting( $setting );
				}
			}
		}

	}

	/**
	 * Adds a provider settings to the array of existing provider settings
	 *
	 * @since 1.4.0
	 *
	 * @param  array  $options options for the provider setting
	 * @return void
	*/
	function add_provider_setting( $options=array() ) {
		if ( empty( $options['type'] ) || empty( $options['slug'] ) )
			return false;

		$options['options'] = empty( $options['options'] ) ? array() : $options['options'];
		$this->provider_settings[$options['slug']] = $options;
		$this->has_settings_page  = true;
	}

	/**
	 * Returns all of the shipping methods for this provider
	 *
	 * @since 1.4.0
	 *
	 * @return array
	*/
	function get_shipping_methods() {
		return (array) $this->shipping_methods;
	}

	/**
	 * Returns all of the settings for this provider
	 *
	 * @since 1.4.0
	 *
	 * @return array
	*/
	function get_provider_settings() {
		return (array) $this->provider_settings;
	}

	/**
	 * Returns the values for the provider settings
	 *
	 * @since 1.4.0
	 *
	 * @return array
	*/
	function get_setting_values() {
		$options = it_exchange_get_option( 'addon_shipping_' . $this->slug );
		return $options;
	}

	/**
	 * Returns the default values for the provider settings
	 *
	 * @since 1.4.0
	 *
	 * @return array
	*/
    function get_default_provider_setting_values( $defaults ) {
        foreach ( (array) $this->provider_settings as $setting ) {
            if ( 'heading' != $setting['type'] ) {
                $defaults[$setting['slug']] = empty( $setting['default'] ) ? false : $setting['default'];
            }
        }
        return $defaults;
    }

	/**
	 * Save provider settings
	 *
	 * @since 1.4.0
	 *
	 * @param array $settings the settings that will replace current settings
	 * @return void
	*/
	function update_settings( $settings ) {
		it_exchange_save_option( 'addon_shipping_' . $this->slug, $settings );
	}
}
