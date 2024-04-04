<?php
/**
 * This file contains all the code for the invoice preview page and the code for the
 * generation of the pdf invoice, we utilized the MPDF library for the generation of the pdf invoice
 */

 defined( 'ABSPATH' ) || exit; // Prevent direct access


function view_all_invoices() {
    // Get the current user ID
    $current_user_id = get_current_user_id();

    // Get all invoices for the current user
    $invoices = Sw_Invoice_Database::get_invoices_by_user( $current_user_id );
    // Output the service navigation bar
    sw_get_navbar( $current_user_id );

    echo do_shortcode( '[sw_invoice_status_counts]' );

    if ( $invoices ) {

        // Display the invoices in a table
        echo '<div class="sw-table-wrapper">';
        echo '<table class="sw-table">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Invoice ID</th>';
        echo '<th>Invoice Date</th>';
        echo '<th>Date Due</th>';
        echo '<th>Total</th>';
        echo '<th>Status</th>';
        echo '<th>Action</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        foreach ( $invoices as $invoice ) {

            $dateCreated = $invoice->getDateCreated();
            $datePaid    = $invoice->getDatePaid();
            $dateDue     = $invoice->getDateDue();

            // Format the dates or display 'Not Available'
            $formattedDateCreated = smartwoo_check_and_format( $dateCreated, true );
            $formattedDateDue     = smartwoo_check_and_format( $dateDue, true );

            echo '<tr>';
            echo '<td>' . esc_html( $invoice->getInvoiceId() ) . '</td>';
            echo '<td>' . esc_html( $formattedDateCreated ) . '</td>';
            echo '<td>' . esc_html( $formattedDateDue ) . '</td>';
            echo '<td>' . wc_price( $invoice->getTotal() ) . '</td>';
            echo '<td class="payment-status">' . esc_html( ucwords( $invoice->getPaymentStatus() ) ) . '</td>';

            echo '<td><a href="?invoice_page=view_invoice&invoice_id=' . esc_attr( $invoice->getInvoiceId() ) . '" class="invoice-preview-button">View Details</a></td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        echo '<p class="sw-table-count">' . count( $invoices ) . ' item(s)</p>';

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
	$biller_details = sw_biller_details(); // instance of stdClass

	// Get the invoice details
	$invoice = Sw_Invoice_Database::get_invoice_by_id( $invoice_id );

	// Check if both invoice and billing details were successfully retrieved
	if ( $invoice && $invoice->getUserId() === $user_id ) {
		// Access individual properties
		$business_name         = $biller_details->business_name;
		$invoice_logo_url      = $biller_details->invoice_logo_url;
		$admin_phone_number    = $biller_details->admin_phone_number;
		$store_address         = $biller_details->store_address;
		$store_city            = $biller_details->store_city;
		$default_country       = $biller_details->default_country;
		$invoice_watermark_url = get_option( 'sw_invoice_watermark_url', '' );

		$user_data = get_userdata( $user_id );
		// Get the first name and last name
		$first_name               = $user_data->first_name;
		$last_name                = $user_data->last_name;
		$billing_email            = get_user_meta( $user_id, 'billing_email', true );
		$billing_phone            = get_user_meta( $user_id, 'billing_phone', true );
		$customer_company_name    = get_user_meta( $user_id, 'billing_company', true );
		$customer_billing_address = esc_html( $invoice->getBillingAddress() );

		// Get related services
		$service_id = $invoice->getServiceId();
		$service    = sw_get_service( null, $service_id );

		// Check if $service is not null before accessing properties
		if ( $service !== null ) {
			// Access the service name from the returned service object
			$service_name = $service->service_name;
			$service_id   = $service->service_id;
		}

		$product      = wc_get_product( $invoice->getProductId() );
		$product_name = $product ? $product->get_name() : 'Product Not Found';

		// Invoice Creation Date and Payment Gateway
		$invoice_date     = $invoice->getDateCreated();
		$transaction_date = $invoice->getDatePaid();
		$invoice_due_date = $invoice->getDateDue();
		$invoice_total    = $invoice->getTotal();

		// format the date
		$formattedInvoiceDate = smartwoo_check_and_format( $invoice_date, true );
		$formattedPaidDate    = smartwoo_check_and_format( $transaction_date, true );
		$formattedDateDue     = smartwoo_check_and_format( $invoice_due_date, true );

		$payment_gateway = $invoice->getPaymentGateway();
		// Check if $payment_gateway is not empty or null
		$invoice_payment_gateway = ! empty( $payment_gateway ) ? $payment_gateway : 'Not Available';
		$invoice_status          = esc_html( $invoice->getPaymentStatus() );
		$transactionId           = $invoice->getTransactionId();
		$transaction_id          = ! empty( $transactionId ) ? $transactionId : 'Not Available';

		// Generate the navigation bar
		sw_get_navbar( $user_id );

		// The back button
		echo '<br>';
		echo '<div class="inv-button-container" style="text-align: left;">';
		echo '<a href="' . esc_url( get_permalink() ) . '" class="back-button">Back to invoices</a>';
		echo '</div>';

		// Generate the invoice content
		$invoice_content  = '<div class="invoice-container">';
		$invoice_content .= '<div class="invoice-preview">';

		// Header section
		$invoice_content .= '<header class="invoice-header">';
		$invoice_content .= '<div class="logo">';
		$invoice_content .= '<img src="' . esc_url( $invoice_logo_url ) . '" alt="Invoice Logo">';
		$invoice_content .= '</div>';
		$invoice_content .= '<div class="invoice-status">';
		$invoice_content .= '<p>' . esc_html( ucfirst( $invoice_status ) ) . '</p>';
		$invoice_content .= '</div>';
		$invoice_content .= '</header>';

		$invoice_content .= '<div class="invoice-number">';
		$invoice_content .= '<p> Invoice ' . esc_html( $invoice->getInvoiceId() );

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
		$invoice_content .= '<p>' . esc_html( $customer_company_name ) . '</p>';
		$invoice_content .= '<p>' . esc_html( $first_name ) . ' ' . esc_html( $last_name ) . '</p>';
		$invoice_content .= '<p>Email: ' . esc_html( $billing_email ) . '</p>';
		$invoice_content .= '<p>Phone: ' . esc_html( $billing_phone ) . '</p>';
		$invoice_content .= '<p>' . esc_html( $customer_billing_address ) . '</p>';
		$invoice_content .= '</div>';
		$invoice_content .= '</div>';

		// Biller details section
		$invoice_content .= '<div class="invoice-details-right">';
		$invoice_content .= '<h3>Pay To:</h3>';
		$invoice_content .= '<div class="invoice-business-info">';
		$invoice_content .= '<p>' . esc_html( $business_name ) . '</p>';
		$invoice_content .= '<p>' . esc_html( $store_address ) . '</p>';
		$invoice_content .= '<p>' . esc_html( $store_city ) . ', ' . esc_html( $default_country ) . '</p>';
		$invoice_content .= '<p>' . esc_html( $admin_phone_number ) . '</p>';
		$invoice_content .= '</div>';
		$invoice_content .= '</div>';
		$invoice_content .= '</section>';

		// Invoice Date and Payment Method section
		$invoice_content .= '<section class="invoice-date-payment">';
		$invoice_content .= '<div class="invoice-date">';
		$invoice_content .= '<h4>Invoice Date:</h4>';
		$invoice_content .= '<p>Generated: ' . esc_html( $formattedInvoiceDate ) . '</p>';
		$invoice_content .= '<p> Due On: ' . esc_html( $formattedDateDue ) . '</p>';
		$invoice_content .= '</div>';

		$invoice_content .= '<div class="payment-method">';
		$invoice_content .= '<h4>Payment Method:</h4>';
		$invoice_content .= '<p>' . esc_html( $invoice_payment_gateway ) . '</p>';
		$invoice_content .= '</div>';
		$invoice_content .= '</section>';

		// Check if the watermark URL is not empty
		if ( ! empty( $invoice_watermark_url ) ) {
			// Add the watermark image to the invoice content with CSS styles
			$invoice_content .= '<div class="invoice-watermark">';
			$invoice_content .= '<img src="' . esc_url( $invoice_watermark_url ) . '" alt="Invoice Watermark" class="watermark-image">';
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
		$invoice_content .= '<p class="description">' . esc_html( $product_name ) . '</p>';
		$invoice_content .= '<p class="amount">' . wc_price( $invoice->getAmount() ) . '</p>';
		$invoice_content .= '</div>';

		// Fee
		$invoice_content .= '<div class="invoice-item">';
		$invoice_content .= '<p class="description">Fee</p>';
		$invoice_content .= '<p class="amount">' . wc_price( $invoice->getFee() ) . '</p>';
		$invoice_content .= '</div>';

		if ( $invoice->getInvoiceType() === 'Service Upgrade Invoice' || $invoice->getInvoiceType() === 'Service Downgrade Invoice' ) {
			// Previous Service Balance
			$balance          = Sw_Invoice_log::get_logs_by_criteria( 'log_id', $invoice_id, true )->getAmount();
			$invoice_content .= '<div class="invoice-item">';
			$invoice_content .= '<p class="description">Previous Service Balance</p>';
			$invoice_content .= '<p class="amount">' . max( 0, wc_price( $balance ) ) . '</p>';
			$invoice_content .= '</div>';

		}

		// Total

		$invoice_content .= '<div class="invoice-item total">';
		$invoice_content .= '<p class="description">Total</p>';
		$invoice_content .= '<p class="amount">' . wc_price( $invoice_total ) . '</p>';
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
		$invoice_content .= '<p class="footer-value">' . esc_html( $invoice->getInvoiceType() ) . '</p>';
		$invoice_content .= '</div>';

		// Transaction Date
		$invoice_content .= '<div class="mini-card">';
		$invoice_content .= '<p class="footer-label">Transaction Date:</p>';
		$invoice_content .= '<p class="footer-value">' . esc_html( $formattedPaidDate ) . '</p>';
		$invoice_content .= '</div>';

		// Transaction ID
		$invoice_content .= '<div class="mini-card">';
		$invoice_content .= '<p class="footer-label">Transaction ID:</p>';
		$invoice_content .= '<p class="footer-value">' . esc_html( $transaction_id ) . '</p>';
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

		$download_url = add_query_arg(
			array(
				'download_invoice' => 'true',
				'invoice_id'       => $invoice_id,
				'user_id'          => $user_id,
			),
			get_permalink()
		);

		// Add nonce to the URL
		$download_url = wp_nonce_url( $download_url, 'download_invoice_nonce' );

		$download_button  = '<div class="download-button-container">';
		$download_button .= '<a href="' . esc_url( $download_url ) . '" class="download-button">Download as PDF</a>';

		$invoice        = Sw_Invoice_Database::get_invoice_by_id( $invoice_id );
		$invoice_status = $invoice->getPaymentStatus();

		// Add the "Pay" button if the order is not paid
		if ( $invoice_status && strtolower( $invoice->getPaymentStatus() ) === 'unpaid' ) {
			$order_id         = $invoice->getOrderId();
			$checkout_url     = wc_get_checkout_url();
			$order_key        = get_post_meta( $order_id, '_order_key', true );
			$pay_button_url   = add_query_arg(
				array(
					'pay_for_order' => 'true',
					'key'           => $order_key,
				),
				$checkout_url . 'order-pay/' . $order_id
			);
			$download_button .= '<a href="' . esc_url( $pay_button_url ) . '" class="invoice-pay-button">Complete Payment</a>';
		}

		$download_button .= '</div>';
		echo $download_button;

	} else {
		echo '<p>Invalid invoice ID or you do not have permission to view this invoice. "' . esc_attr( $invoice_id ) . '"</p>';
	}
}



function sw_invoice_mini_card_shortcode() {
    // Check if the user is logged in
    if ( ! is_user_logged_in() ) {
        return esc_html("Hello! It looks like you're not logged in.");
    }

    // Get the current logged-in user's ID
    $current_user_id = get_current_user_id();
    $table_html      = "<div class='mini-card'>";
    $table_html     .= '<h2>' . esc_html('My Invoices') . '</h2>';
    // Start the table markup
    $table_html .= '<table>';

    // Get the invoice page URL
    $invoice_preview_page = get_option( 'sw_invoice_page', 0 );
    $invoice_page_url     = esc_url( get_permalink( $invoice_preview_page ) );
    // Get all invoices for the current user
    $all_invoices = SW_Invoice_Database::get_invoices_by_user( $current_user_id );

    if ( $all_invoices ) {
        foreach ( $all_invoices as $invoice ) {
            $invoice_id     = esc_html( $invoice->getInvoiceId() );
            $generated_date = esc_html( smartwoo_check_and_format( $invoice->getDateCreated() ) );
            $order_id       = esc_html( $invoice->getOrderId() );
            $order_key      = esc_attr( get_post_meta( $order_id, '_order_key', true ) );

            // Add a table row for each order
            $table_html .= "<tr>
                <td class='invoice-table-heading'>" . esc_html('Invoice ID:') . "</td>
                <td class='invoice-table-value'>$invoice_id</td>
            </tr>";

            $table_html .= "<tr>
                <td class='invoice-table-heading'>" . esc_html('Date:') . "</td>
                <td class='invoice-table-value'>" . esc_html("Generated on - $generated_date") . "</td>
            </tr>";

            $preview_invoice_url = esc_url( get_permalink( $invoice_preview_page ) . '?invoice_page=view_invoice&invoice_id=' . $invoice_id );

            $table_html .= "<tr>
                <td class='invoice-table-heading'>" . esc_html('Action:') . "</td>
                <td class='invoice-table-value'><a href='$preview_invoice_url' class='invoice-preview-button'>" . esc_html('View') . "</a>";

            // Show the "Pay" button beside the "View" button only if the order is pending
            if ( $invoice->getPaymentStatus() === 'unpaid' ) {
                $checkout_url  = esc_url( wc_get_checkout_url() );
                $order_pay_url = esc_url( $checkout_url . 'order-pay/' . $order_id . '/?pay_for_order=true&key=' . $order_key );
                $table_html   .= "<a href='$order_pay_url' class='invoice-pay-button'>" . esc_html('Pay') . "</a>";
            }

            $table_html .= '</td></tr>';

            // Add an empty row for spacing
            $table_html .= "<tr><td colspan='2'></td></tr>";
        }
    } else {
        // Add a message if no invoice is found
        $table_html .= "<tr><td colspan='2'>" . esc_html('All your invoices will appear here.') . "</td></tr>";
    }

    // Close the table markup
    $table_html .= '</table>';

    $table_html .= '</div>'; // Close mini card

    $table_html .= "<div style='text-align: center;'>";
    $table_html .= "<p><a href='$invoice_page_url' class='sw-blue-button'>" . esc_html('View All Invoices') . "</a></p>";
    $table_html .= '</div>';

    // Return the table HTML
    return $table_html;
}

/**
 * Counts and renders payment status counts of all invoice for the current user
 */
function sw_get_invoice_status_count() {
	// Check if the user is logged in
	if ( ! is_user_logged_in() ) {
		return "Hello! It looks like you're not logged in.";
	}

	// Get the current logged-in user's ID
	$current_user_id = get_current_user_id();

	// Get counts for each payment status for the current user
	$counts = array(
		'paid'      => Sw_Invoice_Database::get_invoice_count_by_payment_status_for_user( $current_user_id, 'paid' ),
		'unpaid'    => Sw_Invoice_Database::get_invoice_count_by_payment_status_for_user( $current_user_id, 'unpaid' ),
		'cancelled' => Sw_Invoice_Database::get_invoice_count_by_payment_status_for_user( $current_user_id, 'cancelled' ),
		'due'       => Sw_Invoice_Database::get_invoice_count_by_payment_status_for_user( $current_user_id, 'due' ),
	);

	// Generate the HTML
	$output = '<div class="invoice-status-counts">';
	foreach ( $counts as $status => $count ) {
		$output .= '<div class="status-item">';
		$output .= '<p>' . ucfirst( $status ) . ' (' . $count . ')</p>';
		$output .= '</div>';
	}
	$output .= '</div>';

	return $output;
}


/**
 * ShortCode for Unpaid Invoice Count
 */
function sw_get_unpaid_invoices_count() {
	// Check if the user is logged in
	if ( ! is_user_logged_in() ) {
		return "Hello! It looks like you\'re not logged in.";
	} else {
		// Get the current logged-in user's ID
		$current_user_id       = get_current_user_id();
		$unpaid_invoices_count = Sw_Invoice_Database::get_invoice_count_by_payment_status_for_user( $current_user_id, 'unpaid' );

		// Add classes and inline CSS for centering
		return "<h1 class='centered' style='text-align: center; margin: 0 auto; font-size: 45px;'>" . $unpaid_invoices_count . "</h1><p class='centered' style='text-align: center; font-size: 18px;'>New Invoices</p>";
	}
}


/**
 * Render All WooCommerce Orders as transactions
 */
function smartwoo_transactions_shortcode_output() {
	// Start output buffer
	ob_start();

	// Check if the user is logged in
	if ( ! is_user_logged_in() ) {
		echo '<p>Please log in to view your transaction history.</p>';
	} else {
		// Get the current logged-in user's ID
		$current_user_id = get_current_user_id();

		// Get recent orders for the current user
		$orders = wc_get_orders(
			array(
				'limit'    => 3, // Limit the number of orders to 3
				'status'   => array( 'wc-processing', 'wc-on-hold', 'wc-completed', 'wc-cancelled', 'wc-refunded', 'wc-failed', 'wc-pending', 'wc-partially-paid' ),
				'customer' => $current_user_id,
				'return'   => 'ids', // Only return order IDs
			)
		);

		// Output the recent transactions
		if ( $orders ) {
			echo '<div class="trans-card">';
			echo '<div class="trans-card-column trans-card-left">';
			echo '<h2 class="trans-card-heading">Recent Transactions</h2>';
			echo '<table class="trans-card-table">';
			echo '<tbody>';

			foreach ( $orders as $order_id ) {
				$order = wc_get_order( $order_id );

				// Initialize variables
				$amount         = $order->get_total();
				$order_status   = $order->get_status();
				$order_date     = smartwoo_check_and_format( $order->get_date_created(), true );
				$payment_method = $order->get_payment_method_title();
				$product_names  = array();

				// Retrieve the product names from the order items
				foreach ( $order->get_items() as $item_id => $item ) {
					$product = $item->get_product();
					if ( $product ) {
						$product_names[] = $product->get_name();
					}
				}

				// Output the data inside table rows
				echo '<tr>';
				echo '<th class="trans-card-th">Transaction ID</th>';
				echo '<td class="trans-card-td">' . esc_html( $order_id ) . '</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<th class="trans-card-th">Transaction Status</th>';
				echo '<td class="trans-card-td">' . esc_html( $order_status ) . '</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<th class="trans-card-th">Amount</th>';
				echo '<td class="trans-card-td">' . wc_price( $amount ) . '</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<th class="trans-card-th">Date</th>';
				echo '<td class="trans-card-td">' . esc_html( $order_date ) . '</td>';
				echo '</tr>';

				echo '<tr>';
				echo '<th class="trans-card-th">Payment Method</th>';
				echo '<td class="trans-card-td">' . esc_html( $payment_method ) . '</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<th class="trans-card-th">Transaction Detail</th>';
				echo '<td class="trans-card-td">' . esc_html( implode( ', ', $product_names ) ) . '</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<th class="trans-card-th">Action</th>';
				echo '<td class="trans-card-td">';
				$view_order_url = wc_get_account_endpoint_url( 'view-order' ) . '/' . $order_id;
				echo '<a href="' . esc_url( $view_order_url ) . '" class="trans-card-button">View</a>';
				echo '</td>';
				echo '</tr>';
				echo '<tr class="trans-card-separator"><td colspan="2"></td></tr>';
			}

			echo '</tbody>';
			echo '</table>';

			// Add the "View Older Transactions" button
			$view_all_orders_url = wc_get_account_endpoint_url( 'orders' );
			echo '<p><a href="' . esc_url( $view_all_orders_url ) . '" class="trans-card-button">View Older Transactions</a></p>';

			echo '</div>';
			echo '</div>';
		} else {
			echo '<p>All transaction history will appear here.</p>';
		}
	}

	return ob_get_clean();
}

/**
 * Renders count for all WooCommerce order status counts
 * Transaction in this context is WooCommerce Orders
 */
function sw_transaction_status_shortcode() {
	// Check if the user is logged in
	if ( is_user_logged_in() ) {
		$user    = wp_get_current_user();
		$user_id = $user->ID;

		// Manually define the order statuses you want to display
		$order_statuses_to_display = array(
			'completed'  => 'Complete',
			'pending'    => 'Pending',
			'processing' => 'Processing',
			'on-hold'    => 'On Hold',
			'refunded'   => 'Refunded',
			'cancelled'  => 'Cancelled',
			'failed'     => 'Failed',
		);

		// Initialize an array to store status counts
		$status_counts = array();

		// Loop through the manually defined order statuses and count orders for the current user
		foreach ( $order_statuses_to_display as $status => $label ) {
			$count                   = wc_get_orders(
				array(
					'status'   => $status,
					'customer' => $user_id,
				)
			);
			$status_counts[ $label ] = count( $count );
		}

		// Create the HTML output
		$output = '<div class="invoice-status-counts">';
		foreach ( $status_counts as $label => $count ) {
			$output .= '<div class="status-item">
                <span class="status-label">' . $label . '</span>
                <span class="status-count">(' . $count . ')</span>
            </div>';
		}
		$output .= '</div>';

		return $output;
	}

	return 'Please log in to view transaction status.';
}


/**
 * Function for Pending Transaction Count
 * In this context, pending transactions here means pending orders
 */
function sw_get_pending_transactions_count() {
	// Check if the user is logged in
	if ( ! is_user_logged_in() ) {
		return "Hello! It looks like you\'re not logged in.";
	} else {
		// Get the current logged-in user's ID
		$current_user_id = get_current_user_id();

		// Count pending transactions (orders with 'pending' status)
		$args                 = array(
			'post_type'      => 'shop_order',
			'post_status'    => 'wc-pending', // Count only orders with 'pending' status
			'posts_per_page' => -1,
			'meta_query'     => array(
				array(
					'key'     => '_customer_user',
					'value'   => $current_user_id,
					'compare' => '=',
				),
			),
		);
		$pending_transactions = get_posts( $args );

		// Add classes and inline CSS for centering
		return "<h1 class='centered' style='text-align: center; margin: 0 auto; font-size: 45px;'>" . count( $pending_transactions ) . "</h1><p class='centered' style='text-align: center; font-size: 18px;'>Unpaid Orders</p>";
	}
}
