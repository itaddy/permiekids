<?php
/**
 * Member Dashboard class for THEME API in Membership Add-on
 *
 * @since 1.0.0
*/

class IT_Theme_API_Membership_Confirmation implements IT_Theme_API {
	
	/**
	 * API context
	 * @var string $_context
	 * @since 1.0.0
	*/
	private $_context = 'membership-confirmation';

	/**
	 * Current tramsactopm product being viewed
	 * @var string $_transaction_product
	 * @since 1.0.0
	*/
	private $_transaction_product = '';
	
	/**
	 * Maps api tags to methods
	 * @var array $_tag_map
	 * @since 1.0.0
	*/
	public $_tag_map = array(
		'dashboardlink' => 'dashboard_link',
	);

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 *
	 * @return void
	*/
	function IT_Theme_API_Membership_Confirmation() {
		$this->_transaction_product               = empty( $GLOBALS['it_exchange']['transaction_product'] ) ? false : $GLOBALS['it_exchange']['transaction_product'];
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
	function dashboard_link( $options=array() ) {
		$result = '';
		
		$defaults      = array(
			'before' => '',
			'after'  => '',
			'label'  => __( 'View available content for %s', 'it-l10n-exchange-addon-membership' ),
		);
		$options      = ITUtility::merge_defaults( $options, $defaults );
				
		// Return boolean if has flag was set
		if ( $options['supports'] )
			return it_exchange_product_supports_feature( $this->_transaction_product['product_id'], 'membership-content-access-rules' );

		// Return boolean if has flag was set
		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->_transaction_product['product_id'], 'membership-content-access-rules' );

		// Repeats checks for when flags were not passed.
		if ( it_exchange_product_supports_feature( $this->_transaction_product['product_id'], 'membership-content-access-rules' )	
				&& it_exchange_product_has_feature( $this->_transaction_product['product_id'], 'membership-content-access-rules' ) ) {
			
			$page_slug = 'memberships';
			$permalinks = (bool)get_option( 'permalink_structure' );

			$membership_post = get_post( $this->_transaction_product['product_id'] );
			if ( !empty( $membership_post ) ) {
				$membership_slug = $membership_post->post_name;
				
				if ( $permalinks )
					$url = it_exchange_get_page_url( $page_slug ) . $membership_slug;
				else
					$url = it_exchange_get_page_url( $page_slug ) . '=' . $membership_slug;
					
				$result .= $options['before'];
				$result .= '<a href="' . $url . '">' . sprintf( $options['label'], get_the_title( $this->_transaction_product['product_id'] ) ) . '</a>';
				$result .= $options['after'];
			}

		}
		
		return $result;
					
	}
}
