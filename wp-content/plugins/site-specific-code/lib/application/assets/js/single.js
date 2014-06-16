/**
 *
 * @package LDMW
 * @subpackage Application Approval
 * @since 1.0
 */
jQuery( document ).ready( function ( $ ) {
    postboxes.add_postbox_toggles( pagenow );
    var reject = $( '#reject' );
    var reject_modal = $( "#incomplete_notes_modal" );

    reject.click( function ( e ) {
        if ( $( "#incomplete_notes" ).val().length <= 0 ) {
            e.preventDefault();
            reject_modal.modal( {
                fadeDuration: 250,
                fadeDelay: 0.50
            } );
        }
    } );

    var take_action = $( ".division-assessment" );
    var take_action_modal = $( '#note_modal' );

    take_action.click( function ( e ) {
        if ( $( "#notes" ).val().length <= 0 ) {
            e.preventDefault();

            take_action_modal.modal( {
                fadeDuration: 250,
                fadeDelay: 0.50
            } );
        }
    } );

    var file_frame;
    var file_container = $( "#file-container" );

    $( '#add-file_button' ).on( 'click', function ( event ) {

        event.preventDefault();

        // If the media frame already exists, reopen it.
        if ( file_frame ) {
            file_frame.open();
            return;
        }

        // Create the media frame.
        file_frame = wp.media.frames.file_frame = wp.media( {
            title: "Upload additional attachments",
            button: {
                text: jQuery( this ).data( 'uploader_button_text' )
            },
            multiple: true
        } );

        // When an image is selected, run a callback.
        file_frame.on( 'select', function () {
            file_frame.state().get( 'selection' ).each( function ( element ) {
                var attachment = element.toJSON();

                var label = "<label for='attachment-" + attachment.id + "'>" + attachment.filename + "</label>";
                var input = "<input type='text' id='attachment-" + attachment.id + "'name='new_files[" + attachment.filename + "]' value='" + attachment.url + "' >";

                file_container.append( label ).append( input );
            } );
        } );

        // Finally, open the modal
        file_frame.open();
    } );
} );