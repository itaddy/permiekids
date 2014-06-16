<?php
/*
PluginName: Custom List Table Example
PluginURI: http://www.mattvanandel.com/
Description: A highly documented plugin that demonstrates how to create custom List Tables using official WordPress APIs.
Version: 1.3
Author: Matt Van Andel
Author URI: http://www.mattvanandel.com
License: GPL2
*/
/*  Copyright 2014  Matthew Van Andel  (email : matt@mattvanandel.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( !class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class LDMW_ManageMembers_Controller_Table extends WP_List_Table {

	/**
	 * Hold array of raw user data.
	 *
	 * @var array
	 */
	var $data;

	/**
	 * Set up data.
	 *
	 * Use parent constructor and populate custom fields.
	 *
	 * @param array $users
	 */
	function __construct( $users ) {
		$this->data = $users;

		//Set parent defaults
		parent::__construct( array(
			'singular' => 'member', //singular name of the listed records
			'plural'   => 'members', //plural name of the listed records
			'ajax'     => false //does this table support ajax?
		  )
		);

	}

	/**
	 * Override the text when no items are found.
	 */
	public function no_items() {
		echo "No members found.";
	}

	/**
	 * Recommended. This method is called when the parent class can't find a method
	 * specifically build for a given column.
	 *
	 * @param array $item A singular item (one full row's worth of data)
	 * @param string $column_name The name/slug of the column to be processed
	 *
	 * @return string Text or HTML to be placed inside the column <td>
	 */
	function column_default( $item, $column_name ) {
		if ( isset( $item[$column_name] ) )
			return $item[$column_name];
		else
			return '';
	}

	/**
	 * Render the first name column, with associated row actions.
	 *
	 * @param $item array
	 *
	 * @return string
	 */
	function column_first_name( $item ) {
		//Build row actions
		$actions = array(
		  'edit' => sprintf( '<a href="%s">Edit</a>', admin_url( 'user-edit.php?user_id=' . $item['ID'] ) ),
		  'view' => sprintf( '<a href="%s">View</a>', admin_url( 'user-edit.php?user_id=' . $item['ID'] . "&it_exchange_customer_data=1" ) ),
		);

		//Return the title contents
		return sprintf( '%1$s %2$s',
		  /*$1%s*/
		  $item['first_name'],
		  /*$2%s*/
		  $this->row_actions( $actions )
		);
	}

	/**
	 * Render the date paid column.
	 *
	 * @param $item array
	 *
	 * @return string
	 */
	function column_date_paid( $item ) {
		$date = strtotime( $item['date_paid'] );

		if ( !empty( $date ) ) {
			$date = new DateTime( "@$date" );
			$date = $date->format( "m/d/y" );
		}

		return $date;
	}

	/**
	 * Render the fee paid column
	 *
	 * @param $item array
	 *
	 * @return string
	 */
	function column_fee_paid( $item ) {
		$fee = $item['fee_paid'];

		if ( empty( $fee ) )
			return "";

		return it_exchange_format_price( $fee );
	}

	/**
	 * Render the paid by column.
	 *
	 * @param $item array
	 *
	 * @return string
	 */
	function column_paid_by( $item ) {
		$out = $item['paid_by'];
		$out = str_replace( "-", " ", $out );
		$out = ucfirst( $out );

		return $out;
	}

	/**
	 * Render the receipt column.
	 *
	 * @param $item array
	 *
	 * @return string
	 */
	function column_receipt( $item ) {
		return '<a href="' . admin_url( 'post.php?action=edit&post=' . $item['receipt'] ) . '">' . $item['receipt'] . '</a>';
	}

	/**
	 * REQUIRED! This method dictates the table's columns and titles. This should
	 * return an array where the key is the column slug (and class) and the value
	 * is the column's title text.
	 *
	 * @see WP_List_Table::::single_row_columns()
	 * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
	 */
	function get_columns() {
		$columns = array();

		foreach ( $this->data[0] as $key => $value ) {
			$columns[$key] = ucwords( str_replace( "_", " ", $key ) );
		}

		return $columns;
	}

	/**
	 * This method merely defines which columns should be sortable and makes them
	 * clickable - it does not handle the actual sorting.
	 *
	 * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
	 */
	function get_sortable_columns() {
		$sortable_columns = array();

		foreach ( $this->get_columns() as $column => $title ) {
			$sortable_columns[$column] = array( $column, false );
		}

		return $sortable_columns;
	}

	/**
	 * Prepare data for display.
	 *
	 * Sets up pagination and sorting.
	 *
	 * @uses $this->_column_headers
	 * @uses $this->items
	 * @uses $this->get_columns()
	 * @uses $this->get_sortable_columns()
	 * @uses $this->get_pagenum()
	 * @uses $this->set_pagination_args()
	 */
	function prepare_items() {

		/**
		 * First, lets decide how many records per page to show
		 */
		$user = get_current_user_id();
		$screen = get_current_screen();
		$screen_option = $screen->get_option( 'per_page', 'option' );
		$per_page = get_user_meta( $user, $screen_option, true );

		if ( empty ( $per_page ) || $per_page < 1 ) {
			$per_page = $screen->get_option( 'per_page', 'default' );
		}

		if ( !is_numeric( $per_page ) )
			$per_page = 20;

		/**
		 * Now we need to define our column headers. This includes a complete
		 * array of columns to be displayed (slugs & titles), a list of columns
		 * to keep hidden, and a list of columns that are sortable. Each of these
		 * can be defined in another method (as we've done here) before being
		 * used to build the value for our _column_headers property.
		 */
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();

		/**
		 * Finally, we build an array to be used by the class for column
		 * headers. The $this->_column_headers property takes an array which contains
		 * 3 other arrays. One for all columns, one for hidden columns, and one
		 * for sortable columns.
		 */
		$this->_column_headers = array( $columns, $hidden, $sortable );

		/**
		 * Grab the array of user data.
		 */
		$data = $this->data;

		/**
		 * This checks for sorting input and sorts the data in our array accordingly.
		 *
		 * The majority of sorting is done during the user_query, but if the sorting data is generated dynamically, then we sort it here.
		 */
		function usort_reorder( $a, $b ) {
			$orderby = ( !empty( $_REQUEST['orderby'] ) ) ? $_REQUEST['orderby'] : 'ID'; //If no sort, default to ID
			$order = ( !empty( $_REQUEST['order'] ) ) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc

			if ( !is_numeric( $a[$orderby] ) ) {
				$result = strcmp( $a[$orderby], $b[$orderby] ); //Determine sort order

				return ( $order === 'asc' ) ? $result : - $result; //Send final sort direction to usort
			}
			else {
				if ( $a[$orderby] <= $b[$orderby] )
					$result = - 1;
				else
					$result = 1;

				return ( $order === 'asc' ) ? $result : - $result;
			}
		}

		if ( isset( $_REQUEST['orderby'] ) && in_array( $_REQUEST['orderby'], array( 'receipt', 'invoice', 'paid_by', 'fee_paid' ) ) )
			usort( $data, 'usort_reorder' );

		/**
		 * Let's figure out what page the user is currently looking at.
		 */
		$current_page = $this->get_pagenum();

		$total_items = $this->determine_total_items( $data );

		/**
		 * The WP_List_Table class does not handle pagination for us, so we need
		 * to ensure that the data is trimmed to only the current page. We can use
		 * array_slice() to do that.
		 */
		if ( isset( $_GET['paid_by'] ) )
			$data = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );

		/**
		 * Now we can add our sorted data to the items property, where
		 * it can be used by the rest of the class.
		 */
		$this->items = $data;

		/**
		 * We also have to register our pagination options & calculations.
		 */
		$this->set_pagination_args( array(
			'total_items' => $total_items, // we have to calculate the total number of items
			'per_page'    => $per_page, // we have to determine how many items to show on a page
			'total_pages' => ceil( $total_items / $per_page ) // we have to calculate the total number of pages
		  )
		);
	}

	/**
	 * Get the total items used when doing a full item search
	 *
	 * @return array
	 */
	protected function get_total_items_for_full_search() {
		return ( new WP_User_Query( array_merge( LDMW_ManageMembers_Controller_List::parse_get( $_GET ), array( 'role' => LDMW_Users_Base::$member_role_slug, 'fields' => 'ID' ) ) ) )->get_total();
	}

	/**
	 * Determine the total number of items
	 *
	 * @param $data array
	 *
	 * @return int
	 */
	protected function determine_total_items( $data ) {
		/**
		 * Let's check how many items are in our data array.
		 */
		if ( isset( $_GET['paid_by'] ) ) {
			// if we are doing a paid by search, then we had to return all of our members, so that is the total number of users
			$total_items = count( $data );
		}
		elseif ( isset( $_GET['search'] ) ) {
			$total_items = $this->get_total_items_for_full_search();
		}
		else {
			$total_users = count_users();
			$total_items = $total_users['avail_roles'][LDMW_Users_Base::$member_role_slug];
		}

		return $total_items;
	}

}