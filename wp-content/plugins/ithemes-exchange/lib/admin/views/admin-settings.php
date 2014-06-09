<?php
/**
 * This file contains the contents of the Settings page
 * @since 0.3.6
 * @package IT_Exchange
*/
?>
<div class="wrap">
	<?php
	ITUtility::screen_icon( 'it-exchange' );
	$this->print_general_settings_tabs();
	do_action( 'it_exchange_general_settings_page_top' );

	?>

	<div class="it-exchange-general-settings columns-2">

		<div id="postbox-container-1" class="postbox-container">
			<div class="it-settings-sidebar-content">
				<div class="it-sidebar-content-section">
					<h3>Get Exchange email updates and a <span>free ecommerce ebook</span></h3>
					<p>We'll send you coupons and news about Exchange, as well as ecommerce tips and a free ecommerce ebook!</p>
					<!-- Begin MailChimp Signup Form -->
					<div id="mc_embed_signup">
						<form action="http://ithemes.us2.list-manage.com/subscribe/post?u=7acf83c7a47b32c740ad94a4e&amp;id=9da0741ac0" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
							<div class="mc-field-group">
								<input type="email" value="<?php echo get_option( 'admin_email', 'email address' ); ?>" name="EMAIL" class="required email" id="mce-EMAIL">
							</div>
							<div id="mce-responses" class="clear">
								<div class="response" id="mce-error-response" style="display:none"></div>
								<div class="response" id="mce-success-response" style="display:none"></div>
							</div>    <!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
							<div style="position: absolute; left: -5000px;"><input type="text" name="b_7acf83c7a47b32c740ad94a4e_9da0741ac0" value=""></div>
							<div class="clear"><input type="submit" value="Subscribe and get a free ebook" name="subscribe" id="mc-embedded-subscribe" class="button-primary"></div>
						</form>
					</div>
					<!--End mc_embed_signup-->
				</div>
				<div class="it-sidebar-content-section last">
					<h3>Get the <span>Pro Pack</span></h3>
					<p>The Pro Pack gets you all iThemes Exchange add-ons like Membership, Stripe Payments, MailChimp and more. If you really want to see what Exchange can do, check out the Pro Pack!</p>
					<p class="it-coupon-label">Use this code for <span>25% off</span></p>
					<p class="it-coupon">GOPRO25</p>
					<a class="button-primary green" href="http://ithemes.com/exchange/#pricing" target="_blank">Get the Pro Pack</a>
				</div>
			</div>
		</div>

		<div id="postbox-container-2" class="postbox-container">
		<?php $form->start_form( $form_options, 'exchange-general-settings' ); ?>

			<?php do_action( 'it_exchange_general_settings_form_top', $form ); ?>
			<table class="form-table">
				<?php do_action( 'it_exchange_general_settings_table_top', $form ); ?>
				<tr valign="top">
					<th scope="row"><strong><?php _e( 'Company Details', 'it-l10n-ithemes-exchange' ); ?></strong></th>
					<td></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="company-name"><?php _e( 'Company Name', 'it-l10n-ithemes-exchange' ) ?></label></th>
					<td>
						<?php $form->add_text_box( 'company-name', array( 'class' => 'normal-text' ) ); ?>
						<br /><span class="description"><?php _e( 'The name used in customer receipts.', 'it-l10n-ithemes-exchange' ); ?></span>
					</td>
				</tr>
				<tr valign="top">
					<?php $tax_link = 'http://www.irs.gov/Businesses/Small-Businesses-&amp;-Self-Employed/Apply-for-an-Employer-Identification-Number-(EIN)-Online'; ?>
					<th scope="row"><label for="company-tax-id"><?php _e( 'Company Tax ID', 'it-l10n-ithemes-exchange' ) ?> <?php it_exchange_admin_tooltip( sprintf( __( 'In the U.S., this is your Federal %sTax ID Number%s.', 'it-l10n-ithemes-exchange' ), '<a href="' . $tax_link . '" target="_blank">', '</a>' ) ); ?></label></th>
					<td>
						<?php $form->add_text_box( 'company-tax-id', array( 'class' => 'normal-text' ) ); ?>
						<p class="description"><a href="<?php echo $tax_link; ?>" target="_blank"><?php _e( 'Click here for more info about obtaining a Tax ID in the US', 'it-l10n-ithemes-exchange' ); ?></a></p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="company-email"><?php _e( 'Company Email', 'it-l10n-ithemes-exchange' ) ?> <?php it_exchange_admin_tooltip( __( 'Where do you want customer inquiries to go?', 'it-l10n-ithemes-exchange' ) ); ?></label></th>
					<td>
						<?php $form->add_text_box( 'company-email', array( 'class' => 'normal-text' ) ); ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="company-phone"><?php _e( 'Company Phone', 'it-l10n-ithemes-exchange' ) ?> <?php it_exchange_admin_tooltip( __( 'This is your main customer service line.', 'it-l10n-ithemes-exchange' ) ); ?></label></th>
					<td>
						<?php $form->add_text_box( 'company-phone', array( 'class' => 'normal-text' ) ); ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="company-address"><?php _e( 'Company Address', 'it-l10n-ithemes-exchange' ) ?></label></th>
					<td>
						<?php $form->add_text_area( 'company-address', array( 'rows' => 5, 'cols' => 30 ) ); ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="company-base-country"><?php _e( 'Base Country', 'it-l10n-ithemes-exchange' ) ?> <?php it_exchange_admin_tooltip( __( 'This is the country where your business is located', 'it-l10n-ithemes-exchange' ) ); ?></label></th>
					<td>
						<?php $form->add_drop_down( 'company-base-country', it_exchange_get_data_set( 'countries' ) ); ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="company-base-state"><?php _e( 'Base State / Province', 'it-l10n-ithemes-exchange' ) ?> <?php it_exchange_admin_tooltip( __( 'This is the state / province where your business is located', 'it-l10n-ithemes-exchange' ) ); ?></label></th>
					<td class="company-base-state-field-td">
						<?php
						$country = $form->get_option( 'company-base-country' );
						$states  = it_exchange_get_data_set( 'states', array( 'country' => $country ) );
						if ( ! empty( $states ) ) {
							$form->add_drop_down( 'company-base-state', $states );
						} else {
							$form->add_text_box( 'company-base-state', array( 'class' => 'small-text', 'max-length' => 3 ) );
							?><p class="description"><?php printf( __( 'Please use the 2-3 character %sISO abbreviation%s for country subdivisions', 'it-l10n-ithemes-exchange' ), '<a href="http://en.wikipedia.org/wiki/ISO_3166-2" target="_blank">', '</a>' ); ?></p><?php
						}
						?>
					</td>
				</tr>
				<?php do_action( 'it_exchange_general_settings_before_settings_store', $form ); ?>
				<tr valign="top">
					<th scope="row"><strong><?php _e( 'Store Settings', 'it-l10n-ithemes-exchange' ); ?></strong></th>
					<td></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="store-product-order-by"><?php _e( 'Order Products By', 'it-l10n-ithemes-exchange' ) ?></label></th>
					<td>
						<?php
						$order_by = apply_filters( 'it_exchange_store_order_by_options', array(
							'ID'         => __( 'Product ID', 'it-l10n-ithemes-exchange' ),
							'title'      => __( 'Product Title', 'it-l10n-ithemes-exchange' ),
							'name'       => __( 'Product Slug', 'it-l10n-ithemes-exchange' ),
							'date'       => __( 'Product Published Date/Time', 'it-l10n-ithemes-exchange' ),
							'modified'   => __( 'Product Modified Date/Time', 'it-l10n-ithemes-exchange' ),
							'rand'       => __( 'Random', 'it-l10n-ithemes-exchange' ),
							'menu_order' => __( 'Product Order #', 'it-l10n-ithemes-exchange' ),
						) );
						$form->add_drop_down( 'store-product-order-by', $order_by ); ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="store-product-order"><?php _e( 'Order', 'it-l10n-ithemes-exchange' ) ?></label></th>
					<td>
						<?php
						$order_by = apply_filters( 'it_exchange_store_order_options', array(
							'ASC'  => __( 'Ascending', 'it-l10n-ithemes-exchange' ),
							'DESC' => __( 'Descending', 'it-l10n-ithemes-exchange' ),
						) );
						$form->add_drop_down( 'store-product-order', $order_by ); ?>
					</td>
				</tr>
	
				<?php do_action( 'it_exchange_general_settings_before_settings_currency', $form ); ?>
				<tr valign="top">
					<th scope="row"><strong><?php _e( 'Currency Settings', 'it-l10n-ithemes-exchange' ); ?></strong></th>
					<td></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="default-currency"><?php _e( 'Default Currency', 'it-l10n-ithemes-exchange' ) ?></label></th>
					<td>
						<?php $form->add_drop_down( 'default-currency', $this->get_default_currency_options() ); ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="currency-symbol-position"><?php _e( 'Symbol Position', 'it-l10n-ithemes-exchange' ) ?></label></th>
					<td>
						<?php
						$symbol_positions = array( 'before' => __( 'Before: $10.00', 'it-l10n-ithemes-exchange' ), 'after' => __( 'After: 10.00$', 'it-l10n-ithemes-exchange' ) );
						$form->add_drop_down( 'currency-symbol-position', $symbol_positions ); ?>
						<br /><span class="description"><?php _e( 'Where should the currency symbol be placed in relation to the price?', 'it-l10n-ithemes-exchange' ); ?></span>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="currency-thousands-separator"><?php _e( 'Thousands Separator', 'it-l10n-ithemes-exchange' ) ?></label></th>
					<td>
						<?php $form->add_text_box( 'currency-thousands-separator', array( 'class' => 'small-text', 'maxlength' => '1' ) ); ?>
						<br /><span class="description"><?php _e( 'What character would you like to use to separate thousands when displaying prices?', 'it-l10n-ithemes-exchange' ); ?></span>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="currency-decimals-separator"><?php _e( 'Decimals Separator', 'it-l10n-ithemes-exchange' ) ?></label></th>
					<td>
						<?php $form->add_text_box( 'currency-decimals-separator', array( 'class' => 'small-text', 'maxlength' => '1' ) ); ?>
						<br /><span class="description"><?php _e( 'What character would you like to use to separate decimals when displaying prices?', 'it-l10n-ithemes-exchange' ); ?></span>
					</td>
				</tr>
	            <?php do_action( 'it_exchange_general_settings_before_settings_registration', $form ); ?>
				<tr valign="top">
					<th scope="row"><strong><?php _e( 'Customer Registration Settings', 'it-l10n-ithemes-exchange' ); ?></strong></th>
					<td></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="site-registration"><?php _e( 'Customer Registration', 'it-l10n-ithemes-exchange' ) ?></label></th>
					<td>
						<?php $form->add_radio( 'site-registration', array( 'value' => 'it' ) ); ?>
	                	<label for="site-registration-it"><?php _e( 'Use Exchange Registration Only', 'it-l10n-ithemes-exchange' ) ?></label>
	                    <br />
						<?php $form->add_radio( 'site-registration', array( 'value' => 'wp' ) ); ?>
	                	<label for="site-registration-wp"><?php _e( 'Use WordPress Registration Setting', 'it-l10n-ithemes-exchange' ) ?></label><?php it_exchange_admin_tooltip( __( 'In order to use this setting, you will first need to check the "Anyone can register" checkbox from the WordPress General Settings page to allow site membership.', 'it-l10n-ithemes-exchange' ) ); ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="checkout-reg-form"><?php _e( 'Default Checkout Form', 'it-l10n-ithemes-exchange' ) ?></label></th>
					<td>
						<?php
						$options = array(
							'registration' => __( 'Registration', 'it-l10n-ithemes-exchange' ),
							'login'        => __( 'Log in', 'it-l10n-ithemes-exchange' ),
						);
						?>
						<?php $form->add_drop_down( 'checkout-reg-form', $options ); ?>
					</td>
				</tr>
	            <?php do_action( 'it_exchange_general_settings_before_settings_styles', $form ); ?>
				<tr valign="top">
					<th scope="row"><strong><?php _e( 'Stylesheet Settings', 'it-l10n-ithemes-exchange' ); ?></strong></th>
					<td></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="custom-styles"><?php _e( 'Custom Styles', 'it-l10n-ithemes-exchange' ) ?></label></th>
					<td>
						<?php _e( 'If they exist, the following files will be loaded in order after core Exchange stylesheets:', 'it-l10n-ithemes-exchange' ); ?><br />
						<span class="description">
							<?php
							$parent = get_template_directory() . '/exchange/style.css';
							$child  = get_stylesheet_directory() . '/exchange/style.css';
							$custom_style_locations[$parent] = '&#151;&nbsp;&nbsp;' . $parent;
							$custom_style_locations[$child]  = '&#151;&nbsp;&nbsp;' . $child;
							echo implode( $custom_style_locations, '<br />' );
							?>
						</span>
					</td>
				</tr>
	            <?php do_action( 'it_exchange_general_settings_before_settings_admin', $form ); ?>
				<tr valign="top">
					<th scope="row"><strong><?php _e( 'Admin Settings', 'it-l10n-ithemes-exchange' ); ?></strong></th>
					<td></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="custom-styles"><?php _e( 'Visual Editor for Product Description', 'it-l10n-ithemes-exchange' ) ?></label></th>
					<td>
						<?php $form->add_check_box( 'wysiwyg-for-product-description' ); ?>
						<label for="wysiwyg-for-product-description"><?php _e( 'Enable Visual Editor for Product Descriptions?', 'it-l10n-ithemes-exchange' ); ?>
					</td>
				</tr>
	            <?php do_action( 'it_exchange_general_settings_before_settings_product_gallery', $form ); ?>
				<tr valign="top">
					<th scope="row"><strong><?php _e( 'Product Gallery', 'it-l10n-ithemes-exchange' ); ?></strong></th>
					<td></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="enable-gallery-popup"><?php _e( 'Enable Popup', 'it-l10n-ithemes-exchange' ) ?></label></th>
					<td>
						<?php $form->add_yes_no_drop_down( 'enable-gallery-popup' ); ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="product-gallery-zoom"><?php _e( 'Enable Zoom', 'it-l10n-ithemes-exchange' ) ?><?php it_exchange_admin_tooltip( __( 'Zoom will only work properly when uploading large images.', 'it-l10n-ithemes-exchange' ) ); ?></label></th>
					<td>
						<?php $form->add_yes_no_drop_down( 'enable-gallery-zoom' ); ?>
						<div class="product-gallery-zoom-actions <?php echo ( $form->_options['enable-gallery-zoom'] != 1 ) ? 'hidden' : ''; ?>">
							<?php $form->add_radio( 'product-gallery-zoom-action', array( 'value' => 'click' ) ); ?>
							<label for="product-gallery-zoom-action-click"><?php _e( 'Click', 'it-l10n-ithemes-exchange' ) ?></label>
							<br />
							<?php $form->add_radio( 'product-gallery-zoom-action', array( 'value' => 'hover' ) ); ?>
							<label for="product-gallery-zoom-action-hover"><?php _e( 'Hover', 'it-l10n-ithemes-exchange' ) ?></label>
							<span class="description popup-enabled <?php echo ( $form->_options['enable-gallery-popup'] != 1 ) ? 'hidden' : ''; ?>">
								<p><?php _e( 'Zoom will occur in the popup when popup is enabled.', 'it-l10n-ithemes-exchange' ); ?></p>
							</span>
						</div>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><strong><?php _e( 'Customer Messages', 'it-l10n-ithemes-exchange' ); ?></strong></th>
					<td></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="customer-account-page"><?php _e( 'Customer Account Page', 'it-l10n-ithemes-exchange' ) ?></label></th>
					<td>
						<?php
						if ( $GLOBALS['wp_version'] >= 3.3 && function_exists( 'wp_editor' ) ) {
							echo wp_editor( $settings['customer-account-page'], 'customer-account-page', array( 'textarea_name' => 'it_exchange_settings-customer-account-page', 'textarea_rows' => 20, 'textarea_cols' => 20, 'editor_class' => 'large-text' ) );
							//We do this for some ITForm trickery... just to add customer-account-page to the used inputs field
							$form->get_text_area( 'customer-account-page', array( 'rows' => 20, 'cols' => 20, 'class' => 'large-text' ) );
						} else {
							$form->add_text_area( 'customer-account-page', array( 'rows' => 20, 'cols' => 20, 'class' => 'large-text' ) );
						}
						?>
						<p class="description">
						<?php
						_e( 'Enter your content for the Customer\'s account page. HTML is accepted. Available shortcode functions:', 'it-l10n-ithemes-exchange' );
						echo '<br />';
						printf( __( 'You call these shortcode functions like this: %s', 'it-l10n-ithemes-exchange' ), '[it_exchange_customer show=avatar avatar_size=50]' );
						echo '<ul>';
						echo '<li>first-name - ' . __( "The customer's first name", 'it-l10n-ithemes-exchange' ) . '</li>';
						echo '<li>last-name - ' . __( "The customer's last name", 'it-l10n-ithemes-exchange' ) . '</li>';
						echo '<li>username - ' . __( "The customer's username on the site", 'it-l10n-ithemes-exchange' ) . '</li>';
						echo '<li>email - ' . __( "The customer's email address", 'it-l10n-ithemes-exchange' ) . '</li>';
						echo '<li>avatar - ' . __( "The customer's gravatar image. Use the avatar_size param for square size. Default is 128", 'it-l10n-ithemes-exchange' ) . '</li>';
						echo '<li>sitename - ' . __( 'Your site name', 'it-l10n-ithemes-exchange' ) . '</li>';
						do_action( 'it_customer_account_page_shortcode_tags_list' );
						echo '</ul>';
						?>
						</p>
					</td>
				</tr>
				<?php do_action( 'it_exchange_general_settings_table_bottom', $form ); ?>
			</table>
			<p class="submit"><input type="submit" value="<?php _e( 'Save Changes', 'it-l10n-ithemes-exchange' ); ?>" class="button button-primary" /></p>
			<?php do_action( 'it_exchange_general_settings_form_bottom', $form ); ?>
		<?php $form->end_form(); ?>
		<?php do_action( 'it_exchange_general_settings_page_bottom' ); ?>
		</div>
	</div>
</div>
