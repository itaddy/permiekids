<?php
/**
 * Basic Reporting Dashboard Widget.
 * @package IT_Exchange
 * @since 0.4.9
*/

/**
 * Registers the dasboard reporting widget
 *
 * @since 0.4.9
 *
 * @return void
*/
function it_exchange_basic_reporting_register_dashboard_widget() {
	$cap = apply_filters( 'it_exchange_basic_reporting_capability_level', 'activate_plugins' );
	if ( ! current_user_can( $cap ) )
		return;
	wp_add_dashboard_widget( 'it-exchange-dashboard-reporting-widget', __( 'iThemes Exchange', 'it-l10n-ithemes-exchange' ), 'it_exchange_basic_reporting_print_dashboard_widget' );
}
add_action( 'wp_dashboard_setup', 'it_exchange_basic_reporting_register_dashboard_widget' );

/**
 * Put our widget under the Right Now widget because we think we're important.
 *
 * We're hooking into a filter so we need to make sure we return what we get, regardless of what we do here.
 * At this point we have already registered our dashboard widget. It gets added to the bottom by default.
 * We are repositioning it below the 'right now' meta box. Use the filter to move it elsewhere.
 * To remove this sort, put this code in your theme or plugin: add_filter( 'it_exchange_basic_reporting_dashboard_widget_goes_after', '__return_false' );
 *
 * @param array $widgets
 * @param return array
*/
function it_exchange_basic_reporting_reorder_dashboard_widgets( $widgets ) {
	global $wp_meta_boxes;
	$dashboard_widgets = empty( $wp_meta_boxes['dashboard']['normal']['core'] ) ? array() : $wp_meta_boxes['dashboard']['normal']['core'];
	$reporting_widget  = empty( $dashboard_widgets['it-exchange-dashboard-reporting-widget'] ) ? false : $dashboard_widgets['it-exchange-dashboard-reporting-widget'];
	$modified_array    = array();
	$inserted          = false;
	$target            = apply_filters( 'it_exchange_basic_reporting_dashboard_widget_goes_after', 'dashboard_right_now' );

	// Abort if target is false
	if ( ! $target )
		return $widgets;

	// Abort if reporting widget wasn't registered for some reason
	if ( ! $reporting_widget )
		return $widgets;
	else
		unset( $dashboard_widgets['it-exchange-dashboard-reporting-widget'] );

	// Loop through widgets and add the reporting widget after the key were looking for or at the end.
	foreach( $dashboard_widgets as $key => $params ) {
		$modified_array[$key] = $params;
		if ( $key === $target && ! $inserted ) {
			$modified_array['it-exchange-dashboard-reporting-widget'] = $reporting_widget;
			$inserted = true;
		}
	}

	// If we didn't find the key were were looking for, add it to the end.
	if ( ! $inserted )
		$modified_array['it-exchange-dashboard-reporting-widget'] = $reporting_widget;

	$wp_meta_boxes['dashboard']['normal']['core'] = $modified_array;
	return $widgets;
}
add_filter( 'wp_dashboard_widgets', 'it_exchange_basic_reporting_reorder_dashboard_widgets' );

/**
 * Prints the dashboard reporting widget
 *
 * @since 0.4.9
 *
 * @return void
*/
function it_exchange_basic_reporting_print_dashboard_widget() {
	include( 'dashboard-widget.php' );
}

/**
 * Reporting functions go here. Will grow after version 1
 * @package IT_Exchange
 * @since 0.4.9
*/

/**
 * Returns totals of all sales for a specified time period
 *
 * @since 0.4.9
 *
 * @param array $options
 * @return string
*/
function it_exchange_basic_reporting_get_total ( $options=array() ) {
	$defaults = array(
		'start_time' => strtotime( 'today' ),
		'end_time'   => current_time( 'timestamp' ),
	);
	$options = ITUtility::merge_defaults( $options, $defaults );

	// Set GLOBALS for the WHERE filter
	$GLOBALS['it_exchange']['where_start'] = date_i18n( 'Y-m-d H:i:s', $options['start_time'], false );
	$GLOBALS['it_exchange']['where_end']   = date_i18n( 'Y-m-d H:i:s', $options['end_time'], false );

	// Add the filter
	add_filter( 'posts_where', 'it_exchange_filter_where_clause_for_all_queries' );

	// Grab transactions via transient or make DB call
	if ( false === $transactions = get_transient( 'it-exchange-basic-reporting-tran-total' ) ) {
		$transactions = it_exchange_get_transactions( array( 'posts_per_page' => -1, 'suppress_filters' => false ) );
		set_transient( 'it-exchange-basic-reporting-tran-total', $transactions, DAY_IN_SECONDS );
	}
	if ( $transactions ) {
		$total = 0;
		// Loop through transactions and sum the totals if they are cleared for delivery
		foreach( $transactions as $transaction ) {
			if ( it_exchange_transaction_is_cleared_for_delivery( $transaction ) )
				$total += it_exchange_get_transaction_total( $transaction, false );
		}
		$total = it_exchange_format_price( $total );
	} else {
		$total = it_exchange_format_price( '0' );
	}

	// Unset GLOBALS
	unset( $GLOBALS['it_exchange']['where_start'] );
	unset( $GLOBALS['it_exchange']['where_end'] );

	// Remove Filter
	remove_filter( 'posts_where', 'it_exchange_filter_where_clause_for_all_queries' );

	return $total;
}

/**
 * Returns an average of all sells in a given time period
 *
 * @since 0.4.9
 *
 * @param array $options
 *
 * @return string
*/
function it_exchange_basic_reporting_get_average( $options=array() ) {
	$defaults = array(
		'start_time' => date( 'Y-m-01' ), // PHP 5.3 only (sadpanda) strtotime( 'first day of this month' ),
		'end_time'   => current_time( 'timestamp' ),
	);
	$options = ITUtility::merge_defaults( $options, $defaults );

	// Set GLOBALS for the WHERE filter
	$GLOBALS['it_exchange']['where_start'] = date_i18n( 'Y-m-d H:i:s', $options['start_time'], false );
	$GLOBALS['it_exchange']['where_end']   = date_i18n( 'Y-m-d H:i:s', $options['end_time'], false );

	// Add the filter
	add_filter( 'posts_where', 'it_exchange_filter_where_clause_for_all_queries' );

	// Grab transactions via transient or make DB call
	if ( false === $transactions = get_transient( 'it-exchange-basic-reporting-tran-average' ) ) {
		$transactions = it_exchange_get_transactions( array( 'posts_per_page' => -1, 'suppress_filters' => false ) );
		set_transient( 'it-exchange-basic-reporting-tran-average', $transactions, DAY_IN_SECONDS );
	}
	if ( $transactions  ) {
		// Loop through transactions and sum the totals if they are cleared for delivery
		$totals = array();
		foreach( $transactions as $transaction ) {
			if ( it_exchange_transaction_is_cleared_for_delivery( $transaction ) )
				$totals[] = it_exchange_get_transaction_total( $transaction, false );
		}
		$sum   = array_sum( $totals );
		$count = count( $totals );
		$total = it_exchange_format_price( $sum / $count );
	} else {
		$total = it_exchange_format_price( '0' );
	}

	// Unset GLOBALS
	unset( $GLOBALS['it_exchange']['where_start'] );
	unset( $GLOBALS['it_exchange']['where_end'] );

	// Remove Filter
	remove_filter( 'posts_where', 'it_exchange_filter_where_clause_for_all_queries' );

	return $total;
}

/**
 * Returns number of transactions for a given time period
 *
 * @since 0.4.9
 *
 * @param array $options
 *
 * @return int
*/
function it_exchange_basic_reporting_get_transactions_count( $options=array() ) {
	$defaults = array(
		'start_time' => date( 'Y-m-01' ), // PHP 5.3 only (sadpanda) strtotime( 'first day of this month' ),
		'end_time'   => current_time( 'timestamp' ),
	);
	$options = ITUtility::merge_defaults( $options, $defaults );

	// Set GLOBALS for the WHERE filter
	$GLOBALS['it_exchange']['where_start'] = date_i18n( 'Y-m-d H:i:s', $options['start_time'], false );
	$GLOBALS['it_exchange']['where_end']   = date_i18n( 'Y-m-d H:i:s', $options['end_time'], false );

	// Add the filter
	add_filter( 'posts_where', 'it_exchange_filter_where_clause_for_all_queries' );

	// Grab transactions via transient or make DB call
	if ( false === $transactions = get_transient( 'it-exchange-basic-reporting-tran-count' ) ) {
		$transactions = it_exchange_get_transactions( array( 'posts_per_page' => -1, 'suppress_filters' => false ) );
		set_transient( 'it-exchange-basic-reporting-tran-count', $transactions, DAY_IN_SECONDS );
	}

	// Grab transactions
	if ( $transactions ) {
		// Loop through transactions and sum the totals if they are cleared for delivery
		foreach( $transactions as $key => $transaction ) {
			if ( ! it_exchange_transaction_is_cleared_for_delivery( $transaction ) )
				unset( $transactions[$key] );
		}
		$count = count( $transactions );
	} else {
		$count = 0;
	}

	// Unset GLOBALS
	unset( $GLOBALS['it_exchange']['where_start'] );
	unset( $GLOBALS['it_exchange']['where_end'] );

	// Remove Filter
	remove_filter( 'posts_where', 'it_exchange_filter_where_clause_for_all_queries' );

	return $count;
}

/**
 * Breaks the transient caches when a transaction happens
 *
 * @since 1.7.18 
 *
 * @return void
*/
function it_exchange_basic_reporting_break_transaction_caches() {
	delete_transient( 'it-exchange-basic-reporting-tran-total' );
	delete_transient( 'it-exchange-basic-reporting-tran-average' );
	delete_transient( 'it-exchange-basic-reporting-tran-count' );
}
add_action( 'it_exchange_add_transaction_success', 'it_exchange_basic_reporting_break_transaction_caches' );
