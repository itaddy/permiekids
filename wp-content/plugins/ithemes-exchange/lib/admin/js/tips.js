(function(it_exchange_dialog) {

	it_exchange_dialog(window.jQuery, window, document);

	}(function($, window, document) {
		$(function() {
			$( '.tip' ).tooltip({
				items: "[data-tip-content], [title]",
				content:  function() {
					var element = $( this );

					if ( element.is( '[data-tip-content]' ) ) {
						return element.attr( 'data-tip-content' );
					}
					if ( element.is( '[title]' ) ) {
						return element.attr( 'title' );
					}

					return 'This tooltip is broken. Please add content to the attribute \'data-tip-content\' for this tip.';
				}
			});

			$( '#wpcontent' ).on( 'mouseover', '.it-exchange-tip', function() {
				if ( ! $( this ).attr( 'data-tip-content' ) )
					return;

				if ( $( 'body' ).hasClass( 'it-exchange-dialog-open' ) )
					return;

				var element = $( this );

				$( 'body' ).addClass( 'it-exchange-dialog-open' );

				element.parent().append('<div class="it-exchange-dialog"></div>').find( '.it-exchange-dialog' ).html( $( this ).attr( 'data-tip-content' ) );

				element.parent().find( '.it-exchange-dialog' ).dialog({
					appendTo: element.parent(),
					draggable: false,
					dialogClass: 'it-exchange-tip-dialog',
					resizable: false,
					title: false,
					minHeight: 'none',
					width: 'auto',
					closeText: false,
					position: {
						my: 'left top-15',
						at: 'left bottom',
						of: $( this )
					},
					show: {
						effect: 'fadeIn',
						duration: 400
					},
					hide: {
						effect: 'fadeOut',
						duration: 400
					},
					open: function( event, ui ) {
						$( '.it-exchange-tip-dialog' ).on( 'mouseleave', function() {
							$( event.target ).dialog( 'close' );
						});
					},
					close: function( event, ui ) {
						$( '.tip' ).tooltip();
						$( 'body' ).removeClass( 'it-exchange-dialog-open' );
						$( this ).remove();
					}
				});
			});
		});
	})
);
