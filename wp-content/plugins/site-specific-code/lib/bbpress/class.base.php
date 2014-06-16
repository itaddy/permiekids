<?php

/**
 *
 * @package LDMW
 * @subpackage bbpress
 * @since 5/22
 */
class LDMW_bbpress_Base {
	/**
	 * bbpress hook-ins
	 */
	public function __construct() {
		add_action( 'bbp_spammed_reply', array( $this, 'notify_author_of_spam' ) );
		add_filter( 'map_meta_cap', array( $this, 'map_meta_cap_forum_caps' ), 10, 4 );
		add_filter( 'bbp_get_template_part', array( $this, 'members_only_topics' ), 10, 3 );
		add_action( 'it_exchange_membership_bbpress_addon_content_restricted_after_wrap', array( $this, 'display_login_form' ) );
		add_action( 'it_exchange_membership_addon_content_restricted_after_wrap', array( $this, 'display_login_form' ) );
	}

	/**
	 * Notify a reply author that there reply was deleted/marked as spam
	 *
	 * @param $reply_id int
	 */
	public function notify_author_of_spam( $reply_id ) {
		$reply = bbp_get_reply( $reply_id );
		$author = get_user_by( 'id', $reply->post_author );

		if ( $author === false )
			return;

		wp_mail( $author->user_email, 'Comment Marked as Spam', 'Your comment was flagged as spam or as inappropriate. <br><br>' . $reply->post_content );
	}

	/**
	 * Map our custom meta for
	 *
	 * @param $caps array
	 * @param $cap string
	 * @param $user_id int
	 * @param $args array
	 *
	 * @return string
	 */
	public function map_meta_cap_forum_caps( $caps, $cap, $user_id, $args ) {
		if ( ! LDMW_Users_Util::is_member( get_user_by( 'id', $user_id ) ) )
			return $caps;

		if ( ! in_array( $cap, array( 'publish_topics', 'edit_topics', 'assign_topic_tags' ) ) )
			return $caps;

		if ( LDMW_Users_Util::get_membership_grade( $user_id ) == 'maas' || LDMW_Users_Util::get_membership_grade( $user_id ) == 'faas' ) {
			return array(); // FAAS and MAAS members are the only ones who can create new topics
		}
		else {
			return array( 'do_not_allow' );
		}
	}

	/**
	 * Display Restricted or Dripped message.
	 *
	 * Filters single-forum and single-topic content templates.
	 *
	 * @param $templates array
	 * @param $slug string
	 * @param $name string
	 *
	 * @return array
	 */
	function members_only_topics( $templates, $slug, $name ) {
		if ( 'content' === $slug && ( 'single-forum' === $name || 'single-topic' == $name ) ) {
			global $post;

			$forum_id = $post->post_parent;

			$rules = get_post_meta( $forum_id, '_item-content-rule', true );

			if ( ! empty( $rules ) && $this->is_content_restricted( get_post( $forum_id ) ) ) {
				array_unshift( $templates, 'content-restricted.php' );
			}
		}

		return $templates;
	}

	/**
	 * @param $post WP_Post
	 *
	 * @return bool
	 */
	protected function is_content_restricted( $post ) {
		$restriction = false;

		if ( current_user_can( 'administrator' ) )
			return false;

		$member_access = it_exchange_get_session_data( 'member_access' );

		$restriction_exemptions = get_post_meta( $post->ID, '_item-content-rule-exemptions', true );
		if ( ! empty( $restriction_exemptions ) ) {
			foreach ( $member_access as $product_id => $txn_id ) {
				if ( array_key_exists( $product_id, $restriction_exemptions ) )
					$restriction = true; //we don't want restrict yet, not until we know there aren't other memberships that still have access to this content
				else
					continue; //get out of this, we're in a membership that hasn't been exempted
			}
			if ( $restriction ) //if it has been restricted, we can return true now
				return true;
		}

		$post_rules = get_post_meta( $post->ID, '_item-content-rule', true );
		if ( ! empty( $post_rules ) ) {
			if ( empty( $member_access ) ) return true;
			foreach ( $member_access as $product_id => $txn_id ) {
				if ( in_array( $product_id, $post_rules ) )
					return false;
			}
			$restriction = true;
		}

		$post_type_rules = get_option( '_item-content-rule-post-type-' . $post->post_type, array() );
		if ( ! empty( $post_type_rules ) ) {
			if ( empty( $member_access ) ) return true;
			foreach ( $member_access as $product_id => $txn_id ) {
				if ( ! empty( $restriction_exemptions[$product_id] ) )
					return true;
				if ( in_array( $product_id, $post_type_rules ) )
					return false;
			}
			$restriction = true;
		}

		$taxonomy_rules = array();
		$taxonomies = get_object_taxonomies( $post->post_type );
		$terms = wp_get_object_terms( $post->ID, $taxonomies );
		foreach ( $terms as $term ) {
			$term_rules = get_option( '_item-content-rule-tax-' . $term->taxonomy . '-' . $term->term_id, array() );
			if ( ! empty( $term_rules ) ) {
				if ( empty( $member_access ) ) return true;
				foreach ( $member_access as $product_id => $txn_id ) {
					if ( in_array( $product_id, $term_rules ) )
						return false;
				}
				$restriction = true;
			}
		}

		return $restriction;
	}

	/**
	 * Display Exchange login form on bbPress restricted page
	 */
	public function display_login_form() {
		it_exchange_get_template_part( 'content-login' );
	}
}