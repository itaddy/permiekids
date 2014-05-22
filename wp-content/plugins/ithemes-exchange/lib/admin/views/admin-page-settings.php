<?php
/**
 * This file prints the Page Settings tab in the admin
 *
 * @scine 0.3.7
 * @package IT_Exchange
*/
?>
<div class="wrap page-settings-wrap">
	<?php
		ITUtility::screen_icon( 'it-exchange' );

		$this->print_general_settings_tabs();
		do_action( 'it_exchange_general_settings_page_page_top' );

		$form->start_form( $form_options, 'exchange-page-settings' );
		do_action( 'it_exchange_general_settings_page_form_top' );

		$general_settings = it_exchange_get_option( 'settings_general' );
		$pages            = it_exchange_get_registered_pages();
		$wp_pages         = array( 0 => __( 'Select a Page', 'it-l10n-ithemes-exchange' ) ) + it_exchange_get_wp_pages();
	?>

	<?php do_action( 'it_exchange_general_settings_page_top' ); ?>

	<div class="page-settings">
		<div class="it-row ps-header">
			<div class="it-column column-1">
				<span><?php _e( 'Page', 'it-l10n-ithemes-exchange' ); ?></span>
			</div>
			<div class="it-column column-2">
				<div class="it-column column-2-half">
					<span><?php _e( 'Page Type', 'it-l10n-ithemes-exchange' ); ?></span> <span class="tip" title="<?php _e( 'You have three Page Type options for where and how your store pages are displayed: Exchange (by default & recommended), WordPress (where you can use shortcodes to embed & have max control) or Disabled (if you don\'t need certain features in this list).', 'it-l10n-ithemes-exchange' ); ?>">i</span>
				</div>
				<div class="it-column column-2-half">
					<span><?php _e( 'Page Title', 'it-l10n-ithemes-exchange' ); ?></span>
				</div>
			</div>
			<div class="it-column column-3">
				<span><?php _e( 'Page Slug', 'it-l10n-ithemes-exchange' ); ?></span> <span class="tip" title="<?php _e( 'If these URL examples do not look right, go to Settings > Permalinks and click Save Changes.', 'it-l10n-ithemes-exchange' ); ?>">i</span>
			</div>
		</div>
		<?php foreach ( $pages as $page => $data ) : ?>
			<?php
				/**
				 * Don't show options for transactions at all.
				 * @todo remove transaction from pages and use query args
				*/
				if ( 'transaction' == $page )
					continue;

				$options = array();
				$url = '';
			?>
			<div class="it-row">
				<div class="it-column column-1">
					<span><?php esc_attr_e( $data['settings-name'] ); ?><?php if ( ! empty( $data['tip'] ) ) echo '<span class="tip" title="' . esc_attr( $data['tip'] ) . '">i</span>'; ?></span>
					<!--
						Do we need this page var? - KOOP
						<span class="page-var"><?php esc_attr_e( $page ); ?></span>
					-->
				</div>
				<div class="it-column column-2">
					<div class="it-column column-2-half page-type">
						<?php
							// Build options. Everyone gets Exchange
							$options['exchange'] = __( 'Exchange', 'it-l10n-ithemes-exchange' );

							// Products don't get WordPress
							if ( 'product' != $page )
								$options['wordpress'] = __( 'WordPress', 'it-l10n-ithemes-exchange' );

							// If on registration, we force disabled if WP option is disabled
							if ( 'registration' == $page && ( 'wp' == $general_settings['site-registration'] && ! get_option( 'users_can_register' ) ) )
								unset( $options );

							// Only optional pages get Disabled
							if ( $data['optional'] )
								$options['disabled'] = __( 'Disabled', 'it-l10n-ithemes-exchange' );

							// If count is 1, just print it and create a hidden field
							if ( count( $options ) < 2 ) {
								$form->add_hidden( $page . '-type' );
								$options = array_values( $options );
								esc_attr_e( $options[0] );
								if ( 'registration' == $page )
									echo '<span class="tip" title="' . __( 'You\'ll need to turn registration on in WordPress settings or override the WordPress settings in Exchange to use this, sorry.', 'it-l10n-ithemes-exchange' ) . '">i</span>';
								else
									echo '<span class="tip" title="' . __( 'This has to be Exchange, sorry.', 'it-l10n-ithemes-exchange' ) . '">i</span>';
							} else {
								$form->add_drop_down( $page . '-type', $options );
							}
						?>
					</div>
					<div class="it-column column-2-half page-title toggle-disabled <?php echo ( $form->_options[$page . '-type'] == 'disabled' ) ? 'hidden' : ''; ?>">
						<span class="ex-page <?php echo ( $form->_options[$page . '-type'] == 'wordpress' ) ? 'hidden' : ''; ?>"><?php $form->add_text_box( $page . '-name', array( 'class' => 'normal-text' ) ); ?></span>
						<span class="wp-page wp-page-select-span <?php echo ( $form->_options[$page . '-type'] != 'wordpress' ) ? 'hidden' : ''; ?>"><?php $form->add_drop_down( $page . '-wpid', $wp_pages ); ?></span>
					</div>
					<div class="it-column column-2-full wp-page toggle-disabled <?php echo ( $form->_options[$page . '-type'] == 'exchange' || $form->_options[$page . '-type'] == 'disabled' ) ? 'hidden' : '';?>">
						<div>
							<strong><?php _e( 'Copy this shortcode into the editor of the selected page.', 'it-l10n-ithemes-exchange' ); ?></strong>
							<br/>
							<?php
								if ( 'product' != $page ) {
									echo "<code>[it-exchange-page page='" . esc_attr( $page ) . "']</code>";
								}
							?>
						</div>
					</div>
				</div>
				<div class="it-column column-3 ex-page toggle-disabled <?php echo ( $form->_options[$page . '-type'] == 'wordpress' || $form->_options[$page . '-type'] == 'disabled' ) ? 'hidden' : ''; ?>">
					<?php $form->add_text_box( $page . '-slug', array( 'class' => 'normal-text' ) ); ?>
					<?php
						$url = esc_attr( it_exchange_get_page_url( $page ) );
						if ( 'product' == $page )
							$url = ( false == get_option( 'permalink_structure' ) ) ? get_home_url() . '?' . esc_attr( $form->get_option( 'product-slug' ) ) . '=product-name' : get_home_url() . '/' . esc_attr( $form->get_option( 'product-slug' ) ) . '/product-name';
					?>
					<br />
					<span class="url"><?php echo $url; ?></span>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
	<?php do_action( 'it_exchange_general_settings_page_table_bottom' ); ?>

	<?php wp_nonce_field( 'save-page-settings', 'exchange-page-settings' ); ?>
	<p class="submit">
		<input type="submit" value="<?php _e( 'Save Changes', 'it-l10n-ithemes-exchange' ); ?>" class="button button-primary button-large" />
	</p>

	<?php
		do_action( 'it_exchange_general_settings_page_form_bottom' );
		$form->end_form();
		do_action( 'it_exchange_general_settings_page_page_bottom' );
	?>
</div>
