<?php
/**
 * Member Dashboard class for THEME API in Membership Add-on
 *
 * @since 1.0.0 
*/

class IT_Theme_API_Membership_Product implements IT_Theme_API {
	
	/**
	 * API context
	 * @var string $_context
	 * @since 1.0.0 
	*/
	private $_context = 'membership-product';

	/**
	 * Current product in iThemes Exchange Global
	 * @var object $product
	 * @since 0.4.0
	*/
	private $product;
	
	/**
	 * Maps api tags to methods
	 * @var array $_tag_map
	 * @since 1.0.0 
	*/
	public $_tag_map = array(
		'intendedaudience' => 'intended_audience',
		'objectives'       => 'objectives',
		'prerequisites'    => 'prerequisites',
		'upgradedetails'   => 'upgrade_details',
		'downgradedetails' => 'downgrade_details',
	);

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 *
	 * @return void
	*/
	function IT_Theme_API_Membership_Product() {
		// Set the current global product as a property
		$this->product = empty( $GLOBALS['it_exchange']['product'] ) ? false : $GLOBALS['it_exchange']['product'];
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
	function intended_audience( $options=array() ) {
		$result = '';
		$membership_settings = it_exchange_get_option( 'addon_membership' );
		
		$defaults      = array(
			'before'       => '',
			'after'        => '',
			'label'        => $membership_settings['membership-intended-audience-label'],
			'before_label' => '<h3>',
			'after_label'  => '</h3>',
			'before_desc'  => '<p>',
			'after_desc'   => '</p>',
		);
		$options = ITUtility::merge_defaults( $options, $defaults );
				
		// Return boolean if has flag was set
		if ( $options['supports'] )
			return it_exchange_product_supports_feature( $this->product->ID, 'membership-information' );

		// Return boolean if has flag was set
		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->product->ID, 'membership-information', array( 'setting' => 'intended-audience' ) );

		// Repeats checks for when flags were not passed.
		if ( it_exchange_product_supports_feature( $this->product->ID, 'membership-information' )	
				&& it_exchange_product_has_feature( $this->product->ID, 'membership-information', array( 'setting' => 'intended-audience' ) ) ) {
			
			$description = it_exchange_get_product_feature( $this->product->ID, 'membership-information', array( 'setting' => 'intended-audience' ) );
			
			if ( !empty( $description ) ) {
			
				$result .= $options['before'];
				$result .= $options['before_label'] . $options['label'] . $options['after_label'];
				$description = wpautop( $description );
				$description = shortcode_unautop( $description );
				$description = do_shortcode( $description );
				$result .= $options['before_desc'] . $description . $options['after_desc'];
				$result .= $options['after'];
				
			}
			
		}
		
		return $result;
	}

	/**
	 * @since 1.0.0
	 * @return string
	*/
	function objectives( $options=array() ) {
		$result = '';
		$membership_settings = it_exchange_get_option( 'addon_membership' );
		
		$defaults      = array(
			'before'       => '',
			'after'        => '',
			'label'        => $membership_settings['membership-objectives-label'],
			'before_label' => '<h3>',
			'after_label'  => '</h3>',
			'before_desc'  => '<p>',
			'after_desc'   => '</p>',
		);
		$options = ITUtility::merge_defaults( $options, $defaults );
				
		// Return boolean if has flag was set
		if ( $options['supports'] )
			return it_exchange_product_supports_feature( $this->product->ID, 'membership-information' );

		// Return boolean if has flag was set
		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->product->ID, 'membership-information', array( 'setting' => 'objectives' ) );

		// Repeats checks for when flags were not passed.
		if ( it_exchange_product_supports_feature( $this->product->ID, 'membership-information' )	
				&& it_exchange_product_has_feature( $this->product->ID, 'membership-information', array( 'setting' => 'objectives' ) ) ) {
			
			$description = it_exchange_get_product_feature( $this->product->ID, 'membership-information', array( 'setting' => 'objectives' ) );
			
			if ( !empty( $description ) ) {
			
				$result .= $options['before'];
				$result .= $options['before_label'] . $options['label'] . $options['after_label'];
				$description = wpautop( $description );
				$description = shortcode_unautop( $description );
				$description = do_shortcode( $description );
				$result .= $options['before_desc'] . $description . $options['after_desc'];
				$result .= $options['after'];
				
			}
			
		}
		
		return $result;
	}

	/**
	 * @since 1.0.0
	 * @return string
	*/
	function prerequisites( $options=array() ) {
		$result = '';
		$membership_settings = it_exchange_get_option( 'addon_membership' );
		
		$defaults      = array(
			'before'       => '',
			'after'        => '',
			'label'        => $membership_settings['membership-prerequisites-label'],
			'before_label' => '<h3>',
			'after_label'  => '</h3>',
			'before_desc'  => '<p>',
			'after_desc'   => '</p>',
		);
		$options = ITUtility::merge_defaults( $options, $defaults );
				
		// Return boolean if has flag was set
		if ( $options['supports'] )
			return it_exchange_product_supports_feature( $this->product->ID, 'membership-information' );

		// Return boolean if has flag was set
		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->product->ID, 'membership-information', array( 'setting' => 'prerequisites' ) );

		// Repeats checks for when flags were not passed.
		if ( it_exchange_product_supports_feature( $this->product->ID, 'membership-information' )	
				&& it_exchange_product_has_feature( $this->product->ID, 'membership-information', array( 'setting' => 'prerequisites' ) ) ) {
			
			$description = it_exchange_get_product_feature( $this->product->ID, 'membership-information', array( 'setting' => 'prerequisites' ) );
			
			if ( !empty( $description ) ) {
			
				$result .= $options['before'];
				$result .= $options['before_label'] . $options['label'] . $options['after_label'];
				$description = wpautop( $description );
				$description = shortcode_unautop( $description );
				$description = do_shortcode( $description );
				$result .= $options['before_desc'] . $description . $options['after_desc'];
				$result .= $options['after'];
				
			}
			
		}
		
		return $result;
	}
	
	/**
	 * @since CHANGEME
	 * @return string
	*/
	function upgrade_details( $options=array() ) {
		$result = '';
		$membership_settings = it_exchange_get_option( 'addon_membership' );
		
		$defaults      = array(
			'before'       => '',
			'after'        => '',
			'before_desc'  => '<p class="description">',
			'after_desc'   => '</p>',
		);
		$options = ITUtility::merge_defaults( $options, $defaults );
				
		// Return boolean if has flag was set
		if ( $options['supports'] )
			return it_exchange_product_supports_feature( $this->product->ID, 'membership-hierarchy' );

		// Return boolean if has flag was set
		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->product->ID, 'membership-hierarchy', array( 'setting' => 'children' ) );

		// Repeats checks for when flags were not passed.
		if ( it_exchange_product_supports_feature( $this->product->ID, 'membership-hierarchy' )	
				&& it_exchange_product_has_feature( $this->product->ID, 'membership-hierarchy', array( 'setting' => 'children' ) ) ) {
				
			$child_ids = it_exchange_membership_addon_setup_recursive_member_access_array( array( $this->product->ID => '' ) );
									
			if ( !empty( $child_ids ) ) {
				$base_price = it_exchange_get_product_feature( $this->product->ID, 'base-price' );
				$db_product_price = it_exchange_convert_to_database_number( $base_price );
				$most_priciest = 0;
				$most_priciest_txn_id = 0;
				
				$parent_memberships = it_exchange_get_session_data( 'parent_access' );
								
				foreach ( $parent_memberships as $txn_id => $parent_id ) {
					if ( $parent_id != $this->product->ID && isset( $child_ids[$parent_id]  ) ) {
						$product = it_exchange_get_product( $parent_id );
						$parent_product_base_price = it_exchange_get_product_feature( $parent_id, 'base-price' );
						$db_price = it_exchange_convert_to_database_number( $parent_product_base_price );
						if ( $db_price > $most_priciest ) {
							$most_priciest = $db_price;
							$most_priciest_txn_id = $txn_id;
							$most_producty = $product;
						}
					}
				}
				
				if ( !empty( $most_priciest_txn_id ) ) {
					
					if ( it_exchange_product_has_feature( $most_producty->ID, 'recurring-payments', array( 'setting' => 'time' ) ) ) {
						$existing_membership_time = it_exchange_get_product_feature( $most_producty->ID, 'recurring-payments', array( 'setting' => 'time' ) );
						$existing_auto_renew = it_exchange_get_product_feature( $most_producty->ID, 'recurring-payments', array( 'setting' => 'auto-renew' ) );
					} else {
						$existing_membership_time = 'forever';
						$existing_auto_renew = false;
					}
					
					if ( it_exchange_product_has_feature( $this->product->ID, 'recurring-payments', array( 'setting' => 'time' ) ) ) {
						$upgrade_membership_time = it_exchange_get_product_feature( $this->product->ID, 'recurring-payments', array( 'setting' => 'time' ) );
						$upgrade_auto_renew = it_exchange_get_product_feature( $most_producty->ID, 'recurring-payments', array( 'setting' => 'auto-renew' ) );

					} else {
						$upgrade_membership_time = 'forever';
						$upgrade_auto_renew = false;
					}
					
					if ( !( 'forever' === $existing_membership_time && 'forever' !== $upgrade_membership_time ) ) {
						//forever upgrade to non-forever Products need to be process manually (see notes below)
						$days_this_year = date_i18n( 'z', mktime( 0,0,0,12,31,date_i18n('Y') ) );

						//Try to get the latest child, if one exists
						//children should only exist for auto-renewing products
						$args = array( 
						    'post_parent' => $most_priciest_txn_id,
						    'post_type'   => 'it_exchange_tran',
						    'numberposts' => 1,
						    'post_status' => 'any'
						);
						$children = get_posts( $args );
												
						if ( !empty( $children ) ) {
							$transaction = it_exchange_get_transaction( $children[0]->ID );
							//IF we have children, we know we're auto-renewing
							//so we can just grab the cart_details total as the last payment
							$last_payment = $transaction->cart_details->total;
						} else {
							$transaction = it_exchange_get_transaction( $most_priciest_txn_id );
							//We don't know if we're auto-renewing
							foreach( $transaction->cart_details->products as $key => $product ) {
								if ( $product['product_id'] === $most_producty->ID ) {
									$last_payment = $product['product_base_price'];
									break;
								}
							}
							//Just in case they used a coupon
							if ( $transaction->cart_details->total < $product['product_subtotal'] )
								$last_payment = $transaction->cart_details->total;
							
						}
						
						$post_date = strtotime( $transaction->post_date );
						$todays_date = time();

						if ( 0 === $last_payment ) {
							//get upgrade details if they exist
							//and possibly quit
							$credit = $transaction->get_transaction_meta( 'credit' );
							$free_days = $transaction->get_transaction_meta( 'free_days' );
							
							if ( !empty( $credit ) && !empty( $free_days ) ) {
								$date_diff = ( $post_date + ( $free_days * 60*60 ) ) - $todays_date;
								$days = max( floor( $date_diff / (60*60*24) ), 0 );
							
								$daily_cost_of_existing_membership = $credit / $free_days;
								$next_payment_date = strtotime( '+' . $free_days . ' Days', strtotime( $transaction->post_date ) );
								
								$remaining_days = max( floor( ( $next_payment_date - $todays_date ) / (60*60*24) ), 0 );
							} else {
								return;
							}
						} else {
							switch( $existing_membership_time ) {
								case 'monthly':
									$daily_cost_of_existing_membership = apply_filters( 'daily_cost_of_existing_to_monthly_membership', ( $last_payment * 12 ) / $days_this_year, $base_price, $days_this_year, $this->product->ID, $transaction );
									$next_payment_date = strtotime( '+1 Month', strtotime( $transaction->post_date ) );
									break;
									
								case 'yearly':
									$daily_cost_of_existing_membership = apply_filters( 'daily_cost_of_existing_to_yearly_membership', $last_payment / $days_this_year, $base_price, $days_this_year, $this->product->ID, $transaction );
									$next_payment_date = strtotime( '+1 Year', strtotime( $transaction->post_date ) );
									break;
									
								case 'forever':
									$daily_cost_of_existing_membership = apply_filters( 'daily_cost_of_existing_to_forever_membership', $last_payment / $days_this_year, $base_price, $days_this_year, $this->product->ID, $transaction );
									$next_payment_date = 0;
									break;
							}
							
							$remaining_days = max( floor( ( $next_payment_date - $todays_date ) / (60*60*24) ), 0 );
						}
						
						if ( 0 < $remaining_days ) {
						
							$credit = $remaining_days * $daily_cost_of_existing_membership;
								
							switch( $upgrade_membership_time ) {
								case 'monthly':
									$daily_cost_of_upgrade_membership = apply_filters( 'daily_cost_of_upgrade_to_monthly_membership', ( $base_price * 12 ) / $days_this_year, $base_price, $days_this_year, $this->product->ID, $transaction );
									break;
									
								case 'yearly':
									$daily_cost_of_upgrade_membership = apply_filters( 'daily_cost_of_upgrade_to_yearly_membership', $base_price / $days_this_year, $base_price, $days_this_year, $this->product->ID, $transaction );
									break;
									
								case 'forever': //treat a "forever" upgrade as a "year", gives the customer a small discount
									$daily_cost_of_upgrade_membership = apply_filters( 'daily_cost_of_upgrade_to_forever_membership', $base_price / $days_this_year, $base_price, $days_this_year, $this->product->ID, $transaction );
									break;
							}
							$free_days = max( floor( $credit / $daily_cost_of_upgrade_membership ), 0 );
								
							$transaction_method = it_exchange_get_transaction_method( $transaction->ID );
							
							if ( 0 < $free_days ) {
								$upgrade_type = false;
								
								if ( 'yes' === $upgrade_auto_renew || 'on' === $upgrade_auto_renew ) {
									$day_string = __( 'day', 'it-l10n-exchange-addon-membership' );
									if ( 1 < $free_days )
										$day_string = __( 'days', 'it-l10n-exchange-addon-membership' );
														
									$result = $options['before_desc'] . sprintf( __( ' %s %s free, then regular price', 'it-l10n-exchange-addon-membership' ), $free_days, $day_string ) . $options['after_desc'];
									$upgrade_type = 'days';
								} else if ( $credit < $base_price ) {
									$result = $options['before_desc'] . sprintf( __( ' %s upgrade credit, then regular price', 'it-l10n-exchange-addon-membership' ), it_exchange_format_price( $credit )  ) . $options['after_desc'];
									$upgrade_type = 'credit';
								}
								
								//For cancelling, I need to get the subscription ID and payment method
								//And since I've done all this hard work, I should store the other pertinent information
								$upgrade_details = it_exchange_get_session_data( 'updowngrade_details' );
								$upgrade_details[$this->product->ID] = array(
									'credit'                 => $credit,
									'free_days'              => $free_days,
									'old_transaction_method' => $transaction->transaction_method,
									'old_transaction_id'     => $most_priciest_txn_id,
									'upgrade_type'           => $upgrade_type,
								);
								
								if ( it_exchange_product_has_feature( $most_producty->ID, 'recurring-payments', array( 'setting' => 'auto-renew' ) ) ) {
									if ( 'on' === it_exchange_get_product_feature( $most_producty->ID, 'recurring-payments', array( 'setting' => 'auto-renew' ) ) ) {
										$upgrade_details[$this->product->ID]['old_subscriber_id'] = $transaction->get_transaction_meta( 'subscriber_id' );
									}
								}
								it_exchange_update_session_data( 'updowngrade_details', $upgrade_details );
							} else {
								//no free days, just upgrade!
								return;
							}
							
						} else {
							//no free days, just upgrade!
							return;
						}
						
					} else {
						//If the existing membership is forever and they're wanting to upgrade
						//to a recurring membership, we cannot give them any upgrade options
						//they will need to purchase the recurring membership and ask for a credit from
						//the store owner.
						//
						//The reason for this is that it is too complicated to determine the "daily" cost
						//of a "forever" membership.
						return;
					}
					
				}
				
			}
			
		}
		
		return $result;
	}
	
	/**
	 * @since CHANGEME
	 * @return string
	*/
	function downgrade_details( $options=array() ) {
		$result = '';
		$membership_settings = it_exchange_get_option( 'addon_membership' );
		
		$defaults      = array(
			'before'         => '',
			'after'          => '',
			'before_desc'    => '<p class="description">',
			'after_desc'     => '</p>',
		);
		$options = ITUtility::merge_defaults( $options, $defaults );
				
		// Return boolean if has flag was set
		if ( $options['supports'] )
			return it_exchange_product_supports_feature( $this->product->ID, 'membership-hierarchy' );

		// Return boolean if has flag was set
		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->product->ID, 'membership-hierarchy', array( 'setting' => 'parents' ) );

		// Repeats checks for when flags were not passed.
		if ( it_exchange_product_supports_feature( $this->product->ID, 'membership-hierarchy' )	
				&& it_exchange_product_has_feature( $this->product->ID, 'membership-hierarchy', array( 'setting' => 'parents' ) ) ) {
			
			$parent_access_session = it_exchange_get_session_data( 'parent_access' );
			
			if ( !empty( $parent_access_session ) && !in_array( $this->product->ID, $parent_access_session )
				&& false !== $most_parent = it_exchange_membership_addon_get_most_parent_from_member_access( $this->product->ID, $parent_access_session ) ) {
				
				$base_price = it_exchange_get_product_feature( $this->product->ID, 'base-price' );
				$db_product_price = it_exchange_convert_to_database_number( $base_price );
				$most_priciest = 0;
				$most_priciest_txn_id = 0;
				
				$transaction_ids = array_keys( $parent_access_session, $most_parent );
				$parent_product_base_price = it_exchange_get_product_feature( $most_parent, 'base-price' );
				$most_priciest = it_exchange_convert_to_database_number( $parent_product_base_price );
				$most_priciest_txn_id = array_shift( $transaction_ids );
				$most_producty = it_exchange_get_product( $most_parent );
				
				if ( !empty( $most_priciest_txn_id ) ) {
					
					if ( it_exchange_product_has_feature( $most_producty->ID, 'recurring-payments', array( 'setting' => 'time' ) ) ) {
						$existing_membership_time = it_exchange_get_product_feature( $most_producty->ID, 'recurring-payments', array( 'setting' => 'time' ) );
						$existing_auto_renew = it_exchange_get_product_feature( $most_producty->ID, 'recurring-payments', array( 'setting' => 'auto-renew' ) );
					} else {
						$existing_membership_time = 'forever';
						$existing_auto_renew = false;
					}
					
					if ( it_exchange_product_has_feature( $this->product->ID, 'recurring-payments', array( 'setting' => 'time' ) ) ) {
						$upgrade_membership_time = it_exchange_get_product_feature( $this->product->ID, 'recurring-payments', array( 'setting' => 'time' ) );
						$upgrade_auto_renew = it_exchange_get_product_feature( $most_producty->ID, 'recurring-payments', array( 'setting' => 'auto-renew' ) );
					} else {
						$upgrade_membership_time = 'forever';
						$upgrade_auto_renew = false;
					}
					
					if ( !( 'forever' === $existing_membership_time && 'forever' !== $upgrade_membership_time ) ) {
						//forever upgrade to non-forever Products need to be process manually (see notes below)
						$days_this_year = date_i18n( 'z', mktime( 0,0,0,12,31,date_i18n('Y') ) );

						//Try to get the latest child, if one exists
						//children should only exist for auto-renewing products
						$args = array( 
						    'post_parent' => $most_priciest_txn_id,
						    'post_type'   => 'it_exchange_tran',
						    'numberposts' => 1,
						    'post_status' => 'any'
						);
						$children = get_posts( $args );
												
						if ( !empty( $children ) ) {
							$transaction = it_exchange_get_transaction( $children[0]->ID );
							//IF we have children, we know we're auto-renewing
							//so we can just grab the cart_details total as the last payment
							$last_payment = $transaction->cart_details->total;
						} else {
							$transaction = it_exchange_get_transaction( $most_priciest_txn_id );
							//We don't know if we're auto-renewing
							foreach( $transaction->cart_details->products as $key => $product ) {
								if ( $product['product_id'] === $most_producty->ID ) {
									$last_payment = $product['product_base_price'];
									break;
								}
							}
							//Just in case they used a coupon
							if ( $transaction->cart_details->total < $product['product_subtotal'] )
								$last_payment = $transaction->cart_details->total;
							
						}
						
						$post_date = strtotime( $transaction->post_date );
						$todays_date = time();
						
						if ( 0 === $last_payment ) {
							//get upgrade details if they exist
							//and possibly quit
							$credit = $transaction->get_transaction_meta( 'credit' );
							$free_days = $transaction->get_transaction_meta( 'free_days' );

							if ( !empty( $credit ) && !empty( $free_days ) ) {
								$date_diff = ( $post_date + ( $free_days * 60*60 ) ) - $todays_date;
								$days = max( floor( $date_diff / (60*60*24) ), 0 );
								
								$daily_cost_of_existing_membership = $credit / $free_days;
								$next_payment_date = strtotime( '+' . $free_days . ' Days', strtotime( $transaction->post_date ) );
								
								$remaining_days = max( floor( ( $next_payment_date - $todays_date ) / (60*60*24) ), 0 );
							} else {
								return;
							}
						} else {
							switch( $existing_membership_time ) {
								case 'monthly':
									$daily_cost_of_existing_membership = apply_filters( 'daily_cost_of_existing_to_monthly_membership', ( $last_payment * 12 ) / $days_this_year, $base_price, $days_this_year, $this->product->ID, $transaction );
									$next_payment_date = strtotime( '+1 Month', strtotime( $transaction->post_date ) );
									break;
									
								case 'yearly':
									$daily_cost_of_existing_membership = apply_filters( 'daily_cost_of_existing_to_yearly_membership', $last_payment / $days_this_year, $base_price, $days_this_year, $this->product->ID, $transaction );
									$next_payment_date = strtotime( '+1 Year', strtotime( $transaction->post_date ) );
									break;
									
								case 'forever':
									$daily_cost_of_existing_membership = apply_filters( 'daily_cost_of_existing_to_forever_membership', $last_payment / $days_this_year, $base_price, $days_this_year, $this->product->ID, $transaction );
									$next_payment_date = 0;
									break;
							}
							
							$remaining_days = max( floor( ( $next_payment_date - $todays_date ) / (60*60*24) ), 0 );
						}
						
						if ( 0 < $remaining_days ) {
							
							$credit = $remaining_days * $daily_cost_of_existing_membership;
							
							switch( $upgrade_membership_time ) {
								case 'monthly':
									$daily_cost_of_upgrade_membership = apply_filters( 'daily_cost_of_upgrade_to_monthly_membership', ( $base_price * 12 ) / $days_this_year, $base_price, $days_this_year, $this->product->ID, $transaction );
									break;
									
								case 'yearly':
									$daily_cost_of_upgrade_membership = apply_filters( 'daily_cost_of_upgrade_to_yearly_membership', $base_price / $days_this_year, $base_price, $days_this_year, $this->product->ID, $transaction );
									break;
									
								case 'forever': //treat a "forever" upgrade as a "year", gives the customer a small discount
									$daily_cost_of_upgrade_membership = apply_filters( 'daily_cost_of_upgrade_to_forever_membership', $base_price / $days_this_year, $base_price, $days_this_year, $this->product->ID, $transaction );
									break;
							}
							$free_days = max( floor( $credit / $daily_cost_of_upgrade_membership ), 0 );
								
							$transaction_method = it_exchange_get_transaction_method( $transaction->ID );
							
							if ( 0 < $free_days ) {
								$upgrade_type = false;

								if ( 'yes' === $upgrade_auto_renew || 'on' === $upgrade_auto_renew ) {
									$day_string = __( 'day', 'it-l10n-exchange-addon-membership' );
									if ( 1 < $free_days )
										$day_string = __( 'days', 'it-l10n-exchange-addon-membership' );
														
									$result = $options['before_desc'] . sprintf( __( ' %s %s free, then regular price', 'it-l10n-exchange-addon-membership' ), $free_days, $day_string ) . $options['after_desc'];
									$upgrade_type = 'days';
								} else if ( $credit < $base_price ) {
									$result = $options['before_desc'] . sprintf( __( ' %s downgrade credit, then regular price', 'it-l10n-exchange-addon-membership' ), it_exchange_format_price( $credit ) ) . $options['after_desc'];
									$upgrade_type = 'credit';
								}
								
								//For cancelling, I need to get the subscription ID and payment method
								//And since I've done all this hard work, I should store the other pertinent information
								$upgrade_details = it_exchange_get_session_data( 'updowngrade_details' );
								$upgrade_details[$this->product->ID] = array(
									'credit'                 => $credit,
									'free_days'              => $free_days,
									'old_transaction_method' => $transaction->transaction_method,
									'old_transaction_id'     => $most_priciest_txn_id,
									'upgrade_type'           => $upgrade_type,
								);
								
								if ( it_exchange_product_has_feature( $most_producty->ID, 'recurring-payments', array( 'setting' => 'auto-renew' ) ) ) {
									if ( 'on' === it_exchange_get_product_feature( $most_producty->ID, 'recurring-payments', array( 'setting' => 'auto-renew' ) ) ) {
										$upgrade_details[$this->product->ID]['old_subscriber_id'] = $transaction->get_transaction_meta( 'subscriber_id' );
									}
								}
								it_exchange_update_session_data( 'updowngrade_details', $upgrade_details );	
							} else {
								//no free days, just downgrade!
								return;
							}
							
						} else {
							//no free days, just downgrade!
							return;
						}
												
					} else {
						//If the existing membership is forever and they're wanting to downgrade
						//to a recurring membership, we cannot give them any downgrade options
						//they will need to purchase the recurring membership and ask for a credit from
						//the store owner.
						//
						//The reason for this is that it is too complicated to determine the "daily" cost
						//of a "forever" membership.
						return;
					}
					
				}
				
			}
			/**/
			
		}
		
		return $result;
	}
}
