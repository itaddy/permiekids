<?php
/**
 * This is the class for our From Address shipping feature
 *
 * @since 1.4.0
*/
class IT_Exchange_Core_Shipping_Feature_From_Address extends IT_Exchange_Shipping_Feature {

	var $slug = 'core-from-address';

	/**
	 * Constructor
	*/
	function __construct( $product=false, $options=array() ) {
		parent::__construct( $product, $options );
	}

	/**
	 * Is this shipping feature available as an option
	*/
	function set_availability() {
		$general_shipping_options = it_exchange_get_option( 'shipping-general' );
		$this->available = ! empty( $general_shipping_options['products-can-override-ships-from'] );
	}

	/*
	 * If it is available as an options, is it enabled?
	*/
	function set_enabled() {
		$post_meta = get_post_meta( $this->product->ID, '_it_exchange_override_from_address', true );
		$this->enabled = ! empty( $post_meta['override_defaults'] );
	}

	/**
	 * Sets the values
	*/
	function set_values() {
		$general_shipping_options = it_exchange_get_option( 'shipping-general' );

		// @todo Fix this elsewhere - check for values in rare instance where General Settings page wasn't viewed before Add Product page
		$general_shipping_from_fields = array( 'address1', 'address2', 'city', 'state', 'country', 'zip' );
		foreach( $general_shipping_from_fields as $field ) {
			if ( empty( $general_shipping_options['product-ships-from-' . $field] ) )
				$general_shipping_options['product-ships-from-' . $field] = '';
		}

		// Product specific values
		$post_meta = get_post_meta( $this->product->ID, '_it_exchange_override_from_address', true );

		$values = new stdClass();
		$values->override_defaults = ! empty( $post_meta['override_defaults'] );
		$values->address1          = empty( $post_meta['address1'] ) || empty( $this->enabled ) ? $general_shipping_options['product-ships-from-address1'] : $post_meta['address1'];
		$values->address2          = ! isset( $post_meta['address2'] ) || empty( $this->enabled ) ? $general_shipping_options['product-ships-from-address2'] : $post_meta['address2'];
		$values->city              = empty( $post_meta['city'] ) || empty( $this->enabled ) ? $general_shipping_options['product-ships-from-city'] : $post_meta['city'];
		$values->state             = empty( $post_meta['state'] ) || empty( $this->enabled ) ? $general_shipping_options['product-ships-from-state'] : $post_meta['state'];
		$values->country           = empty( $post_meta['country'] ) || empty( $this->enabled ) ? $general_shipping_options['product-ships-from-country'] : $post_meta['country'];
		$values->zip               = empty( $post_meta['zip'] ) || empty( $this->enabled ) ? $general_shipping_options['product-ships-from-zip'] : $post_meta['zip'];
		$this->values              = $values;
	}

	/**
	 * Updates the data when a product is saved
	 *
	*/
	function update_on_product_save() {
		if ( empty( $_POST ) )
			return;

		$data['override_defaults'] = ! empty( $_POST['core-shipping-feature-override-from-address'] );
		$data['address1']          = empty( $_POST['core-shipping-feature-from-address-address1'] ) ? '' : $_POST['core-shipping-feature-from-address-address1'];
		$data['address2']          = empty( $_POST['core-shipping-feature-from-address-address2'] ) ? '' : $_POST['core-shipping-feature-from-address-address2'];
		$data['city']              = empty( $_POST['core-shipping-feature-from-address-city'] ) ? '' : $_POST['core-shipping-feature-from-address-city'];
		$data['state']             = empty( $_POST['core-shipping-feature-from-address-state'] ) ? '' : $_POST['core-shipping-feature-from-address-state'];
		$data['country']           = empty( $_POST['core-shipping-feature-from-address-country'] ) ? '' : $_POST['core-shipping-feature-from-address-country'];
		$data['zip']               = empty( $_POST['core-shipping-feature-from-address-zip'] ) ? '' : $_POST['core-shipping-feature-from-address-zip'];

		$this->update_value( $data );
	}

	function update_value( $new_value ) {
		update_post_meta( $this->product->ID, '_it_exchange_override_from_address', $new_value );
	}

	/**
	 * Prints the interior of the feature box in the add/edit product view
	*/
	function print_add_edit_feature_box_interior() {
		?>
		<div class="core-shipping-feature-from-address">
        <ul>
            <li>
                <label id="core-shipping-feature-override-from-address-label" for="core-shipping-feature-override-from-address">
                    <input type="checkbox" id="core-shipping-feature-override-from-address" name="core-shipping-feature-override-from-address" <?php checked( ! empty( $this->values->override_defaults ) ); ?> /> <?php _e( 'Override shipping from address for this product?', 'it-l10n-ithemes-exchange' ); ?>
                </label>
			</li>
			<ul class="core-shipping-feature-from-address-ul <?php echo empty( $this->values->override_defaults ) ? 'hidden' : ''; ?>">
				<li><input type="text" name="core-shipping-feature-from-address-address1" value="<?php esc_attr_e( $this->values->address1 ); ?>" placeholder="<?php esc_attr_e( __( 'Address 1', 'it-l10n-ithemes-exchange' ) ); ?>" /></li>
				<li><input type="text" name="core-shipping-feature-from-address-address2" value="<?php esc_attr_e( $this->values->address2 ); ?>" placeholder="<?php esc_attr_e( __( 'Address 2', 'it-l10n-ithemes-exchange' ) ); ?>" /></li>
				<li><input type="text" name="core-shipping-feature-from-address-city" value="<?php esc_attr_e( $this->values->city ); ?>" placeholder="<?php esc_attr_e( __( 'City', 'it-l10n-ithemes-exchange' ) ); ?>" /></li>
				<li>
					<select name="core-shipping-feature-from-address-country">
						<?php foreach( it_exchange_get_data_set( 'countries' ) as $value => $country ) : ?>
							<option value="<?php esc_attr_e( $value ); ?>" <?php selected( $value, $this->values->country ); ?>><?php echo $country; ?></option>
						<?php endforeach; ?>
					</select>
				</li>
				<li>
					<?php
					$states = it_exchange_get_data_set( 'states', array( 'country' => $this->values->country ) );
					if ( ! empty ( $states ) ) :
					?>
						<select name="core-shipping-feature-from-address-state">
							<?php foreach( $states as $value => $state ) : ?>
								<option value="<?php esc_attr_e( $value ); ?>" <?php selected( $value, $this->values->state ); ?>><?php echo $state; ?></option>
							<?php endforeach; ?>
						</select>
					<?php else : ?>
						<input type="text" name="core-shipping-feature-from-address-state" value="<?php esc_attr_e( $this->values->state ); ?>" placeholder="<?php esc_attr_e( __( 'State', 'it-l10n-ithemes-exchange' ) ); ?>" />
					<?php endif; ?>
				</li>
				<li><input type="text" name="core-shipping-feature-from-address-zip" value="<?php esc_attr_e( $this->values->zip ); ?>" placeholder="<?php esc_attr_e( __( 'Zip', 'it-l10n-ithemes-exchange' ) ); ?>" /></li>
			</ul>
		</div>
		<?php
	}
}
