jQuery( document ).ready( function($) {
	$( '.details-toggle' ).on( 'mouseover', function() {
		$( $( this ).parent() ).find( '.details-toggle' ).css( 'color', '#999' );
	}).on( 'mouseout', function() {
		$( $( this ).parent() ).find( '.details-toggle' ).css( 'color', '#C9C9C9' );
	}).on( 'click', function() {
		$( $( this ).parent().parent().parent() ).find( '.block-column-full' ).toggle();
	});
});
