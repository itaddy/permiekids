/**
 *
 * @package site-specific-code
 * @subpackage
 * @since
 */
jQuery( document ).ready( function ( $ ) {
    var datepicker = $( ".datepicker" );

    datepicker.each( function () {
        $( this ).datepicker( {
            prevText: "",
            nextText: "",
            dateFormat: $( this ).data( 'date-format' )
        } );
    } )

} );