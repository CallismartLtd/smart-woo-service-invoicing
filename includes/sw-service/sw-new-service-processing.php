<?php
/**
 * File name sw-new-service-processing.php
 * Handles new service processing from the admin area
 * 
 * @author	Callistus.
 * @package	SmartWooService
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access.
 
/**
 * Call back function for new service order admin page
 * 
 * @param object $order WC_Order object.
 */
function smartwoo_service_order_table( $orders ) {

	$page_html  = '';
	$page_html .= '<h1 class="wp-heading-inline">Service Orders</h1>';


	if ( empty( $orders ) ) {
		return $page_html .= smartwoo_notice( 'All Service orders will appear here when a customer purchases a service product.' );
		
	}

	$page_html .= '<table class="sw-table">';
	$page_html .= '<thead>';
	$page_html .= '<tr>';
	$page_html .= '<th>Order ID</th>';
	$page_html .= '<th> Date Created</th>';
	$page_html .= '<th>Status</th>';
	$page_html .= '<th>Service Name</th>';
	$page_html .= '<th>Client\'s Name</th>';
	$page_html .= '<th>Action</th>';
	$page_html .= '</tr>';
	$page_html .= '</thead>';
	$page_html .= '<tbody>';

	foreach ( $orders as $order ) {

		$order_status 	= $order->get_status();
		$order_id		= $order->get_id();
		$created_date 	= smartwoo_check_and_format( $order->get_date_created(), true );
		$user_full_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
		$service_name 	= '';
		$process_url  	= '';
		$items 			= $order->get_items();

		foreach ( $items as $item_id => $item ) {
			$service_name = wc_get_order_item_meta( $item_id, 'Service Name', true );

			if ( $service_name ) {
				break;
			}
		}

		// Display row.
		$page_html .= '<tr>';
		$page_html .= '<td>' . esc_html( $order_id ) . '</td>';
		$page_html .= '<td>' . esc_html( $created_date ) . '</td>';
		$page_html .= '<td>' . esc_html( ucwords( $order_status ) ) . '</td>';
		$page_html .= '<td>' . esc_html( $service_name ) . '</td>';
		$page_html .= '<td>' . esc_html( $user_full_name ) . '</td>';

		if ( 'processing' === $order_status ) {
			$process_url = '<a href="' . esc_url( admin_url( 'admin.php?page=sw-admin&action=process-new-service&order_id=' . $order_id ) ) . '" class="sw-red-button">' .__( 'Process Now', 'smart-woo-service-invoicing' ). '</a>';
		} elseif ( 'pending' === $order_status ) {
			$process_url = 'Order is Unpaid';
		} elseif ( 'completed' === $order_status ) {
			$process_url = 'Completed';
		} else {
			$process_url = 'Cannot be proceesed';
		}

		$page_html .= '<td>' . $process_url . '</td>';
		$page_html .= '</tr>';
	}

	$page_html .= '</tbody>';
	$page_html .= '</table>';
	$page_html .= '<p style="text-align: right;">' . count( $orders ) . ' items</p>';
	return $page_html;
}

/**
 * Conversion of WooCommerce Order to Smart Woo Service subscription
 *
 * IMPORTANT NOTE: Before calling this function, ensure that the order is of the 'sw_product' type
 * Failure to verify the order type may lead to unexpected behavior
 *
 * @see Helper Function: smartwoo_check_if_configured()
 * This function can be used to check if the order contains configured 'sw_product' types.
 *
 * @param int $order_id Order ID of the new service order.
 */
function smartwoo_convert_wc_order_to_smartwoo_service( $order_id ) {

	// Get order details and user data.
	$order          = wc_get_order( $order_id );
	$user_id        = $order->get_user_id();
	$user_info      = get_userdata( $user_id );
	$user_full_name = $order ? $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() : 'Not Found';

	// Get the configured data from order item meta data.
	$items = $order->get_items();
	foreach ( $items as $item_id => $item ) {
		$service_name = wc_get_order_item_meta( $item_id, 'Service Name', true );
		$service_url  = wc_get_order_item_meta( $item_id, 'Service URL', true );
	}

	// Convert order paid date to Service subscription start date.
	$start_date 		= $order->get_date_paid() ? date_i18n( 'Y-m-d', strtotime( $order->get_date_paid() ) ) : date_i18n( 'Y-m-d' );
	$billing_cycle     	= '';
	$next_payment_date 	= '';
	$end_date          	= '';
	$status            	= 'Pending';

	if ( ! empty( $items ) ) {

		// Retrieve billing_cycle from the product in the order.
		$first_item = reset( $items ); // Get the first item( Will be extended).
		$product_id = $first_item->get_product_id();

		// Fetch the billing cycle from product metadata.
		$billing_cycle = get_post_meta( $product_id, '_smartwoo_billing_cycle', true );

		// Set next payment date and end date based on billing cycle.
		switch ( $billing_cycle ) {
			case 'Monthly':
				$end_date          = date_i18n( 'Y-m-d', strtotime( $start_date . ' +1 month' ) );
				$next_payment_date = date_i18n( 'Y-m-d', strtotime( $end_date . ' -7 days' ) );
				break;

			case 'Quarterly':
				$end_date          = date_i18n( 'Y-m-d', strtotime( $start_date . ' +3 months' ) );
				$next_payment_date = date_i18n( 'Y-m-d', strtotime( $end_date . ' -7 days' ) );
				break;

			case 'Six Monthly':
				$end_date          = date_i18n( 'Y-m-d', strtotime( $start_date . ' +6 months' ) );
				$next_payment_date = date_i18n( 'Y-m-d', strtotime( $end_date . ' -7 days' ) );
				break;

			case 'Yearly':
				$end_date          = date_i18n( 'Y-m-d', strtotime( $start_date . ' +1 year' ) );
				$next_payment_date = date_i18n( 'Y-m-d', strtotime( $end_date . ' -7 days' ) );
				break;
			default:
				break;
		}
	}

	$product_name	= wc_get_product( $product_id )->get_name();
	$status_options = array(
		''                => esc_html__( 'Auto Calculate', 'smart-woo-service-invoicing' ),
		'Pending'         => esc_html__( 'Pending', 'smart-woo-service-invoicing' ),
		'Active (NR)'     => esc_html__( 'Active (NR)', 'smart-woo-service-invoicing' ),
		'Suspended'       => esc_html__( 'Suspended', 'smart-woo-service-invoicing' ),
		'Due for Renewal' => esc_html__( 'Due for Renewal', 'smart-woo-service-invoicing' ),
		'Expired'         => esc_html__( 'Expired', 'smart-woo-service-invoicing' ),
	);

	// The form.
	// return smartwoo_new_service_order_form( $user_id, $order_id, $service_name, $service_url, $user_full_name, $start_date, $billing_cycle, $next_payment_date, $end_date, $status );
	ob_start();
	include_once SMARTWOO_PATH . 'templates/service-admin-temp/new-service-order-form.php';
	return ob_get_clean();
}
