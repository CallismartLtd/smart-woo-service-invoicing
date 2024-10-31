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

	if ( isset( $_POST['sw_update_invoice'], $_POST['sw_edit_invoice_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sw_edit_invoice_nonce'] ) ), 'sw_edit_invoice_nonce' ) ) {
		// Sanitize and validate inputs
		$user_id        = isset( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : $existingInvoice->getUserId();
		$product_id     = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : $existingInvoice->getProductId();
		$invoice_type   = isset( $_POST['invoice_type'] ) ? sanitize_text_field( wp_unslash( $_POST['invoice_type'] ) ) : $existingInvoice->getInvoiceType();
		$service_id     = isset( $_POST['service_id'] ) ? sanitize_text_field( wp_unslash( $_POST['service_id'] ) ) : null;
		$fee            = isset( $_POST['fee'] ) ? floatval( $_POST['fee'] ) : $existingInvoice->getFee();
		$payment_status = isset( $_POST['payment_status'] ) ? sanitize_text_field( wp_unslash( $_POST['payment_status'] ) ) : $existingInvoice->getPaymentStatus();
		$due_date       = isset( $_POST['due_date'] ) ? sanitize_text_field( wp_unslash( $_POST['due_date'] ) ) : null;

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
			$existingInvoice->setFee(floatval( $fee ) );
			$existingInvoice->setPaymentStatus( sanitize_text_field( $payment_status ) );
			$existingInvoice->setDateDue( sanitize_text_field( $due_date ) );

			// Call the method to update the invoice in the database
			$updated = SmartWoo_Invoice_Database::update_invoice( $existingInvoice );

			// Check the result
			if ( $updated ) {
				if ( 'paid' === $payment_status ) {
					$existingInvoice->get_order() ? $existingInvoice->get_order()->update_status( 'completed' ): '';
				}
				$page_html .= esc_html( "Invoice updated successfully! ID: $invoice_id" );
			} else {
				$page_html .= 'Failed to update the invoice.';
			}
		}
	}
	smartwoo_set_document_title( 'Edit Invoice' );
	$page_html .= smartwoo_edit_invoice_form( $existingInvoice );
	return $page_html;
}