<?php

/**
 *
 * @package LDMW
 * @subpackage Exchange
 * @since 1.0
 */
class LDMW_Exchange_Display {
	/**
	 *
	 */
	public function __construct() {
		add_filter( 'it_exchange_pages_to_protect_redirect_if_not_logged_in', array( $this, 'redirect_if_not_logged_in' ) );
		add_action( 'template_redirect', array( $this, 'redirect_away_from_registration' ), 15 );
		add_filter( 'it_exchange_get_content_login_actions_elements', array( $this, 'remove_register_link' ) );
		add_action( 'template_redirect', array( $this, 'redirect_non_approved_applications_from_membership_product' ) );
		add_action( 'it_exchange_content_invoice_product_begin_to-from_wrap', array( $this, 'add_logo_to_invoice' ) );
		add_filter( 'it_exchange_theme_api_invoice_to', array( $this, 'add_to_address_to_invoice' ) );
		add_filter( 'it_exchange_theme_api_invoice_from', array( $this, 'add_abn_to_invoice' ) );
		add_filter( 'it_exchange_theme_api_invoice_terms', array( $this, 'modify_terms_text' ) );
		add_filter( 'it_exchange_api_theme_product_base_price', array( $this, 'add_gst_to_invoice' ), 10, 2 );
		add_filter( 'it_exchange_get_content_confirmation_transaction_summary_elements', array( $this, 'add_gst_template_to_confirmation' ) );

		add_filter( 'it_exchange_email_notification_shortcode_functions', array( $this, 'add_custom_shortcodes_to_email_shortcode' ) );
		add_action( 'it_exchange_email_template_tags_list', array( $this, 'display_added_email_shortcodes' ) );
		add_filter( 'it_exchange_email_notification_order_table_purchase_message', array( $this, 'translate_shortcodes_in_purchase_message' ) );
		add_action( 'send_purchase_emails_body_offline-payments', array( $this, 'use_custom_template_for_offline_payment_receipt_emails' ), 5, 2 );
		add_filter( 'send_purchase_emails_body', function ( $content ) {
			  add_filter( 'it_exchange_get_transaction_total', array( $this, 'add_gst_to_email' ), 10, 2 );

			  return $content;
		  }
		);

		add_filter( 'it_exchange_possible_template_paths', array( $this, 'add_template_paths' ), 1 );
		add_filter( 'it_exchange_customer_menu_pages', array( $this, 'filter_account_pages' ) );

		add_filter( 'the_title', array( $this, 'change_application_product_page_title_tab' ), 10, 2 );
		add_filter( 'it_exchange_get_content_confirmation_header_loops', array( $this, 'remove_menu_from_confirmation' ) );

		add_action( 'it_exchange_super_widget_registration_before_login_elements', array( $this, 'add_message_before_super_widget_login' ) );
		remove_filter( 'it_exchange_content_confirmation_after_product_attibutes', 'it_exchange_membership_addon_content_confirmation_after_product_attrubutes' );
	}

	/**
	 * Redirect user away from protected pages.
	 *
	 * @param $url
	 *
	 * @return string
	 */
	public function redirect_if_not_logged_in( $url ) {
		return it_exchange_get_page_url( 'login' );
	}

	/**
	 * Redirect visitors away from the registration page
	 */
	public function redirect_away_from_registration() {
		if ( get_query_var( 'it_exchange_view' ) == 'registration' ) {
			wp_redirect( it_exchange_get_page_url( 'login' ) );
			exit();
		}
	}

	/**
	 * Remove the register link from the log in page.
	 *
	 * @param $parts array
	 *
	 * @return array
	 */
	public function remove_register_link( $parts ) {
		if ( ( $key = array_search( 'register', $parts ) ) !== false ) {
			unset( $parts[$key] );
		}

		return $parts;
	}

	/**
	 * Redirect members who have not had their membership application approved away from the membership product page.
	 */
	public function redirect_non_approved_applications_from_membership_product() {
		/**
		 * @var $wp_query WP_Query
		 */
		global $wp_query;

		if ( $wp_query->get_queried_object_id() != LDMW_Options_Model::get_instance()->membership_product )
			return;

		if ( ( is_user_logged_in() && get_user_meta( get_current_user_id(), 'ldmw_membership_approved', true ) == true ) || current_user_can( 'edit_pages' ) )
			return;

		wp_redirect( get_permalink( LDMW_Options_Model::get_instance()->membership_info_page ) );
		exit;
	}

	/**
	 * Add a logo to the top right of the IT Exchange invoice.
	 */
	public function add_logo_to_invoice() {
		$logo_url = LDMW_Options_Model::get_instance()->logo_url;
		if ( empty( $logo_url ) )
			return;

		?>
		<div class="it-exchange-right">
			<div style="float:right;margin-bottom: -150px">
				<img src="<?php echo esc_attr( $logo_url ); ?>" style="width:150px">
			</div>
		</div>
		<div class="it-exchange-clearfix"></div>

	<?php
	}

	/**
	 * Add customer to address to invoice.
	 *
	 * @param $content string
	 *
	 * @return string
	 */
	public function add_to_address_to_invoice( $content ) {
		$product = empty( $GLOBALS['it_exchange']['product'] ) ? false : $GLOBALS['it_exchange']['product'];

		if ( ! $product )
			return $content;

		$meta = it_exchange_get_product_feature( $product->ID, 'invoices' );

		if ( empty( $meta ) )
			return $content;

		$client = it_exchange_get_customer( $meta['client'] );

		if ( empty( $client ) )
			return $content;

		$raw_address = it_exchange_get_customer_billing_address( $client->ID );

		unset( $raw_address['first-name'] ); // first & last name are already on the invoice
		unset( $raw_address['last-name'] );

		$content .= it_exchange_get_formatted_billing_address( $raw_address );

		return $content;
	}

	/**
	 * @param $content
	 *
	 * @return mixed
	 */
	public function add_abn_to_invoice( $content ) {
		$settings = it_exchange_get_option( 'settings_general' );
		$tax_id = $settings['company-tax-id'];

		$content .= "<div>ABN: $tax_id</div>";

		return $content;
	}

	/**
	 * Modify the terms text to display payment is due within 30 days,
	 * if this is a renewal invoice
	 *
	 * @param $content string
	 *
	 * @return string
	 */
	public function modify_terms_text( $content ) {
		if ( is_bool( $content ) )
			return $content;

		if ( get_post_meta( $GLOBALS['it_exchange']['product']->ID, 'ldmw_invoice_is_renewal_invoice', true ) == true ) {
			$content = '<div class="it-exchange-invoice-terms-block">';
			$content .= '	<span class="label">Terms</span>';
			$content .= '	<span class="value">Payment is due within 30 days</span>';
			$content .= '</div>';
		}

		return $content;
	}

	/**
	 * Add the GST to the confirmation page.
	 *
	 * @param $parts array
	 *
	 * @return array
	 */
	public function add_gst_template_to_confirmation( $parts ) {
		array_unshift( $parts, 'totals-gst' );

		return $parts;
	}

	/**
	 * Add GST to invoice
	 *
	 * @param $price string
	 * @param $product_id int
	 *
	 * @return string
	 */
	public function add_gst_to_invoice( $price, $product_id ) {
		$gst_percent = LDMW_Options_Model::get_instance()->gst_percentage;

		$base_price = IT_Exchange_Prorated_Subscriptions::remove_currency_format( $price );

		if ( empty( $gst_percent ) )
			return $price;

		$gst_percent /= (float) 100.0;

		$gst_amount = $base_price * $gst_percent;
		$gst_amount = it_exchange_format_price( $gst_amount );

		return $price . "<br><span style='font-size: 50%;display:block'>GST: $gst_amount</span>";
	}

	/**
	 * @param $replacements array
	 *
	 * @return mixed
	 */
	public function add_custom_shortcodes_to_email_shortcode( $replacements ) {
		if ( ! isset( $replacements['billing_address'] ) )
			$replacements['billing_address'] = array( 'LDMW_Exchange_Display', 'email_shortcode_print_billing_address' );

		if ( ! isset( $replacements['ldmw_grade'] ) )
			$replacements['ldmw_grade'] = array( 'LDMW_Exchange_Display', 'email_shortcode_print_grade' );

		if ( ! isset( $replacements['ldmw_division'] ) )
			$replacements['ldmw_division'] = array( 'LDMW_Exchange_Display', 'email_shortcode_print_division' );

		return $replacements;
	}

	/**
	 * Display our custom shortcode on the email settings page
	 */
	public function display_added_email_shortcodes() {
		echo "<li>billing_address - The buyer's billing address</li>";
		echo "<li>ldmw_grade – The member's membership grade</li>";
		echo "<li>ldmw_division - The member's membership division</li>";
	}

	/**
	 * Allow shortcodes in the product message section
	 *
	 * @param $content string
	 *
	 * @return string
	 */
	public function translate_shortcodes_in_purchase_message( $content ) {
		return do_shortcode( $content );
	}

	/**
	 * @param $args IT_Exchange_Email_Notifications
	 *
	 * @return string
	 */
	public static function email_shortcode_print_billing_address( $args ) {
		$IT_Exchange_Email_Notifications_Reflection = new ReflectionClass( get_class( $args ) ); // we have to use reflection because the damn $customer_id property is private
		$secret = $IT_Exchange_Email_Notifications_Reflection->getProperty( 'customer_id' );
		$secret->setAccessible( true );
		$user_id = $secret->getValue( $args );

		$content = it_exchange_get_formatted_billing_address( it_exchange_get_customer_billing_address( $user_id ) );

		return $content;
	}

	/**
	 * Add membership grade shortcode
	 *
	 * @param $args IT_Exchange_Email_Notifications
	 *
	 * @return string
	 */
	public static function email_shortcode_print_grade( $args ) {
		return LDMW_Users_Util::membership_grade_slug_to_name( LDMW_Users_Util::get_membership_grade( $args->customer_id ) );
	}

	/**
	 * Add membership division shortcode
	 *
	 * @param $args IT_Exchange_Email_Notifications
	 *
	 * @return string
	 */
	public static function email_shortcode_print_division( $args ) {
		return LDMW_Users_Util::membership_division_slug_to_name( LDMW_Users_Util::get_membership_division( $args->customer_id ) );
	}

	/**
	 * Use our custom template for offline payment receipt emails.
	 *
	 * @param $body string
	 * @param $transaction IT_Exchange_Transaction
	 *
	 * @return string
	 */
	public function use_custom_template_for_offline_payment_receipt_emails( $body, $transaction ) {
		if ( it_exchange_transaction_is_cleared_for_delivery( $transaction->ID ) )
			return $body;

		$template = LDMW_Options_Model::get_instance()->offline_payments_payment_email;

		if ( empty( $template ) )
			return $body;
		else
			return $template;
	}

	/**
	 * Add the GST to the email
	 *
	 * @param $price string
	 *
	 * @return string
	 */
	public function add_gst_to_email( $price ) {
		if ( ! isset( $GLOBALS['ldmw_add_gst_to_email'] ) ) {
			$GLOBALS['ldmw_add_gst_to_email'] = true;

			return $price;
		}

		$gst_percent = LDMW_Options_Model::get_instance()->gst_percentage;

		$base_price = IT_Exchange_Prorated_Subscriptions::remove_currency_format( $price );

		if ( empty( $gst_percent ) )
			return $price;

		$gst_percent /= (float) 100.0;

		$gst_amount = $base_price * $gst_percent;
		$gst_amount = it_exchange_format_price( $gst_amount );

		$return = $price . "<br><span style='font-size: 50%;display:block'>GST: $gst_amount</span>";

		unset( $GLOBALS['ldmw_add_gst_to_email'] );

		return $return;

	}

	/**
	 * Register our exchange template path.
	 *
	 * @param array $paths
	 *
	 * @return array
	 */
	public function add_template_paths( $paths = array() ) {
		$paths[] = LDMW_Plugin::$dir . 'templates/exchange';

		return $paths;
	}

	/**
	 * @param $pages array
	 *
	 * @return array
	 */
	public function filter_account_pages( $pages ) {
		foreach ( $pages as $key => $page ) {
			if ( $page == 'downloads' ) {
				unset( $pages[$key] );
			}
		}

		return $pages;
	}

	/**
	 * Modify the title of Application Fee when we are in the account area
	 *
	 * @param $title string
	 * @param $id int
	 *
	 * @return string
	 */
	public function change_application_product_page_title_tab( $title, $id ) {
		if ( $id != LDMW_Options_Model::get_instance()->application_form_product )
			return $title;

		$page_slug = it_exchange_get_page_slug( 'account' );
		$link = $_SERVER['REQUEST_URI'];

		if ( false === strpos( $link, $page_slug ) )
			return $title;

		$title = "Submit Application";

		return $title;
	}

	/**
	 * @param $parts
	 *
	 * @return mixed
	 */
	public function remove_menu_from_confirmation( $parts ) {
		foreach ( $parts as $key => $part ) {
			if ( $part == 'menu' )
				unset( $parts[$key] );
		}

		return $parts;
	}

	/**
	 * Add a message before the login button on the super widget registration screen.
	 */
	public function add_message_before_super_widget_login() {
		echo "<br>If you already have an account with us, sign in below";
	}
}