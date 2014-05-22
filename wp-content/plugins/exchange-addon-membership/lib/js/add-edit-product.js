jQuery(document).ready(function($) {
	contentAccessRulesSortable();

	$( '.it-exchange-membership-content-access-add-new-rule' ).live( 'click', function( event ) {
		event.preventDefault();
		var parent = $( this ).parent();
		var data = {
			'action': 'it-exchange-membership-addon-add-content-access-rule',
			'count':  it_exchange_membership_addon_content_access_iteration,
		}
		it_exchange_membership_addon_content_access_iteration++;
		$.post( ajaxurl, data, function( response ) {
			$( '.it-exchange-content-access-list' ).removeClass('hidden');
			$( '.it-exchange-content-no-rules ' ).addClass('hidden');
			$( '.it-exchange-membership-addon-content-access-rules' ).append( response );
		});
	});
	
	$( '.it-exchange-membership-content-type-selections' ).live( 'change', function() { //not working with .on
		var parent = $( this ).parent().parent();
		var data = {
			'action':  'it-exchange-membership-addon-content-type-terms',
			'type':    $( 'option:selected', this ).attr( 'data-type' ),
			'value':   $( 'option:selected', this ).val(),
			'count':   $( parent ).attr( 'data-count' ),
		}
		if ( data['type'] ) { //Only call AJAX if we have a content type
			$.post( ajaxurl, data, function( response ) {
				$( '.it-exchange-membership-content-type-terms', parent ).removeClass( 'hidden' );
				$( '.it-exchange-membership-content-type-terms', parent ).html( response );
				if ( 'posts' === data['type'] ) {
					$( '.it-exchange-membership-content-type-drip', parent ).removeClass( 'hidden' );
					$( '.it-exchange-content-access-delay-unavailable', parent ).addClass( 'hidden' );
				} else {
					$( '.it-exchange-membership-content-type-drip', parent ).addClass( 'hidden' );
					$( '.it-exchange-content-access-delay-unavailable', parent ).removeClass( 'hidden' );
				}
			});
		} else {
			$( '.it-exchange-membership-content-type-terms', parent ).addClass( 'hidden' );
			$( '.it-exchange-membership-content-type-drip', parent ).addClass( 'hidden' );
			$( '.it-exchange-content-access-delay-unavailable', parent ).addClass( 'hidden' );
		}
	});
	
	$( '.it-exchange-membership-addon-remove-content-access-rule, .it-exchange-membership-addon-remove-content-access-group' ).live('click', function( event ) { //not working with .on
		event.preventDefault();
		$( this ).closest( '.it-exchange-membership-addon-content-access-rule' ).remove();
		if ( !$.trim( $( '.it-exchange-membership-addon-content-access-rules' ).html() ) ) {
			$( '.it-exchange-content-access-list' ).addClass('hidden');
			$( '.it-exchange-content-no-rules ' ).removeClass('hidden');
		}
	});
	
	$( '.it-exchange-membership-addon-ungroup-content-access-group' ).live('click', function( event ) {
		event.preventDefault();
		var group_parent = $( this ).closest( '.it-exchange-membership-addon-content-access-rule' );
		var grouped_items = $( group_parent ).find( '.it-exchange-membership-content-access-group-content > .it-exchange-membership-addon-content-access-rule' );
		grouped_items.each( function( index, item ) {
			$( '.it-exchange-content-access-group', item ).val( '' );
			$( item ).insertAfter( group_parent );
		});
		$( group_parent ).remove();
	});
	
	function contentAccessRulesSortable() {
		$( '.content-access-sortable' ).sortable({
			placeholder: 'it-exchange-membership-addon-sorting-placeholder',
			connectWith: '.content-access-sortable',
			items: '.it-exchange-membership-addon-content-access-rule',
			stop: function(event, ui) {
				if ( false !== ( grouped_id = ui.item.parent().data( 'group-id' ) ) ) {
					$( ui.item ).children( '.it-exchange-content-access-group' ).each( function( index, child ) {
						$( child ).val( grouped_id );
					})
				} else {
					$( ui.item ).children( '.it-exchange-content-access-group' ).each( function( index, child ) {
						$( child ).val( '' );
					})
				}
		    },
		    receive: function(event, ui) {
                 //show empty message on sender if applicable
                 if( $( '.it-exchange-membership-addon-content-access-rule', ui.sender ).length == 0 ){
					$( '.nosort', ui.sender ).slideDown();
                 } else {
					$( '.nosort', ui.sender ).slideUp();
                 }            
             }
		});
	}
		
	$( '.it-exchange-membership-content-access-add-new-group' ).live( 'click', function( event ) {
		event.preventDefault();
		var parent = $( this ).parent();
		var data = {
			'action': 'it-exchange-membership-addon-add-content-access-group',
			'count':  it_exchange_membership_addon_content_access_iteration,
			'group_count':  it_exchange_membership_addon_content_access_group_iteration,
		}
		it_exchange_membership_addon_content_access_iteration++;
		it_exchange_membership_addon_content_access_group_iteration++;
		$.post( ajaxurl, data, function( response ) {
			$( '.it-exchange-content-access-list' ).removeClass('hidden');
			$( '.it-exchange-content-no-rules ' ).addClass('hidden');
			$( '.it-exchange-membership-addon-content-access-rules' ).append( response );
			contentAccessRulesSortable();
		});
	});
	
	$( '.group-layout' ).live( 'click', function( event ) {
		var parent = $( this ).parent();
		type = $( this ).data( 'type' );
		$( 'span', parent ).removeClass( 'active-group-layout' );
		$( this ).addClass( 'active-group-layout' );
		$( 'input.group-layout-input', parent ).val( type );
	});
	
	$( '.it-exchange-membership-addon-group-action-wrapper' ).live({
		mouseenter: function() {
			$( this ).children('.it-exchange-membership-addon-group-actions').show();
		}, 
		mouseleave: function() {
			$( this ).children('.it-exchange-membership-addon-group-actions').hide();
		}
	});
	
	function it_exchange_membership_hierarchy_duplicate_check( product_id ) {
		var found = false;
		$( 'div.it-exchange-membership-child-ids-list-div ul li' ).each( function() {
			if ( $( this ).data( 'child-id' ) == product_id ) {
		        alert( 'Already a child of this membership' );
				found = true;
		        return;       
		    }
		});
		if ( !found ) {
			$( 'div.it-exchange-membership-parent-ids-list-div ul li' ).each( function() {
				if ( $( this ).data( 'parent-id' ) == product_id ) {
			        alert( 'Already a parent of this membership' );
					found = true;
			        return;         
			    }
			});
		}
		return found;
	}
	
	$( '.it-exchange-membership-hierarchy-add-child a' ).on('click', function( event ) {
		event.preventDefault();
		var parent = $( this ).parent();
		var data = {
			'action':     'it-exchange-membership-addon-add-membership-child',
			'product_id': $( '.it-exchange-membership-child-id option:selected' ).val(),
			'child_ids':  $( 'input[name=it-exchange-membership-child-ids\\[\\]]' ).serializeArray(),
			'post_id':    $( 'input[name=post_ID]' ).val(),
		}
		if ( 0 !== data['product_id'].length ) {
			var found = it_exchange_membership_hierarchy_duplicate_check( data['product_id'] );
			
			if ( !found ) {
				$.post( ajaxurl, data, function( response ) {
					$( 'ul li', response ).each( function() {
						child_id = $( this ).data( 'child-id' );
						$( 'div.it-exchange-membership-parent-ids-list-div ul li' ).each( function() {
							if ( $( this ).data( 'parent-id' ) == child_id ) {
						        alert( 'Already a parent of this membership' );
						        found = true;
						        return;
						    }
						});
					});
					
					if ( !found )
						$( '.it-exchange-membership-child-ids-list-div' ).html( response );
				});
			}
		}
	});
	
	$( '.it-exchange-membership-hierarchy-add-parent a' ).on('click', function( event ) {
		event.preventDefault();
		var parent = $( this ).parent();
		var data = {
			'action':     'it-exchange-membership-addon-add-membership-parent',
			'product_id': $( '.it-exchange-membership-parent-id option:selected' ).val(),
			'parent_ids': $( 'input[name=it-exchange-membership-parent-ids\\[\\]]' ).serializeArray(),
			'post_id':    $( 'input[name=post_ID]' ).val(),
		}
		if ( 0 !== data['product_id'].length ) {
			var found = it_exchange_membership_hierarchy_duplicate_check( data['product_id'] );
	
			if ( !found ) {
				$.post( ajaxurl, data, function( response ) {
					$( '.it-exchange-membership-parent-ids-list-div' ).html( response );
				});
			}
		}
	});
	
	$( '.it-exchange-membership-addon-delete-membership-child, .it-exchange-membership-addon-delete-membership-parent' ).live('click', function( event ) {
		event.preventDefault();
		$( this ).closest( 'li' ).remove();
	});
	
});