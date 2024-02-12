<?php

/**
 * This file contains all the code for the invoice preview page and the code for the 
 * generation of the pdf invoice, we utilized the MPDF library for the generation of the pdf invoice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function view_all_invoices() {
    // Get the current user ID
    $current_user_id = get_current_user_id();

    // Get all invoices for the current user
    $invoices = Sw_Invoice_Database::get_invoices_by_user($current_user_id);
    // Output the service navigation bar
    sw_get_navbar($current_user_id);


    
    if ($invoices) {
        // Output the count of invoices
        echo '<p>Number of Invoices: ' . count($invoices) . '</p>';


        // Display the invoices in a table
        echo '<table class="sw-invoices-table">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Invoice</th>';
        echo '<th>Invoice Date</th>';
        echo '<th>Date Due</th>';
        echo '<th>Total</th>';
        echo '<th>Status</th>';
        echo '<th></th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        foreach ($invoices as $invoice) {

            $dateCreated = $invoice->getDateCreated();
            $datePaid = $invoice->getDatePaid();
            $dateDue = $invoice->getDateDue();

        // Format the dates or display 'Not Available'
        $formattedDateCreated = sw_check_and_format($dateCreated);
        $formattedDateDue = sw_check_and_format($dateDue);

            echo '<tr>';
            echo '<td>' . esc_html($invoice->getInvoiceId()) . '</td>';
            echo '<td>' . esc_html($formattedDateCreated) . '</td>';
            echo '<td>' . esc_html($formattedDateDue) . '</td>';
            echo '<td>' . esc_html($invoice->getTotal()) . '</td>';
            echo '<td style="border: 1px solid #000; padding: 9px; text-align: center;">' . esc_html(ucwords($invoice->getPaymentStatus() ) ) . '</td>';

            echo '<td><a href="?invoice_page=view_invoice&invoice_id=' . esc_attr($invoice->getInvoiceId()) . '" class="sw-blue-button">View Details</a></td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
    } else {
        echo '<p>All your invoices will appear here.</p>';
    }
}


/**
 * Display details of a specific invoice.
 *
 * @param string $invoice_id The ID of the invoice to display.
 */
function view_invoice_details( $invoice_id, $user_id = null ) {
    // If $user_id is null, use the current user's ID
    if ( $user_id === null ) {
        $user_id = get_current_user_id();
    }    // Get the billing details
    $biller_details = sw_biller_details(); //instance of stdClass

    // Get the invoice details
    $invoice = Sw_Invoice_Database::get_invoice_by_id($invoice_id);

    // Check if both invoice and billing details were successfully retrieved
    if ($invoice && $invoice->getUserId() === $user_id && $biller_details instanceof stdClass) {
        // Access individual properties
        $business_name = $biller_details->business_name;
        $invoice_logo_url = $biller_details->invoice_logo_url;
        $admin_phone_number = $biller_details->admin_phone_number;
        $store_address = $biller_details->store_address;
        $store_city = $biller_details->store_city;
        $default_country = $biller_details->default_country;
        // Get the URL of the invoice watermark image from the settings
        $invoice_watermark_url = get_option('sw_invoice_watermark_url', '');
   
    

    $user_data = get_userdata($user_id);
    // Get the first name and last name
    $first_name = $user_data->first_name;
    $last_name = $user_data->last_name;
    $billing_email = get_user_meta( $user_id, 'billing_email', true );
    $billing_phone = get_user_meta($user_id, 'billing_phone', true);
    $customer_company_name = get_user_meta($user_id, 'billing_company', true);
    $customer_billing_address = esc_html($invoice->getBillingAddress());

    //Get related services
    $service_id = $invoice->getServiceId();
    $service = sw_get_service(null, $service_id);

    // Check if $service is not null before accessing properties
    if ($service !== null) {
        // Access the service name from the returned service object
        $service_name = $service->service_name;
        $service_id   = $service->service_id;
    }

    $product = wc_get_product($invoice->getProductId());
    $product_name = $product ? $product->get_name() : 'Product Not Found';
    
    //Invoice Creation Date and Payment Gateway 
    $invoice_date = $invoice->getDateCreated();
    $transaction_date = $invoice->getDatePaid();
    $invoice_due_date = $invoice->getDateDue();
    $invoice_total = $invoice->getTotal();

    //format the date
    $formattedInvoiceDate = sw_check_and_format($invoice_date);
    $formattedPaidDate = sw_check_and_format($transaction_date);
    $formattedDateDue = sw_check_and_format($invoice_due_date);

    $payment_gateway = $invoice->getPaymentGateway();
    // Check if $payment_gateway is not empty or null
    $invoice_payment_gateway = !empty($payment_gateway) ? $payment_gateway : 'Not Available';
    $invoice_status = esc_html($invoice->getPaymentStatus());
    $transactionId = $invoice->getTransactionId();
    $transaction_id = !empty($transactionId) ? $transactionId :'Not Available';

    //Generate the navigation bar
    sw_get_navbar($user_id);

    //The back button
    echo '<br>';
    echo  '<div class="inv-button-container" style="text-align: left;">';
    echo'<a href="' . get_permalink() . '" class="back-button">Back to invoices</a>';

        // Generate the invoice content
        $invoice_content = '<div class="invoice-container">';
        $invoice_content .= '<div class="invoice-preview">';

        // Header section
        $invoice_content .= '<header class="invoice-header">';
        $invoice_content .= '<div class="logo">';
        $invoice_content .= '<img src="' . esc_url($invoice_logo_url) . '" alt="Invoice Logo">';
        $invoice_content .= '</div>';
        $invoice_content .= '<div class="invoice-status">';
        $invoice_content .= '<p>' . esc_html( ucfirst( $invoice_status ) ) . '</p>';
        $invoice_content .= '</div>';
        $invoice_content .= '</header>';

        $invoice_content .= '<div class="invoice-number">';
        $invoice_content .= '<p> Invoice ' . esc_html($invoice->getInvoiceId());

        // Check if the service name is available and append it to the same line
        if ( ! empty( $service_name ) ) {
            $invoice_content .= ' for ' . esc_html( $service_name );
        }

        $invoice_content .= '</p>';
        $invoice_content .= '</div>';


        // Invoice Reference (Client Details) section
        $invoice_content .= '<section class="invoice-details-container">';
        $invoice_content .= '<div class="invoice-details-left">';
        $invoice_content .= '<h3>Invoiced To:</h3>';
        $invoice_content .= '<div class="invoice-customer-info">';
        $invoice_content .= '<p>' . esc_html($customer_company_name) . '</p>';
        $invoice_content .= '<p>' . esc_html($first_name) . ' ' . esc_html($last_name) . '</p>';
        $invoice_content .= '<p>Email: ' . esc_html($billing_email) . '</p>';
        $invoice_content .= '<p>Phone: ' . esc_html($billing_phone) . '</p>';
        $invoice_content .= '<p>' . esc_html($customer_billing_address) . '</p>';
        $invoice_content .= '</div>';
        $invoice_content .= '</div>';

        // Biller details section
        $invoice_content .= '<div class="invoice-details-right">';
        $invoice_content .= '<h3>Pay To:</h3>';
        $invoice_content .= '<div class="invoice-business-info">';
        $invoice_content .= '<p>' . esc_html($business_name) . '</p>';
        $invoice_content .= '<p>' . esc_html($store_address) . '</p>';
        $invoice_content .= '<p>' . esc_html($store_city) . ', ' . esc_html($default_country) . '</p>';
        $invoice_content .= '<p>' . esc_html($admin_phone_number) . '</p>';
        $invoice_content .= '</div>';
        $invoice_content .= '</div>';
        $invoice_content .= '</section>';

        // Invoice Date and Payment Method section
        $invoice_content .= '<section class="invoice-date-payment">';
        $invoice_content .= '<div class="invoice-date">';
        $invoice_content .= '<h4>Invoice Date:</h4>';
        $invoice_content .= '<p>Generated: ' . esc_html($formattedInvoiceDate) . '</p>';
        $invoice_content .= '<p> Due On: ' . esc_html($formattedDateDue) . '</p>';
        $invoice_content .= '</div>';
    
        $invoice_content .= '<div class="payment-method">';
        $invoice_content .= '<h4>Payment Method:</h4>';
        $invoice_content .= '<p>' . esc_html($invoice_payment_gateway) . '</p>';
        $invoice_content .= '</div>';
        $invoice_content .= '</section>';

        // Check if the watermark URL is not empty
        if (!empty($invoice_watermark_url)) {
            // Add the watermark image to the invoice content with CSS styles
            $invoice_content .= '<div class="invoice-watermark">';
            $invoice_content .= '<img src="' . esc_url($invoice_watermark_url) . '" alt="Invoice Watermark" class="watermark-image">';
            $invoice_content .= '</div>';
        }
        
        // Invoice Items section
        $invoice_content .= '<section class="invoice-items">';
        $invoice_content .= '<div class="invoice-card">';

        // Invoice Items header with Description and Amount
        $invoice_content .= '<div class="invoice-card-header">';
        $invoice_content .= '<h4 class="description-heading">Description</h4>';
        $invoice_content .= '<h4 class="amount-heading">Amount</h4>';
        $invoice_content .= '</div>';


        // Display product name and amount
        $invoice_content .= '<div class="invoice-item">';
        $invoice_content .= '<p class="description">' . esc_html($product_name) . '</p>';
        $invoice_content .= '<p class="amount">' . wc_price($invoice->getAmount()) . '</p>';
        $invoice_content .= '</div>';

        // Fee
        $invoice_content .= '<div class="invoice-item">';
        $invoice_content .= '<p class="description">Fee</p>';
        $invoice_content .= '<p class="amount">' . wc_price($invoice->getFee()) . '</p>';
        $invoice_content .= '</div>';

        if($invoice->getInvoiceType() === 'Service Upgrade Invoice'){
            // Previous Service Balance
            $balance = ( $invoice->getAmount() + $invoice->getFee() ) - ( $invoice_total ?? 0 );
            $invoice_content .= '<div class="invoice-item">';
            $invoice_content .= '<p class="description">Previous Service Balance</p>';
            $invoice_content .= '<p class="amount">- ' . max(0, wc_price( $balance ) ) . '</p>';
            $invoice_content .= '</div>';


        }

        // Total
       
        $invoice_content .= '<div class="invoice-item total">';
        $invoice_content .= '<p class="description">Total</p>';
        $invoice_content .= '<p class="amount">' . wc_price($invoice_total) . '</p>';
        $invoice_content .= '</div>';

        $invoice_content .= '</div>'; // Close .invoice-card
        $invoice_content .= '</section>'; // Close Invoice Items section

        // Footer section
        $invoice_content .= '<section class="invoice-footer">';
        $invoice_content .= '<div class="invoice-footer-content">';

        // Mini card container for other items
        $invoice_content .= '<div class="mini-card-container">';

              // Invoice Type
        $invoice_content .= '<div class="mini-card">';
        $invoice_content .= '<p class="footer-label">Invoice Type:</p>';
        $invoice_content .= '<p class="footer-value">' . esc_html($invoice->getInvoiceType()) . '</p>';
        $invoice_content .= '</div>';

        // Transaction Date
        $invoice_content .= '<div class="mini-card">';
        $invoice_content .= '<p class="footer-label">Transaction Date:</p>';
        $invoice_content .= '<p class="footer-value">' . esc_html($formattedPaidDate) . '</p>';
        $invoice_content .= '</div>';

        // Transaction ID
        $invoice_content .= '<div class="mini-card">';
        $invoice_content .= '<p class="footer-label">Transaction ID:</p>';
        $invoice_content .= '<p class="footer-value">' . esc_html($transaction_id) . '</p>';
        $invoice_content .= '</div>';


        // Related Service
        $invoice_content .= '<div class="mini-card">';
        $invoice_content .= '<p class="footer-label">Related Service</p>';
        $invoice_content .= '<p class="footer-value">' . esc_html( $service_id ) . '</p>';
        $invoice_content .= '</div>';



        $invoice_content .= '</div>'; // Close .mini-card-container

        // Thank you message
        $invoice_content .= '<p class="thank-you-message">Thank you for the continued business and support. We value you so much.</p>';
        $invoice_content .= '<p class="regards">Kind Regards,</p>';
        $invoice_content .= '</section>'; // Close invoice-preview

        $invoice_content .= '</div>'; // Close invoice-preview
        $invoice_content .= '</div>'; // Close invoice-container



        // Output the generated invoice content
        echo $invoice_content;

        $download_url = add_query_arg( ['download_invoice' => 'true', 'invoice_id' => $invoice_id, 'user_id' => $user_id], get_permalink() );

        // Add nonce to the URL
        $download_url = wp_nonce_url($download_url, 'download_invoice_nonce');

        $download_button = '<div class="download-button-container">';
        $download_button .= '<a href="' . esc_url($download_url) . '" class="download-button">Download as PDF</a>';
        
        $invoice = Sw_Invoice_Database::get_invoice_by_id($invoice_id);
        $invoice_status = $invoice->getPaymentStatus();

        // Add the "Pay" button if the order is not paid
        if ($invoice_status && strtolower($invoice->getPaymentStatus()) === 'unpaid') {
            $order_id = $invoice->getOrderId();
            $checkout_url = wc_get_checkout_url();
            $order_key = get_post_meta($order_id, '_order_key', true);
            $pay_button_url = add_query_arg(['pay_for_order' => 'true', 'key' => $order_key], $checkout_url . 'order-pay/' . $order_id);
            $download_button .= '<a href="' . esc_url($pay_button_url) . '" class="invoice-pay-button">Complete Payment</a>';
        }

        $download_button .= '</div>';
        echo $download_button;

    } else {
        echo '<p>Invalid invoice ID or you do not have permission to view this invoice.</p>';
    }
}
