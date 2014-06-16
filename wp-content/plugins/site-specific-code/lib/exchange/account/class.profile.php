<?php

/**
 *
 * @package LDMW
 * @subpackage Exchange/Account
 * @since 1.0
 */
class LDMW_Exchange_Account_Profile extends LDMW_Exchange_Account_View {
	/**
	 * @var array
	 */
	protected $prefs = array();

	/**
	 * @var array|mixed|null
	 */
	protected $form = array();

	/**
	 * @var array
	 */
	protected $home_address;

	/**
	 * @var array
	 */
	protected $work_address;

	/**
	 * @var bool
	 */
	protected $doing_onboard = false;

	/**
	 * Constructor. Hook actions and filters.
	 */
	function __construct() {
		parent::__construct();

		$this->form = RGFormsModel::get_form_meta( LDMW_Options_Model::get_instance()->communication_preference );
		$this->prefs = LDMW_Users_Util::get_communication_preferences( $this->user_id );
		$this->home_address = LDMW_Users_Util::get_home_address( $this->user_id );
		$this->work_address = LDMW_Users_Util::get_work_address( $this->user_id );

		add_action( 'it_exchange_content_profile_after_fields_loop', array( $this, 'render' ) );
		add_action( 'it_exchange_content_profile_begin_fields_loop', function () {
			  echo '<h3>Account Info</h3>';
		  }
		);

		if ( ! empty( $_POST ) )
			$this->process_data( $_POST );

		$do_onboard = get_user_meta( $this->user_id, 'ldmw_onboarded_user', true );

		if ( empty( $do_onboard ) ) {
			add_action( 'it_exchange_content_profile_begin_wrap', array( $this, 'render_onboard' ) );
			$this->doing_onboard = true;
		}

		add_filter( 'body_class', array( $this, 'add_noscroll' ) );
	}

	/**
	 * @param $classes
	 *
	 * @return array
	 */
	public function add_noscroll( $classes ) {
		if ( $this->doing_onboard )
			$classes[] = "noscroll";

		return $classes;
	}

	/**
	 * Process submitted data.
	 *
	 * @param $data array
	 *
	 * @return void
	 */
	function process_data( $data ) {
		if ( ! isset( $data['ldmw_nonce'] ) || ! wp_verify_nonce( $data['ldmw_nonce'], 'ldmw-account-profile' ) )
			return;

		foreach ( $this->form['fields'] as $field ) {
			foreach ( $field['inputs'] as $input ) {
				$key = str_replace( " ", "_", $input['label'] );
				if ( isset( $data[$key] ) ) {
					$this->prefs[$input['label']] = true;
				}
				else {
					$this->prefs[$input['label']] = false;
				}
			}
		}

		LDMW_Users_Util::update_communication_preferences( $this->user_id, $this->prefs );
		LDMW_Users_Util::update_home_address( $this->user_id, $data['home'] );
		LDMW_Users_Util::update_work_address( $this->user_id, $data['work'] );

		foreach ( array( 'home', 'mobile', 'work' ) as $phone ) {
			LDMW_Users_Util::update_phone( $this->user_id, $phone, preg_replace( '/\D/', '', $data[$phone . "_phone"] ) );
		}

		$billing = it_exchange_get_customer_billing_address( $this->user_id );

		foreach ( $data['billing'] as $key => $value ) {
			$key = str_replace( "_", "-", $key );
			if ( isset( $billing[$key] ) ) {

				if ( $key == "phone" )
					$value = preg_replace( '/\D/', '', $value );

				$billing[$key] = sanitize_text_field( $value );
			}
		}

		update_user_meta( $this->user_id, 'it-exchange-billing-address', $billing );
		$this->home_address = LDMW_Users_Util::get_home_address( $this->user_id );
		$this->work_address = LDMW_Users_Util::get_work_address( $this->user_id );

		if ( LDMW_Users_Util::is_sustaining_member( $this->user_id ) ) {
			update_user_meta( $this->user_id, 'ldmw_sustaining_description', sanitize_text_field( $data['sustaining_members_description'] ) );
		}
	}

	/**
	 * Render the onboarding modal
	 */
	public function render_onboard() {
		wp_enqueue_style( 'ldmw-profile-onboarding', LDMW_Plugin::$url . "lib/exchange/assets/css/onboarding.css" );
		wp_enqueue_script( 'ldmw-profile-onboarding', LDMW_Plugin::$url . "lib/exchange/assets/js/onboarding.js", array( 'jquery-ui-core' ) );
		wp_enqueue_style( 'dashicons' );
		?>

		<?php if ( is_admin_bar_showing() ) : ?>
			<style type="text/css">
				@media (max-width: 980px) {
					#modal {
						top: 32px;
					}
				}

				@media (max-width: 782px) {
					#modal {
						top: 46px;
					}
				}
			</style>
		<?php endif; ?>

		<form action="<?php echo ( "" == get_user_meta( $this->user_id, 'ldmw_membership_application', true ) && false == LDMW_Users_Util::is_member( get_user_by( 'id', $this->user_id ) ) ) ?
		  get_permalink( LDMW_Options_Model::get_instance()->application_form_page ) :
		  it_exchange_get_page_url( 'profile' ); ?>" method="POST" novalidate="novalidate">
			<div id="modal">
				<?php wp_nonce_field( 'ldmw-onboard', 'ldmw_nonce' ); ?>
				<div class="modal-header">
					<a href="#" id="esc" class="close-modal dashicons dashicons-no"></a>
				</div>

				<div class="content">

					<div id="tab-1" class="tab current-tab center-tab" data-current-tab="1">
						<h3>Let's get started</h3>

						<?php if ( current_user_can( 'member' ) ) : ?>
							<p class="description">Please complete your contact details to ensure your profile is up to date.</p>
						<?php else : ?>
							<p class="description">The next step is to complete your contact details and submit your application. Let's get started now.</p>
						<?php endif; ?>
						<div class="it-exchange-clearfix"></div>

						<a href="#" id="get-started" class="btn btn-flat btn-default">Get Started</a>
					</div>

					<div id="tab-2" class="tab" data-current-tab="2">
						<h2>Welcome to your account!</h2>
						<p>Please complete your account details.</p>

						<h3>Home Contact Details</h3>

						<div class="inputs">

							<div class="input-row">
								<div class="left">
									<label for="modal-mobile_phone">Mobile Phone</label>
									<input type="tel" id="modal-mobile_phone" name="mobile_phone" required>
								</div>

								<div class="right">
									<label for="modal-home_phone">Home Phone</label>
									<input type="tel" id="modal-home_phone" name="home_phone">
								</div>
							</div>
							
							<div class="input-row">
								<div class="left">
									<label for="modal-home_address_1">Address</label>
									<input type="text" id="modal-home_address_1" name="home_m[address_1]" required>
								</div>
	
								<div class="right">
									<label for="modal-home_address_2">Address 2</label>
									<input type="text" id="modal-home_address_2" name="home_m[address_2]">
								</div>
							</div>
							
							<div class="input-row">
								<div class="left">
									<label for="modal-home_suburb">Suburb</label>
									<input type="text" id="modal-home_suburb" name="home_m[suburb]" required>
								</div>
	
								<div class="right">
									<label for="modal-home_address_2">Postcode</label>
									<input type="text" id="modal-home_address_2" name="home_m[postcode]" required>
								</div>
							</div>
							
							<div class="input-row">
								<div class="left">
									<label for="modal-home_country">Country</label>
									<select id="modal-home_country" class="country autocomplete" data-state="modal-home_state" name="home_m[country]" required>
										<?php foreach ( it_exchange_get_data_set( 'countries' ) as $slug => $country ) : ?>
											<option value="<?php echo $slug ?>" <?php selected( $slug, $this->home_address['country'] ) ?>><?php echo $country; ?></option>
										<?php endforeach; ?>
									</select>
								</div>
	
								<div class="right">
									<label for="modal-home_state">State</label>
									<select id="modal-home_state" name="home_m[state]" class="autocomplete" required>
										<?php foreach ( it_exchange_get_data_set( 'states', array( 'country' => $this->home_address['country'] ) ) as $slug => $state ) : ?>
											<option value="<?php echo $slug ?>" <?php selected( $slug, $this->home_address['state'] ) ?>><?php echo $state; ?></option>
										<?php endforeach; ?>
									</select>
								</div>
							</div>

						</div>

					</div>

					<div id="tab-3" class="tab" data-current-tab="3">
						<h2>You're almost there.</h2>

						<h3>Work Contact Details</h3>

						<div class="inputs">
							<div class="input-row">
								<div class="left">
									<label for="modal-work_phone">Work Phone</label>
									<input type="tel" id="modal-work_phone" name="work_phone">
								</div>

								<div class="right">
									<label for="modal-website">Website</label>
									<input type="url" id="modal-website" name="website">
								</div>
							</div>

							<div class="input-row">
								<div class="left">
									<label for="modal-work_address_1">Address</label>
									<input type="text" id="modal-work_address_1" name="work_m[address_1]" required>
								</div>

								<div class="right">
									<label for="modal-work_address_2">Address 2</label>
									<input type="text" id="modal-work_address_2" name="work_m[address_2]">
								</div>
							</div>

							<div class="input-row">
								<div class="left">
									<label for="modal-work_suburb">Suburb</label>
									<input type="text" id="modal-work_suburb" name="work_m[suburb]" required>
								</div>

								<div class="right">
									<label for="modal-work_address_2">Postcode</label>
									<input type="text" id="modal-work_address_2" name="work_m[postcode]" required>
								</div>
							</div>

							<div class="input-row">
								<div class="left">
									<label for="modal-work_country">Country</label>
									<select id="modal-work_country" class="country autocomplete" data-state="modal-work_state" name="work_m[country]" required>
										<?php foreach ( it_exchange_get_data_set( 'countries' ) as $slug => $country ) : ?>
											<option value="<?php echo $slug ?>" <?php selected( $slug, $this->home_address['country'] ) ?>><?php echo $country; ?></option>
										<?php endforeach; ?>
									</select>
								</div>

								<div class="right">
									<label for="modal-work_state">State</label>
									<select id="modal-work_state" name="work_m[state]" class="autocomplete" required>
										<?php foreach ( it_exchange_get_data_set( 'states', array( 'country' => $this->home_address['country'] ) ) as $slug => $state ) : ?>
											<option value="<?php echo $slug ?>" <?php selected( $slug, $this->home_address['state'] ) ?>><?php echo $state; ?></option>
										<?php endforeach; ?>
									</select>
								</div>
							</div>
						</div>

					</div>

					<div id="tab-4" class="tab" data-current-tab="4">
						<h2>Last step!</h2>

						<h3>Contact Preferences</h3>
						<p>Let us know how to get in touch with you.</p>

						<input type="checkbox" id="modal-email" class="ldmw-icon-input" name="contact[Email]">
						<label for="modal-email" class="ldmw-icon-label">
							<div class="ldmw-icon-container">
								<div class="ldmw-icon ldmw-icon-envelope"></div>
								<span>Email</span>
							</div>
						</label>

						<input type="checkbox" id="modal-home-address" class="ldmw-icon-input" name="contact[Home_Address]">
						<label for="modal-home-address" class="ldmw-icon-label">
							<div class="ldmw-icon-container">
								<div class="ldmw-icon ldmw-icon-home"></div>
								<span>Home Address</span>
							</div>
						</label>

						<input type="checkbox" id="modal-work-address" class="ldmw-icon-input" name="contact[Work_Address]">
						<label for="modal-work-address" class="ldmw-icon-label">
							<div class="ldmw-icon-container">
								<div class="ldmw-icon ldmw-icon-office"></div>
								<span>Work Address</span>
							</div>
						</label>

						<input type="checkbox" id="modal-home-phone" class="ldmw-icon-input" name="contact[Home_Phone]">
						<label for="modal-home-phone" class="ldmw-icon-label">
							<div class="ldmw-icon-container">
								<div class="ldmw-icon ldmw-icon-mobile"></div>
								<span>Home Phone</span>
							</div>
						</label>

						<input type="checkbox" id="modal-work-phone" class="ldmw-icon-input" name="contact[Work_Phone]">
						<label for="modal-work-phone" class="ldmw-icon-label">
							<div class="ldmw-icon-container">
								<div class="ldmw-icon ldmw-icon-phone"></div>
								<span>Work Phone</span>
							</div>
						</label>
					</div>

					<div id="tab-5" class="tab center-tab" data-current-tab="5">
						<h3>All finished</h3>

						<?php if ( "" == get_user_meta( $this->user_id, 'ldmw_membership_application', true ) && false == LDMW_Users_Util::is_member( get_user_by( 'id', $this->user_id ) ) ) : ?>

							<p class="description">Thanks for completing your contact details. Now it's time to submit your application.</p>
							<div class="it-exchange-clearfix"></div>

							<input type="submit" class="btn btn-flat btn-default" value="Submit Application">

						<?php else : ?>

							<p class="description">Thanks for completing your contact details.</p>
							<div class="it-exchange-clearfix"></div>

							<input type="submit" class="btn btn-flat btn-default" value="Continue">

						<?php endif; ?>
					</div>

				</div>

			    <div class="modal-footer">
				    <a href="#" id="prev" class="left-modal dashldmw-icons hidden" data-load-tab="0"></a>
				    <div id="progress"><div id="progress-bar"></div></div>
				    <a href="#" id="next" class="right-modal dashldmw-icons" data-load-tab="2"></a>
			    </div>
		    </div>
			<div class="it-exchange-clearfix"></div>

			<div id="modal-blocker" class="modal-blocker"></div>
			<input type="hidden" name="ldmw_onboard">
		</form>

	<?php
	}

	/**
	 * Render the fields on the form
	 *
	 * @return void
	 */
	function render() {
		wp_enqueue_script( 'ldmw-account-ajax', LDMW_Plugin::$url . "lib/exchange/assets/js/ajax.js", array( 'jquery' ) );
		wp_localize_script( 'ldmw-account-ajax', 'ldmw', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' )
		  )
		);

		global $IT_Exchange;

		wp_enqueue_script( 'jquery-select-to-autocomplete', $IT_Exchange->_plugin_url . '/lib/assets/js/jquery.select-to-autocomplete.min.js', array( 'jquery', 'jquery-ui-autocomplete' ) );
		wp_enqueue_style( 'it-exchange-autocomplete-style', $IT_Exchange->_plugin_url . '/lib/assets/styles/autocomplete.css' );

		$billing_address = it_exchange_get_customer_billing_address( $this->user_id );
		wp_nonce_field( 'ldmw-account-profile', 'ldmw_nonce' );
		?>

		<?php if ( LDMW_Users_Util::is_sustaining_member( $this->user_id ) ) : ?>
			<div class="input-section">
				<h3>Sustaining Members Directory Description</h3>
				<textarea name="sustaining_members_description" id="sustaining_members_description"><?php echo esc_textarea( get_user_meta( $this->user_id, 'ldmw_sustaining_description', true ) ); ?></textarea>
			</div>

		<?php endif; ?>
		<div class="input-section">
			<h3>Contact Details</h3>

			<div class="input-row">
				<div class="pull-left">
					<label for="work_phone">Work Phone</label>
					<input type="tel" id="work_phone" name="work_phone" value="<?php echo esc_attr( LDMW_Users_Util::get_phone( $this->user_id, 'work' ) ); ?>">
				</div>

				<div class="pull-left">
					<label for="billing_phone">Billing Phone</label>
					<input type="tel" id="billing_phone" name="billing[phone]" value="<?php echo esc_attr( $billing_address['phone'] ); ?>">
				</div>
			</div>

			<div class="input-row">
				<div class="pull-left">
					<label for="home_phone">Home Phone</label>
					<input type="tel" id="home_phone" name="home_phone" value="<?php echo esc_attr( LDMW_Users_Util::get_phone( $this->user_id, 'home' ) ); ?>">
				</div>

				<div class="pull-left">
					<label for="mobile_phone">Mobile Phone</label>
					<input type="tel" id="mobile_phone" name="mobile_phone" value="<?php echo esc_attr( LDMW_Users_Util::get_phone( $this->user_id, 'mobile' ) ); ?>">
				</div>
			</div>
		</div>

		<div class="input-section">
			<h4>Home Address</h4>
			<div class="input-row">
				<div class="pull-left">
					<label for="home_address_1">Address</label>
					<input type="text" id="home_address_1" name="home[address_1]" value="<?php echo esc_attr( $this->home_address['address_1'] ); ?>">
				</div>

				<div class="pull-left">
					<label for="home_address_2">Address 2</label>
					<input type="text" id="home_address_2" name="home[address_2]" value="<?php echo esc_attr( $this->home_address['address_2'] ); ?>">
				</div>
			</div>

			<div class="input-row">
				<div class="pull-left">
					<label for="home_suburb">Suburb</label>
					<input type="text" id="home_suburb" name="home[suburb]" value="<?php echo esc_attr( $this->home_address['suburb'] ); ?>">
				</div>

				<div class="pull-left">
					<label for="home_postcode">Postcode</label>
					<input type="text" id="home_postcode" name="home[postcode]" value="<?php echo esc_attr( $this->home_address['postcode'] ); ?>">
				</div>
			</div>

			<div class="input-row">
				<div class="pull-left">
					<label for="home_country">Country</label>
					<select id="home_country" class="country autocomplete" data-state="home_state" name="home[country]">
						<?php foreach ( it_exchange_get_data_set( 'countries' ) as $slug => $country ) : ?>
							<option value="<?php echo $slug ?>" <?php selected( $slug, $this->home_address['country'] ) ?>><?php echo esc_attr( $country ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="pull-left">
					<label for="home_state">State</label>
					<select id="home_state" name="home[state]" class="autocomplete">
							<option></option>
						<?php foreach ( it_exchange_get_data_set( 'states', array( 'country' => $this->home_address['country'] ) ) as $slug => $state ) : ?>
							<option value="<?php echo $slug ?>" <?php selected( $slug, $this->home_address['state'] ) ?>><?php echo esc_attr( $state ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
	    </div>

		<div class="input-section">
			<h4>Work Address</h4>

			<div class="input-row">
				<div class="pull-left">
					<label for="work_address_1">Address</label>
					<input type="text" id="work_address_1" name="work[address_1]" value="<?php echo esc_attr( $this->work_address['address_1'] ); ?>">
				</div>

				<div class="pull-left">
					<label for="work_address_2">Address 2</label>
					<input type="text" id="work_address_2" name="work[address_2]" value="<?php echo esc_attr( $this->work_address['address_2'] ); ?>">
				</div>
			</div>

			<div class="input-row">
				<div class="pull-left">
					<label for="work_suburb">Suburb</label>
					<input type="text" id="work_suburb" name="work[suburb]" value="<?php echo esc_attr( $this->work_address['suburb'] ); ?>">
				</div>

				<div class="pull-left">
					<label for="work_postcode">Postcode</label>
					<input type="text" id="work_postcode" name="work[postcode]" value="<?php echo esc_attr( $this->work_address['postcode'] ); ?>">
				</div>
			</div>

			<div class="input-row">
				<div class="pull-left">
					<label for="work_country">Country</label>
					<select id="work_country" class="country autocomplete" data-state="work_state" name="work[country]">
						<?php foreach ( it_exchange_get_data_set( 'countries' ) as $slug => $country ) : ?>
							<option value="<?php echo $slug ?>" <?php selected( $slug, $this->work_address['country'] ) ?>><?php echo esc_attr( $country ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="pull-left">
					<label for="work_state">State</label>
					<select id="work_state" name="work[state]" class="autocomplete">
						<?php foreach ( it_exchange_get_data_set( 'states', array( 'country' => $this->work_address['country'] ) ) as $slug => $state ) : ?>
							<option value="<?php echo $slug ?>" <?php selected( $slug, $this->work_address['state'] ); ?>><?php echo esc_attr( $state ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
		</div>

		<div class="input-section">

			<h3>Billing Contact</h3>

			<div class="input-row">
				<div class="pull-left">
					<label for="billing_first">First Name</label>
					<input type="text" id="billing_first" name="billing[first_name]" value="<?php echo esc_attr( $billing_address['first-name'] ); ?>">
				</div>

			    <div class="pull-left">
				    <label for="billing_last">Last Name</label>
					<input type="text" id="billing_last" name="billing[last_name]" value="<?php echo esc_attr( $billing_address['last-name'] ); ?>">
			    </div>
		    </div>

			<div class="input-row">
				<div class="pull-left">
					<label for="billing_company">Company Name</label>
					<input type="text" id="billing_company" name="billing[company_name]" value="<?php echo esc_attr( $billing_address['company-name'] ); ?>">
				</div>

				<div class="pull-left">
					<label for="billing_email">Email Address</label>
					<input type="email" id="billing_email" name="billing[email]" value="<?php echo esc_attr( $billing_address['email'] ); ?>">
				</div>
			</div>

			<div class="input-row">
				<div class="pull-left">
					<label for="billing_address_1">Address</label>
					<input type="text" id="billing_address_1" name="billing[address1]" value="<?php echo esc_attr( $billing_address['address1'] ); ?>">
				</div>

				<div class="pull-left">
					<label for="billing_address_2">Address 2</label>
					<input type="text" id="billing_address_2" name="billing[address2]" value="<?php echo esc_attr( $billing_address['address2'] ); ?>">
				</div>
			</div>

			<div class="input-row">
				<div class="pull-left">
					<label for="billing_suburb">City</label>
					<input type="text" id="billing_suburb" name="billing[city]" value="<?php echo esc_attr( $billing_address['city'] ); ?>">
				</div>

				<div class="pull-left">
					<label for="billing_postcode">Postcode</label>
					<input type="text" id="billing_postcode" name="billing[zip]" value="<?php echo esc_attr( $billing_address['zip'] ); ?>">
				</div>
			</div>

			<div class="input-row">
				<div class="pull-left">
					<label for="billing_country">Country</label>
					<select id="billing_country" class="country autocomplete" data-state="billing_state" name="billing[country]">
							<option value="-1">Select a Country</option>
						<?php foreach ( it_exchange_get_data_set( 'countries' ) as $slug => $country ) : ?>
							<option value="<?php echo $slug ?>" <?php selected( $slug, $this->home_address['country'] ) ?>><?php echo esc_attr( $country ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="pull-left">
					<label for="billing_state">State</label>
					<select id="billing_state" name="billing[state]" class="autocomplete">
						<?php foreach ( it_exchange_get_data_set( 'states', array( 'country' => $billing_address['country'] ) ) as $slug => $state ) : ?>
							<option value="<?php echo $slug ?>" <?php selected( $slug, $billing_address['state'] ); ?>><?php echo esc_attr( $state ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>

		</div>

		<div class="input-section">
			<h3>Communication Preference</h3>

			<?php foreach ( $this->form['fields'] as $field ) : ?>
				<?php foreach ( $field['inputs'] as $input ) : ?>
					<input type="checkbox" name="<?php echo $input['label'] ?>" id="<?php echo $input['label'] ?>"
					  <?php if ( isset( $this->prefs[$input['label']] ) ) checked( $this->prefs[$input['label']] ); ?>>
					<label for="<?php echo $input['label'] ?>"><?php echo $input['label']; ?></label>
					<br>
				<?php endforeach; ?>
			<?php endforeach; ?>
		</div>

	<?php
	}

}