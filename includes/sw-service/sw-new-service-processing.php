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
			$process_url = '<a href="' . esc_url( admin_url( "admin.php?page=sw-admin&action=process-new-service&order_id={$order_id}" ) ) . '" class="sw-red-button">' .__( 'Process Now', 'smart-woo-service-invoicing' ). '</a>';
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
		$billing_cycle = get_post_meta( $product_id, 'billing_cycle', true );

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

	// The form.
	return smartwoo_new_service_order_form( $user_id, $order_id, $service_name, $service_url, $user_full_name, $start_date, $billing_cycle, $next_payment_date, $end_date, $status );
}

/**
 * Handle the processing of new service orders.
 */
function smartwoo_process_new_service_order() {

	if ( isset( $_POST['smartwoo_process_new_service'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sw_process_new_service_nonce'])), 'sw_process_new_service_nonce') ) {

		$product_id        	= isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		$order_id          	= isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
		$service_url       	= isset( $_POST['service_url'] ) ? esc_url_raw( $_POST['service_url'] ) : '';
		$service_type      	= isset( $_POST['service_type'] ) ? sanitize_text_field( $_POST['service_type'] ) : '';
		$user_id           	= isset( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : '';
		$start_date        	= isset( $_POST['start_date'] ) ? sanitize_text_field( $_POST['start_date'] ) : '';
		$billing_cycle     	= isset( $_POST['billing_cycle'] ) ? sanitize_text_field( $_POST['billing_cycle'] ) : '';
		$next_payment_date 	= isset( $_POST['next_payment_date'] ) ? sanitize_text_field( $_POST['next_payment_date'] ) : '';
		$end_date          	= isset( $_POST['end_date'] ) ? sanitize_text_field( $_POST['end_date'] ) : '';
		$status            	= isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : '';
		$service_name 		= isset( $_POST['service_name'] ) ? sanitize_text_field( $_POST['service_name'] ) : '';
		$service_id 		= isset( $_POST['service_id'] ) ? sanitize_text_field( $_POST['service_id'] ) : '';
		// Validation.
		$validation_errors 	= array();

		if ( ! preg_match( '/^[A-Za-z0-9 ]+$/', $service_name ) ) {
			$validation_errors[] = 'Service name should only contain letters, and numbers.';
		}

		if ( ! empty( $service_type ) && ! preg_match( '/^[A-Za-z0-9 ]+$/', $service_type ) ) {
			$validation_errors[] = 'Service type should only contain letters, numbers, and spaces.';
		}

		if ( ! empty( $service_url ) && filter_var( $service_url, FILTER_VALIDATE_URL ) === false ) {
			$validation_errors[] = 'Invalid service URL format.';
		}

		if ( empty( $service_id ) ) {
			$validation_errors[] = 'Service ID is required.';
		}

		if ( empty( $start_date ) || empty( $end_date ) || empty( $next_payment_date ) || empty( $billing_cycle ) ) {
			$validation_errors[] = 'All Dates must correspond to the billing circle';
		}

		if ( ! empty( $validation_errors ) ) {
			smartwoo_error_notice( $validation_errors );
		}

		$new_service = new SmartWoo_Service(
			$user_id,
			$product_id,
			$service_id,
			$service_name,
			$service_url,
			$service_type,
			null, // Invoice ID is null.
			$start_date,
			$end_date,
			$next_payment_date,
			$billing_cycle,
			$status
		);

			$saved_service_id = $new_service->save();

		if ( $saved_service_id ) {
			$order = wc_get_order( $order_id );
			
			if ( 'processing' === $order->get_status()  ) {
				$order->update_status( 'completed' );
			}

			do_action( 'smartwoo_new_service_is_processed' . $saved_service_id );
			wp_safe_redirect( esc_url_raw( admin_url( 'admin.php?page=sw-admin&action=view-service&service_id=' . $saved_service_id ) ) );
			exit;
		}
	}
}
