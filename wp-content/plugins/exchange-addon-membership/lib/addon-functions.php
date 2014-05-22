<?php
/**
 * iThemes Exchange Membership Add-on
 * @package IT_Exchange_Addon_Membership
 * @since 1.0.0
*/

/**
 * The following file contains utility functions specific to our membership add-on
 * If you're building your own product-type addon, it's likely that you will
 * need to do similar things. This includes enqueueing scripts, formatting data for stripe, etc.
*/

/**
 * Returns HTML w/ selection options for content access rule builder
 *
 * @since 1.0.0
 *
 * @param int $selection current selection (if it exists)
 * @param string $selection_type current selection type (if it exists)
 * @param int $count current row count, used for JavaScript/AJAX
 * @return string HTML output of selections row div
*/
function it_exchange_membership_addon_get_selections( $selection = 0, $selection_type = NULL, $count ) {
	
	$return  = '<div class="it-exchange-content-access-type column"><select class="it-exchange-membership-content-type-selections" name="it_exchange_content_access_rules[' . $count . '][selection]">';
	$return .= '<option value="">' . __( 'Select Content', 'it-l10n-exchange-addon-membership' ) . '</option>';
	
	//Posts
	$hidden_post_types = apply_filters( 'it_exchange_membership_addon_hidden_post_types', array( 'attachment', 'revision', 'nav_menu_item', 'it_exchange_tran', 'it_exchange_coupon', 'it_exchange_prod', 'it_exchange_download' ) );
	$post_types = get_post_types( array(), 'objects' );
	
	foreach ( $post_types as $post_type ) {
		if ( in_array( $post_type->name, $hidden_post_types ) ) 
			continue;
			
		if ( 'posts' === $selection_type && $post_type->name === $selection )
			$selected = 'selected="selected"';
		else
			$selected = '';
			
		$return .= '<option data-type="posts" value="' . $post_type->name . '" ' . $selected . '>' . $post_type->label . '</option>';	
	}
	
	//Post Types
	if ( 'post_types' === $selection_type && 'post_type' === $selection )
		$selected = 'selected="selected"';
	else
		$selected = '';
		
	$return .= '<option data-type="post_types" value="post_type" ' . $selected . '>' . __( 'Post Types', 'it-l10n-exchange-addon-membership' ) . '</option>';
	
	//Taxonomies
	$taxonomies = get_taxonomies( array( 'public' => true ), 'objects' );
	foreach ( $taxonomies as $tax ) {
		// we want to skip post format taxonomies, not really needed here
		if ( 'post_format' === $tax->name )
			continue;
			
		if ( 'taxonomy' === $selection_type && $tax->name === $selection )
			$selected = 'selected="selected"';
		else
			$selected = '';
			
		$return .= '<option data-type="taxonomy" value="' . $tax->name . '" ' . $selected . '>' . $tax->label . '</option>';	
	}	
	$return .= '</select></div>';
	
	return $return;
}

/**
 * Builds the actual content rule HTML
 *
 * @since 1.0.0
 *
 * @param array $rule A Memberships rule
 * @param int $count current row count, used for JavaScript/AJAX
 * @param int $product_id Memberhip's product ID
 * @return string HTML output of selections row div
*/
function it_exchange_membership_addon_build_content_rule( $rule, $count, $product_id ) {

	$options = '';
	
	$selection    = !empty( $rule['selection'] )    ? $rule['selection'] : false;
	$selected     = !empty( $rule['selected'] )     ? $rule['selected'] : false;
	$value        = !empty( $rule['term'] )         ? $rule['term'] : false;
	$group        = isset( $rule['group'] )         ? $rule['group'] : NULL;
	$group_layout = !empty( $rule['group_layout'] ) ? $rule['group_layout'] : 'grid';
	$group_id     = isset( $rule['group_id'] )      ? $rule['group_id'] : NULL;
	$grouped_id   = isset( $rule['grouped_id'] )    ? $rule['grouped_id'] : NULL;
	
	if ( isset( $group ) && isset( $group_id ) )
		$group_class = 'it-exchange-membership-addon-content-access-group';
	else
		$group_class = '';

	$return  = '<div class="it-exchange-membership-addon-content-access-rule ' . $group_class . ' columns-wrapper" data-count="' . $count . '">';
	$return .= '<div class="it-exchange-membership-addon-sort-content-access-rule column col-1_4-12"></div>';

	if ( isset( $group_id ) ) {
							
		$return .= '<input type="text" name="it_exchange_content_access_rules[' . $count . '][group]" value="' . $group . '" />';
		$return .= '<input type="hidden" name="it_exchange_content_access_rules[' . $count . '][group_id]" value="' . $group_id  . '" />';
		
		$return .= '<div class="group-layout-options">';
		$return .= '<span class="group-layout ' . ( 'grid' === $group_layout ? 'active-group-layout' : '' ) . '" data-type="grid">grid</span><span class="group-layout ' . ( 'list' === $group_layout ? 'active-group-layout' : '' ) . '" data-type="list">list</span>';
		$return .= '<input type="hidden" class="group-layout-input" name="it_exchange_content_access_rules[' . $count . '][group_layout]" value="' . $group_layout . '" />';
		$return .= '</div>';
		
		$return .= '<div class="it-exchange-membership-addon-group-action-wrapper">';
		$return .= '<div class="it-exchange-membership-addon-group-action">ACTION</div>';
		$return .= '<div class="it-exchange-membership-addon-group-actions">';
		$return .= '	<div class="it-exchange-membership-addon-ungroup-content-access-group column">';
		$return .= '		<a href="#">' . __( 'Ungroup', 'it-l10n-exchange-addon-membership' ) . '</a>';
		$return .= '	</div>';		
		$return .= '	<div class="it-exchange-membership-addon-remove-content-access-group column">';
		$return .= '		<a href="#">' . __( 'Delete Group', 'it-l10n-exchange-addon-membership' ) . '</a>';
		$return .= '	</div>';
		$return .= '</div>';
		$return .= '</div>';
		
		$return .= '<input type="hidden" class="it-exchange-content-access-group" name="it_exchange_content_access_rules[' . $count . '][grouped_id]" value="' . $grouped_id . '" />';
		
		$return .= '<div class="columns-wrapper it-exchange-membership-content-access-group-content content-access-sortable" data-group-id="' . $group_id . '">';
		//we don't want to end the <div> yet, because the next bunch of rules are grouped under this
		//we only want to end the <div> when a new group_id is set
		//or the div above
					
	} else {
		
		$return .= it_exchange_membership_addon_get_selections( $selection, $selected, $count );
		$return .= '<div class="it-exchange-content-access-content column col-6-12"><div class="it-exchange-membership-content-type-terms">';
		switch( $selected ) {
			
			case 'posts':
				$posts = get_posts( array( 'post_type' => $selection, 'posts_per_page' => -1 ) );
				foreach ( $posts as $post ) {
					$options .= '<option value="' . $post->ID . '" ' . selected( $post->ID, $value, false ) . '>' . get_the_title( $post->ID ) . '</option>';	
				}
				break;
			
			case 'post_types':
				$hidden_post_types = apply_filters( 'it_exchange_membership_addon_hidden_post_types', array( 'attachment', 'revision', 'nav_menu_item', 'it_exchange_tran', 'it_exchange_coupon', 'it_exchange_prod', 'it_exchange_download', 'page' ) );
				$post_types = get_post_types( array(), 'objects' );
				foreach ( $post_types as $post_type ) {
					if ( in_array( $post_type->name, $hidden_post_types ) ) 
						continue;
						
					$options .= '<option value="' . $post_type->name . '" ' . selected( $post_type->name, $value, false ) . '>' . $post_type->label . '</option>';	
				}
				break;
			
			case 'taxonomy':
				$terms = get_terms( $selection, array( 'hide_empty' => false ) );
				foreach ( $terms as $term ) {
					$options .= '<option value="' . $term->term_id . '"' . selected( $term->term_id, $value, false ) . '>' . $term->name . '</option>';	
				}
				break;
			
		}
	
		$return .= '<input type="hidden" value="' . $selected . '" name="it_exchange_content_access_rules[' . $count . '][selected]" />';
		$return .= '<select class="it-exchange-membership-content-type-term" name="it_exchange_content_access_rules[' . $count . '][term]">';
		$return .= $options;
		$return .= '</select>';
				
		if ( 'post_types' === $selected || 'taxonomy' === $selected ) {
			$return .= '<div class="group-layout-options">';
			$return .= '<span class="group-layout ' . ( 'grid' === $group_layout ? 'active-group-layout' : '' ) . '" data-type="grid">grid</span><span class="group-layout ' . ( 'list' === $group_layout ? 'active-group-layout' : '' ) . '" data-type="list">list</span>';
			$return .= '<input type="hidden" class="group-layout-input" name="it_exchange_content_access_rules[' . $count . '][group_layout]" value="' . $group_layout . '" />';
			$return .= '</div>';
		}
		
		$return .= '</div></div>';
		
		if ( 'posts' === $selected ) {
			$drip_hidden = ''; $unavail_hidden = 'hidden';
		} else {
			$drip_hidden = 'hidden'; $unavail_hidden = '';
		}
		$return .= '<div class="it-exchange-content-access-delay column col-3-12 column-reduce-padding">';
		$return .= '<div class="it-exchange-membership-content-type-drip ' . $drip_hidden . '">';
		$return .= it_exchange_membership_addon_build_drip_rules( $rule, $count , $product_id );
		$return .= '</div>';
		$return .= '<div class="it-exchange-content-access-delay-unavailable ' . $unavail_hidden . '">';
		$return .= __( 'Available for single posts or pages', 'it-l10n-exchange-addon-membership' );	
		$return .= '</div>';
		$return .= '</div>';
		
		$return .= '<div class="it-exchange-membership-addon-remove-content-access-rule column col-3_4-12">';
		$return .= '<a href="#">×</a>';	
		$return .= '</div>';
		
		$return .= '<input type="hidden" class="it-exchange-content-access-group" name="it_exchange_content_access_rules[' . $count . '][grouped_id]" value="' . $grouped_id . '" />';
		
		
		$return .= '</div>';
	}
	
	return $return;
	
}

/**
 * Builds the actual drip rule HTML
 *
 * @since 1.0.0
 *
 * @param array $rule A Memberships rule
 * @param int $count current row count, used for JavaScript/AJAX
 * @param int $product_id Memberhip's product ID
 * @return string HTML output of drip rule div
*/
function it_exchange_membership_addon_build_drip_rules( $rule = false, $count, $product_id = false ) {
	
	$return = '';

	if ( !empty( $product_id ) && !empty( $rule['selected'] ) && 'posts' === $rule['selected'] && !empty( $rule['term'] ) )
		$drip_interval = get_post_meta( $rule['term'], '_item-content-rule-drip-interval-' . $product_id, true );
	else
		$drip_interval = 0;
		
	if ( 0 < $drip_interval ) {
		$drip_duration = get_post_meta( $rule['term'], '_item-content-rule-drip-duration-' . $product_id, true );
		$drip_duration = !empty( $drip_duration ) ? $drip_duration : 'days';
	} else {
		$drip_interval = 0;
		$drip_duration = 'days';
	}
	
	$return  .= '<input type="number" min="0" value="' . $drip_interval . '" name="it_exchange_content_access_rules[' . $count . '][drip-interval]" />';
	$return .= '<select class="it-exchange-membership-content-drip-duration" name="it_exchange_content_access_rules[' . $count . '][drip-duration]">';
	$durations = array(
		'days'   => __( 'Days', 'it-l10n-exchange-addon-membership' ),
		'weeks'  => __( 'Weeks', 'it-l10n-exchange-addon-membership' ),
		'months' => __( 'Months', 'it-l10n-exchange-addon-membership' ),
		'years'  => __( 'Years', 'it-l10n-exchange-addon-membership' ),
	);
	$durations = apply_filters( 'it-exchange-membership-drip-durations', $durations );
	foreach( $durations as $key => $string ) {
		$return .= '<option value="' . $key . '"' . selected( $key, $drip_duration, false ) . '>' . $string . '</option>';
	}
	$return .= '</select>';
	
	return $return;
}

/**
 * Builds the actual restriction rule HTML, used for non-iThemes Exchange post types
 *
 * @since 1.0.0
 *
 * @param int $post_id WordPress $post ID
 * @return string HTML output of drip rule div
*/
function it_exchange_membership_addon_build_post_restriction_rules( $post_id ) {
	
	$return = '';
	$rules = array();
	
	$post_type = get_post_type( $post_id );
	
	/*
	* Use get_post_meta() to retrieve an existing value
	* from the database and use the value for the form.
	*/
	$post_rules = get_post_meta( $post_id, '_item-content-rule', true );
	$post_type_rules = get_option( '_item-content-rule-post-type-' . $post_type, array() );
	$taxonomy_rules = array();
	$restriction_exemptions = get_post_meta( $post_id, '_item-content-rule-exemptions', true );
	
	$taxonomies = get_object_taxonomies( $post_type );
	$terms = wp_get_object_terms( $post_id, $taxonomies );
	
	foreach( $terms as $term ) {
		$term_rules = get_option( '_item-content-rule-tax-' . $term->taxonomy . '-' . $term->term_id, array() );
		if ( !empty( $term_rules ) )
			$taxonomy_rules[$term->taxonomy][$term->term_id]  = array_merge( $taxonomy_rules, $term_rules );
	}
		
	//Re-order for output!
	if ( !empty( $post_rules ) ) {
		foreach( $post_rules as $product_id ) {
			$rules[$product_id]['post'] = true;
		}
	}
	if ( !empty( $post_type_rules ) ) {
		foreach( $post_type_rules as $product_id ) {
			$post_type = get_post_type_object( $post_type );
			if ( !empty( $post_type->labels->singular_name ) )
				$name = $post_type->labels->singular_name;
			else if ( !empty( $post_type->labels->name ) )
				$name = $post_type->labels->name;
			else
				$name = $post_type->label;
			$rules[$product_id]['post_type'] = $post_type->labels->singular_name;
		}
	}
	if ( !empty( $taxonomy_rules ) ) {
		foreach( $taxonomy_rules as $taxonomy => $term_rules ) {
			foreach( $term_rules as $term_id => $product_ids ) {
				foreach( $product_ids as $product_id ) {
					$rules[$product_id]['taxonomy'][] = $taxonomy;
					$rules[$product_id][$taxonomy]['term_ids'][] = $term_id;
				}
			}
		}
	}	
	
	$return .= '<div class="it-exchange-membership-restrictions">';
	
	if ( !empty( $rules ) ) {
			
		foreach ( $rules as $membership_id => $rule ) {
			$return .= '<div class="it-exchange-membership-restriction-group">';
			$title = get_the_title( $membership_id );
			$parents = it_exchange_membership_addon_get_all_the_parents( $membership_id );
			$restriction_exception = !empty( $restriction_exemptions[$membership_id] ) ? $restriction_exemptions[$membership_id] : array();
			
			$return .= '<input type="hidden" name="it_exchange_membership_id" value="' . $membership_id . '">';
			
			if ( !empty( $rule['post'] ) && true === $rule['post'] ) {
				$return .= '<div class="it-exchange-membership-rule-post it-exchange-membership-rule">';
				$return .= '<input class="it-exchange-restriction-exceptions" type="checkbox" name="restriction-exceptions[]" value="post" ' . checked( in_array( 'post', $restriction_exception ), false, false ) . '>';
				$return .= $title;
				if ( !empty( $parents ) )
					$return .= '<p class="description">' . sprintf( __( 'Included in: %s', 'it-l10n-exchange-addon-membership' ), join( ', ', array_map( 'get_the_title', $parents ) ) ) . '</p>';
				$return .= '<span class="it-exchange-membership-remove-rule">&times;</span>';
				
				$drip_interval = get_post_meta( $post_id, '_item-content-rule-drip-interval-' . $membership_id, true );				
				
				if ( 0 < $drip_interval ) {
					$drip_duration = get_post_meta( $post_id, '_item-content-rule-drip-duration-' . $membership_id, true );
					$drip_duration = !empty( $drip_duration ) ? $drip_duration : 'days';
					
					if ( !empty( $drip_interval ) && !empty( $drip_duration ) ) {
					
						$return .= '<div class="it-exchange-membership-rule-delay">' . __( 'Delay', 'it-l10n-exchange-addon-membership' ) . '</div>';
						$return .= '<div class="it-exchange-membership-drip-rule">';
						$return .= '<input class="it-exchange-membership-drip-rule-interval" type="number" min="0" value="' . $drip_interval . '" name="it_exchange_membership_drip_interval" />';
						$return .= '<select class="it-exchange-membership-drip-rule-duration" name="it_exchange_membership_drip_duration">';
						$durations = array(
							'days'   => __( 'Days', 'it-l10n-exchange-addon-membership' ),
							'weeks'  => __( 'Weeks', 'it-l10n-exchange-addon-membership' ),
							'months' => __( 'Months', 'it-l10n-exchange-addon-membership' ),
							'years'  => __( 'Years', 'it-l10n-exchange-addon-membership' ),
						);
						$durations = apply_filters( 'it-exchange-membership-drip-durations', $durations );
						foreach( $durations as $key => $string ) {
							$return .= '<option value="' . $key . '"' . selected( $key, $drip_duration, false ) . '>' . $string . '</option>';
						}
						$return .= '</select>';
						$return .= '</div>';
					
					}

				}
				
				$return .= '</div>';
			}
			
			if ( !empty( $rule['post_type'] ) ) {
				$return .= '<div class="it-exchange-membership-rule-post-type it-exchange-membership-rule">';
				$return .= '<input class="it-exchange-restriction-exceptions" type="checkbox" name="restriction-exceptions[]" value="posttype" ' . checked( in_array( 'posttype', $restriction_exception ), false, false ) . '>';
				$return .= $title;
				$return .= '<div class="it-exchange-membership-rule-description">' . $rule['post_type'] . '</div>';
				$return .= '</div>';
			}
			
			if ( !empty( $rule['taxonomy'] ) ) {
				foreach ( $rule['taxonomy'] as $taxonomy ) {
					foreach( $rules[$product_id][$taxonomy]['term_ids'] as $term_id ) {
						$term = get_term_by( 'id', $term_id, $taxonomy );
						$return .= '<div class="it-exchange-membership-rule-post-type it-exchange-membership-rule">';
						$return .= '<input class="it-exchange-restriction-exceptions" type="checkbox" name="restriction-exceptions[]" value="taxonomy|' . $taxonomy . '|' . $term_id . '" ' . checked( in_array( 'taxonomy|' . $taxonomy . '|' . $term_id, $restriction_exception ), false, false ) . '>';
						$return .= $title;
						$return .= '<div class="it-exchange-membership-rule-description">' . ucwords( $taxonomy ) . ' "' .  $term->name . '"</div>';
						$return .= '</div>';
					}
				}
			}
			$return .= '</div>';
		}
	
	} else {
	
		$return .= '<div class="it-exchange-membership-no-restrictions">' . __( 'No membership restrictions for this content.', 'it-l10n-exchange-addon-membership' ) . '</div>';
		
	}
	
	$return .= '</div>';
	
	return $return;
	
}

/**
 * Checks if current content should be restricted
 * if admin - false
 * if member has access - false
 * if post|posttype|taxonomy has rule - true (unless above rule overrides)
 * if exemption exists - true
 *
 * An exemption basically tells the Membership addon that a member who has access to
 * specific content should not have access to it. For instance, say you have a post in 
 * a restricted category and you have two membership levels who have access to that category
 * but you only want that post to be visible to one of the memberships. By adding the
 * exemption for the other membership, they will no longer have access to that content.
 *
 * @since 1.0.0
 *
 * @return bool
*/
function it_exchange_membership_addon_is_content_restricted() {
	global $post;
	$restriction = false;
	
	if ( current_user_can( 'administrator' ) )
		return false;
	
	$member_access = it_exchange_get_session_data( 'member_access' );
		
	$restriction_exemptions = get_post_meta( $post->ID, '_item-content-rule-exemptions', true );
	if ( !empty( $restriction_exemptions ) ) {
		foreach( $member_access as $product_id => $txn_id ) {
			if ( array_key_exists( $product_id, $restriction_exemptions ) )
				$restriction = true; //we don't want restrict yet, not until we know there aren't other memberships that still have access to this content
			else
				continue; //get out of this, we're in a membership that hasn't been exempted
		}
		if ( $restriction ) //if it has been restricted, we can return true now
			return true;
	}
	
	$post_rules = get_post_meta( $post->ID, '_item-content-rule', true );
	if ( !empty( $post_rules ) ) {
		if ( empty( $member_access ) ) return true;
		foreach( $member_access as $product_id => $txn_id ) {
			if ( in_array( $product_id, $post_rules ) )
				return false;	
		}
		$restriction = true;
	}
	
	$post_type_rules = get_option( '_item-content-rule-post-type-' . $post->post_type, array() );	
	if ( !empty( $post_type_rules ) ) {
		if ( empty( $member_access ) ) return true;
		foreach( $member_access as $product_id => $txn_id ) {
			if ( !empty( $restriction_exemptions[$product_id] )  )
				return true;
			if ( in_array( $product_id, $post_type_rules ) )
				return false;	
		}
		$restriction = true;
	}
	
	$taxonomy_rules = array();
	$taxonomies = get_object_taxonomies( $post->post_type );
	$terms = wp_get_object_terms( $post->ID, $taxonomies );
	foreach( $terms as $term ) {
		$term_rules = get_option( '_item-content-rule-tax-' . $term->taxonomy . '-' . $term->term_id, array() );
		if ( !empty( $term_rules ) ) {
			if ( empty( $member_access ) ) return true;
			foreach( $member_access as $product_id => $txn_id ) {
				if ( in_array( $product_id, $term_rules ) )
					return false;	
			}
			$restriction = true;
		}
	}
	
	return $restriction;
}

/**
 * Checks if current content should be dripped
 * if admin - false
 * if member has access - check if content is dripped, otherwise false
 * Dripped content is basically published content that you want to arbitrarily delay for
 * your members. Say you have a class and you want to release 1 class a week to your membership
 * this will allow you to do that. Simply set your content to the appropriate timeline and new members 
 * will have access to the classes based on the set schedule.
 *
 * @since 1.0.0
 *
 * @return bool
*/
function it_exchange_membership_addon_is_content_dripped() {
	global $post;
	$dripped = false;
	
	if ( current_user_can( 'administrator' ) )
		return false;

	$member_access = it_exchange_get_session_data( 'member_access' );

	foreach( $member_access as $product_id => $txn_id  ) {
		$interval = get_post_meta( $post->ID, '_item-content-rule-drip-interval-' . $product_id, true );
		$interval = !empty( $interval ) ? $interval : 0;
		$duration = get_post_meta( $post->ID, '_item-content-rule-drip-duration-' . $product_id, true );
		$duration = !empty( $duration ) ? $duration : 'days';
		if ( 0 < $interval ) {
			$purchase_time = strtotime( 'midnight', get_post_time( 'U', true, $txn_id ) );
			$dripping = strtotime( $interval . ' ' . $duration, $purchase_time );
			$now = time();
			
			if ( $dripping < $now )						
				return false; // we can return here because they should have access to this content with this membership
			else
				$dripped = true; // we don't want to return here, because other memberships might have access to content sooner
		}
	}
	return $dripped;
}

/**
 * Gets the membership currently being viewed from the membership dashboard on the
 * WordPress frontend
 *
 * @since 1.0.0
 *
 * @return mixed object|bool
*/
function it_exchange_membership_addon_get_current_membership() {
	$page_slug = it_exchange_get_page_slug( 'memberships', true );
	if ( $membership_slug = get_query_var( $page_slug ) ) {
		$args = array(
		  'name' => $membership_slug,
		  'post_type' => 'it_exchange_prod',
		  'post_status' => 'publish',
		  'numberposts' => 1
		);
		$posts = get_posts( $args );
		foreach( $posts as $post ) { //should only be one
			return it_exchange_get_product( $post );
		}
	}
	return false;
}

/*
 * Returns membership access rules sorted by selected type
 *
 * @since 1.0.0
 *
 * @param int $membership_product_id
 * @param bool $exclude_exempted (optional) argument to exclude exemptions from access rules (true by default)
 * @return array
*/
function it_exchange_membership_access_rules_sorted_by_selected_type( $membership_product_id, $exclude_exempted=true ) {
	$access_rules = it_exchange_get_product_feature( $membership_product_id, 'membership-content-access-rules' );
	$sorted_access_rules = array();
	
	foreach( $access_rules as $rule ) {
		if ( $exclude_exempted && 'posts' === $rule['selected'] ) {		
			$restriction_exemptions = get_post_meta( $rule['term'], '_item-content-rule-exemptions', true );
			if ( !empty( $restriction_exemptions ) ) {
				if ( array_key_exists( $membership_product_id, $restriction_exemptions ) )
					continue;
			}
		}
		$sorted_access_rules[$rule['selected']][] = array(
			'type' => $rule['selection'],
			'term' => $rule['term'],
		);
	}
	
	return $sorted_access_rules;
}

/*
 * Returns true if product in cart is a membership product
 *
 * @since 1.0.0 
 *
 * @param object cart
 * @return bool
*/
function it_exchange_membership_cart_contains_membership_product( $cart_products = false ) {
	if ( !$cart_products )
		$cart_products = it_exchange_get_cart_products();
	
	foreach ( $cart_products as $product ) {
		if ( 'membership-product-type' === it_exchange_get_product_type( $product['product_id'] ) )
			return true;
	}
	
	return false;
}

/*
 * For hierarchical membership types
 * Finds all the most-parental membership types in the member_access session
 * Used generally to prevent duplicate content from being printed
 * in the member's dashboard
 *
 * @since CHANGEME
 *
 * @param array $membership_products current list of accessible membership products
 * @return array
*/
function it_exchange_membership_addon_setup_most_parent_member_access_array( $membership_products ) {
	$found_ids = array();
	$parent_ids = array();
	foreach( $membership_products as $txn_id => $product_id ) {
		if ( false !== get_post_status( $product_id ) ) {
			if ( false !== $found_id = it_exchange_membership_addon_get_most_parent_from_member_access( $product_id, $membership_products ) ) {
				if ( !in_array( $found_id, $found_ids ) )
					$found_ids[] = $found_id;
			}
		}
	}
	foreach( $found_ids as $found_id ) {
		$txn_keys = array_keys( $membership_products, $found_id );
		if ( !empty( $txn_keys ) )
			$txn_id = array_shift( $txn_keys );
		if ( !empty( $txn_id ) )
			$parent_ids[$txn_id] = $found_id;
	}
	return $parent_ids;
}

/*
 * For hierarchical membership types
 * Get all child membership products and adds it to an array to be used
 * for generating the member_access session
 *
 * @since CHANGEME 
 *
 * @param array $membership_products current list of accessible membership products
 * @param array $product_ids
 * @return array
*/
function it_exchange_membership_addon_setup_recursive_member_access_array( $membership_products, $product_ids = array(), $parent_txn_id=false ) {
	foreach( $membership_products as $product_id => $txn_id ) {
		if ( false !== get_post_status( $product_id ) ) {
			if ( array_key_exists( $product_id, $product_ids ) )
				continue;
				
			if ( !$parent_txn_id )
				$proper_txn_id = $txn_id;
			else
				$proper_txn_id = $parent_txn_id;
				
			$product_ids[$product_id] = $proper_txn_id;
			if ( $child_ids = get_post_meta( $product_id, '_it-exchange-membership-child-id' ) ) {
				$child_ids = array_flip( $child_ids ); //we need the child IDs to be the keys
				$product_ids = it_exchange_membership_addon_setup_recursive_member_access_array( $child_ids, $product_ids, $proper_txn_id );
			}
		}
	}
	return $product_ids;
}

/*
 * Gets the highest level parent from the parent access session for a given product ID
 *
 * @since CHANGEME 
 *
 * @param int $product_id Membership product to check
 * @param array $parent_access Parent access session (or other array)
 * @return array
*/
function it_exchange_membership_addon_get_most_parent_from_member_access( $product_id, $parent_access ) {
	$most_parent = false;
	if ( $childs_parent_ids = get_post_meta( $product_id, '_it-exchange-membership-parent-id' ) ) {
		foreach( $childs_parent_ids as $parent_id ) {
			if ( false !== get_post_status( $parent_id ) ) {
				if ( in_array( $parent_id, $parent_access ) )
					$most_parent = $parent_id; //potentially the most parent, but we need to keep checking!
				
				if ( false !== $found_id = it_exchange_membership_addon_get_most_parent_from_member_access( $parent_id, $parent_access ) )
					$most_parent = $found_id;
			}
		}
	}
	if ( !$most_parent && in_array( $product_id, $parent_access ) ) {
		$most_parent = $product_id;
	}
	return $most_parent;
}

/*
 * For hierarchical membership types
 * Prints or returns an HTML formatted list of memberships and their children
 *
 * @since CHANGEME 
 *
 * @param array $membership_products parent IDs of membership products
 * @param array $args array of arguments for the function
 * @return string|null
*/
function it_exchange_membership_addon_display_membership_hierarchy( $product_ids, $args = array() ) {
	$defaults = array(
		'echo'          => true,
		'delete'        => true,
		'hidden_input'  => true,
	);
	$args = wp_parse_args( $args, $defaults );
	extract( $args );

	$output = '';
	foreach( $product_ids as $product_id ) {
		if ( false !== get_post_status( $product_id ) ) {
			$output .= '<ul>';
			$output .= '<li data-child-id="' . $product_id . '"><div class="inner-wrapper">' . get_the_title( $product_id );
			
			if ( $delete )
				$output .= ' <a href data-membership-id="' . $product_id . '" class="it-exchange-membership-addon-delete-membership-child it-exchange-remove-item">&times;</a>';
				
			if ( $hidden_input ) {
				$output .= ' <input type="hidden" name="it-exchange-membership-child-ids[]" value="' . $product_id . '" />';
			}
			
			$output .= '</div>';
			
			if ( $child_ids = get_post_meta( $product_id, '_it-exchange-membership-child-id' ) ) {
				$output .= it_exchange_membership_addon_display_membership_hierarchy( $child_ids, array( 'echo' => false, 'delete' => false, 'hidden_input' => false ) );
			}
			
			$output .= '</li>';
			$output .= '</ul>';
		}
	}
	
	if ( $echo )
		echo $output;
	else
		return $output;
}

/*
 * For hierarchical membership types
 * Returns an array of all the product's parents
 *
 * @since CHANGEME 
 *
 * @param int $membership_id product ID of membership
 * @param array $parent_ids array of of current parent_ids
 * @return array|bool
*/
function it_exchange_membership_addon_get_all_the_parents( $membership_id, $parent_ids = array() ) {
	$parents = it_exchange_get_product_feature( $membership_id, 'membership-hierarchy', array( 'setting' => 'parents' ) );
	if ( !empty( $parents ) ) {
		foreach( $parents as $parent_id ) {
			if ( false !== get_post_status( $parent_id ) ) {
				$parent_ids[] = $parent_id;
				if ( false !== $results = it_exchange_membership_addon_get_all_the_parents( $parent_id ) )
					$parent_ids = array_merge( $parent_ids, $results );
			}
		}
	} else {
		return false;
	}
	return $parent_ids;
}
