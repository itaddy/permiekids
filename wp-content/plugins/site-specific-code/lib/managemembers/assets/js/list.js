/**
 *
 * @package site-specific-code
 * @subpackage
 * @since
 */
jQuery( document ).ready( function ( $ ) {
    $( "#start_date" ).datepicker( {
        numberOfMonths: 1,
        prevText: "",
        nextText: "",
        dateFormat: $( '#start_date' ).data( 'date-format' ),
        onClose: function ( selectedDate ) {
            $( "#end_date" ).datepicker( "option", "minDate", selectedDate );
        }
    } );
    $( "#end_date" ).datepicker( {
        numberOfMonths: 1,
        prevText: "",
        nextText: "",
        dateFormat: $( '#end_date' ).data( 'date-format' ),
        onClose: function ( selectedDate ) {
            $( "#start_date" ).datepicker( "option", "maxDate", selectedDate );
        }
    } );
} );