<?php

/**
 * File name    :   contr.php
 *
 * @author      :   Callistus
 * Description  :   Controller file for Service
 */


/**
 * Handles add-new service page.
 * This function is responsible for handling the manual creation of a
 * service subscription in the admin area
 */
function smartwoo_new_service_page() {
	$page_html  = '';

	if ( isset( $_POST['add_new_service_submit'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sw_add_new_service_nonce'] ) ), 'sw_add_new_service_nonce' ) ) {

		$user_id           = isset( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : 0;
		$product_id        = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		$service_name      = isset( $_POST['service_name'] ) ? sanitize_text_field( wp_unslash( $_POST['service_name'] ) ) : '';
		$service_type      = isset( $_POST['service_type'] ) ? sanitize_text_field( wp_unslash( $_POST['service_type'] ) ) : '';
		$service_url       = isset( $_POST['service_url'] ) ? esc_url_raw( $_POST['service_url'] ) : '';
		$invoice_id        = isset( $_POST['invoice_id'] ) ? sanitize_text_field( $_POST['invoice_id'] ) : '';
		$start_date        = isset( $_POST['start_date'] ) ? sanitize_text_field( $_POST['start_date'] ) : '';
		$billing_cycle     = isset( $_POST['billing_cycle'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_cycle'] ) ) : '';
		$next_payment_date = isset( $_POST['next_payment_date'] ) ? sanitize_text_field( $_POST['next_payment_date'] ) : '';
		$end_date          = isset( $_POST['end_date'] ) ? sanitize_text_field( $_POST['end_date'] ) : '';
		$status            = isset( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : '';
		$our_billing_cycles = array(
			'Monthly',
			'Quarterly',
			'Six Monthly',
			'Yearly',
		);
		// Validation
		$validation_errors = array();

		if ( ! preg_match( '/^[A-Za-z0-9\s]+$/', $service_name ) ) {
			$validation_errors[] = 'Service name should only contain letters, and numbers.';
		}

		if ( ! in_array( $billing_cycle, $our_billing_cycles, true ) ) {
			$validation_errors[] = 'Billing Cycle should not be modified';
		}

		if ( ! empty( $service_type ) && ! preg_match( '/^[A-Za-z0-9\s]+$/', $service_type ) ) {
			$validation_errors[] = 'Service type should only contain letters, numbers, and spaces.';
		}

		if ( ! empty( $service_url ) && filter_var( $service_url, FILTER_VALIDATE_URL ) === false ) {
			$validation_errors[] = 'Invalid service URL format.';
		}

		if ( empty( $product_id ) ) {
			$validation_errors[] = 'A product is required to set up a service.';
		}

		if ( empty( $start_date ) || empty( $end_date ) || empty( $next_payment_date ) || empty( $billing_cycle ) ) {
			$validation_errors[] = 'All Dates must correspond to the billing circle';
		}

		if ( ! empty( $validation_errors ) ) {
			// Display validation errors.
			$page_html .= smartwoo_error_notice( $validation_errors );
		} else {
			// Create a new Sw_Service object
			$newservice = sw_generate_service(
				$user_id,
				$product_id,
				$service_name,
				$service_url,
				$service_type,
				$invoice_id,
				$start_date,
				$end_date,
				$next_payment_date,
				$billing_cycle,
				$status
			);

			if ( $newservice ) {
				$service_id_value = $newservice->getServiceId();
				$details_url      = admin_url( 'admin.php?page=sw-admin&action=view-service&service_id=' . $service_id_value );
				$page_html       .= '<div class="notice notice-success is-dismissible"><p><strong>Service successfully added.</strong> <a href="' . esc_url( $details_url ) . '">View Details</a></p></div>';
			}
		}
	}
	
	$page_html .= smartwoo_new_service_form();
	return $page_html;
}

/**
 * Handle edit service page
 */
function smartwoo_process_edit_service_form( $service ) {
	$page_html = '';

	if ( isset( $_POST['edit_service_submit'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sw_edit_service_nonce'] ) ), 'sw_edit_service_nonce' ) ) {
		
		// Initialize an array to store validation errors.
		$errors 	= array();
		$user_id    = isset( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : 0;
		$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		// Validate Service Name.
		$service_name = isset( $_POST['service_name'] ) ? sanitize_text_field( $_POST['service_name'] ) : '';
		
		if ( ! preg_match( '/^[a-zA-Z0-9\s]+$/', $service_name ) ) {
			$errors['service_name'] = 'Service Name should only contain letters, numbers, and spaces.';
		}

		// Validate Service Type
		$service_type = isset( $_POST['service_type'] ) ? sanitize_text_field( $_POST['service_type'] ) : '';
		if ( ! empty( $service_type ) && ! preg_match( '/^[a-zA-Z0-9\s]+$/', $service_type ) ) {
			$errors['service_type'] = 'Service Type should only contain letters, numbers, and spaces.';
		}
		// Validate Service URL
		$service_url = isset( $_POST['service_url'] ) ? esc_url_raw( $_POST['service_url'] ) : '';
		if ( ! empty( $service_url ) && ( ! filter_var( $service_url, FILTER_VALIDATE_URL ) || strpos( $service_url, ' ' ) !== false ) ) {
			$errors['service_url'] = 'Service URL should be a valid URL without spaces.';
		}

		$invoice_id        = isset( $_POST['invoice_id'] ) ? sanitize_text_field( $_POST['invoice_id'] ) : '';
		$start_date        = isset( $_POST['start_date'] ) ? sanitize_text_field( $_POST['start_date'] ) : '';
		$billing_cycle     = isset( $_POST['billing_cycle'] ) ? sanitize_text_field( $_POST['billing_cycle'] ) : '';
		$next_payment_date = isset( $_POST['next_payment_date'] ) ? sanitize_text_field( $_POST['next_payment_date'] ) : '';
		$end_date          = isset( $_POST['end_date'] ) ? sanitize_text_field( $_POST['end_date'] ) : '';
		$status            = isset( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : '';

		// Check for validation errors before updating
		if ( empty( $errors ) ) {

			$service->setUserId( $user_id );
			$service->setProductId( $product_id );
			$service->setServiceName( $service_name );
			$service->setServiceType( $service_type );
			$service->setServiceUrl( $service_url );
			$service->setInvoiceId( $invoice_id );
			$service->setStartDate( $start_date );
			$service->setBillingCycle( $billing_cycle );
			$service->setNextPaymentDate( $next_payment_date );
			$service->setEndDate( $end_date );
			$service->setStatus( $status );

			// Perform the update
			$updated = Sw_Service_Database::update_service( $service );
			if ( $status === 'Cancelled' || $status === 'Suspended' || $status === 'Expired' && $service_type === 'Web Service' ) {
				do_action( 'sw_service_deactivated', $service );
			} else {
				do_action( 'sw_service_active', $service );
			}

			if ( $updated ) {
				$page_html .= smartwoo_notice('Service updated.', true );
			} else {
				$page_html .= smartwoo_error_notice( 'Failed to update the service.' );
			}
		} else {
			// Display validation errors
			$page_html .= smartwoo_error_notice( $errors );

		}
	} 
    echo wp_kses_post( $page_html );
}


/**
 * New Service processing page controller
 */
function smartwoo_process_new_service_order_page() {

	// Get the order ID from the query parameter
	$order_id = isset( $_GET['order_id'] ) ? absint( $_GET['order_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	$is_configured_order = smartwoo_check_if_configured( $order_id );

	if ( $order_id > 0 && true === $is_configured_order ) {
		if ( 'processing' !== wc_get_order( $order_id )->get_status() ) {
			return smartwoo_error_notice( 'This order can no longer be processed.' );
		}

		return smartwoo_convert_wc_order_to_smartwoo_service( $order_id );

	} else {
		return smartwoo_error_notice( 'This order is not configured for service subscription.' );
	}
}
