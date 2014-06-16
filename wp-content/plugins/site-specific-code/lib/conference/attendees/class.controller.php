<?php

/**
 *
 * @package Conferences
 * @subpackage Attendees
 * @since 6/2
 */
class LDMW_Conference_Attendees_Controller extends LDMW_ManageMembers_Controller_List {
	/**
	 * @var int
	 */
	protected $conference_id;

	/**
	 * Setup the controller
	 *
	 * @param LDMW_ManageMembers_Model_Users $model
	 * @param int $conference_id
	 */
	public function __construct( $model, $conference_id ) {
		$this->conference_id = $conference_id;
		parent::__construct( $model );
	}

	/**
	 * Serve the page
	 */
	public function serve() {
		$data = $this->prepare_users( $this->users );

		$view = new LDMW_Conference_Attendees_View( $data, new LDMW_Conference_Attendees_Table( $data ) );
		$view->render();
	}

	/**
	 * Prepare the individual user row
	 *
	 * @param WP_User $user
	 *
	 * @return array
	 */
	protected function prepare_user( $user ) {
		$current_user = array();
		$current_user['ID'] = $user->ID;
		$current_user['first_name'] = $user->first_name;
		$current_user['last_name'] = $user->last_name;
		$current_user['email'] = $user->user_email;
		$current_user['phone'] = LDMW_Users_Util::get_phone( $user->ID, 'work' );

		$transaction_id = get_user_meta( $user->ID, 'ldmw_conference_purchased_' . $this->conference_id, true );
		$transaction = it_exchange_get_transaction( $transaction_id );

		if ( empty( $transaction ) ) {
			$current_user['date_paid'] = "";
			$current_user['fee_paid'] = "";
			$current_user['type'] = "";
			$current_user['paid_by'] = "";
			$current_user['receipt'] = "";
		}
		else {
			$current_user['date_paid'] = $transaction->get_date();
			$current_user['fee_paid'] = $transaction->cart_details->total;

			foreach ( $transaction->cart_details->products as $product ) {
				$product_object = it_exchange_get_product( $product['product_id'] );

				if ( $product_object->product_type != 'event-product-type' )
					continue;

				$description = $product['product_name'];
				$parts = explode( ":", $description );
				$variant = array_pop( $parts );

				$variant = str_replace( array( "<br>", "<br />" ), " - ", $variant );
				$variant = trim( $variant, ' -' );
				$current_user['type'] = $variant;
				break;
			}

			$current_user['paid_by'] = $transaction->transaction_method;
			$current_user['receipt'] = $transaction->ID;
		}

		return $current_user;
	}

}