/**
 * This gets loaded on the checkout page.
*/
// Bind to page load
jQuery(document).ready('itExchangeInitPurchaseDialogs');

// Bind our init to the custom jQuery trigger that fires when the Checkout page is reloaded.
jQuery( document).on( 'itExchangeCheckoutReloaded', itExchangeInitPurchaseDialogs);

// Function to init
function itExchangeInitPurchaseDialogs() {
	// Hide all dialogs
	jQuery('.it-exchange-purchase-dialog' ).hide();

	// Open dialogs when triggers are clicked
	jQuery( '.it-exchange-purchase-dialog-trigger' ).on( 'click', function(event) {
		event.preventDefault();
		var addon_slug = jQuery(this).data('addon-slug');
		jQuery('.it-exchange-purchase-dialog-trigger').hide();
		jQuery('form', '.it-exchange-checkout-transaction-methods').hide();
		jQuery('form', '.it-exchange-purchase-dialog-' + addon_slug).show();
		jQuery('.it-exchange-purchase-dialog-' + addon_slug ).show();
	});

	// Open any dialog that has errors, hide the rest of the buttons
	jQuery('.it-exchange-purchase-dialog-trigger').filter('.has-errors').trigger('click');

	// Cancel
	jQuery( '.it-exchange-purchase-dialog-cancel' ).on( 'click', function(event) {
		event.preventDefault();
		jQuery('.it-exchange-purchase-dialog' ).hide();
		jQuery('.it-exchange-purchase-dialog-trigger').show();
		jQuery('form', '.it-exchange-checkout-transaction-methods').show();
	});

	jQuery('#it-exchnage-purchase-dialog-cc-number-for-authorizenet').it_exchange_detect_credit_card_type({ 'element' : '#it-exchnage-purchase-dialog-cc-number-for-authorizenet' });
}

// Finally, since its printed half way through - call it as well.
itExchangeInitPurchaseDialogs();
