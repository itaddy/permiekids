<?php
/**
 * iThemes Exchange Membership Add-on
 * @package IT_Exchange_Addon_Membership
 * @since 1.0.0
*/

/**
 * Creates a shortcode that returns content template parts for pages
 *
 * @since 1.0.0
 *
 * @param array $atts attributes passed in via shortcode arguments
 * @return string the shortcode content
*/
function it_exchange_membership_addon_add_included_content_shortcode( $atts ) {
	global $post;
	
	if ( empty( $post->ID ) )
		return;
	
	$membership_settings = it_exchange_get_option( 'addon_membership' );
	
	$defaults = array(
		'product_id'         => $post->ID,
		'before'             => '<div class="it-exchange-restricted-content">',
		'after'              => '</div>',
		'title'              => '',
		'toggle'             => $membership_settings['memberships-group-toggle'],
		'posts_per_grouping' => 5,
		'show_drip'          => 'on',
		'show_drip_time'     => 'on',
		'show_icon'          => 'on',
		'layout'             => $membership_settings['memberships-dashboard-view'],
		'child_description'  => '<p class="description">' . sprintf( __( '(Included with %s)', 'it-l10n-exchange-addon-membership' ), get_the_title( $post->ID ) ) . '</p>',
	);
	$atts = shortcode_atts( $defaults, $atts );
		
	$product_type = it_exchange_get_product_type( $atts['product_id'] );
			
	if ( 'membership-product-type' === $product_type ) {
	
		$result = '';
		$parent_title = get_the_title( $atts['product_id'] );
		$all_access = it_exchange_membership_addon_setup_recursive_member_access_array( array( $atts['product_id'] => '' ) );
		$now = time();
		
		if ( !empty( $all_access ) ) {
			$count = 0;
		
			foreach( $all_access as $product_id => $ignore ) {
				$count++;
				
				$rules = it_exchange_get_product_feature( $product_id, 'membership-content-access-rules' );
	
				if ( !empty( $rules ) ) {
		
					$result .= '<div class="it-exchange-membership-membership-content">';
					
					if ( !empty( $atts['title'] ) )
						$result .= '<h4>' . $atts['title'] . '</h4>';
					
					if ( 1 < count( $all_access ) )
						$result .= '<h3>' . get_the_title( $product_id ) . '</h4>';

					if ( 2 <= $count )
						$result .= $atts['child_description'];

					$result .= '<div class="it-exchange-content-wrapper it-exchange-content-' . $atts['layout'] . ' it-exchange-clearfix">'; 
					
		            $groupings = array();
					
					foreach ( $rules as $rule ) {
					
						$restricted_posts = array();
						$selection    = !empty( $rule['selection'] )    ? $rule['selection'] : false;
						$selected     = !empty( $rule['selected'] )     ? $rule['selected'] : false;
						$value        = !empty( $rule['term'] )         ? $rule['term'] : false;
						$group        = isset( $rule['group'] )         ? $rule['group'] : NULL;
						$group_id     = isset( $rule['group_id'] )      ? $rule['group_id'] : false;
						$grouped_id   = isset( $rule['grouped_id'] )    ? $rule['grouped_id'] : false;
														
						if ( !empty( $groupings ) && $grouped_id !== end( $groupings ) ) {
							$result .= '</ul>'; //this is ending the uls from the group opening
							$result .= '</div>'; //this is ending the divs from the group opening
							array_pop( $groupings );
						
						} else if ( false === $grouped_id && !empty( $groupings ) ) {
										
							foreach( $groupings as $group ) {
								$result .= '</ul>'; //this is ending the uls from the group opening
								$result .= '</div>'; //this is ending the divs from the group opening
							}
							$groupings = array();
						
						}
							
						if ( false !== $group_id ) {
						
							$group_layout = !empty( $rule['group_layout'] ) ? $rule['group_layout'] : 'grid';
		
							if ( 'true' == $atts['toggle'] )
								$result .= '<div class="it-exchange-content-group it-exchange-content-group-toggle it-exchange-content-group-layout-' . $group_layout . '">';
							else
								$result .= '<div class="it-exchange-content-group it-exchange-content-group-layout-' . $group_layout . '">';
							
							if ( 'true' == $atts['toggle'] ) {
								$result .= '<p class="it-exchange-group-content-label"><span class="it-exchange-group-title">' . $group . '</span><span class="it-exchange-open-group"></span></p>';
								$result .= '<ul class="it-exchange-hidden">';
							} else {
								$result .= '<p class="it-exchange-group-content-label"><span class="it-exchange-group-title">' . $group . '</span></p>';
								$result .= '<ul>';
							}
						
						} else if ( !empty( $selected ) ) {
							
							switch ( $selected ) {
								
								case 'taxonomy':
									$term = get_term_by( 'id', $value, $selection );
									$label = $term->name;
									$args = array(
										'posts_per_page' => $atts['posts_per_grouping'],
										'tax_query' => array(
											array(
												'taxonomy' => $selection,
												'field' => 'id',
												'terms' => $value
											)
										)
									);
									$restricted_posts = get_posts( $args );
									break;
								
								case 'post_types':
									$post_type = get_post_type_object( $value );
									$label = $post_type->labels->name;
									$args = array(
										'post_type'      => $value,
										'posts_per_page' => $atts['posts_per_grouping'],
									);
									$restricted_posts = get_posts( $args );
									break;
									
								case 'posts':
									$label = '';
									$args = array(
										'p'         => $value,
										'post_type' => 'any',
									);
									$restricted_posts = get_posts( $args );
									break;
								
							}
							
							if ( !empty( $restricted_posts ) ) {
								$result .= $atts['before'];	
								
								if ( !empty( $label ) ) {
									// We're in a group.
									$group_layout = !empty( $rule['group_layout'] ) ? $rule['group_layout'] : 'grid';
			
									if ( 'true' == $atts['toggle'] )
										$result .= '<div class="it-exchange-content-group it-exchange-content-group-toggle it-exchange-content-group-layout-' . $group_layout . '">';
									else
										$result .= '<div class="it-exchange-content-group it-exchange-content-group-layout-' . $group_layout . '">';
									
									if ( 'true' == $atts['toggle'] ) {
										$result .= '<p class="it-exchange-group-content-label"><span class="it-exchange-group-title">' . $label . '</span><span class="it-exchange-open-group"></span></p>';
										$result .= '<ul class="it-exchange-hidden">';
									} else {
										$result .= '<p class="it-exchange-group-content-label"><span class="it-exchange-group-title">' . $label . '</span></p>';
										$result .= '<ul>';
									}
									
									foreach( $restricted_posts as $restricted_post ) {
										$result .= '<li>';
										$result .= '	<div class="it-exchange-content-group it-exchange-content-single">';
										$result .= '		<div class="it-exchange-content-item-icon"><span class="it-exchange-item-icon"></span></div>';
										$result .= '		<div class="it-exchange-content-item-info"><p class="it-exchange-group-content-label">' . get_the_title( $restricted_post->ID ) . '</p></div>';
										$result .= '	</div>';
										$result .= '</li>';
									}
									
									if ( $atts['posts_per_grouping'] <= count( $restricted_posts ) )
										$result .= '<li class="it-exchange-content-more">' . __( 'And More Content In This Group', 'it-l10n-exchange-addon-membership' ) . '</li>';
									
									$result .= '</ul>';
									$result .= '</div>';
								} else {
									foreach( $restricted_posts as $restricted_post ) { //should just be a regular post
										
										$drip_label = '';
									
										if ( 0 < $interval = get_post_meta( $restricted_post->ID, '_item-content-rule-drip-interval-' . $atts['product_id'], true ) ) {
											
											if ( 'on' !== $atts['show_drip'] )
												continue;
											
											if ( 'on' === $atts['show_drip_time'] ) {
												$duration = get_post_meta( $restricted_post->ID, '_item-content-rule-drip-duration-' . $atts['product_id'], true );
												$duration = !empty( $duration ) ? $duration : 'days';
												
												$now = strtotime( 'midnight', time() );
												$dripping = strtotime( $interval . ' ' . $duration, $now );
												$earliest_drip = $dripping - $now;
												$drip_label = ' <span class="it-exchange-restricted-content-drip-label">(' . sprintf( __( 'available in %s days', 'it-l10n-exchange-addon-membership' ), ceil( $earliest_drip / 60 / 60 / 24 ) ) . ')</span>';
											}
											
										}
										
										$result .= '<li>';	
										$result .= '<div class="it-exchange-content-group it-exchange-content-single it-exchange-content-available">';
										$result .= '	<div class="it-exchange-content-item-icon"><span class="it-exchange-item-icon"></span></div>';
										$result .= '	<div class="it-exchange-content-item-info"><p class="it-exchange-group-content-label">' . get_the_title( $restricted_post->ID ) . '</p></div>';
										$result .= '</div>';
										$result .= '</li>';
									}
								}
								
								$result .= $atts['after'];
							}
						
						}
										
						if ( false !== $group_id && !in_array( $group_id, $groupings ) )
							$groupings[] = $group_id;
					
					}
					
					$result .= '</div></div>';
					
					if ( !empty( $groupings ) ) {
						foreach( $groupings as $group ) {
							$result .= '</div>'; //this is ending the divs from the group opening in it_exchange_membership_addon_build_content_rule()
						}
						$groupings = array();
					}
				}
				
			}
			return $result;
		
		}
		
	}
	return false;
}
add_shortcode( 'it-exchange-membership-included-content', 'it_exchange_membership_addon_add_included_content_shortcode' );

/**
 * Creates a shortcode that hides/displays member content
 *
 * @since 1.0.18 
 *
 * @param array $atts attributes passed in via shortcode arguments
 * @param string $content current content
 * @return string the shortcode content
*/
function it_exchange_membership_addon_member_content_shortcode( $atts, $content = null ) {	
	$membership_settings = it_exchange_get_option( 'addon_membership' );
	
	$defaults = array(
		'membership_ids'      => 0,
	);
	$atts = shortcode_atts( $defaults, $atts );
	extract( $atts );
	$membership_ids = explode( ',', $membership_ids );
	
	if ( is_user_logged_in() ) {
		$member_access = it_exchange_get_session_data( 'member_access' );		
		if ( !empty( $member_access )  ) {
			foreach( $member_access as $product_id => $txn_id ) {
				if ( in_array( $product_id, $membership_ids ) )
					return $content;
			}
		}
	} 
	
	return '';
}
add_shortcode( 'it-exchange-member-content', 'it_exchange_membership_addon_member_content_shortcode' );
