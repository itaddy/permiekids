<?php

/**
 *
 * @package LDMW
 * @subpackage Manage Members
 * @since 1.0
 */
class LDMW_ManageMembers_View_List extends LDMW_ManageMembers_View {
	/**
	 * @var WP_List_Table
	 */
	protected $list_table;

	/**
	 * @var array
	 */
	private $search_data = array();

	/**
	 * @param array $users
	 * @param WP_List_Table $table
	 */
	public function __construct( $users, $table ) {
		parent::__construct( $users );
		$this->list_table = $table;
		$this->search_data = wp_parse_args( $_GET, $this->get_possible_search_vars() );

		$this->scripts_and_styles();
	}

	/**
	 * Return an array of the possible search vars
	 *
	 * @return array
	 */
	protected function get_possible_search_vars() {
		return array(
		  'member_id'  => '',
		  'first_name' => '',
		  'last_name'  => '',
		  'email'      => '',
		  'start_date' => '',
		  'end_date'   => '',
		  'grade'      => array(),
		  'status'     => array(),
		  'division'   => array(),
		  'paid_by'    => array()
		);
	}

	/**
	 * Enqueue necessary scripts and styles
	 */
	protected function scripts_and_styles() {
		wp_enqueue_script( 'ldmw-managemembers-list', LDMW_Plugin::$url . "lib/managemembers/assets/js/list.js", array( 'jquery' ) );
		wp_enqueue_style( 'ldmw-managemembers-list', LDMW_Plugin::$url . "lib/managemembers/assets/css/list.css" );
	}

	/**
	 * Render the page.
	 *
	 * @return void
	 */
	public function render() {
		?>

		<div class="wrap">
		    <h2><?php echo get_admin_page_title(); ?></h2>

			<h2>
				<?php foreach ( $this->get_nav_urls() as $url => $label ) : ?>
					<a href="<?php echo esc_attr( $url ); ?>" class="add-new-h2"><?php echo esc_html( $label ); ?></a>
				<?php endforeach; ?>
			</h2>

		    <form id="manage-members" method="GET">
			    <?php $this->render_search_controls(); ?>
			    <div class="cf"></div>
			    <?php submit_button( 'Export', 'secondary', 'export' ); ?>
			    <div id="table">
				    <?php $this->list_table->prepare_items();
				    $this->list_table->display(); ?>
			    </div>
			    <input type="hidden" name="page" value="<?php echo esc_attr( $_GET['page'] ); ?>">
			    <input type="hidden" name="order" value="<?php echo isset( $this->search_data['order'] ) ? $this->search_data['order'] : 'asc'; ?>">
			    <input type="hidden" name="orderby" value="<?php echo isset( $this->search_data['orderby'] ) ? $this->search_data['orderby'] : 'ID'; ?>">
		    </form>
	    </div>

	<?php
	}

	/**
	 * Get an associative array of URL => Title
	 *
	 * @return array
	 */
	protected function get_nav_urls() {
		return array(
		  admin_url( 'user-new.php?create-member=1' ) => 'Create Member',
		  "?page=" . $_GET['page'] . "&view=import"   => 'Import members',
		  "?page=" . $_GET['page'] . "&view=export"   => 'Export members',
		);
	}

	/**
	 * Render the search controls
	 */
	protected function render_search_controls() {
		?>
		<div id="search-controls">
		    <div>
			    <table>
				    <tr>
					    <th><label for="member_id">Member ID</label></th>
					    <td><input type="number" min="1" id="member_id" name="member_id" value="<?php if ( isset( $this->search_data['member_id'] ) ) echo $this->search_data['member_id']; ?>"></td>
				    </tr>
				    <tr>
					    <th><label for="first_name">First</label></th>
					    <td><input type="text" id="first_name" name="first_name" value="<?php if ( isset( $this->search_data['first_name'] ) ) echo $this->search_data['first_name']; ?>"></td>
				    </tr>
				    <tr>
					    <th><label for="last_name">Last</label></th>
					    <td><input type="text" id="last_name" name="last_name" value="<?php if ( isset( $this->search_data['last_name'] ) ) echo $this->search_data['last_name']; ?>"></td>
				    </tr>
				    <tr>
					    <th><label for="email">Email</label></th>
					    <td><input type="email" id="email" name="email" value="<?php if ( isset( $this->search_data['email'] ) ) echo $this->search_data['email']; ?>"></td>
				    </tr>
				    <tr>
					    <th colspan="2"><h4>Membership Period</h4></th>
				    </tr>
				    <tr>
					    <th><label for="start_date">From</label></th>
					    <td><input type="text" id="start_date" name="start_date" data-date-format="<?php echo LDMW_Util::dateStringToDatepickerFormat( get_option( 'date_format' ) ); ?>" value="<?php if ( isset( $this->search_data['start_date'] ) ) echo $this->search_data['start_date']; ?>"></td>
				    </tr>
				    <tr>
					    <th><label for="end_date">To</label></th>
					    <td><input type="text" id="end_date" name="end_date" data-date-format="<?php echo LDMW_Util::dateStringToDatepickerFormat( get_option( 'date_format' ) ); ?>" value="<?php if ( isset( $this->search_data['end_date'] ) ) echo $this->search_data['end_date']; ?>"></td>
				    </tr>
			    </table>
		    </div>
		    <div>
			    <table>
				    <tr>
					    <th class="label-top"><label for="grade">Grade</label></th>
					    <td>
						    <select id="grade" name="grade[]" multiple>
							    <?php foreach ( LDMW_Users_Util::get_membership_grades() as $slug => $grade ) : ?>
								    <option value="<?php echo $slug; ?>" <?php if ( in_array( $slug, $this->search_data['grade'] ) ) echo 'selected="selected"'; ?>><?php echo $grade; ?></option>
							    <?php endforeach; ?>
					        </select>
					    </td>
				    </tr>
				    <tr>
					    <th class="label-top"><label for="status">Status</label></th>
					    <td>
						    <select id="status" name="status[]" multiple>
							    <?php foreach ( LDMW_Users_Util::get_membership_statuses() as $slug => $status ) : ?>
								    <option value="<?php echo $slug; ?>" <?php if ( in_array( $slug, $this->search_data['status'] ) ) echo 'selected="selected"'; ?>><?php echo $status; ?></option>
							    <?php endforeach; ?>
					        </select>
					    </td>
				    </tr>
			    </table>
		    </div>
		    <div>
			    <table>
				    <tr>
					    <th class="label-top"><label for="division">Division</label></th>
					    <td>
						    <select id="division" name="division[]" multiple>
							    <?php foreach ( LDMW_Users_Util::get_membership_divisions() as $slug => $division ) : ?>
								    <option value="<?php echo $slug; ?>" <?php if ( in_array( $slug, $this->search_data['division'] ) ) echo 'selected="selected"'; ?>><?php echo $division; ?></option>
							    <?php endforeach; ?>
					        </select>
					    </td>
				    </tr>
				    <tr>
					    <th class="label-top"><label for="paid_by">Method</label></th>
					    <td>
						    <select id="paid_by" name="paid_by[]" multiple>
							    <option value="stripe" <?php if ( in_array( 'stripe', $this->search_data['paid_by'] ) ) echo 'selected="selected"'; ?>>Stripe</option>
							    <option value="offline-payments" <?php if ( in_array( 'offline-payments', $this->search_data['paid_by'] ) ) echo 'selected="selected"'; ?>>Cheque</option>
							    <option value="offline-payments" <?php if ( in_array( 'offline-payments', $this->search_data['paid_by'] ) ) echo 'selected="selected"'; ?>>Bank Transfer</option>
					        </select>
					    </td>
				    </tr>
				    <tr>
					    <td></td>
					    <td><?php submit_button( "Search", 'primary', 'search' ); ?></td>
				    </tr>
				    <tr>
					    <td></td>
					    <td><p class="submit"><a href="<?php echo admin_url( 'admin.php?page=ldmw-manage-members' ); ?>" id="reset" class="button">Reset Search</a></p></td>
				    </tr>
			    </table>
		    </div>
	    </div>
	<?php
	}

}