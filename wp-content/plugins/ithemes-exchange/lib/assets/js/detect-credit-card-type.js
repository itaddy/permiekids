/**
 * Detect Credit Card Types
 *
 * This function will detect a credit card type and add a class
 * ot the chosen element.
 *
 * (Re)Written by Justin Kopepasah - kopepasah.com
 *
 * Inspired by Christian Reed's Credit Card Type Detector
 * https://github.com/christianreed/Credit-Card-Type-Detector
*/
(function( $ ) {
	$.fn.it_exchange_detect_credit_card_type = function( options ) {
		var settings = $.extend({
				'element'        : '.card-type',
				'class_prefix'   : 'card',
				// 'accepted_cards' : 'visa,amex,mastercard', // Let's add some other options later.
			}, options ),

			// The element that we should add the classes to.
			element = settings.element,

			// Set the class prefix for the cards.
			class_prefix = settings.class_prefix,

			// Set the regurlar expressions for the card types.
			regex = {
				'visa'       : new RegExp( '^4[0-9]{0,15}$' ),
				'mastercard' : new RegExp( '^5$|^5[1-5][0-9]{0,14}$' ),
				'amex'       : new RegExp( '^3$|^3[47][0-9]{0,13}$' ),
				'diners'     : new RegExp( '^3$|^3[068]$|^3(?:0[0-5]|[68][0-9])[0-9]{0,11}$' ),
				'discover'   : new RegExp( '^6$|^6[05]$|^601[1]?$|^65[0-9][0-9]?$|^6(?:011|5[0-9]{2})[0-9]{0,12}$' ),
				'jbc'        : new RegExp( '^2[1]?$|^21[3]?$|^1[8]?$|^18[0]?$|^(?:2131|1800)[0-9]{0,11}$|^3[5]?$|^35[0-9]{0,14}$' ),
			};

		return this.each( function() {
			// On keyup, check which class to add (if any)
			$( this ).keyup( function() {
				var current_value = $( this ).val();

				// Remove empty spaces and dashes.
				current_value = current_value.replace(/ /g,'').replace(/-/g,'');

				var classes = $( element ).attr( 'class' ).split( /\s+/ );

				// If we have less than two numbers, remove the classses. Otherwise let's run the check.
				if ( current_value.length < 2 ) {
					$.each( classes, function( index, value ) {
						if ( value.match( class_prefix, 'g' ) ) {
							$( element ).removeClass( value );
						}
					});
				} else {
					$.each( regex, function( index, value ) {
						if ( current_value.match( value ) ) {
							$( element ).addClass( class_prefix + '-' + index );
						} else {
							$( element ).removeClass( class_prefix + '-' + index );
						}
					});
				}
			});
		});
	};
})( jQuery );