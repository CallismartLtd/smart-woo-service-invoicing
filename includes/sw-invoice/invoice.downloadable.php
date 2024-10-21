<?php
/**
 * File name: invoice.downloadable.php
 * PDF invoice template file, uses mPDF library
 *
 * @author Callistus
 * @credit mPDF library by Ian Back
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access.

/**
 * Check pdf invoice download request.
 */
function smartwoo_invoice_download() {
	if ( isset( $_GET['download_invoice'], $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'download_invoice_nonce' ) ) {

		// Get user ID and invoice ID url param
		$user_id    = isset( $_GET['user_id'] ) ? absint( $_GET['user_id'] ) : 0;
		$invoice_id = isset( $_GET['invoice_id'] ) ? sanitize_key( $_GET['invoice_id'] ) : '';

		smartwoo_pdf_invoice_template( $invoice_id, $user_id );
	}
}

add_action( 'template_redirect', 'smartwoo_invoice_download' );


function smartwoo_pdf_invoice_template( $invoice_id, $user_id = 0 ) {

	if (  ! is_user_logged_in() ) {
		return;
	}

	$biller_details = smartwoo_biller_details();

	if ( 0 === $user_id ) {
		$user_id = get_current_user_id();
	} 

	$invoice = SmartWoo_Invoice_Database::get_invoice_by_id( $invoice_id );

	if ( $invoice && $user_id === $invoice->getUserId() ) {

		$business_name      = $biller_details->business_name;
		$invoice_logo_url   = $biller_details->invoice_logo_url;
		$admin_phone_number = $biller_details->admin_phone_number;
		$store_address      = $biller_details->store_address;
		$store_city         = $biller_details->store_city;
		$default_country    = $biller_details->default_country;

		$invoice_watermark_url    = get_option( 'smartwoo_invoice_watermark_url', '' );
		$user_data                = get_userdata( $user_id );
		$first_name               = $user_data->first_name;
		$last_name                = $user_data->last_name;
		$billing_email            = get_user_meta( $user_id, 'billing_email', true );
		$billing_phone            = get_user_meta( $user_id, 'billing_phone', true );
		$customer_company_name    = get_user_meta( $user_id, 'billing_company', true );
		$customer_billing_address = $invoice->getBillingAddress();

		// Get related services
		$service_id = $invoice->getServiceId();
		if ( ! empty( $service_id ) ) {
			$service_name = SmartWoo_Service_Database::get_service_by_id( $service_id )->getServiceName();
		}

		$product      				= wc_get_product( $invoice->getProductId() );
		$product_name 				= $product ? $product->get_name() : 'Product Not Found';
		$invoice_date     			= $invoice->getDateCreated();
		$transaction_date 			= $invoice->getDatePaid();
		$Invoice_due_date 			= $invoice->getDateDue();
		$formattedInvoiceDate		= smartwoo_check_and_format( $invoice_date );
		$formattedInvoiceDueDate	= smartwoo_check_and_format( $Invoice_due_date );
		$formattedPaidDate       	= smartwoo_check_and_format( $transaction_date );
		$payment_gateway 			= $invoice->getPaymentGateway();
		$invoice_payment_gateway 	= ! empty( $payment_gateway ) ? $payment_gateway : 'Not Available';
		$invoice_status          	= $invoice->getPaymentStatus();
		$transactionId           	= $invoice->getTransactionId();
		$transaction_id          	= ! empty( $transactionId ) ? $transactionId : 'Not Available';
		$invoice_total           	= $invoice->getTotal();

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

		// Include mPDF library.
		include_once SMARTWOO_PATH . 'vendor/autoload.php';
		// Create a new mPDF instance.
		$pdf = new \Mpdf\Mpdf();
		$pdf->AddPage();
		$spacer = '<br><br><br>';
		// Write the invoice logo and other elements before rotating the status
		$invoice_header = '
		<!-- Header Section -->
		<div style="display: flex; justify-content: space-between; align-items: center; margin: 5px; position: relative;">
			<div class="pdf-logo">
				<img src="' . esc_url( $invoice_logo_url ) . '" alt="Site Logo" width="150">
			</div>
		</div>';

		// Write the header first.
		$pdf->WriteHTML( $invoice_header );

		// Set the position where you want the status to appear (right corner)
		$pdf->SetXY(150, 0); // Adjust X and Y based on your layout needs

		// Rotate the status text by 40 degrees counterclockwise
		$pdf->Rotate(-40, -1, -35);
		$pdf->SetFont('Arial', 'B', 20); // Set the font, style, and size for the status text
		$pdf->SetTextColor(0, 0, 0); // Set text color to black
		$pdf->SetFillColor(241, 241, 241); // Set background color to Anti-Flash White.

		// Increase the width from 50 to, for example, 80 to make the background color wider
		$pdf->Cell(119, 10, ucfirst( $invoice_status ), 0, 1, 'C', true); // Output status text with red background

		// Reset rotation after rotating the status
		$pdf->Rotate(0);

		$pdf->WriteHTML( $spacer );
		$pdf->WriteHTML( $spacer );
		$pdf->WriteHTML( $spacer );
		$pdf->WriteHTML( $spacer );
		
		$business_section = '
		<!-- Business Information Section -->
		<div class="business-info" style="text-align: right; font-size: 12px;">
			<p style="font-weight: bold;">' . esc_html( $business_name ) . '</p>
			<p>' . wordwrap( esc_html( smartwoo_get_formatted_biller_address() ), 50, "<br>" ) . '</p>
			<p>' . esc_html( $admin_phone_number ) . '</p>
		</div>';
		$pdf->WriteHTML( $business_section );

		$invoice_number_section = '
		<!-- Invoice Number and Date Section -->
		<div class="invoice-number-container" style="background-color: #f2f2f2; padding: 10px; text-align: center; margin: 10px;">
			<div class="invoice-number" style="font-size: 12px; font-weight: bold;">Invoice ' . esc_html( $invoice_id ) . ( ! empty( $service_name ) ? ' for ' . esc_html( $service_name ) : '' ) . '</div>
			<div class="invoice-date" style="font-size: 9px;">Invoice Date: ' . esc_html( $formattedInvoiceDate ) . '</div>
			<div class="invoice-date" style="font-size: 9px;">Due Date: ' . esc_html( $formattedInvoiceDueDate ) . '</div>
		</div>';
		$pdf->WriteHTML( $invoice_number_section );

		$client_info_section = '
		<!-- Invoiced To Section -->
		<div class="invoiced-to" style="text-align: left; font-size: 12px;">
			<h4>Invoiced To:</h4>
			<p style="font-weight: bold;">' . esc_html( $customer_company_name ) . '</p>
			<p>' . esc_html( $first_name ) . ' ' . esc_html( $last_name ) . '</p>
			<p>Email: ' . esc_html( $billing_email ) . '</p>
			<p>Phone: ' . esc_html( $billing_phone ) . '</p>
			<p>' . wordwrap( esc_html( $customer_billing_address ), 50, "<br>" ) . '</p>
		</div>';
		$pdf->WriteHTML( $client_info_section );

		$pdf->WriteHTML( $spacer );

		$invoice_items_table_open = '
		<table style="width: 70%; margin: 0 auto; border-collapse: collapse; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
			<!-- Invoice Items Header -->
			<thead style="background-color: #f2f2f2;">
				<tr>
					<th style="text-align: left; padding: 5px 10px; font-weight: bold; background-color: #f2f2f2;">Description</th>
					<th style="text-align: right; padding: 5px 10px; font-weight: bold; background-color: #f2f2f2;">Amount</th>
				</tr>
			</thead>
			
			<tbody>';
			$pdf->WriteHTML( $invoice_items_table_open );

				foreach( $invoice_items as $item_name => $item_value ) {
					$invoice_items_list = '
					<!-- Invoice Items -->
	
					<tr style="border-bottom: 1px solid #ccc;">
						<td style="text-align: left; padding: 10px;">' . esc_html( $item_name ) . '</td>
						<td style="text-align: right; padding: 10px;">' . smartwoo_price( $item_value ) . '</td>
					</tr>';
					$pdf->WriteHTML( $invoice_items_list );

				}
				
				$table_closure = '
			</tbody>
		
			<!-- Total -->
			<tfoot>
				<tr style="border-top: 1px solid #ccc;">
					<td style="padding: 10px; font-weight: bold; background-color: #f2f2f2;">Total</td>
					<td style="text-align: right; padding: 10px; background-color: #f2f2f2;">' . smartwoo_price( apply_filters( 'smartwoo_display_invoice_total', $invoice_total, $invoice ) ) . '</td>
				</tr>
			</tfoot>
		</table>';
		$pdf->WriteHTML( $table_closure );

		$invoice_footer_section = '<p style="text-align: center;">Auto-generated on ' . smartwoo_check_and_format( current_time( 'mysql' ), true ) . '</p>';
		$pdf->SetHTMLFooter( $invoice_footer_section );

		$invoice_id = esc_html( $invoice->getInvoiceId() );

		// $pdf->SetWatermarkImage( $invoice_watermark_url );
		// $pdf->showWatermarkImage = true;

		$file_name = $invoice_id . '.pdf';
		$pdf->Output( $file_name, 'I' );


	}
}

