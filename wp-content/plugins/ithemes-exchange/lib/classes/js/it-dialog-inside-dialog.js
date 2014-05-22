var it_dialog_update_size = function() {
	var win = window.dialogArguments || opener || parent || top;
	
	if ( 'undefined' != typeof win.it_dialog )
		win.it_dialog.update_size();
};

var it_dialog_remove = function() {
	var win = window.dialogArguments || opener || parent || top;
	
	if ( 'undefined' != typeof win.it_dialog )
		win.it_dialog.remove();
	
	return false;
};

var it_dialog_add_form_submission_message = function( message ) {
	if ( 'undefined' == typeof message )
		message = '';
	
	jQuery('form').submit( function() {
		it_dialog_show_loading_message( message );
	} );
};

var it_dialog_show_loading_message = function( message ) {
	var win = window.dialogArguments || opener || parent || top;
	
	if ( 'undefined' != typeof win.it_dialog ) {
		if ( 'undefined' == typeof message )
			message = '';
		
		win.it_dialog.show_loading_message( message );
	}
};
