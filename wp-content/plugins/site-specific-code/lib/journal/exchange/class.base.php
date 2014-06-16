<?php

/**
 *
 * @package Journal
 * @subpackage Exchange
 * @since 5/7
 */
class LDMW_Journal_Exchange_Base {
	/**
	 *
	 */
	public function __construct() {
		add_action( "save_post_" . LDMW_Journal_Admin_CPT::$slug, array( $this, 'save_exchange_data_on_new_article' ), 20, 3 );
		add_action( 'created_' . LDMW_Journal_Admin_Taxonomy::$slug, array( $this, 'create_exchange_product_on_new_volume' ), 10, 2 );
		add_action( 'before_delete_post', array( $this, 'remove_exchange_product' ) );
		add_action( 'delete_term_taxonomy', array( $this, 'remove_exchange_tax_product' ) );
		add_action( 'it_exchange_add_transaction_success', array( $this, 'add_journal_purchase_to_user' ) );
	}

	/**
	 * Save our product's exchange data when we create a new article.
	 *
	 * @param $post_id int
	 * @param $post WP_Post
	 * @param $update boolean
	 */
	public function save_exchange_data_on_new_article( $post_id, $post, $update ) {
		if ( $post->post_status == 'auto-draft' )
			return;

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return;

		if ( wp_is_post_revision( $post_id ) )
			return;

		if ( "" != get_post_meta( $post_id, '_ldmw_it_exchange_product_id', true ) )
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

		update_post_meta( $product_id, '_it_exchange_product_type', 'membership-product-type' );
		update_post_meta( $product_id, '_it-exchange-visibility', 'hidden' );
		it_exchange_update_product_feature( $product_id, 'base-price', 50.00 );
		it_exchange_update_product_feature( $product_id, 'description', $post->post_excerpt );

		$content_access_rules = array(
		  'selection'     => LDMW_Journal_Admin_CPT::$slug,
		  'selected'      => 'posts',
		  'term'          => $post_id,
		  'group_layout'  => 'grid',
		  'drip-interval' => '0',
		  'drip-duration' => 'days',
		  'grouped_id'    => '',
		);

		$existing_rules = it_exchange_get_product_feature( $product_id, 'membership-content-access-rules' );

		$existing_rules[] = $content_access_rules;

		it_exchange_update_product_feature( $product_id, 'membership-content-access-rules', $existing_rules );

		if ( ! ( $rules = get_post_meta( $post_id, '_item-content-rule', true ) ) )
			$rules = array();

		$rules[] = $product_id;

		update_post_meta( $post_id, '_item-content-rule', $rules );

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

		update_post_meta( $post_id, '_ldmw_it_exchange_product_id', $product_id );
		update_post_meta( $product_id, '_ldmw_article_id', $post_id );
	}

	/**
	 * Create a new exchange product when we create a new volume or issue taxonomy
	 *
	 * @param $term_id int
	 * @param $tt_id int
	 */
	public function create_exchange_product_on_new_volume( $term_id, $tt_id ) {
		$term = get_term( $term_id, LDMW_Journal_Admin_Taxonomy::$slug );

		if ( is_wp_error( $term ) || is_null( $term ) )
			return;

		$product_id = wp_insert_post( array(
			'post_title'     => $term->name,
			'post_author'    => LDMW_Options_Model::get_instance()->general_secretary,
			'post_type'      => 'it_exchange_prod',
			'post_status'    => 'publish',
			'comment_status' => 'closed'
		  )
		);

		update_term_meta( $tt_id, '_ldmw_it_exchange_product_id', $product_id );
		update_post_meta( $product_id, '_ldmw_volume_id', $tt_id );

		update_post_meta( $product_id, '_it_exchange_product_type', 'membership-product-type' );
		update_post_meta( $product_id, '_it-exchange-visibility', 'hidden' );
		it_exchange_update_product_feature( $product_id, 'base-price', 50.00 );
		it_exchange_update_product_feature( $product_id, 'description', $term->description );

		$content_access_rules = array(
		  'selection'     => LDMW_Journal_Admin_Taxonomy::$slug,
		  'selected'      => 'taxonomy',
		  'term'          => $term_id,
		  'group_layout'  => 'grid',
		  'drip-interval' => '0',
		  'drip-duration' => 'days',
		  'grouped_id'    => '',
		);

		$existing_rules = it_exchange_get_product_feature( $product_id, 'membership-content-access-rules' );

		$existing_rules[] = $content_access_rules;

		it_exchange_update_product_feature( $product_id, 'membership-content-access-rules', $existing_rules );

		$rules = get_option( '_item-content-rule-tax-' . LDMW_Journal_Admin_Taxonomy::$slug . '-' . $term_id, array() );

		if ( ! in_array( $product_id, $rules ) ) {
			$rules[] = $product_id;
			update_option( '_item-content-rule-tax-' . LDMW_Journal_Admin_Taxonomy::$slug . '-' . $term_id, $rules );
		}

		$publish_date = get_term_meta( $tt_id, '_ldmw_publish_date', true );

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

		it_exchange_update_product_feature( $product_id, 'membership-product-restriction', $mpr_data );

	}

	/**
	 * Remove the Exchange product associated with the article when it is deleted
	 *
	 * @param $post_id
	 */
	public function remove_exchange_product( $post_id ) {
		$post = get_post( $post_id );

		if ( $post->post_type != LDMW_Journal_Admin_CPT::$slug )
			return;

		$product_id = get_post_meta( $post_id, '_ldmw_it_exchange_product_id', true );

		if ( empty( $product_id ) )
			return;

		wp_delete_post( $product_id );
	}

	/**
	 * Remove the exchange product associated with a term
	 *
	 * @param $tt_id int term taxonomy id
	 */
	public function remove_exchange_tax_product( $tt_id ) {
		$product_id = get_term_meta( $tt_id, '_ldmw_it_exchange_product_id', true );

		if ( empty( $product_id ) )
			return;

		wp_delete_post( $product_id );
	}

	/**
	 * Add journal purchases to a user
	 *
	 * @param $transaction_id int
	 */
	public function add_journal_purchase_to_user( $transaction_id ) {
		$transaction = it_exchange_get_transaction( $transaction_id );

		$products = $transaction->cart_details->products;

		$existing_purchases = get_user_meta( $transaction->customer_id, '_ldmw_journal_products_purchased', true );

		if ( ! is_array( $existing_purchases ) )
			$existing_purchases = array();

		$existing_count = count( $existing_purchases );

		foreach ( $products as $product ) {
			$id = $product['product_id'];

			$article_id = get_post_meta( $id, '_ldmw_article_id', true );

			if ( ! empty( $article_id ) ) {
				$existing_purchases[]['post'] = $article_id;
			}
			else {
				$volume_id = get_post_meta( $id, '_ldmw_volume_id', true );

				if ( ! empty( $volume_id ) ) {
					$existing_purchases[]['tax'] = $volume_id;
				}
			}
		}

		if ( count( $existing_purchases ) != $existing_count )
			update_user_meta( $transaction->customer_id, '_ldmw_journal_products_purchased', $existing_purchases );
	}
}