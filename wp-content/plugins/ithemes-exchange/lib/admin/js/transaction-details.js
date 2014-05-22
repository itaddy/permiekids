jQuery( document ).ready( function( $ ) {
	$('.handlediv, .hndle').remove();

	$( '.tip' ).tooltip();

	// do transaction status update
	$( '#it-exchange-update-transaction-status' ).on('change', function() {
		var nonce         = $('#it-exchange-update-transaction-nonce').val();
		var currentStatus = $('#it-exchange-update-transaction-current-status').val();
		var newStatus     = $('#it-exchange-update-transaction-status').find(":selected").val();
		var txnID         = $('#it-exchange-update-transaction-id').val();

		var data = {
			'action': 'it-exchange-update-transaction-status',
			'it-exchange-nonce': nonce,
			'it-exchange-current-status': currentStatus,
			'it-exchange-new-status': newStatus,
			'it-exchange-transaction-id': txnID
		}
		$.post( ajaxurl, data, function( response ) {
			$('#it-exchange-update-transaction-status-success, #it-exchange-update-transaction-status-failed').css( 'opacity','0' ).stop();

			$('#it-exchange-update-transaction-status-' + response ).animate({
				opacity: '1'
			}, 500, function() {
				$( this ).delay(2000).animate({
					opacity: '0'
				});
			});
		}).fail( function() {
			$('#it-exchange-update-transaction-status-failed').css( 'opacity','0' ).stop();

			$('#it-exchange-update-transaction-status-failed' ).animate({
				opacity: '1'
			}, 500, function() {
				$( this ).delay(2000).animate({
					opacity: '0'
				});
			});
		});
	});
});
