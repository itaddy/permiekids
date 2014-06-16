/**
 *
 * @package site-specific-code
 * @subpackage
 * @since
 */

jQuery( document ).ready( function ( $ ) {
    $( '.resend-receipt' ).click( function ( e ) {
        e.preventDefault();

        var link = $( this );
        link.addClass( 'disabled' );
        link.text( "Sending..." );

        var data = {
            action: 'ldmw_resend_email_receipt',
            resend_email: link.data( 'id' ),
            nonce: link.data( 'nonce' )
        };

        $.post( ldmw.ajaxurl, data, function ( response ) {
            link.text( response );
            link.removeClass( 'disabled' );
        } );
    } );

    $( '.country' ).change( function () {
        var data = {
            action: 'ldmw_country_to_states',
            country: $( this ).val()
        };

        var target = $( this ).data( 'state' );
        target = $( "#" + target );

        $.post( ldmw.ajaxurl, data, function ( response ) {
            if ( false != response ) {
                var countries = JSON.parse( response );

                target.find( 'option' ).remove();

                target.next().remove();
                target.next().remove();

                $.each( countries, function ( key, value ) {
                    target.append( $( "<option></option>" ).attr( "value", key ).text( value ) );
                } );

                target.selectToAutocomplete();

                var auto = target.next().next();

                auto.css( 'display', 'block' );

                if ( auto.length !== 0 ) {
                    auto.focus();
                } else {
                    target.focus();
                }
            }

        } );
    } );

    if ( $.fn.selectToAutocomplete ) {
        $( '.autocomplete' ).selectToAutocomplete();
    }
} );