<?php

/**
 * File name    :   contr.php
 *
 * @author      :   Callistus
 * Description  :   Controller file for Smart Woo Invoice Object
 */

/**
 * Edit invoice page controller
 *
 * @param string $invoice_id      The ID of the Invoice to be edited
 */
function sw_edit_invoice_page() {
	// Assuming the invoice ID is passed in the URL as 'invoice_id'
	$invoice_id = isset( $_GET['invoice_id'] ) ? sanitize_text_field( $_GET['invoice_id'] ) : null;
	echo '<h2>Edit Invoice 📄</h2>';

	// Fetch the existing invoice data based on the provided invoice_id
	$existingInvoice = Sw_Invoice_Database::get_invoice_by_id( $invoice_id );

	if ( $existingInvoice ) {
		// Handle form submission
		if ( isset( $_POST['sw_update_invoice'] ) && wp_verify_nonce( $_POST['sw_edit_invoice_nonce'], 'sw_edit_invoice_nonce' ) ) {
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
				$errors[] = 'Select a product';
			}

			// If there are no errors, update the invoice
			if ( empty( $errors ) ) {

				// Get the product price dynamically from WooCommerce
				$amount = wc_get_product( $product_id )->get_price();

				// Calculate the total by adding the fee (if provided)
				$total = $amount + ( $fee ?? 0 );

				// Update the existing invoice with the new data
				$existingInvoice->setAmount( $amount );
				$existingInvoice->setTotal( $total );
				$existingInvoice->setUserId( $user_id );
				$existingInvoice->setProductId( $product_id );
				$existingInvoice->setInvoiceType( $invoice_type );
				$existingInvoice->setServiceId( $service_id );
				$existingInvoice->setFee( $fee );
				$existingInvoice->setPaymentStatus( $payment_status );
				$existingInvoice->setDateDue( $due_date );

				// Call the method to update the invoice in the database
				$updated = Sw_Invoice_Database::update_invoice( $existingInvoice );

				// Check the result
				if ( $updated ) {
					echo esc_html( "Invoice updated successfully! ID: $invoice_id" );
				} else {
					echo 'Failed to update the invoice.';
				}
			} else {
				// Display specific errors
				sw_error_notice( $errors );
			}
		}

		// Output the edit invoice form
		sw_render_edit_invoice_form( $existingInvoice );
	} else {
		echo '<div class="invoice-details">';
		wp_die( '<p>Invoice not found.</p>' );
	}
}


/**
 * Function to handle creating a new invoice form
 */
function sw_create_new_invoice_form() {
	echo '<h2>Create New Invoice 📄</h2>';
	// Handle form submission
	if ( isset( $_POST['create_invoice'] ) && wp_verify_nonce( $_POST['sw_create_invoice_nonce'], 'sw_create_invoice_nonce' ) ) {
		// Sanitize and validate inputs
		$user_id        = isset( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : 0;
		$product_id     = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		$invoice_type   = isset( $_POST['invoice_type'] ) ? sanitize_text_field( $_POST['invoice_type'] ) : '';
		$service_id     = isset( $_POST['service_id'] ) ? sanitize_text_field( $_POST['service_id'] ) : '';
		$due_date       = isset( $_POST['due_date'] ) ? sanitize_text_field( $_POST['due_date'] ) : '';
		$fee            = isset( $_POST['fee'] ) ? floatval( $_POST['fee'] ) : 0.0;
		$payment_status = isset( $_POST['payment_status'] ) ? sanitize_text_field( $_POST['payment_status'] ) : 'unpaid';

		// Check for a duplicate unpaid invoice for a service
		$existing_invoice_type_for_a_service = sw_evaluate_service_invoices( $service_id, $invoice_type, 'unpaid' );

		// Validate inputs
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
		if ( empty( $invoice_type ) || $invoice_type === 'select_invoice_type' ) {
			$errors[] = 'Please select a valid Invoice Type.';
		}
		if ( empty( $due_date ) ) {
			$errors[] = 'Due Date is required';
		}

		// If there are no errors, create the invoice
		if ( empty( $errors ) ) {
			// Call the function to create a new invoice
			$createdInvoiceID = sw_generate_new_invoice( $user_id, $product_id, $payment_status, $invoice_type, $service_id, $fee, $due_date );

			// Check the result
			if ( $createdInvoiceID !== false ) {
				$detailsPageURL = esc_url( admin_url( "admin.php?page=sw-invoices&action=view-invoice&invoice_id=$createdInvoiceID" ) );
				echo "Invoice created successfully! <a href='$detailsPageURL'>View Invoice Details</a>"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			} else {
				sw_error_notice( 'Failed to create the invoice.' );
			}
		} else {
			// Display errors
			sw_error_notice( $errors );
		}
	}
	sw_render_create_invoice_form();
}


/**
 * Create an Invoice for Newly configured order
 *
 * @param object        The WooCommerce Order Object
 */
// Hook into action after the user checks out
add_action( 'woocommerce_checkout_order_created', 'sw_create_invoice_for_new_order', 10, 2 );

function sw_create_invoice_for_new_order( $order ) {

	// Check if the order is configured
	$is_configured_order = has_sw_configured_products( $order );

	// Check if the new order has configured products
	if ( $is_configured_order ) {
		// Get all fees associated with the order
		$fees = $order->get_fees();

		// Set target fee name
		$target_fee_name = 'Sign-up Fee';

		$fee = array_reduce(
			$fees,
			function ( $foundFee, $currentFee ) use ( $target_fee_name, $order ) {
				return $currentFee->get_name() === $target_fee_name ? $currentFee : $foundFee;
			},
			0
		);

		// Decode the JSON-encoded fee string
		$fee_data = json_decode( $fee, true );

		// Extract the fee amount
		$fee_amount = isset( $fee_data['total'] ) ? floatval( $fee_data['total'] ) : 0;

		// Get all items in the order
		$order_items = $order->get_items();
		// Extract the order ID
		$order_id = $order->get_id();

		foreach ( $order_items as $item_id => $item ) {
			// Check if the product is of type 'sw_product'
			$product = $item->get_product();
			if ( $product && $product->get_type() === 'sw_product' ) {

				/**
				* Set up the necessary properties for new invoice
				*/

				$invoice_id      = sw_generate_invoice_id();
				$product_id      = $product->get_id();
				$amount          = $product->get_price();
				$total           = $amount + ( $fee_amount ?? 0 );
				$payment_status  = 'unpaid';
				$user_id         = $order->get_user_id();
				$billing_address = sw_get_user_billing_address( $user_id );
				$service_id      = null;
				$invoice_type    = 'New Service Invoice';
				$service_id      = null; // Will be set when Service is processed
				$date_due        = current_time( 'mysql' ); // New Service invoices are due same day

				// generate an invoice for the order
				$newInvoice = new Sw_Invoice(
					$invoice_id,
					$product_id,
					$amount,
					$total,
					$payment_status,
					null, // Date Created will be set to the current date in the constructor
					$user_id,
					$billing_address,
					$invoice_type,
					$service_id,
					$fee_amount,
					$order_id
				);
				$newInvoice->setDateDue( $date_due );

				// Call the sw_create_invoice method to save the invoice to the database
				$new_invoice_id = Sw_Invoice_Database::sw_create_invoice( $newInvoice );

				if ( $new_invoice_id ) {
					$order->update_meta_data( 'Order Type', 'Invoice Payment' );
					$order->update_meta_data( 'Invoice ID', $new_invoice_id );
					$order->update_meta_data( '_wc_order_attribution_source_type', 'Smart Woo Service Invoicing' );

					// Save the order to persist the changes
					$order->save();
				}
			}
		}
	}
}

/**
 * Marks invoice as paid.
 *
 * @param  string $invoice_id   The ID of the invoice to be updated
 * @do_action @param object $invoice  Triggers "sw_invoice_is_paid" action with the invoice instance
 * @return bool     false if the invoice is already 'Paid' | true if update is successful
 */
function sw_mark_invoice_as_paid( $invoice_id ) {
	// Get the invoice associated with the service
	$invoice = Sw_Invoice_Database::get_invoice_by_id( $invoice_id );

	// Check if the invoice is valid and payment_status is not 'paid'
	if ( $invoice && $invoice->getPaymentStatus() !== 'paid' ) {
		// Get the order associated with the invoice
		$order = wc_get_order( $invoice->getOrderId() );

		// Update additional fields in the invoice
		$fields          = array(
			'payment_status'  => 'paid',
			'date_paid'       => current_time( 'Y-m-d H:i:s' ),
			'transaction_id'  => $order->get_transaction_id(), // Use order transaction id
			'payment_gateway' => $order->get_payment_method(), // Use payment gateway used for the order
		);
		$updated_invoice = Sw_Invoice_Database::update_invoice_fields( $invoice_id, $fields );
		do_action( 'sw_invoice_is_paid', $updated_invoice );

		return true;

	} else {
		// Invoice is already paid, terminate further execution
		return false;
	}
}
