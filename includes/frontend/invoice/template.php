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
	global $wp_query;

	if ( ! is_user_logged_in() ) {
		return smartwoo_login_form( array( 'notice' => smartwoo_notice( 'Login to view invoices.' ), 'redirect' => add_query_arg( array_map( 'sanitize_text_field', wp_unslash( $_GET ) ) ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    }

	$limit 			= 10;
	$page 			= isset( $wp_query->query_vars['paged'] ) && ! empty( $wp_query->query_vars['paged'] ) ? absint( $wp_query->query_vars['paged'] ) : 1;
    $invoices		= SmartWoo_Invoice_Database::get_invoices_by_user( get_current_user_id() );
	$all_inv_count	= SmartWoo_Invoice_Database::count_all_by_user( get_current_user_id() );
	$total_pages 	= ceil( $all_inv_count / $limit );


	/**
	 * Start frontpage markup
	 */
	$output  = smartwoo_get_navbar( 'My Invoices', smartwoo_invoice_page_url() );
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
		$output .= '<td>' . smartwoo_price( $invoice->getTotal() ) . '</td>';
		$output .= '<td class="payment-status">' . esc_html( ucwords( $invoice->getPaymentStatus() ) ) . '</td>';
		$output .= '<td><a href="?invoice_page=view_invoice&invoice_id=' . esc_attr( $invoice->getInvoiceId() ) . '" class="invoice-preview-button">' . esc_html__( 'View Details', 'smart-woo-service-invoicing' ) . '</a></td>';
		$output .= '</tr>';
	}

	$output .= '</tbody>';
	$output .= '</table>';
	$output .= '<div class="sw-pagination-buttons">';

	$output .= '<p>' . count( $invoices ) . ' item' . ( count( $invoices ) > 1 ? 's' : '' ) . '</p>';

	if ( $page > 1 ) {
		$prev_page = $page - 1;
		$output .= '<a class="sw-pagination-button" href="' . esc_url( add_query_arg( 'paged', $prev_page ) ) . '"><span class="dashicons dashicons-arrow-left-alt2"></span></a>';
	}

	$output .= '<p>'. absint( $page ) . ' of ' . absint( $total_pages ) . '</p>';

	if ( $page < $total_pages ) {
		$next_page = $page + 1;
		$output .= '<a class="sw-pagination-button" href="' . esc_url( add_query_arg( 'paged', $next_page ) ) . '"><span class="dashicons dashicons-arrow-right-alt2"></span></a>';
	}
	$output .= '</div>'; // Pagination container.
	$output .= '</div>';
	$output .= '</div>';
	
	return $output;
}


/**
 * Display details of a specific invoice.
 * 
 * @return string HTML Post markup
 */
function smartwoo_invoice_details( $invoice_id = '' ) {

	if ( ! is_user_logged_in() ) {
		woocommerce_login_form( array( 'message' => smartwoo_notice( __( 'You must be logged in to access this page', 'smart-woo-service-invoicing' ) ) ) );
	   return;
    }
	$invoice_content	= '<div class="smartwoo-page">';
	$invoice_content	.= smartwoo_get_navbar( 'My Invoice', smartwoo_invoice_page_url() );

	$invoice_id	= isset( $_GET['invoice_id'] ) ? sanitize_text_field( wp_unslash( $_GET['invoice_id'] ) ) : $invoice_id; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	
	if ( empty( $invoice_id ) ) {
		$invoice_content = smartwoo_notice( 'Invalid or Missing Invoice ID' );
		$invoice_content = '</div>';
		return $invoice_content;
	}
	
	$invoice	= ! empty( $invoice_id ) ? SmartWoo_Invoice_Database::get_invoice_by_id( $invoice_id ) : false;

	
	if ( ! $invoice || $invoice->getUserId() !== get_current_user_id() ) {
		$invoice_content .= smartwoo_notice( 'Invalid or deleted Invoice' );
		$invoice_content .= '</div>';
		return $invoice_content;
	}

	$user_id				= get_current_user_id();
	$biller_details			= smartwoo_biller_details();
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
	$service    			= ! empty( $service_id ) ? SmartWoo_Service_Database::get_service_by_id( $service_id ) : false;

	if ( $service ) {
		$service_name 		= $service->getServiceName();
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
	 * @filter smartwoo_invoice_items_display add or remove items from invoice table.
	 * 
	 * @param array $items
	 * @param SmartWoo_Invoice Invoice Object.
	 */
	$invoice_items	=	apply_filters( 'smartwoo_invoice_items_display', 
		array( 
			$product_name 								=> $invoice->getAmount(),
			__( 'Fee', 'smart-woo-service-invoicing' ) =>	$invoice->getFee() 
		),

		$invoice
		
	);
	
	/**
	 * Start building the page content.
	 */
	$invoice_content	.= '<div style="margin: 20px">';
	$invoice_content	.= '<a href="' . esc_url( smartwoo_invoice_page_url() ) . '" class="sw-blue-button">' . esc_html__( 'Back to invoices', 'smart-woo-service-invoicing' ) . '</a>';
	
	if ( 'unpaid' ===  strtolower( $invoice_status ) ) {
		$order_id         = $invoice->getOrderId();
		$pay_button_url   = smartwoo_invoice_pay_url( $order_id );
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
	$invoice_content	.= '</div>'; // Close buttons div.

	/**
	 * Invoice template.
	 * 
	 * @filter smartwoo_invoice_template.
	 * @param string $template_path template file.
	 * @param SmartWoo_Invoice The invoice object.
	 */
	$template_path	= SMARTWOO_PATH . 'templates/frontend/invoices/view-invoice-temp.php';
	$file			= apply_filters( 'smartwoo_invoice_template', $template_path, $invoice );

	if ( file_exists( $file ) ) {
		ob_start();
		include_once( $file );
		$invoice_content .= ob_get_clean();
	}

	ob_start();
	include_once SMARTWOO_PATH . 'templates/frontend/invoices/invoice-footer-section.php';
	$invoice_content .= ob_get_clean();

	$invoice_content .= '</div>'; // Close smartwoo-page div.

	return $invoice_content;

}

/**
 * User invoice filter by status
 * 
 * @return HTML Post markup
 */
function smartwoo_invoices_by_status() {
	global $wp_query;

	if ( ! is_user_logged_in() ) {
		return smartwoo_login_form( array( 'notice' => smartwoo_notice( 'Login to view invoices.' ), 'redirect' => add_query_arg( array_map( 'sanitize_text_field', wp_unslash( $_GET ) ) ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    }

	$status = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	$limit 			= 10;
	$page 			= isset( $wp_query->query_vars['paged'] ) && ! empty( $wp_query->query_vars['paged'] ) ? absint( $wp_query->query_vars['paged'] ) : 1;
    $invoices		= SmartWoo_Invoice_Database::get_invoices_by_payment_status( $status );
	$all_inv_count	= SmartWoo_Invoice_Database::count_this_status( $status );
	$total_pages 	= ceil( $all_inv_count / $limit );


	/**
	 * Start frontpage markup
	 */
	$output  = smartwoo_get_navbar( 'My Invoices', smartwoo_invoice_page_url() );
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
		$output .= '<tr><td colspan="6" style="text-align: center;"> All '. $status .' invoices will appear here</td></tr>';
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
		$output .= '<td>' . smartwoo_price( $invoice->getTotal() ) . '</td>';
		$output .= '<td class="payment-status">' . esc_html( ucwords( $invoice->getPaymentStatus() ) ) . '</td>';
		$output .= '<td><a href="?invoice_page=view_invoice&invoice_id=' . esc_attr( $invoice->getInvoiceId() ) . '" class="invoice-preview-button">' . esc_html__( 'View Details', 'smart-woo-service-invoicing' ) . '</a></td>';
		$output .= '</tr>';
	}

	$output .= '</tbody>';
	$output .= '</table>';
	$output .= '<div class="sw-pagination-buttons">';

	$output .= '<p>' . count( $invoices ) . ' item' . ( count( $invoices ) > 1 ? 's' : '' ) . '</p>';

	if ( $page > 1 ) {
		$prev_page = $page - 1;
		$output .= '<a class="sw-pagination-button" href="' . esc_url( add_query_arg( 'paged', $prev_page ) ) . '"><span class="dashicons dashicons-arrow-left-alt2"></span></a>';
	}

	$output .= '<p>'. absint( $page ) . ' of ' . absint( $total_pages ) . '</p>';

	if ( $page < $total_pages ) {
		$next_page = $page + 1;
		$output .= '<a class="sw-pagination-button" href="' . esc_url( add_query_arg( 'paged', $next_page ) ) . '"><span class="dashicons dashicons-arrow-right-alt2"></span></a>';
	}
	$output .= '</div>'; // Pagination container.
	$output .= '</div>';
	$output .= '</div>';
	
	return $output;

}

/**
 * Invoice mini card, aids in displaying invoice content anywhere with a post. 
 * 
 * @return string HTML Post markup.
 */
function smartwoo_invoice_mini_card() {

	if ( ! is_user_logged_in() ) {
		woocommerce_login_form( array( 'message' => smartwoo_notice( 'You must be logged in to access this page' ) ) );
	   return;
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
                $checkout_url  = smartwoo_invoice_pay_url( $invoice->getOrderID() );
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
		$nav_url = add_query_arg( array( 'invoice_page' => 'invoices_by_status', 'status' => $status ) );
		$output .= '<div class="status-item">';
		$output .= '<p><a href="' . esc_url( $nav_url ) .'">' . ucfirst( $status ) . ' <small>' . $count . '</small></a></p>';
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
		return;
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
		woocommerce_login_form( array( 'message' => smartwoo_notice( 'You must be logged in to access this page' ) ) );
	   return;
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
			$output	.= '<td>' . smartwoo_price( $amount ) . '</td>';
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
		$output	.= '<p>' . esc_html__( 'Transaction histories will appear here','smart-woo-service-invoicing' ) . '</p>';
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
