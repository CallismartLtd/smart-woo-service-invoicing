<?php

/**
 * File name    :   sw-new-service-processing.php
 *
 * @author      :   Callistus
 * Description  :   Handles new service processing from the admin area
 */

 defined( 'ABSPATH' ) || exit; // Prevent direct access
 
/**
 * Call back function for new service order admin page
 */
function sw_render_order_for_sw_products() {
	echo '<h1 class="wp-heading-inline">Service Orders</h1>';

	$order_ids = sw_get_orders_for_configured_products();

	if ( ! empty( $order_ids ) ) {
		echo '<table class="wp-list-table widefat fixed striped">';
		echo '<thead>';
		echo '<tr>';
		echo '<th>Order ID</th>';
		echo '<th> Date Created</th>';
		echo '<th>Status</th>';
		echo '<th>Service Name</th>';
		echo '<th>Client\'s Name</th>';
		echo '<th>Action</th>';
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';

		foreach ( $order_ids as $order_id ) {
			// Get order details
			$order = wc_get_order( $order_id );

			if ( ! $order ) {
				continue; // Skip invalid orders
			}

			// Get order status
			$order_status = $order->get_status();
			$created_date = $order->get_date_created()->format( 'Y-m-d H:i:s' );

			// Get user full name
			$user_full_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();

			// Initialize variables for order item details
			$service_name = '';
			$process_url  = '';

			// Check if order has items
			$items = $order->get_items();

			foreach ( $items as $item_id => $item ) {
				// Get the service name from order item meta
				$service_name = wc_get_order_item_meta( $item_id, 'Service Name', true );

				// Break the loop once the service name is found
				if ( $service_name ) {
					break;
				}
			}

			// Display row
			echo '<tr>';
			echo '<td>' . esc_html( $order_id ) . '</td>';
			echo '<td>' . esc_html( $created_date ) . '</td>';
			echo '<td>' . esc_html( ucwords( $order_status ) ) . '</td>';
			echo '<td>' . esc_html( $service_name ) . '</td>';
			echo '<td>' . esc_html( $user_full_name ) . '</td>';

			// Check if the order is in a state where it can be processed
			if ( $order_status === 'processing' ) {
				$process_url = '<a href="' . esc_url( admin_url( "admin.php?page=sw-admin&action=process-new-service&order_id={$order_id}" ) ) . '" class="sw-red-button">Process This Order</a>';
			} elseif ( $order_status === 'pending' ) {
				$process_url = 'This Order is Unpaid';
			} elseif ( $order_status === 'completed' ) {
				$process_url = 'Can no longer be processed';
			} else {
				$process_url = 'Cannot be proceesed';
			}

			echo '<td>' . $process_url . '</td>';
			echo '</tr>';
		}

		echo '</tbody>';
		echo '</table>';
	} else {
		echo '<table class="wp-list-table widefat fixed striped">';
		echo '<thead>';
		echo '<tr>';
		echo '<th>Order ID</th>';
		echo '<th>Status</th>';
		echo '<th>Service Name</th>';
		echo '<th>Client\'s Name</th>';
		echo '<th>Action</th>';
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';
		echo '<td>New Service Orders will appear here.</td>';
		echo '</tbody>';
		echo '</table>';
	}

	echo '<p style="text-align: right;">' . count( $order_ids ) . ' items</p>';
}

/**
 * Conversion of WooCommerce Order to Smart Woo Service subscription.
 *
 * IMPORTANT NOTE: Before calling this function, ensure that the order is of the 'sw_product' type.
 * Failure to verify the order type may lead to unexpected behavior.
 *
 * Helper Function: has_sw_configured_products( $order )
 * This function can be used to check if the order contains configured 'sw_product' types.
 *
 * @param int $order_id Order ID of the new service order.
 */
function sw_convert_WC_order_to_SW_service( $order_id ) {

	// Get order details and user data
	$order          = wc_get_order( $order_id );
	$user_id        = $order->get_user_id();
	$user_info      = get_userdata( $user_id );
	$user_full_name = $order ? $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() : 'Not Found';

	// Get the configured data from order item meta data
	$items = $order->get_items();
	foreach ( $items as $item_id => $item ) {
		$service_name = wc_get_order_item_meta( $item_id, 'Service Name', true );
		$service_url  = wc_get_order_item_meta( $item_id, 'Service URL', true );
	}

	// Convert order paid date to Service subscription start date
	$start_date = $order->get_date_paid() ? date_i18n( 'Y-m-d', strtotime( $order->get_date_paid() ) ) : date( 'Y-m-d' );
	// Instantiate the form variables
	$billing_cycle     = '';
	$next_payment_date = '';
	$end_date          = '';
	$status            = 'Pending';  // Set the initial service status to pending

	// Check if there are items in the order
	$items = $order->get_items();
	if ( ! empty( $items ) ) {
		// Retrieve billing_cycle from the product in the order
		$first_item = reset( $items ); // Get the first item
		$product_id = $first_item->get_product_id();

		// Fetch the billing cycle from product metadata
		$billing_cycle = get_post_meta( $product_id, 'billing_cycle', true );

		// Set next payment date and end date based on billing cycle
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
			// Add additional cases as needed
			default:
				break;
		}
	}

	// Display the form
	sw_render_new_service_order_form( $user_id, $order_id, $service_name, $service_url, $user_full_name, $start_date, $billing_cycle, $next_payment_date, $end_date, $status );
}



/**
 * Handle the processing of new service orders
 */
function sw_process_new_service_order() {
	if ( isset( $_POST['action'] ) && $_POST['action'] === 'sw_process_new_service' ) {
		// Check if the nonce is set and valid
		if ( isset( $_POST['sw_process_new_service_nonce'] ) && wp_verify_nonce( $_POST['sw_process_new_service_nonce'], 'sw_process_new_service_nonce' ) ) {

			// Sanitize and validate form data
			$product_id        = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
			$order_id          = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
			$service_url       = isset( $_POST['service_url'] ) ? esc_url_raw( $_POST['service_url'] ) : '';
			$service_type      = isset( $_POST['service_type'] ) ? sanitize_text_field( $_POST['service_type'] ) : '';
			$user_id           = isset( $_POST['user_id'] ) ? sanitize_text_field( $_POST['user_id'] ) : '';
			$start_date        = isset( $_POST['start_date'] ) ? sanitize_text_field( $_POST['start_date'] ) : '';
			$billing_cycle     = isset( $_POST['billing_cycle'] ) ? sanitize_text_field( $_POST['billing_cycle'] ) : '';
			$next_payment_date = isset( $_POST['next_payment_date'] ) ? sanitize_text_field( $_POST['next_payment_date'] ) : '';
			$end_date          = isset( $_POST['end_date'] ) ? sanitize_text_field( $_POST['end_date'] ) : '';
			$status            = isset( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : '';

			// Retrieve service name and service ID
			$service_name = isset( $_POST['service_name'] ) ? sanitize_text_field( $_POST['service_name'] ) : '';
			$service_id   = isset( $_POST['service_id'] ) ? sanitize_text_field( $_POST['service_id'] ) : '';

			// Validation
			$validation_errors = array();

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
				// Display validation errors using the custom error notice function
				sw_error_notice( $validation_errors );

			} elseif ( empty( $validation_errors ) ) {

				// Create a new Sw_Service object
				$new_service = new Sw_Service(
					$user_id,
					$product_id,
					$service_id,
					$service_name,
					$service_url,
					$service_type,
					null, // Invoice ID is null
					$start_date,
					$end_date,
					$next_payment_date,
					$billing_cycle,
					$status
				);

				// Save the new service to the database
				$saved_service_id = Sw_Service_Database::sw_create_service( $new_service );
			}

			// Check if the service was saved successfully
			if ( ! empty( $saved_service_id ) ) {
				// Update the Order and perform necessary tasks after processing
				$order = wc_get_order( $order_id );
				if ( $order->get_status() === 'processing' ) {
					$order->update_status( 'completed' );
				}
				do_action( 'sw_new_service_is_processed' . $saved_service_id );

				wp_safe_redirect( admin_url( 'admin.php?page=sw-admin&action=service_details&service_id=' . $saved_service_id ) );
				exit;
			}
		} else {
			// Nonce verification failed, handle accordingly (you might want to show an error message)
			wp_die( 'Security check failed!' );
		}
	}
}
