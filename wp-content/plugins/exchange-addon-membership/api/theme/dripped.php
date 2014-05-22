<?php
/**
 * Dripped class for THEME API in Membership Add-on
 *
 * @since 1.0.0
*/

class IT_Theme_API_Dripped implements IT_Theme_API {
	
	/**
	 * API context
	 * @var string $_context
	 * @since 1.0.0
	*/
	private $_context = 'dripped';

	/**
	 * Maps api tags to methods
	 * @var array $_tag_map
	 * @since 1.0.0
	*/
	public $_tag_map = array(
		'content' => 'content',
		'excerpt' => 'excerpt',
	);

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 *
	 * @return void
	*/
	function IT_Theme_API_Dripped() {
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
	function content( $options=array() ) {
		global $post;
		$earliest_drip = false;
		$now = time();
		$membership_settings = it_exchange_get_option( 'addon_membership' );
		
		$member_access = it_exchange_get_session_data( 'member_access' );
		foreach( $member_access as $product_id => $txn_id ) {
			$interval = get_post_meta( $post->ID, '_item-content-rule-drip-interval-' . $product_id, true );
			$interval = !empty( $interval ) ? $interval : 0;
			$duration = get_post_meta( $post->ID, '_item-content-rule-drip-duration-' . $product_id, true );
			$duration = !empty( $duration ) ? $duration : 'days';
			if ( 0 < $interval ) {
				$purchase_time = strtotime( 'midnight', get_post_time( 'U', true, $txn_id ) );
				$dripping = strtotime( $interval . ' ' . $duration, $purchase_time ) - $now;
				if ( !$earliest_drip || $dripping < $earliest_drip )
					$earliest_drip = $dripping;
			}
		}
		
		$defaults = array(
			'before' => '',
			'after'  => '',
			'message' => sprintf( $membership_settings['membership-dripped-content-message'], ceil( $earliest_drip / 60 / 60 / 24 ) ),
			'class'  => 'it-exchange-membership-restricted-content',
		);
		$options = ITUtility::merge_defaults( $options, $defaults );
			
		$content  = $options['before'];
		$content .= '<p class="' . $options['class'] . '">' . $options['message'] . '</p>';
		$content .= $options['after'];
		
		return $content;
	}

	/**
	 * @since 1.0.0
	 * @return string
	*/
	function excerpt( $options=array() ) {
		global $post;
		$earliest_drip = false;
		$now = time();
		$membership_settings = it_exchange_get_option( 'addon_membership' );
		
		$member_access = it_exchange_get_session_data( 'member_access' );
		foreach( $member_access as $product_id => $txn_id ) {
			$interval = get_post_meta( $post->ID, '_item-content-rule-drip-interval-' . $product_id, true );
			$interval = !empty( $interval ) ? $interval : 0;
			$duration = get_post_meta( $post->ID, '_item-content-rule-drip-duration-' . $product_id, true );
			$duration = !empty( $duration ) ? $duration : 'days';
			if ( 0 < $interval ) {
				$purchase_time = strtotime( 'midnight', get_post_time( 'U', true, $txn_id ) );
				$dripping = strtotime( $interval . ' ' . $duration, $purchase_time ) - $now;
				if ( !$earliest_drip || $dripping < $earliest_drip )
					$earliest_drip = $dripping;
			}
		}
		
		$defaults = array(
			'before' => '',
			'after'  => '',
			'message' => sprintf( $membership_settings['membership-dripped-content-message'], ceil( $earliest_drip / 60 / 60 / 24 ) ),
			'class'   => 'it-exchange-membership-restricted-excerpt',
		);
		$options = ITUtility::merge_defaults( $options, $defaults );
		
		$content  = $options['before'];
		$content .= '<p class="' . $options['class'] . '">' . $options['message'] . '</p>';
		$content .= $options['after'];
		
		return $excerpt;
	}
}
