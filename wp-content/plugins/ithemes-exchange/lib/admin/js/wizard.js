jQuery( document ).ready( function($) {
	$( '.tip' ).tooltip();

	// Product Types
	$( '.product-types' ).on( 'click', 'li', function() {
		if ( $( this ).hasClass( 'membership-product-type-product-option' ) )
			return;

		$( this ).toggleClass( 'selected' );

		$( '.' + $( this ).attr( 'data-toggle' ) ).toggle();
		$( '.' + $( this ).attr( 'data-ships' ) ).toggle();

		if ( $( this ).hasClass( 'product-option' ) ) {

			if ( $(this).hasClass('selected') ) {
				$(this).append( '<input class="enable-' + $( this ).attr( 'product-type' ) + '" type="hidden" name="it-exchange-product-types[]" value="' + $( this ).attr( 'product-type' ) + '" />' );
			} else {
				$( '.enable-' + $( this ).attr( 'product-type' ) ).remove();
			}
			/**
			if ( $( '.' + $( this ).attr( 'data-toggle' ) ).is( ':visible' ) )
				$( '.' + $( this ).attr( 'data-toggle' ) ).append( '<input class="enable-' + $( this ).attr( 'product-type' ) + '" type="hidden" name="it-exchange-product-types[]" value="' + $( this ).attr( 'product-type' ) + '" />' );
			else
				$( '.enable-' + $( this ).attr( 'product-type' ) ).remove();
			*/

		}

	});

	// Membership upsell
	$( '.membership-wizard' ).on( 'click', '.membership-action', function( e ) {
		e.preventDefault();
		window.open( $( this ).find( 'a' ).attr( 'href' ), $( this ).find( 'a' ).attr( 'target' ) );
	});

	// Shipping Methods
	$( '.shipping-types' ).on( 'click', 'li', function() {
		$( this ).toggleClass( 'selected' );

		$( '.' + $( this ).attr( 'data-toggle' ) ).toggle();

		if ( $( this ).hasClass( 'shipping-option' ) ) {

			if ( $(this).hasClass('selected') ) {
				$(this).append( '<input class="enable-' + $( this ).attr( 'shipping-method' ) + '" type="hidden" name="it-exchange-shipping-methods[]" value="' + $( this ).attr( 'shipping-method' ) + '" />' );
			} else {
				$( '.enable-' + $( this ).attr( 'shipping-method' ) ).remove();
			}

			/**
			if ( $( '.' + $( this ).attr( 'data-toggle' ) ).is( ':visible' ) )
				$( '.' + $( this ).attr( 'data-toggle' ) ).append( '<input class="enable-' + $( this ).attr( 'shipping-method' ) + '" type="hidden" name="it-exchange-shipping-methods[]" value="' + $( this ).attr( 'shipping-method' ) + '" />' );
			else
				$( '.enable-' + $( this ).attr( 'shipping-method' ) ).remove();
			*/

		}

	});

	// Payment Methods
	$( '.payments' ).on( 'click', 'li', function() {
		$( this ).toggleClass( 'selected' );

		$( '.' + $( this ).attr( 'data-toggle' ) ).toggle();

		if ( $( this ).hasClass( 'payoption' ) ) {

			if ( $( '.' + $( this ).attr( 'data-toggle' ) ).is( ':visible' ) )
				$( '.' + $( this ).attr( 'data-toggle' ) ).append( '<input class="enable-' + $( this ).attr( 'transaction-method' ) + '" type="hidden" name="it-exchange-transaction-methods[]" value="' + $( this ).attr( 'transaction-method' ) + '" />' );
			else
				$( '.enable-' + $( this ).attr( 'transaction-method' ) ).remove();

		}

	});

	// Stripe Upsell
	$( '.stripe-wizard' ).on( 'click', '.stripe-action', function( e ) {
		e.preventDefault();
		window.open( $( this ).find( 'a' ).attr( 'href' ), $( this ).find( 'a' ).attr( 'target' ) );
	});

	$( '.remove-if-js' ).remove();
});
