jQuery( function() {
	// Switch to guest checkout view when guest checkout link is clicked
	jQuery(document).on('click', 'a.it-exchange-login-requirement-guest-checkout', function(event) {
		event.preventDefault();
		jQuery('.it-exchange-logged-in-purchase-requirement-link-div').removeClass('it-exchange-hidden');
		jQuery('.checkout-purchase-requirement-login').addClass( 'it-exchange-hidden');
		jQuery('.checkout-purchase-requirement-registration').addClass('it-exchange-hidden');
		jQuery('.it-exchange-content-checkout-logged-in-purchase-requirement-guest-checkout-link').addClass('it-exchange-hidden');
		jQuery('.checkout-purchase-requirement-guest-checkout').removeClass('it-exchange-hidden');
	});

	jQuery(document).on('click', 'a.it-exchange-button', function(event) {
		event.preventDefault();
		console.log(this);
		if ( ! jQuery(this).hasClass( 'it-exchange-login-requirement-guest-checkout') )
			jQuery('.checkout-purchase-requirement-guest-checkout').addClass('it-exchange-hidden');
	});
});
