/**
 * jQuery used by the Shipping Address Purchase Requirement on the Checkout Page
 * @since 1.3.0
*/
jQuery( function() {
	itExchangeInitShippingJSForCheckout();
});

function itExchangeInitShippingJSForCheckout() {
	// Switch to edit address view when link is clicked
	jQuery(document).on('click', 'a.it-exchange-purchase-requirement-edit-shipping', function(event) {
		event.preventDefault();
		jQuery('.checkout-purchase-requirement-shipping-address-options').addClass( 'it-exchange-hidden');
		jQuery('.checkout-purchase-requirement-shipping-address-edit').removeClass('it-exchange-hidden');
	});

	// Switch to existing address view when clancel link is clicked
	jQuery(document).on('click', 'a.it-exchange-shipping-address-requirement-cancel', function(event) {
		event.preventDefault();
		jQuery('.checkout-purchase-requirement-shipping-address-options').removeClass( 'it-exchange-hidden');
		jQuery('.checkout-purchase-requirement-shipping-address-edit').addClass('it-exchange-hidden');
	});

	// Init country state sync
	var iteCountryStatesSyncOptions = {
		statesWrapper:     '.it-exchange-state',
		stateFieldID:      '#it-exchange-shipping-address-state',
		templatePart:      'content-checkout/elements/purchase-requirements/shipping-address/elements/state',
		autoCompleteState: true
	};
	jQuery('#it-exchange-shipping-address-country').itCountryStatesSync(iteCountryStatesSyncOptions);

	// Enable Autocomplete on country and state
	jQuery('#it-exchange-shipping-address-country').selectToAutocomplete();
	jQuery('#it-exchange-shipping-address-state').selectToAutocomplete();

	// Save value and reload checkout page when shipping method is changed
	jQuery(document).on('change', '#it-exchange-cart .it-exchange-shipping-method-select', function(event) {
		event.preventDefault();
		var value = jQuery(this).val();
        jQuery.post(ITExchangeCheckoutRefreshAjaxURL, {'shipping-method':value}, function(response) {
            if (response) {
                jQuery('.entry-content').html(response);
                jQuery.event.trigger({
                    type: "itExchangeCheckoutReloaded"
                });
            }
        });
    });

	// Save value and reload checkout page when multiple methods shipping method is changed for a product
	jQuery(document).on('change', '.it-exchange-multiple-shipping-methods-select', function(event) {
		event.preventDefault();
		var value         = jQuery(this).val();
		var cartProductID = jQuery(this).data('it-exchange-product-cart-id');

        jQuery.post(ITExchangeCheckoutRefreshAjaxURL, {'cart-product-id':cartProductID, 'shipping-method':value}, function(response) {
            if (response) {
                jQuery('.entry-content').html(response);
                jQuery.event.trigger({
                    type: "itExchangeCheckoutReloaded"
                });
            }
        });
    });
}

jQuery(document).on('itExchangeCheckoutReloaded', function() {
	itExchangeInitShippingJSForCheckout();
});
