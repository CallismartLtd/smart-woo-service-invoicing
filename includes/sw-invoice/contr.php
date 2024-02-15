<?php

/**
 * File name    :   contr.php
 * @author      :   Callistus
 * Description  :   Controller file for Smart Woo Invoice Object
 */

/**
 * Edit invoice page controller
 * @param string   $invoice_id      The ID of the Invoice to be edited
 */

function sw_edit_invoice_page() {
    // Assuming the invoice ID is passed in the URL as 'invoice_id'
    $invoice_id = isset( $_GET['invoice_id'] ) ? sanitize_text_field( $_GET['invoice_id'] ) : null;
    echo '<h2>Edit Invoice 📄</h2>';

    // Fetch the existing invoice data based on the provided invoice_id
    $existingInvoice = Sw_Invoice_Database::get_invoice_by_id( $invoice_id );

    if ( $existingInvoice ) {
        // Handle form submission
        if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['sw_update_invoice'] ) ) {
            // Sanitize and validate inputs
            $user_id = isset( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : $existingInvoice->getUserId();
            $product_id = isset( $_POST['product_id']) ? absint( $_POST['product_id'] ) : $existingInvoice->getProductId();
            $invoice_type = isset( $_POST['invoice_type'] ) ? sanitize_text_field( $_POST['invoice_type'] ) : $existingInvoice->getInvoiceType();
            $service_id = isset( $_POST['service_id'] ) ? sanitize_text_field( $_POST['service_id'] ) : null;
            $fee = isset( $_POST['fee'] ) ? floatval( $_POST['fee'] ) : $existingInvoice->getFee();
            $payment_status = isset( $_POST['payment_status'] ) ? sanitize_text_field( $_POST['payment_status'] ) : $existingInvoice->getPaymentStatus();
            $due_date = isset( $_POST['due_date'] ) ? sanitize_text_field($_POST['due_date'] ) : null;

            // Validate inputs
            $errors = array();
            if ( empty( $user_id ) ) {
                $errors[] = 'Select a user.';
            }

            if ( empty( $product_id ) ) {
                $errors[] = 'Select a product';
            }

            // If there are no errors, update the invoice
            if (empty($errors)) {

                // Get the product price dynamically from WooCommerce
                $amount = wc_get_product( $product_id )->get_price();

                // Calculate the total by adding the fee (if provided)
                $total = $amount + ($fee ?? 0);

                // Update the existing invoice with the new data
                $existingInvoice->setAmount( $amount );
                $existingInvoice->setTotal( $total );
                $existingInvoice->setUserId( $user_id);
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
                    echo "Invoice updated successfully! ID: $invoice_id";
                } else {
                    echo "Failed to update the invoice.";
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
    if ( $_SERVER[ 'REQUEST_METHOD' ] === 'POST' && isset($_POST[ 'create_invoice' ] ) ) {
        // Sanitize and validate inputs
        $user_id        = isset( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : 0;
        $product_id     = isset( $_POST['product_id']) ? absint( $_POST['product_id'] ) : 0;
        $invoice_type   = isset( $_POST['invoice_type'] ) ? sanitize_text_field( $_POST['invoice_type'] ) : '';
        $service_id     = isset( $_POST['service_id'] ) ? sanitize_text_field( $_POST['service_id'] ) : '';
        $due_date       = isset( $_POST['due_date'] ) ? sanitize_text_field( $_POST['due_date']) : '';
        $fee            = isset( $_POST['fee'] ) ? floatval( $_POST['fee'] ) : 0.0;
        $payment_status = isset( $_POST['payment_status'] ) ? sanitize_text_field( $_POST['payment_status'] ) : 'unpaid';
        
        //Check for a duplicate unpaid invoice for a service
        $existing_invoice_type_for_a_service = sw_evaluate_service_invoices( $service_id, $invoice_type, 'unpaid' );


        // Validate inputs
        $errors = array();
        if ( $existing_invoice_type_for_a_service ) {
            $errors[] = 'This Service has "' .$invoice_type . '" That is ' .$payment_status;
        }
        if ( empty( $user_id ) ) {
            $errors[] = 'User ID is required.';
        }
        if ( empty( $product_id ) ) {
            $errors[] = 'Service Product is required.';
        }
        if ( empty($invoice_type) || $invoice_type === 'select_invoice_type' ) {
            $errors[] = 'Please select a valid Invoice Type.';
        }
        if ( empty( $due_date ) ) {
            $errors[] = 'Due Date is required';
        }

        // If there are no errors, create the invoice
        if (empty($errors)) {
            // Call the function to create a new invoice
            $createdInvoiceID = sw_generate_new_invoice( $user_id, $product_id, $payment_status, $invoice_type, $service_id, $fee, $due_date );

            // Check the result
            if ($createdInvoiceID !== false) {
                $detailsPageURL = admin_url("admin.php?page=sw-invoices&action=view-invoice&invoice_id=$createdInvoiceID" );
                echo "Invoice created successfully! <a href='$detailsPageURL'>View Invoice Details</a>";
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


