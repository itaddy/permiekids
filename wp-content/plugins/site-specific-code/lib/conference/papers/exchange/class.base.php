<?php

/**
 *
 * @package Conferences
 * @subpackage Papers/Exchange
 * @since 5/29
 */
class LDMW_Conference_Papers_Exchange_Base {

	/**
	 *
	 */
	public function __construct() {
		add_action( 'it_exchange_save_product_' . 'event-product-type', array( $this, 'create_combined_papers_product' ), 20 );
		add_action( "save_post_" . LDMW_Conference_Papers_Admin_CPT::$slug, array( $this, 'save_exchange_data_on_new_paper' ), 20, 3 );
		add_action( 'it_exchange_add_transaction_success', array( $this, 'add_papers_purchase_to_user' ) );
	}

	/**
	 * Create a membership product that will contain all of the conference papers
	 * when a new conference product is created
	 *
	 * @param $ticket_id int
	 */
	public function create_combined_papers_product( $ticket_id ) {
		if ( !isset( $_POST['ID'] ) )
			return;

		if ( "" != get_post_meta( $ticket_id, '_ldmw_papers_product_id', true ) )
			return;

		$conference_id = it_exchange_get_product_feature( $ticket_id, IT_Exchange_Events_ProductFeatures_Event::$slug, array( 'field' => 'event.ID' ) );
		$event = get_post( $conference_id );

		// prevent recursion
		remove_action( 'it_exchange_save_product_' . 'event-product-type', array( $this, 'create_combined_papers_product' ), 20 );
		// insert the product that contains all of the papers for that conference
		$papers_id = wp_insert_post( array(
			'post_content'   => $event->post_excerpt,
			'post_title'     => $event->post_title . " Papers",
			'post_author'    => LDMW_Options_Model::get_instance()->general_secretary,
			'post_type'      => 'it_exchange_prod',
			'post_status'    => $event->post_status,
			'comment_status' => 'closed'
		  )
		);

		update_post_meta( $papers_id, '_ldmw_conference_id', $conference_id );
		update_post_meta( $papers_id, '_ldmw_ticket_id', $ticket_id );

		update_post_meta( $conference_id, '_ldmw_papers_product_id', $papers_id );

		update_post_meta( $ticket_id, '_ldmw_papers_product_id', $papers_id );
		update_post_meta( $ticket_id, '_ldmw_conference_id', $conference_id );

		update_post_meta( $papers_id, '_it_exchange_product_type', 'membership-product-type' );
		update_post_meta( $papers_id, '_it-exchange-visibility', 'hidden' );
		it_exchange_update_product_feature( $papers_id, 'base-price', 50.00 );
		it_exchange_update_product_feature( $papers_id, 'description', $event->post_excerpt );

		$publish_date = it_exchange_get_product_feature( $ticket_id, IT_Exchange_Events_ProductFeatures_Event::$slug, array( 'field' => 'event_date_start' ) );

		if ( empty( $publish_date ) )
			$publish_date = 'now';
		else
			$publish_date = "@$publish_date";

		$free_after_date = new DateTime( $publish_date );
		$free_after_date->add( new DateInterval( "P2Y" ) );

		$mpr_data = array(
		  'enable'                      => true,
		  'action'                      => 'free-for-member',
		  'non_members_free_after_date' => $free_after_date->getTimestamp(),
		  'hide_from_store'             => false,
		  'additional_fee'              => 0,
		  'membership_product'          => LDMW_Options_Model::get_instance()->membership_product
		);

		it_exchange_update_product_feature( $papers_id, 'membership-product-restriction', $mpr_data );
	}

	/**
	 * Save our product's exchange data when we create a new article.
	 *
	 * @param $post_id int
	 * @param $post WP_Post
	 * @param $update boolean
	 */
	public function save_exchange_data_on_new_paper( $post_id, $post, $update ) {
		if ( $post->post_status == 'auto-draft' )
			return;

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return;

		if ( wp_is_post_revision( $post_id ) )
			return;

		if ( "" != get_post_meta( $post_id, '_ldmw_it_exchange_product_id_paper', true ) )
			return;

		$product_id = wp_insert_post( array(
			'post_content'   => $post->post_excerpt,
			'post_title'     => $post->post_title,
			'post_author'    => LDMW_Options_Model::get_instance()->general_secretary,
			'post_type'      => 'it_exchange_prod',
			'post_status'    => $post->post_status,
			'comment_status' => 'closed'
		  )
		);

		update_post_meta( $product_id, '_ldmw_paper_id', $post_id );
		update_post_meta( $post_id, '_ldmw_it_exchange_product_id_paper', $product_id );

		update_post_meta( $product_id, '_it_exchange_product_type', 'membership-product-type' );
		update_post_meta( $product_id, '_it-exchange-visibility', 'hidden' );
		it_exchange_update_product_feature( $product_id, 'base-price', 50.00 );
		it_exchange_update_product_feature( $product_id, 'description', $post->post_excerpt );

		$free_after_date = new DateTime( $post->post_date_gmt );
		$free_after_date->add( new DateInterval( "P2Y" ) );

		$mpr_data = array(
		  'enable'                      => true,
		  'action'                      => 'free-for-member',
		  'non_members_free_after_date' => $free_after_date->getTimestamp(),
		  'hide_from_store'             => false,
		  'additional_fee'              => 0,
		  'membership_product'          => LDMW_Options_Model::get_instance()->membership_product
		);

		it_exchange_update_product_feature( $product_id, 'membership-product-restriction', $mpr_data );

		$single_product_existing_rules = it_exchange_get_product_feature( $product_id, 'membership-content-access-rules' );
		$single_product_access_rules = array(
		  'selection'     => LDMW_Conference_Papers_Admin_CPT::$slug,
		  'selected'      => 'posts',
		  'term'          => $post_id,
		  'group_layout'  => 'grid',
		  'drip-interval' => '0',
		  'drip-duration' => 'days',
		  'grouped_id'    => '',
		);
		$single_product_existing_rules[] = $single_product_access_rules;
		it_exchange_update_product_feature( $product_id, 'membership-content-access-rules', $single_product_existing_rules );

		$conferences = get_posts( array(
			'connected_direction' => 'from',
			'connected_type'      => LDMW_Conference_Papers_Admin_CPT::$connected_type,
			'connected_items'     => $post_id
		  )
		);

		if ( !( $rules = get_post_meta( $post_id, '_item-content-rule', true ) ) )
			$rules = array();

		$rules[] = $product_id;

		if ( !empty( $conferences[0] ) ) {
			$papers_id = get_post_meta( $conferences[0]->ID, '_ldmw_papers_product_id', true );
			$rules[] = $papers_id;

			$all_papers_existing_rules = it_exchange_get_product_feature( $papers_id, 'membership-content-access-rules' );
			$all_papers_existing_rules[] = array(
			  'selection'     => LDMW_Conference_Papers_Admin_CPT::$slug,
			  'selected'      => 'posts',
			  'term'          => $post_id,
			  'group_layout'  => 'grid',
			  'drip-interval' => '0',
			  'drip-duration' => 'days',
			  'grouped_id'    => '',
			);
			it_exchange_update_product_feature( $papers_id, 'membership-content-access-rules', $all_papers_existing_rules );
		}

		update_post_meta( $post_id, '_item-content-rule', $rules );
	}

	/**
	 * Add the purchase of a conference paper to an internal user meta list
	 *
	 * @param $transaction_id int
	 */
	public function add_papers_purchase_to_user( $transaction_id ) {

		$transaction = it_exchange_get_transaction( $transaction_id );

		$products = $transaction->cart_details->products;

		$existing_purchases = get_user_meta( $transaction->customer_id, '_ldmw_paper_products_purchased', true );

		if ( !is_array( $existing_purchases ) )
			$existing_purchases = array();

		$existing_count = count( $existing_purchases );

		foreach ( $products as $product ) {
			$id = $product['product_id'];

			$paper_id = get_post_meta( $id, '_ldmw_paper_id', true );

			if ( !empty( $paper_id ) ) {
				$existing_purchases[]['post'] = $paper_id;
			}
			else {
				$conference_id = get_post_meta( $id, '_ldmw_conference_id', true );

				if ( !empty( $conference_id ) ) {
					$existing_purchases[]['conference'] = $conference_id;
				}
			}
		}

		if ( count( $existing_purchases ) != $existing_count )
			update_user_meta( $transaction->customer_id, '_ldmw_paper_products_purchased', $existing_purchases );
	}
}