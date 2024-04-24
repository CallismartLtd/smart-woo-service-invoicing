<?php
/**
 * File name    :   contr.php
 * Description  :   Controller file for Smart Woo Invoice Object
 *
 * @author  Callistus
 * @package SmartWooAdminTemplates
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access.

/**
 * Edit invoice page controller.
 */
function smartwoo_edit_invoice_page() {

	$invoice_id	= isset( $_GET['invoice_id'] ) ? sanitize_key( $_GET['invoice_id'] ) : null; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$page_html	= '<h2>Edit Invoice 📄</h2>';
	if ( empty( $invoice_id ) ) {
		$page_html .= smartwoo_error_notice( 'Missing Invoice ID' );
		return $page_html;
	}
	$existingInvoice = SmartWoo_Invoice_Database::get_invoice_by_id( $invoice_id );

	if ( empty( $existingInvoice ) ) {
		return smartwoo_error_notice( 'Invoice not found' );
	}

	if ( isset( $_POST['sw_update_invoice'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sw_edit_invoice_nonce'] ) ), 'sw_edit_invoice_nonce' ) ) {
		// Sanitize and validate inputs
		$user_id        = isset( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : $existingInvoice->getUserId();
		$product_id     = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : $existingInvoice->getProductId();
		$invoice_type   = isset( $_POST['invoice_type'] ) ? sanitize_text_field( $_POST['invoice_type'] ) : $existingInvoice->getInvoiceType();
		$service_id     = isset( $_POST['service_id'] ) ? sanitize_text_field( $_POST['service_id'] ) : null;
		$fee            = isset( $_POST['fee'] ) ? floatval( $_POST['fee'] ) : $existingInvoice->getFee();
		$payment_status = isset( $_POST['payment_status'] ) ? sanitize_text_field( $_POST['payment_status'] ) : $existingInvoice->getPaymentStatus();
		$due_date       = isset( $_POST['due_date'] ) ? sanitize_text_field( $_POST['due_date'] ) : null;

		// Validate inputs
		$errors = array();
		if ( empty( $user_id ) ) {
			$errors[] = 'Select a user.';
		}

		if ( empty( $product_id ) ) {
			$errors[] = 'Select a product.';
		}

		if ( ! empty( $errors ) ) {

			return smartwoo_error_notice( $errors );
		}

		if ( empty( $errors ) ) {

			$amount = wc_get_product( $product_id )->get_price();
			$total = $amount + ( $fee ?? 0 );

			$existingInvoice->setAmount( floatval( $amount ) );
			$existingInvoice->setTotal( floatval( $total ) );
			$existingInvoice->setUserId( absint( $user_id ) );
			$existingInvoice->setProductId( absint( $product_id ) );
			$existingInvoice->setInvoiceType( sanitize_text_field( $invoice_type ) );
			$existingInvoice->setServiceId( sanitize_text_field( $service_id ) );
			$existingInvoice->setFee( $fee );
			$existingInvoice->setPaymentStatus( sanitize_text_field( $payment_status ) );
			$existingInvoice->setDateDue( sanitize_text_field( $due_date ) );

			// Call the method to update the invoice in the database
			$updated = SmartWoo_Invoice_Database::update_invoice( $existingInvoice );

			// Check the result
			if ( $updated ) {
				$page_html .= esc_html( "Invoice updated successfully! ID: $invoice_id" );
			} else {
				$page_html .= 'Failed to update the invoice.';
			}
		}
	}

	// Output the edit invoice form
	$page_html .= smartwoo_edit_invoice_form( $existingInvoice );
	return $page_html;
}


/**
 * Function to handle creating a new invoice form
 */
function smartwoo_new_invoice_page() {
	$page_html = '<h2>Create New Invoice 📄</h2>';

	if ( isset( $_POST['create_invoice'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sw_create_invoice_nonce'] ) ), 'sw_create_invoice_nonce' ) ) {
		$user_id        = isset( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : 0;
		$product_id     = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		$invoice_type   = isset( $_POST['invoice_type'] ) ? sanitize_text_field( wp_unslash( $_POST['invoice_type'] ) ) : '';
		$service_id     = isset( $_POST['service_id'] ) ? sanitize_text_field( wp_unslash( $_POST['service_id'] ) ) : '';
		$date           = isset( $_POST['due_date'] ) ? sanitize_text_field( wp_unslash( $_POST['due_date'] ) ) : '';				
		$fee            = isset( $_POST['fee'] ) ? floatval( $_POST['fee'] ) : 0;
		$payment_status = isset( $_POST['payment_status'] ) ? sanitize_text_field( $_POST['payment_status'] ) : 'unpaid';
		// Check for a duplicate unpaid invoice for a service.
		$existing_invoice_type_for_a_service = smartwoo_evaluate_service_invoices( $service_id, $invoice_type, 'unpaid' );

		// Validate inputs.
		$errors = array();
		if ( $existing_invoice_type_for_a_service ) {
			$errors[] = 'This Service has "' . $invoice_type . '" That is ' . $payment_status;
		}

		if ( empty( $user_id ) ) {
			$errors[] = 'User ID is required.';
		}

		if ( empty( $product_id ) ) {
			$errors[] = 'Service Product is required.';
		}

		if ( empty( $invoice_type ) ) {
			$errors[] = 'Please select a valid Invoice Type.';
		}

		if ( empty( $payment_status ) ) {
			$errors[] = 'Please select Payment Status';
		}

		if ( empty( $date ) ) {
			$errors[] = 'Due Date is required';
		}

		// If there are no errors, create the invoice
		if ( empty( $errors ) ) {
			// We need to format the date input without relying on automatic conversion from database.
			$datetime = DateTime::createFromFormat( 'Y-m-d\TH:i', $date );
			$due_date = $datetime->format( 'Y-m-d H:i:s' );
			$createdInvoiceID = smartwoo_create_invoice( $user_id, $product_id, $payment_status, $invoice_type, $service_id, $fee, $due_date );

			if ( $createdInvoiceID !== false ) {
				$detailsPageURL = esc_url( admin_url( "admin.php?page=sw-invoices&tab=view-invoice&invoice_id=$createdInvoiceID" ) );
				$page_html .= 'Invoice created successfully! <a href="' . esc_url( $detailsPageURL ) .'">' . __( 'View Invoice Details', 'smart-woo-service-invoicing' ) .'</a>';
			} else {
				$page_html .= smartwoo_error_notice( 'Failed to create the invoice.' );
			}
		} else {
			// Display errors.
			$page_html .= smartwoo_error_notice( $errors );
		}
	}
	$page_html .= smartwoo_new_invoice_form();
	return $page_html;
}
