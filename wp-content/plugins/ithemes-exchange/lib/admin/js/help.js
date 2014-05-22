jQuery( document ).ready( function($) {
	$( '.help-tip' ).tooltip({
		position: {
			my: "left+15 center",
			at: "right center"
		}
	});

	$( '.help-wrap' ).on( 'click', '.help-action', function() {
		window.open( $( this ).find( 'a' ).attr( 'href' ), $( this ).find( 'a' ).attr( 'target' ) );
	});

	// $( '.remove-if-js' ).remove();
});