jQuery( document ).ready( function() {
	jQuery( '.it-tooltip' ).tooltip( {
		delay:    0,
		fade:     250,
		showBody: " |:|~| ",
		showURL:  false,
		track:    true,
	} );
	
	jQuery( '.it-tooltip-left' ).tooltip( {
		delay:        0,
		fade:         250,
		positionLeft: true,
		showBody:     " |:|~| ",
		showURL:      false,
		track:        true,
	} );
} );
