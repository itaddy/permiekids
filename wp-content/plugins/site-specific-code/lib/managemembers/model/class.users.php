<?php

/**
 *
 * @package LDMW
 * @subpackage Manage Members
 * @since 1.0
 */
class LDMW_ManageMembers_Model_Users {
	/**
	 * Get array of WP_User objects based on passed args.
	 *
	 * @param array $args
	 *
	 * @return WP_User[]
	 */
	public function get_users( $args = array() ) {
		$defaults = $this->get_users_default_args();

		$args = wp_parse_args( $args, $defaults );

		if ( isset( $args['paid_by'] ) ) {
			$paid_by = $args['paid_by']; // we have to process this data after our initial search.
			unset( $args['paid_by'] );
		}

		$query = new WP_User_Query( $args );
		$results = $query->get_results();

		if ( isset( $paid_by ) ) {
			foreach ( $results as $key => $user ) {
				$transaction_query_args = array(
				  'post_type'  => 'it_exchange_tran',
				  'meta_query' => array(
					'relation' => 'AND',
					array(
					  'key'   => '_it_exchange_customer_id',
					  'value' => $user->ID
					),
					array(
					  'key'     => '_it_exchange_transaction_method',
					  'value'   => (array) $paid_by,
					  'compare' => 'IN'
					)
				  )
				);

				$transactions = get_posts( $transaction_query_args );
				if ( empty( $transactions ) ) {
					unset( $results[$key] );
				}
			}
		}

		return $results;
	}

	/**
	 * Get the default args for the user query
	 *
	 * @return array
	 */
	protected function get_users_default_args() {
		return array(
		  'role'   => LDMW_Users_Base::$member_role_slug,
		  'fields' => 'all_with_meta'
		);
	}

	/**
	 * Update users by either inserting user, or changing user data.
	 *
	 * @param $users_data array[]
	 *
	 * @return array$
	 */
	public function update_or_insert_users( $users_data ) {
		add_filter( 'it_exchange_send_purchase_emails_to', array( $this, 'remove_to' ) );
		$inserted = 0;
		$inserted_failed = 0;
		$updated = 0;
		$updated_failed = 0;

		foreach ( $users_data as $user_data ) {
			if ( false === get_user_by( 'id', $user_data['id'] ) && false === get_user_by( 'email', $user_data['user_email'] ) )
				$this->insert_user( $user_data ) === true ? $inserted ++ : $inserted_failed ++;
			else
				$this->update_user( $user_data ) === true ? $updated ++ : $updated_failed ++;
		}

		remove_filter( 'it_exchange_send_purchase_emails_to', array( $this, 'remove_to' ) );

		return array(
		  'insert'      => $inserted,
		  'insert_fail' => $inserted_failed,
		  'update'      => $updated,
		  'update_fail' => $updated_failed
		);
	}

	/**
	 * Remove the to address for email notifications,
	 * this effectively prevents them from being sent
	 *
	 * @param $to string existing to address
	 *
	 * @return string
	 */
	public function remove_to( $to ) {
		$to = "";

		return $to;
	}

	/**
	 * Update a user.
	 *
	 * @param $user_data array
	 *
	 * @return boolean
	 */
	protected function update_user( $user_data ) {
		$update_user_data = array();

		if ( !empty( $user_data['id'] ) ) {
			$update_user_data['ID'] = $user_data['id'];
			$user = get_user_by( 'id', $user_data['id'] );
		}
		else if ( !empty( $user_data['user_email'] ) ) {
			$user = get_user_by( 'email', $user_data['user_email'] );
			$update_user_data['ID'] = $user->ID;
		}
		else {
			return false;
		}

		if ( !empty( $user_data['first_name'] ) )
			$update_user_data['first_name'] = $user_data['first_name'];

		if ( !empty( $user_data['last_name'] ) )
			$update_user_data['last_name'] = $user_data['last_name'];

		if ( !empty( $user_data['first_name'] ) && empty( $user_data['last_name'] ) ) {
			$update_user_data['user_nicename'] = $user_data['first_name'] . " " . $user->last_name;
			$update_user_data['display_name'] = $update_user_data['user_nicename'];
		}
		else if ( empty( $user_data['first_name'] ) && !empty( $user_data['last_name'] ) ) {
			$update_user_data['user_nicename'] = $user->last_name . " " . $user_data['last_name'];
			$update_user_data['display_name'] = $update_user_data['user_nicename'];
		}
		else if ( !empty( $user_data['first_name'] ) && !empty( $user_data['last_name'] ) ) {
			$update_user_data['user_nicename'] = $user_data['first_name'] . " " . $user_data['last_name'];
			$update_user_data['display_name'] = $update_user_data['user_nicename'];
		}

		wp_update_user( $update_user_data );

		if ( !empty( $user_data['membership_grade'] ) )
			update_user_meta( $user->ID, 'ldmw_membership_grade', $user_data['membership_grade'] );

		if ( !empty( $user_data['membership_division'] ) )
			update_user_meta( $user->ID, 'ldmw_membership_division', $user_data['membership_division'] );

		if ( !empty( $user_data['membership_status'] ) )
			update_user_meta( $user->ID, 'ldmw_membership_status', $user_data['membership_status'] );

		if ( !empty( $user_data['title'] ) )
			update_user_meta( $user->ID, 'ldmw_title', $user_data['title'] );

		if ( !empty( $user_data['mobile_phone'] ) )
			LDMW_Users_Util::update_phone( $user->ID, 'mobile', $user_data['mobile_phone'] );

		if ( !empty( $user_data['work_phone'] ) )
			LDMW_Users_Util::update_phone( $user->ID, 'work', $user_data['work_phone'] );

		if ( !empty( $user_data['billing'] ) )
			update_user_meta( $user->ID, 'it-exchange-billing-address', $user_data['billing'] );

		if ( !empty( $user_data['customer_note'] ) )
			update_user_meta( $user->ID, '_it_exchange_customer_note', $user_data['customer_note'] );

		return true;
	}

	/**
	 * Insert the user data.
	 *
	 * @param $user_data array
	 *
	 * @return boolean
	 */
	protected function insert_user( $user_data ) {
		$insert_user_data = array(
		  'role'          => LDMW_Users_Base::$member_role_slug,
		  'user_login'    => $user_data['user_login'],
		  'user_pass'     => $user_data['user_pass'],
		  'user_email'    => $user_data['user_email'],
		  'first_name'    => $user_data['first_name'],
		  'last_name'     => $user_data['last_name'],
		  'user_nicename' => $user_data['first_name'] . " " . $user_data['last_name'],
		  'display_name'  => $user_data['first_name'] . " " . $user_data['last_name'],
		);

		$user_id = wp_insert_user( $insert_user_data );

		if ( is_wp_error( $user_id ) )
			return false;

		/*
		 * Adding the membership generates an email which uses the billing address.
		 *
		 * So we want to update the billing address first
		 */
		update_user_meta( $user_id, 'it-exchange-billing-address', $user_data['billing'] );

		if ( $user_data['membership_status'] == 'current' )
			self::add_membership( $user_id );

		update_user_option( $user_id, 'default_password_nag', true, true ); // Set up the Password change nag.

		update_user_meta( $user_id, 'ldmw_membership_grade', $user_data['membership_grade'] );
		update_user_meta( $user_id, 'ldmw_membership_division', $user_data['membership_division'] );
		update_user_meta( $user_id, 'ldmw_membership_status', $user_data['membership_status'] );
		update_user_meta( $user_id, 'ldmw_membership_start_date', $user_data['start_date'] );

		update_user_meta( $user_id, 'ldmw_title', $user_data['title'] );

		LDMW_Users_Util::update_phone( $user_id, 'mobile', $user_data['mobile_phone'] );
		LDMW_Users_Util::update_phone( $user_id, 'work', $user_data['work_phone'] );

		update_user_meta( $user_id, '_it_exchange_customer_note', $user_data['customer_note'] );

		return true;
	}

	/**
	 * Add the membership product to a user.
	 *
	 * Creates a transaction with the manual-purchases method for the main membership product.
	 *
	 * @see it_exchange_manual_purchase_print_add_payment_screen
	 *
	 * @param $user_id int WP_User::ID
	 */
	public static function add_membership( $user_id ) {
		// Grab default currency
		$settings = it_exchange_get_option( 'settings_general' );
		$currency = $settings['default-currency'];

		foreach ( array( LDMW_Options_Model::get_instance()->membership_product, LDMW_Options_Model::get_instance()->application_form_product ) as $product_id ) {
			$description = array();
			$products = array();

			if ( !empty( $product_id ) ) {
				if ( !$product = it_exchange_get_product( $product_id ) ) {
					error_log( "Failed to add membership during user import. Invalid product ID was passed: $product_id" );

					return;
				}

				$itemized_data = apply_filters( 'it_exchange_add_itemized_data_to_cart_product', array(), $product_id );
				if ( !is_serialized( $itemized_data ) )
					$itemized_data = maybe_serialize( $itemized_data );

				$key = $product_id . '-' . md5( $itemized_data );

				$products[$key]['product_base_price'] = it_exchange_get_product_feature( $product_id, 'base-price' );
				$products[$key]['product_subtotal'] = $products[$key]['product_base_price']; //need to add count
				$products[$key]['product_name'] = get_the_title( $product_id );
				$products[$key]['product_id'] = $product_id;
				$products[$key]['count'] = 1;
				$description[] = $products[$key]['product_name'];
			}

			$description = apply_filters( 'it_exchange_get_cart_description', join( ', ', $description ), $description );

			// Package it up and send it to the transaction method add-on
			$total = empty( $post['total'] ) ? 0 : it_exchange_convert_to_database_number( $post['total'] );
			$transaction_object = new stdClass();
			$transaction_object->total = number_format( it_exchange_convert_from_database_number( $total ), 2, '.', '' );
			$transaction_object->currency = $currency;
			$transaction_object->description = $description;
			$transaction_object->products = $products;

			$transaction_id = it_exchange_manual_purchases_addon_process_transaction( $user_id, $transaction_object );
			update_post_meta( $transaction_id, '_it_exchange_transaction_manual_purchase_description', "Product added during import from CSV." );
		}
	}
}