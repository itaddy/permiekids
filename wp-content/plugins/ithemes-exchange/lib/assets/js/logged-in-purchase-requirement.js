jQuery( function() {
	// Switch to login view when login link is clicked
	jQuery(document).on('click', 'a.it-exchange-login-requirement-login', function(event) {
		event.preventDefault();
		jQuery('.it-exchange-logged-in-purchase-requirement-link-div').removeClass('it-exchange-hidden');
		jQuery('.checkout-purchase-requirement-login-options').addClass( 'it-exchange-hidden');
		jQuery('.checkout-purchase-requirement-registration').addClass('it-exchange-hidden');
		jQuery('.it-exchange-content-checkout-logged-in-purchase-requirement-login-link').addClass('it-exchange-hidden');
		jQuery('.checkout-purchase-requirement-login').removeClass('it-exchange-hidden');
	});

	// Switch to registration view when register link is clicked
	jQuery(document).on('click', 'a.it-exchange-login-requirement-registration', function(event) {
		event.preventDefault();
		jQuery('.it-exchange-logged-in-purchase-requirement-link-div').removeClass('it-exchange-hidden');
		jQuery('.checkout-purchase-requirement-login-options').addClass( 'it-exchange-hidden');
		jQuery('.checkout-purchase-requirement-login').addClass('it-exchange-hidden');
		jQuery('.it-exchange-content-checkout-logged-in-purchase-requirement-register-link').addClass('it-exchange-hidden');
		jQuery('.checkout-purchase-requirement-registration').removeClass('it-exchange-hidden');
	});

	// Switch to login options view when clancel link is clicked
	jQuery(document).on('click', 'a.it-exchange-login-requirement-cancel', function(event) {
		event.preventDefault();
		jQuery('.checkout-purchase-requirement-login-options').removeClass( 'it-exchange-hidden');
		jQuery('.checkout-purchase-requirement-login').addClass('it-exchange-hidden');
		jQuery('.checkout-purchase-requirement-registration').addClass('it-exchange-hidden');
	});
});
