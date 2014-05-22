<?php
/**
 * Member Dashboard class for THEME API in Membership Add-on
 *
 * @since 1.0.0
*/

class IT_Theme_API_Member_Dashboard implements IT_Theme_API {
	
	/**
	 * API context
	 * @var string $_context
	 * @since 1.0.0
	*/
	private $_context = 'member-dashboard';

	/**
	 * Current customer being viewed
	 * @var string $_customer
	 * @since 1.0.0
	*/
	private $_customer = '';
	

	/**
	 * Current membership product being viewed
	 * @var string $_membership_product
	 * @since 1.0.0
	*/
	private $_membership_product = '';

	/**
	 * Maps api tags to methods
	 * @var array $_tag_map
	 * @since 1.0.0
	*/
	public $_tag_map = array(
		'welcomemessage'    => 'welcome_message',
		'membershipcontent' => 'membership_content',
	);

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 *
	 * @return void
	*/
	function IT_Theme_API_Member_Dashboard() {
		if ( is_user_logged_in() )
			$this->_customer = it_exchange_get_current_customer();
			
		$this->_membership_product = it_exchange_membership_addon_get_current_membership();
	}

	/**
	 * Returns the context. Also helps to confirm we are an iThemes Exchange theme API class
	 *
	 * @since 1.0.0
	 * 
	 * @return string
	*/
	function get_api_context() {
		return $this->_context;
	}

	/**
	 * @since 1.0.0
	 * @return string
	*/
	function welcome_message( $options=array() ) {
		
		if ( empty( $this->_membership_product ) )
			return false;
		
		// Return boolean if has flag was set
		if ( $options['supports'] )
			return it_exchange_product_supports_feature( $this->_membership_product->ID, 'membership-welcome-message' );

		// Return boolean if has flag was set
		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->_membership_product->ID, 'membership-welcome-message' );

		// Repeats checks for when flags were not passed.
		if ( it_exchange_product_supports_feature( $this->_membership_product->ID, 'membership-welcome-message' )	
				&& it_exchange_product_has_feature( $this->_membership_product->ID, 'membership-welcome-message' ) ) {
			$result        = false;
			$message       = it_exchange_get_product_feature( $this->_membership_product->ID, 'membership-welcome-message' );
			$defaults      = array(
				'before' => '<div class="entry-content">',
				'after'  => '</div>',
				'title'              => __( 'Welcome', 'it-l10n-exchange-addon-membership' ),
			);
			$options      = ITUtility::merge_defaults( $options, $defaults );
			
			$result .= '<h2>' . $options['title'] . '</h2>';
			$result .= $options['before'];
			$result .= $message;
			$result .= $options['after'];
				
			return $result;
		}
		return false;
	}

	/**
	 * @since 1.0.0
	 * @return string
	*/
	function membership_content( $options=array() ) {
				
		if ( empty( $this->_membership_product ) )
			return false;
			
		// Return boolean if has flag was set
		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->_membership_product->ID, 'membership-content-access-rules' );
			
		// Return boolean if has flag was set
		if ( $options['supports'] )
			return it_exchange_product_supports_feature( $this->_membership_product->ID, 'membership-content-access-rules' );
		
		$count = 0;
		$result = false;
		$product_id = $this->_membership_product->ID;
		$parent_title = get_the_title( $product_id );
		$all_access = it_exchange_membership_addon_setup_recursive_member_access_array( array( $product_id => '' ) );
		$now = time();
		
		if ( !empty( $all_access ) ) {
			$result = '';
			
			$membership_settings = it_exchange_get_option( 'addon_membership' );
			
			$defaults      = array(
				'before'             => '<div class="it-exchange-restricted-content">',
				'after'              => '</div>',
				'title'              => __( 'Membership Content', 'it-l10n-exchange-addon-membership' ),
				'toggle'             => $membership_settings['memberships-group-toggle'],
				'layout'             => $membership_settings['memberships-dashboard-view'],
				'posts_per_grouping' => 5,
				'child_description'  => '<p class="description">' . sprintf( __( '(Included with %s)', 'it-l10n-exchange-addon-membership' ), $parent_title ) . '</p>',
			);
			$options = ITUtility::merge_defaults( $options, $defaults );
		
			foreach( $all_access as $product_id => $ignore ) {
				$count++;
				if ( it_exchange_product_supports_feature( $product_id, 'membership-content-access-rules' )	
						&& it_exchange_product_has_feature( $product_id, 'membership-content-access-rules' ) ) {
							
					$access_rules = it_exchange_get_product_feature( $product_id, 'membership-content-access-rules' );
			
					// Repeats checks for when flags were not passed.
					if ( !empty( $access_rules ) ) {
						if ( 1 === $count )					
							$result .= '<h2>' . $options['title'] . '</h2>';
						
						$result .= '<div class="it-exchange-content-wrapper it-exchange-content-' . $options['layout'] . ' it-exchange-clearfix">'; 
						
						if ( 1 < count( $all_access ) )
							$result .= '<h3>' . get_the_title( $product_id ) . '</h4>';

						if ( 2 <= $count )
							$result .= $options['child_description'];
						
			            $groupings = array();
			            	
						foreach ( $access_rules as $rule ) {
							
							$more_content_link = '';
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
								
								if ( 'true' == $options['toggle'] )
									$result .= '<div class="it-exchange-content-group it-exchange-content-group-toggle it-exchange-content-group-layout-' . $group_layout . '">';
								else
									$result .= '<div class="it-exchange-content-group it-exchange-content-group-layout-' . $group_layout . '">';
			
								if ( 'true' == $options['toggle'] ) {
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
											'posts_per_page' => $options['posts_per_grouping'],
											'tax_query' => array(
												array(
													'taxonomy' => $selection,
													'field' => 'id',
													'terms' => $value
												)
											)
										);
										$restricted_posts = get_posts( $args );
										$more_content_link = get_term_link( $term, $selection );
										break;
									
									case 'post_types':
										$post_type = get_post_type_object( $value );
										$label = $post_type->labels->name;
										$args = array(
											'post_type'      => $value,
											'posts_per_page' => $options['posts_per_grouping'],
										);
										$restricted_posts = get_posts( $args );
										switch( $value ) {
											
											case 'post':
												$more_content_link = get_home_url();
												break;
												
											default:
												$more_content_link = get_post_type_archive_link( $value );
												break;
										}
										break;
										
									case 'posts':
										$label = '';
										$args = array(
											'p'         => $value,
											'post_type' => 'any',
										);
										$restricted_posts = get_posts( $args );
										$more_content_link = '';
										break;
									
								}
								
								if ( !empty( $restricted_posts ) ) {
							
									$result .= $options['before'];	
									
									if ( !empty( $label ) ) {
										// We're in a group.
										$group_layout = !empty( $rule['group_layout'] ) ? $rule['group_layout'] : 'grid';
					
										if ( 'true' == $options['toggle'] )
											$result .= '<div class="it-exchange-content-group it-exchange-content-group-toggle it-exchange-content-group-layout-' . $group_layout . '">';
										else
											$result .= '<div class="it-exchange-content-group it-exchange-content-group-layout-' . $group_layout . '">';				
														
										if ( 'true' == $options['toggle'] ) {
											$result .= '<p class="it-exchange-group-content-label"><span class="it-exchange-group-title">' . $label . '</span><span class="it-exchange-open-group"></span></p>';
											$result .= '<ul class="it-exchange-hidden">';
										} else {
											$result .= '<p class="it-exchange-group-content-label"><span class="it-exchange-group-title">' . $label . '</span></p>';
											$result .= '<ul>';
										}
										
										foreach( $restricted_posts as $post ) {
											$result .= '<li>';
											$result .= '	<div class="it-exchange-content-group it-exchange-content-single">';
											$result .= '		<div class="it-exchange-content-item-icon">';
											$result .= '			<a class="it-exchange-item-icon" href="' .get_permalink( $post->ID ) . '"></a>';
											$result .= '		</div>';
											$result .= '		<div class="it-exchange-content-item-info">';
											$result .= '			<p class="it-exchange-group-content-label">';
											$result .= '				<a href="' . get_permalink( $post->ID ) . '">' . get_the_title( $post->ID ) . '</a>';
											$result .= '			</p>';
											$result .= '		</div>';
											$result .= '	</div>';
											$result .= '</li>';
										}
										
										if ( ! empty( $more_content_link ) && $options['posts_per_grouping'] <= count( $restricted_posts ) )
											$result .= '<li class="it-exchange-content-more"><a href="' . $more_content_link . '">' . __( 'Read More Content In This Group', 'it-l10n-exchange-addon-membership' ) . '</a></li>';
										
										$result .= '</ul>';
										$result .= '</div>';
									} else {
										foreach( $restricted_posts as $post ) { //should just be a regular post
											if ( 0 < $interval = get_post_meta( $post->ID, '_item-content-rule-drip-interval-' . $product_id, true ) ) {
												$duration = get_post_meta( $post->ID, '_item-content-rule-drip-duration-' . $product_id, true );
												$duration = !empty( $duration ) ? $duration : 'days';
												$member_access = it_exchange_get_session_data( 'member_access' );
												if ( !empty( $member_access[$product_id] ) ) {

													$purchase_time = strtotime( 'midnight', get_post_time( 'U', true, $member_access[$product_id] ) );
													$dripping = strtotime( $interval . ' ' . $duration, $purchase_time );
													
													if ( $dripping < $now )	{
														$result .= '<li>';	
														$result .= '<div class="it-exchange-content-group it-exchange-content-single it-exchange-content-available">';
														$result .= '	<div class="it-exchange-content-item-icon">';
														$result .= '		<a class="it-exchange-item-icon" href="' .get_permalink( $post->ID ) . '"></a>';
														$result .= '	</div>';
														$result .= '	<div class="it-exchange-content-item-info">';
														$result .= '		<p class="it-exchange-group-content-label">';
														$result .= '			<a href="' . get_permalink( $post->ID ) . '">';
														$result .= '				<span class="it-exchange-item-title">' . get_the_title( $post->ID ) . '</span>';
														$result .= '			</a>';
														$result .= '		</p>';
														$result .= '	</div>';
														$result .= '</div>';
														$result .= '</li>';
													} else {
														$earliest_drip = $dripping - $now;
														$result .= '<li>';
														$result .= '<div class="it-exchange-content-group it-exchange-content-single it-exchange-content-unavailable">';
														$result .= '	<div class="it-exchange-content-item-icon">';
														$result .= '		<a class="it-exchange-item-icon" href="#"></a>';
														$result .= '	</div>';
														$result .= '	<div class="it-exchange-content-item-info">';
														$result .= '		<p class="it-exchange-group-content-label">';
														$result .= '			<span class="it-exchange-item-unavailable-message it-exchange-right">' . sprintf( __( 'available in %s days', 'it-l10n-exchange-addon-membership' ), ceil( $earliest_drip / 60 / 60 / 24 ) ) . '</span>';
														$result .= '			<span class="it-exchange-item-title">' . get_the_title( $post->ID ) . '</span>';
														$result .= '		</p>';
														$result .= '	</div>';
														$result .= '</div>';
														$result .= '</li>';
													}
												}
												
											} else {
												$result .= '<li>';
												$result .= '<div class="it-exchange-content-group it-exchange-content-single">';
												$result .= '	<div class="it-exchange-content-item-icon">';
												$result .= '		<a class="it-exchange-item-icon" href="' .get_permalink( $post->ID ) . '"></a>';
												$result .= '	</div>';
												$result .= '	<div class="it-exchange-content-item-info">';
												$result .= '		<p class="it-exchange-group-content-label">';
												$result .= '			<a href="' . get_permalink( $post->ID ) . '">';
												$result .= '				<span class="it-exchange-item-title">' . get_the_title( $post->ID ) . '</span>';
												$result .= '			</a>';
												$result .= '		</p>';
												$result .= '	</div>';
												$result .= '</div>';
												$result .= '</li>';
												
											}
										}
									}
									
									$result .= $options['after'];
								
								}
									
							}
							
							if ( false !== $group_id && !in_array( $group_id, $groupings ) )
								$groupings[] = $group_id;
			
						}
						
						if ( !empty( $groupings ) ) {
							foreach( $groupings as $group ) {
								$result .= '</div>'; //this is ending the divs from the group opening in it_exchange_membership_addon_build_content_rule()
							}
							$groupings = array();
						}
						$result .= '</div>';
					}
				
				}
					
			}
		
		}
		return $result;
	}
}
