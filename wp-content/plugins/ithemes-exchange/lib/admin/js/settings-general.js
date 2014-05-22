jQuery( document ).ready( function( $ ) {
	$( 'form#it-exchange-settings' ).submit( function() {
		if ( $( '#reset-exchange' ).is(':checked') ) {
			return confirm( settingsGenearlL10n.delteConfirmationText );
		}
	});

	$( '#company-base-country' ).on( 'change', function() {
		var itExchangeCountryStateData = {};
		itExchangeCountryStateData.ite_base_country_ajax = $(this).val();
		itExchangeCountryStateData.ite_base_state_ajax   = $('company-base-state').val();
		itExchangeCountryStateData.action                = 'ite-country-state-update';

		$.post(ajaxurl, itExchangeCountryStateData, function(response) {
			if ( 0 !== response )
				$('.company-base-state-field-td').html(response);
		});
	});

	$( '#enable-gallery-popup' ).on( 'change', function() {
		if ( $( this ).val() == 1 ) {
			$( '.product-gallery-zoom-actions' ).find( '.popup-enabled' ).removeClass( 'hidden' );
		} else {
			$( '.product-gallery-zoom-actions' ).find( '.popup-enabled' ).addClass( 'hidden' );
		}
	});

	$( '#enable-gallery-zoom' ).on( 'change', function() {
		if ( $( this ).val() == 1 ) {
			$( '.product-gallery-zoom-actions' ).removeClass( 'hidden' );
			$( this ).parent().find( '.tip' ).removeClass( 'hidden' );
		} else {
			$( '.product-gallery-zoom-actions' ).addClass( 'hidden' );
			$( this ).parent().find( '.tip' ).addClass( 'hidden' );
		}
	});
});
