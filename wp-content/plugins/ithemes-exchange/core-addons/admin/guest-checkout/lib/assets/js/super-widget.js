// Register action when guest checkout option is clicked
jQuery(document).on('click', '.it-exchange-super-widget .it-exchange-guest-checkout-link', function(event) {
	event.preventDefault();
	itExchangeGetSuperWidgetState( 'guest-checkout' );
});

// Register Submit button
jQuery(document).on('submit', 'form.it-exchange-guest-checkout-form', function(event) {
	event.preventDefault();
	data = jQuery( ':input', this ).serializeArray();
	itExchangeGuestCheckoutSWRegister(data);
});

// Registers a user as a guest. Logs them if email is already registered
function itExchangeGuestCheckoutSWRegister(formData) {
	jQuery.post( itExchangeSWAjaxURL+'&sw-action=guest-checkout', data, function(data) {
		if ( '0' === data )
			itExchangeGetSuperWidgetState( 'guest-checkout' );
		else
			itExchangeGetSuperWidgetState( 'checkout' );
	});
}

// Change state on cancel
jQuery(document).on('click', '.it-exchange-super-widget a.it-exchange-sw-cancel-guest-checkout-link', function(event) {
	event.preventDefault();
	if ( itExchangeSWMultiItemCart )
		if ( itExchangeSWOnProductPage )
			itExchangeGetSuperWidgetState( 'product', itExchangeSWOnProductPage );
		else
			itExchangeGetSuperWidgetState( 'cart' );
	else
		itExchangeSWEmptyCart( itExchangeSWOnProductPage );
});

/**
 * Processes the guest login
*/
function itExchangeGuestCheckoutSWRegister( data ) {
    jQuery.post( itExchangeSWAjaxURL+'&sw-action=guest-checkout', data, function(data) {
        if ( '0' === data )
            itExchangeGetSuperWidgetState( 'guest-checkout' );
        else
            itExchangeGetSuperWidgetState( 'checkout' );
    });
}
