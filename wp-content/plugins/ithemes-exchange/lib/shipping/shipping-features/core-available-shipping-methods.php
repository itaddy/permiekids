<?php
/**
 * This is the class for our available shipping methods shipping feature
 *
 * @since 1.4.0
*/
class IT_Exchange_Core_Shipping_Feature_Available_Shipping_Methods extends IT_Exchange_Shipping_Feature {

	var $slug = 'core-available-shipping-methods';

	/**
	 * Constructor
	*/
	function __construct( $product=false, $options=array() ) {
		parent::__construct( $product, $options );
	}

	/**
	 * Is this shipping feature available
	 *
	 * @since 1.4.0
	*/
	function set_availability() {
		$options = it_exchange_get_option( 'shipping-general' );
		$this->available = ! empty( $options['products-can-override-available-shipping-methods'] );
	}

	function set_enabled() {
		$pm = get_post_meta( $this->product->ID, '_it_exchange_override_shipping_methods', true );
		$enabled = ! empty( $pm['enabled'] );
		$this->enabled = $this->available && $enabled;
	}

	function update_on_product_save() {
		$post_meta         = get_post_meta( $this->product->ID, '_it_exchange_override_shipping_methods', true );
		$values['enabled'] = ! empty( $_POST['it-exchange-shipping-override-methods'] );

		foreach( it_exchange_get_registered_shipping_methods() as $method => $class ) {
			$current_method = it_exchange_get_registered_shipping_method( $method, $this->product->ID );
			// Set to original
			$values[$method] = empty( $post_meta[$method] ) ? false : $post_meta[$method];

			// Overwrite current pm value if the current method is available
			if ( $current_method->available )
				$values[$method] = ! empty( $_POST['it-exchange-shipping-override-' . $method . '-method'] );
		}
		$this->update_value( $values );
	}

	function update_value( $new_value ) {
		update_post_meta( $this->product->ID, '_it_exchange_override_shipping_methods', $new_value );
	}

	/**
	 * Sets the values
	*/
	function set_values() {
		$post_meta = get_post_meta( $this->product->ID, '_it_exchange_override_shipping_methods', true );
		$values    = new stdClass();

		// Are we overriding defaults?
		$values->override_defaults = $this->enabled;

		// Set available methods for current product
		foreach( it_exchange_get_registered_shipping_methods() as $method => $class ) {

			if ( $this->enabled ) {
				$values->$method = ! empty( $post_meta[$method] );
			} else {
				$current_method = it_exchange_get_registered_shipping_method( $method, $this->product->ID );
				$values->$method = $current_method->enabled;
			}
		}

		$this->values = $values;
	}

    /**
     * Prints the shipping box on the add/edit product page
     *
     * Relies on methods provided by extending classes
     * If the shipping feature isn't availabe to this product, it is hidden
     *
     * @since 1.4.0
     *
     * @return void
    */
    function print_add_edit_feature_box() {
        ?>
        <div class="shipping-feature <?php esc_attr_e( $this->slug ); ?>">
            <?php $this->print_add_edit_feature_box_interior(); ?>
        </div>
        <?php
    }

	/**
	 * Prints the interior of the feature box in the add/edit product view
	*/
	function print_add_edit_feature_box_interior() {
		?>
		<ul>
			<li>
				<?php if ( $this->available ) : ?>
					<label id="it-exchange-shipping-override-methods-label" for="it-exchange-shipping-override-methods">
						<input type="checkbox" id="it-exchange-shipping-override-methods" name="it-exchange-shipping-override-methods" <?php checked( ! empty( $this->enabled ) ); ?> />
						&nbsp;<?php _e( 'Override available shipping methods for this product?', 'it-l10n-ithemes-exchange' ); ?>
					</label>
				<?php endif; ?>
				<span id="it-exchange-avialable-shipping-methods-heading"><?php _e( 'Available Shipping Methods', 'it-l10n-ithemes-exchange' ); ?></span>
				<ul class="core-shipping-overridable-methods <?php echo ( empty( $this->available ) || empty( $this->enabled ) ) ? 'hidden' : ''; ?>">
					<?php
					foreach( it_exchange_get_registered_shipping_methods() as $method => $class ) {
						$current_method = it_exchange_get_registered_shipping_method( $method, $this->product->ID );
						if ( ! $current_method->available )
							continue;
						?>
						<li>
							<label id="it-exchange-shipping-override-<?php esc_attr_e( $method ); ?>-method-label" for="it-exchange-shipping-override-<?php esc_attr_e( $method ); ?>-method">
								<input type="checkbox" id="it-exchange-shipping-override-<?php esc_attr_e( $method ); ?>-method" name="it-exchange-shipping-override-<?php esc_attr_e( $method ); ?>-method" <?php checked( $this->values->$method ); ?>/>
								&nbsp;<?php echo $current_method->label; ?>
							</label>
						</li>
						<?php
					}
					?>
				</ul>
				<ul class="it-exchange-enabled-shipping-methods-for-product<?php echo empty( $this->available ) || empty( $this->enabled ) ? '' : ' hidden'; ?>">
				<?php
				if ( $enabled_methods = it_exchange_get_enabled_shipping_methods_for_product( $this->product ) ) {
					foreach( (array) $enabled_methods as $current_method ) {
						echo '<li>' . $current_method->label . '</li>';
					}
				}else {
						echo '<li>' . __( 'No shipping methods enabled.', 'it-l10n-ithemes-exchange' ) . '</li>';
				}
				?>
				</ul>
			</li>
		</ul>
		<?php
	}
}
