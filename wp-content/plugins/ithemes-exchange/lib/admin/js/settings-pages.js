jQuery( document ).ready( function( $ ) {
	$( '.tip' ).tooltip();

	$( '.page-type select' ).on( 'change', function() {
		var current_row = $( this ).parent().parent().parent();

		if ( $( this ).val() == 'exchange' ) {
			current_row.find( '.toggle-disabled' ).removeClass( 'hidden' );
			current_row.find( '.ex-page' ).removeClass( 'hidden' );
			current_row.find( '.wp-page' ).addClass( 'hidden' );
		} else if ( $( this ).val() == 'wordpress' ) {
			current_row.find( '.toggle-disabled' ).removeClass( 'hidden' );
			current_row.find( '.wp-page' ).removeClass( 'hidden' );
			current_row.find( '.ex-page' ).addClass( 'hidden' );
		} else if ( $( this ).val() == 'disabled' ) {
			current_row.find( '.toggle-disabled' ).addClass( 'hidden' );
			current_row.find( '.wp-page' ).addClass( 'hidden' );
		}
	});

	// Don't allow form to be saved with WP type but no WP page selected
	$( '#it-exchange-page-settings' ).on( 'submit', function() {
		var wpPageNotSet = false;
		$( '.wp-page-select-span' ).each(function(){
			if ( ! $(this).hasClass('hidden') ) {
				$(this).children('select').each(function(){
					if ( '0' === $(this).find(':selected').val() )
						wpPageNotSet = true;
				});
			}
		});
		if ( wpPageNotSet ) {
			alert( settingsPagesL10n.emptyWPPage );
			event.preventDefault();
		}
	});
});
