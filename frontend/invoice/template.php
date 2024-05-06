<?php
/**
 * This file contains all the code for the invoice preview page and the code for the
 * generation of the pdf invoice, we utilized the MPDF library for the generation of the pdf invoice
 */

 defined( 'ABSPATH' ) || exit; // Prevent direct access.

/**
 * Invoice main page callback.
 * 
 * @return string HTML post mark up
 */
function smartwoo_invoice_front_temp() {

	$current_user_id = get_current_user_id();
    $invoices        = SmartWoo_Invoice_Database::get_invoices_by_user( $current_user_id );

	/**
	 * Start frontpage markup
	 */
	$output  = smartwoo_get_navbar( 'My Invoices' );
	$output .= '<div class="site-content">';
    $output .= smartwoo_all_user_invoices_count();
	// Display the invoices in a table.
	$output .= '<div class="sw-table-wrapper">';
	$output .= '<table class="sw-table">';
	$output .= '<thead>';
	$output .= '<tr>';
	$output .= '<th>Invoice ID</th>';
	$output .= '<th>Invoice Date</th>';
	$output .= '<th>Date Due</th>';
	$output .= '<th>Total</th>';
	$output .= '<th>Status</th>';
	$output .= '<th>Action</th>';
	$output .= '</tr>';
	$output .= '</thead>';
	$output .= '<tbody>';
	
	if ( empty( $invoices ) ) {
		$output .= '<tr><td colspan="6" style="text-align: center;"> All Invoices will appear here</td></tr>';
		$output .= '</tbody></table></div></div>';
		return $output;
	}

	foreach ( $invoices as $invoice ) {

		$date_created = smartwoo_check_and_format( $invoice->getDateCreated(), true );
		$datePaid     = $invoice->getDatePaid();
		$date_due     = smartwoo_check_and_format( $invoice->getDateDue() );
		// Table content.
		$output .= '<tr>';
		$output .= '<td>' . esc_html( $invoice->getInvoiceId() ) . '</td>';
		$output .= '<td>' . esc_html( $date_created ) . '</td>';
		$output .= '<td>' . esc_html( $date_due ) . '</td>';
		$output .= '<td>' . wc_price( $invoice->getTotal() ) . '</td>';
		$output .= '<td class="payment-status">' . esc_html( ucwords( $invoice->getPaymentStatus() ) ) . '</td>';
		$output .= '<td><a href="?invoice_page=view_invoice&invoice_id=' . esc_attr( $invoice->getInvoiceId() ) . '" class="invoice-preview-button">' . esc_html__( 'View Details', 'smart-woo-service-invoicing' ) . '</a></td>';
		$output .= '</tr>';
	}

	$output .= '</tbody>';
	$output .= '</table>';
	$output .= '</div>';
	$output .= '</div>';
	$output .= '<p class="sw-table-count">' . absint( count( $invoices ) ) . ' item(s)</p>';
	
	return $output;
}


/**
 * Display details of a specific invoice.
 * 
 * @return string HTML Post markup
 */
function smartwoo_invoice_details() {

	$invoice_id		= isset( $_GET['invoice_id'] ) ? sanitize_key( $_GET['invoice_id'] ) : ""; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	
	if ( empty( $invoice_id ) ) {
		return smartwoo_notice('Invalid or Missing Invoice ID' );
	}
	
	$user_id		= get_current_user_id();
	$biller_details = smartwoo_biller_details();
	$invoice 		= ! empty( $invoice_id ) ? SmartWoo_Invoice_Database::get_invoice_by_id( $invoice_id ) : "";

	
	if ( $invoice && $invoice->getUserId() === $user_id ) {

		$business_name			= $biller_details->business_name;
		$invoice_logo_url		= $biller_details->invoice_logo_url;
		$admin_phone_number		= $biller_details->admin_phone_number;
		$store_address			= $biller_details->store_address;
		$store_city				= $biller_details->store_city;
		$default_country		= $biller_details->default_country;
		$user 					= new WC_Customer( $user_id );
		$first_name				= $user->get_first_name();
		$last_name				= $user->get_last_name();
		$billing_email			= $user->get_billing_email();
		$billing_phone			= $user->get_billing_phone();
		$customer_company_name	= $user->get_billing_company();
		$user_address			= $invoice->getBillingAddress();
		$service_id 			= $invoice->getServiceId();
		$service    			= ! empty( $service_id ) ? SmartWoo_Service_Database::get_service_by_id( $service_id ) : null;
 
		if ( $service ) {
			// Access the service name from the returned service object.
			$service_name 		= $service->getServiceName();
			$service_id   		= $service->getServiceId();
		}

		$product      			= wc_get_product( $invoice->getProductId() );
		$product_name 			= $product ? $product->get_name() : 'Product Not Found';
		$invoice_date			= smartwoo_check_and_format( $invoice->getDateCreated(), true );
		$transaction_date 		= smartwoo_check_and_format( $invoice->getDatePaid(), true );
		$invoice_due_date 		= smartwoo_check_and_format( $invoice->getDateDue(), true );
		$invoice_total    		= $invoice->getTotal();
		$payment_gateway 		= ! empty( $invoice->getPaymentGateway() ) ? $invoice->getPaymentGateway() : 'Not Available';
		$invoice_status			= $invoice->getPaymentStatus();
		$transaction_id			= ! empty( $invoice->getTransactionId() ) ? $invoice->getTransactionId() : 'Not Available';
		
		/**
		 * Start building the invoice.
		 */
		
		$invoice_content	= smartwoo_get_navbar( 'My Invoice' );
		$invoice_content	.= '<div class="site-content">';
		$invoice_content	.= '<div class="inv-button-container">';
		$invoice_content	.= '<a href="' . esc_url( smartwoo_invoice_page_url() ) . '" class="back-button">' . esc_html__( 'Back to invoices', 'smart-woo-service-invoicing' ) . '</a>';
		
		if ( 'unpaid' ===  strtolower( $invoice_status ) ) {
			$order_id         = $invoice->getOrderId();
			$pay_button_url   = smartwoo_order_pay_url( $order_id );
			$invoice_content .= '<a href="' . esc_url( $pay_button_url ) . '" class="invoice-pay-button">' . esc_html__( 'Pay Now', 'smart-woo-service-invoicing' ) . '</a>';
		}
		$download_url = add_query_arg(
			array(
				'download_invoice' => 'true',
				'invoice_id'       => $invoice_id,
				'user_id'          => $user_id,
			),
			get_permalink()
		);

		// Add nonce to the URL.
		$download_url 		= wp_nonce_url( $download_url, 'download_invoice_nonce' );
		$invoice_content 	.= '<a href="' . esc_url( $download_url ) . '" class="download-button">' . esc_html__( 'Download as PDF', 'smart-woo-service-invoicing' ) . '</a>';
		$invoice_content	.= '</div>';
		// Generate the invoice content.
		$invoice_content	.= '<div class="invoice-container">';
		$invoice_content	.= '<div class="invoice-preview">';
		// Header section.
		$invoice_content	.= '<header class="invoice-header">';
		$invoice_content	.= '<div class="logo">';
		$invoice_content	.= '<img src="' . esc_url( $invoice_logo_url ) . '" alt="Invoice Logo">';
		$invoice_content	.= '</div>';
		$invoice_content	.= '<div class="invoice-status">';
		$invoice_content	.= '<p>' . esc_html( ucfirst( $invoice_status ) ) . '</p>';		
		$invoice_content	.= '</div>';
		$invoice_content	.= '</header>';
		// Invoice Number section.
		$invoice_content	.= '<div class="invoice-number">';
		$invoice_content	.= '<p>' . esc_html__( 'Invoice #', 'smart-woo-service-invoicing' ) . esc_html( $invoice->getInvoiceId() );

		if ( ! empty( $service_name ) ) {
			$invoice_content .=  esc_html__( ' for ', 'smart-woo-service-invoicing' ) . esc_html( $service_name );
		}

		$invoice_content .= '</p>';
		$invoice_content .= '</div>';
		// Invoice Reference (Client Details) section.
		$invoice_content .= '<section class="invoice-details-container">';
		$invoice_content .= '<div class="invoice-details-left">';
		$invoice_content .= '<h3>' . esc_html__( 'Invoiced To', 'smart-woo-service-invoicing' ) . '</h3>';
		$invoice_content .= '<div class="invoice-customer-info">';
		$invoice_content .= '<p>' . esc_html( $customer_company_name ) . '</p>';
		$invoice_content .= '<p>' . esc_html( $first_name ) . ' ' . esc_html( $last_name ) . '</p>';
		$invoice_content .= '<p>' . esc_html__( 'Email: ', 'smart-woo-service-invoicing' ) . esc_html( $billing_email ) . '</p>';
		$invoice_content .= '<p>' . esc_html__( 'Phone: ', 'smart-woo-service-invoicing' ) . esc_html( $billing_phone ) . '</p>';
		$invoice_content .= '<p>' . esc_html( $user_address ) . '</p>';
		$invoice_content .= '</div>';
		$invoice_content .= '</div>';
		// Biller details section.
		$invoice_content .= '<div class="invoice-details-right">';
		$invoice_content .= '<h3>' . esc_html__( 'Pay To', 'smart-woo-service-invoicing' ) . '</h3>';
		$invoice_content .= '<div class="invoice-business-info">';
		$invoice_content .= '<p>' . esc_html( $business_name ) . '</p>';
		$invoice_content .= '<p>' . esc_html( smartwoo_get_formatted_biller_address() ) . '</p>';
		$invoice_content .= '<p>' . esc_html( $admin_phone_number ) . '</p>';
		$invoice_content .= '</div>';
		$invoice_content .= '</div>';
		$invoice_content .= '</section>';
		// Invoice Date.
		$invoice_content .= '<section class="invoice-date-payment">';
		$invoice_content .= '<div class="invoice-date">';
		$invoice_content .= '<h4>' . esc_html__( 'Invoice Date:', 'smart-woo-service-invoicing' ) . '</h4>';
		$invoice_content .= '<p>' . esc_html__( 'Generated: ', 'smart-woo-service-invoicing' ) . esc_html( $invoice_date ) . '</p>';
		$invoice_content .= '<p>' . esc_html__( 'Due On: ', 'smart-woo-service-invoicing' ) . esc_html( $invoice_due_date ) . '</p>';
		$invoice_content .= '</div>';
		//Payment Method section.
		$invoice_content .= '<div class="payment-method">';
		$invoice_content .= '<h4>' . esc_html__( 'Payment Method:', 'smart-woo-service-invoicing' ) . '</h4>';
		$invoice_content .= '<p>' . esc_html( $payment_gateway ) . '</p>';
		$invoice_content .= '</div>';
		$invoice_content .= '</section>';
		$invoice_content .= apply_filters( 'smartwoo_invoice_content', '', $invoice );

		// Invoice Items section.
		$invoice_items		= array();
		$invoice_items[]	= array(
			'description' => esc_html( $product_name ),
			'amount'      => $invoice->getAmount(),
		);

		$invoice_items[] = array(
			'description' => esc_html__( 'Fee', 'smart-woo-service-invoicing' ),
			'amount'      => $invoice->getFee(),
		);

		$items	= apply_filters( 'smartwoo_invoice_items', array(), $invoice );
		$invoice_items	= array_merge( $invoice_items, $items );

		$invoice_content .= '<section class="invoice-items">';
		$invoice_content .= '<div class="invoice-card">';
		$invoice_content .= '<div class="invoice-card-header">';
		$invoice_content .= '<h4 class="description-heading">' . esc_html__( 'Description', 'smart-woo-service-invoicing' ) . '</h4>';
		$invoice_content .= '<h4 class="amount-heading">' . esc_html__( 'Amount', 'smart-woo-service-invoicing' ) . '</h4>';
		$invoice_content .= '</div>';

		foreach ( $invoice_items as $item ) {
			$invoice_content .= '<div class="invoice-item">';
			$invoice_content .= '<p class="description">' . esc_html( $item['description'] ) . '</p>';
			$invoice_content .= '<p class="amount">' . wc_price( $item['amount'] ) . '</p>';
			$invoice_content .= '</div>';
		}

		if (  class_exists( 'SmartWooPro' , false ) && 'Service Upgrade Invoice' === $invoice->getInvoiceType() || 'Service Downgrade Invoice' === $invoice->getInvoiceType() ) {
			// Previous Service Balance.
			$balance          = $invoice->get_balance();
			$invoice_content .= '<div class="invoice-item">';
			$invoice_content .= '<p class="description">' . esc_html__( 'Previous Service Balance', 'smart-woo-service-invoicing' ) . '</p>';
			$invoice_content .= '<p class="amount">' . max( 0, wc_price( $balance ) ) . '</p>';
			$invoice_content .= '</div>';

		}

		// Total.
		$invoice_content .= '<div class="invoice-item total">';
		$invoice_content .= '<p class="description">' . esc_html__( 'Total', 'smart-woo-service-invoicing' ) . '</p>';
		$invoice_content .= '<p class="amount">' . wc_price( $invoice_total ) . '</p>';
		$invoice_content .= '</div>';
		$invoice_content .= '</div>'; // Close .invoice-card.
		$invoice_content .= '</section>'; // Close Invoice Items section.
		// Footer section.
		$invoice_content .= '<section class="invoice-footer">';
		$invoice_content .= '<div class="invoice-footer-content">';
		// Mini card container for other items.
		$invoice_content .= '<div class="mini-card-container">';
		// Invoice Type.
		$invoice_content .= '<div class="mini-card">';
		$invoice_content .= '<p class="footer-label">' . esc_html__( 'Invoice Type:', 'smart-woo-service-invoicing' ) . '</p>';
		$invoice_content .= '<p class="footer-value">' . esc_html( $invoice->getInvoiceType() ) . '</p>';
		$invoice_content .= '</div>';
		// Transaction Date.
		$invoice_content .= '<div class="mini-card">';
		$invoice_content .= '<p class="footer-label">' . esc_html__( 'Transaction Date:', 'smart-woo-service-invoicing' ) . '</p>';
		$invoice_content .= '<p class="footer-value">' . esc_html( $transaction_date ) . '</p>';
		$invoice_content .= '</div>';
		// Transaction ID.
		$invoice_content .= '<div class="mini-card">';
		$invoice_content .= '<p class="footer-label">' . esc_html__( 'Transaction ID:', 'smart-woo-service-invoicing' ) . '</p>';
		$invoice_content .= '<p class="footer-value">' . esc_html( $transaction_id ) . '</p>';
		$invoice_content .= '</div>';
		// Related Service.
		$invoice_content .= '<div class="mini-card">';
		$invoice_content .= '<p class="footer-label">' . esc_html__( 'Related Service', 'smart-woo-service-invoicing' ) . '</p>';
		$invoice_content .= '<p class="footer-value">' . esc_html( $service_id ) . '</p>';
		$invoice_content .= '</div>';
		$invoice_content .= '</div>'; // Close .mini-card-container.
		// Thank you message.
		$invoice_content .= '<p class="thank-you-message">' . esc_html__( 'Thank you for the continued business and support. We value you so much.', 'smart-woo-service-invoicing' ) . '</p>';
		$invoice_content .= '<p class="regards">' . esc_html__( 'Kind Regards.', 'smart-woo-service-invoicing' ) . '</p>';
		$invoice_content .= '</section>';
		$invoice_content .= '</div>'; 
		$invoice_content .= '</div>';
		$invoice_content .= '</div>'; 

		return $invoice_content;

	} else {
		return '<p>' . esc_html__( 'Invalid invoice ID or you do not have permission to view this invoice', 'smart-woo-service-invoicing' ) . ' "' . esc_html( $invoice_id ) . '"</p>';
	}
}

/**
 * User invoice filter by status
 * 
 * @return HTML Post markup
 */
function smartwoo_invoices_by_status() {

}

/**
 * Invoice mini card, aids in displaying invoice content anywhere with a post. 
 * 
 * @return string HTML Post markup.
 */
function smartwoo_invoice_mini_card() {

	if ( ! is_user_logged_in() ) {
        return esc_html__('Hello! It looks like you\'re not logged in.', 'smart-woo-service-invoicing');
    }

    $current_user_id = get_current_user_id();
	/**
	 * Starts card markup.
	 */
    $table_html      	  = '<div class="mini-card">';
    $table_html     	 .= '<h2>' . esc_html__( 'My Invoices', 'smart-woo-service-invoicing') . '</h2>';
    $table_html     	 .= '<table>';   
    $all_invoices         = SmartWoo_Invoice_Database::get_invoices_by_user( $current_user_id );

    if ( $all_invoices ) {

        foreach ( $all_invoices as $invoice ) {

            $invoice_id     = esc_html( $invoice->getInvoiceId() );
            $generated_date = esc_html( smartwoo_check_and_format( $invoice->getDateCreated() ) );
            $order_id       = esc_html( $invoice->getOrderId() );
            $table_html    .= '<tr>
                <td class="invoice-table-heading">' . esc_html__( 'Invoice ID:', 'smart-woo-service-invoicing' ) . '</td>
                <td class="invoice-table-value">' . esc_html( $invoice_id ) . '</td>
            </tr>';

            $table_html .= '<tr>
                <td class="invoice-table-heading">' . esc_html__( 'Date:', 'smart-woo-service-invoicing' ) . '</td>
                <td class="invoice-table-value">' . esc_html__( 'Generated on - ', 'smart-woo-service-invoicing' ) . $generated_date. '</td>
            </tr>';

            $preview_invoice_url = smartwoo_invoice_preview_url( $invoice->getInvoiceId() );

            $table_html .= '<tr>
                <td class="invoice-table-heading">' . esc_html__( 'Action:', 'smart-woo-service-invoicing' ) . '</td>
                <td class="invoice-table-value"><a href="' . esc_url( $preview_invoice_url ) .'" class="invoice-preview-button">' . esc_html__( 'View', 'smart-woo-service-invoicing' ) . '</a>';

            // Show the "Pay" button beside the "View" button only if the order is pending.
            if ( 'unpaid' === $invoice->getPaymentStatus() ) {
                $checkout_url  = smartwoo_order_pay_url( $invoice->getOrderID() );
                $table_html   .= '<a href="' . esc_url( $checkout_url ) .'" class="invoice-pay-button">' . esc_html__( 'Pay', 'smart-woo-service-invoicing' ) . '</a>';
            }

            $table_html .= '</td></tr>';

            $table_html .= "<tr><td colspan='2'></td></tr>";
        }
    } else {
        $table_html .= "<tr><td colspan='2'>" . esc_html__( 'All your invoices will appear here.', 'smart-woo-service-invoicing' ) . "</td></tr>";
    }

    // Close the table markup.
    $table_html .= '</table>';

    $table_html .= '</div>';

    return $table_html;
}

/**
 * Counts and renders payment status counts of all invoice for the current user
 */
function smartwoo_all_user_invoices_count() {

	if ( ! is_user_logged_in() ) {
		return;
	}

	$current_user_id = get_current_user_id();

	// Get counts for each payment status for the current user.
	$counts = array(
		'paid'      => SmartWoo_Invoice_Database::count_payment_status( $current_user_id, 'paid' ),
		'unpaid'    => SmartWoo_Invoice_Database::count_payment_status( $current_user_id, 'unpaid' ),
		'cancelled' => SmartWoo_Invoice_Database::count_payment_status( $current_user_id, 'cancelled' ),
		'due'       => SmartWoo_Invoice_Database::count_payment_status( $current_user_id, 'due' ),
	);

	// Generate the HTML.
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
 * ShortCode for Unpaid Invoice Count.
 */
function smartwoo_get_unpaid_invoices_count() {

	if ( ! is_user_logged_in() ) {
		return "Hello! It looks like you\'re not logged in.";
	} 
	
	$count = SmartWoo_Invoice_Database::count_payment_status( get_current_user_id(), 'unpaid' );
	$output	 = '<h1 class="centered" style="text-align: center; margin: 0 auto; font-size: 45px;">' . esc_html( absint( $count ) ) . '</h1>';
	$output .= '<p class="centered" style="text-align: center; font-size: 18px;">' . esc_html__( 'New Invoices', 'smart-woo-service-invoicing' ) . '</p>';
	
	return  $output;
}


/**
 * Render WooCommerce Orders as transactions.
 */
function smartwoo_transactions_shortcode() {
	$output		= "";

	if ( ! is_user_logged_in() ) {
		$output .= '<p>' . esc_html__( 'Please log in to view your transaction history', 'smart-woo-service-invoicing' ) . '</p>';
		return $output;
	}

	$orders = smartwoo_get_configured_orders_for_service( null, true );

	if ( $orders ) {
		$output	.= '<div class="sw-table-wrapper">';
		$output	.= '<table class="sw-table">';
		$output	.= '<thead>';
		$output	.= '<tr>';
		$output	.= '<th>' . esc_html__( 'Status', 'smart-woo-service-invoicing' ) . '</th>';
		$output	.= '<th>' . esc_html__( 'Amount', 'smart-woo-service-invoicing' ) . '</th>';
		$output	.= '<th>' . esc_html__( 'Date', 'smart-woo-service-invoicing' ) . '</th>';
		$output	.= '<th>' . esc_html__( 'Action', 'smart-woo-service-invoicing' ) .'</th>';
		$output	.= '</tr>';
		$output	.= '<tbody>';

		foreach ( $orders as $order ) {

			$order_id		= $order->get_id();
			$amount         = $order->get_total();
			$order_status   = $order->get_status();
			$order_date     = smartwoo_check_and_format( $order->get_date_created(), true );
			$payment_method = $order->get_payment_method_title();
			$product_names  = array();
			$output	.= '<tr>';
			$output	.= '<td>' . esc_html( $order_status ) . '</td>';
			$output	.= '<td>' . wc_price( $amount ) . '</td>';
			$output	.= '<td>' . esc_html( $order_date ) . '</td>';
			$view_url = $order->get_view_order_url();
			$output	.= '<td><a href="' . esc_url( $view_url ) . '" class="invoice-preview-button">' . esc_html__( 'View', 'smart-woo-service-invoicing' ) . '</a></td>';
			$output	.= '</tr>';
		}

		$output	.= '</tbody>';
		$output	.= '</table>';
		$view_all_url = wc_get_account_endpoint_url( 'orders' );
		$output	.= '<p><a href="' . esc_url( $view_all_url ) . '" class="sw-blue-button">' . esc_html__( 'View Older Transactions', 'smart-woo-service-invoicing' ) . '</a></p>';
		$output	.= '</div>';
		$output	.= '</div>';

	} else {
		$output	.= '<p>' . esc_html__( 'All transaction history will appear here','smart-woo-service-invoicing' ) . '</p>';
	}
	

	return $output;
}

/**
 * Renders count for all WooCommerce order statuses for the current user
 * Transaction in this context is WooCommerce Orders.
 */
function smartwoo_transaction_status_shortcode() {
	$output = "";

	if ( is_user_logged_in() ) {
		return $output;
	}

	$user    = wp_get_current_user();
	$user_id = $user->ID;

	$defualt_statuses = array(
		'completed'  => 'Complete',
		'pending'    => 'Pending',
		'processing' => 'Processing',
		'on-hold'    => 'On Hold',
		'refunded'   => 'Refunded',
		'cancelled'  => 'Cancelled',
		'failed'     => 'Failed',
	);

	$status_counts = array();

	foreach ( $defualt_statuses as $status => $label ) {
		$count = wc_get_orders(
			array(
				'status'   => $status,
				'customer' => $user_id,
			)
		);
		$status_counts[ $label ] = count( $count );
	}

	$output .= '<div class="invoice-status-counts">';

	foreach ( $status_counts as $label => $count ) {
		$output .= '<div class="status-item">
			<span class="status-label">' . esc_html( $label ) . '</span>
			<span class="status-count">(' . esc_html( $count ) . ')</span>
		</div>';
	}
	$output .= '</div>';

	return $output;
	
}


/**
 * Function for Pending Transaction Count
 * In this context, pending transactions here means pending orders
 */
function smartwoo_get_pending_transactions_count() {

	$count_htm ="";

	if ( ! is_user_logged_in() ) {
		return $count_htm;

	}

	$args = array(
		'status'   => 'pending',
		'customer' => get_current_user_id(),
	);
	$pending_transactions = wc_get_orders( $args );
	$count_htm .= '<h1 class="centered" style="text-align: center; margin: 0 auto; font-size: 45px;">' . esc_html( count( $pending_transactions ) ) .'</h1>';
	$count_htm .= '<p class="centered" style="text-align: center; font-size: 18px;">' . esc_html__( 'Unpaid Orders', 'smart-woo-service-invoicing' ) . '</p>';
	return $count_htm;

}
