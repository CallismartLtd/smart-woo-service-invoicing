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
	if (  ! is_user_logged_in() ) {
		return;
	}
	
	if ( isset( $_GET['_sw_download_token'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_sw_download_token'] ) ), '_sw_download_token' ) ) {

		// Get user ID and invoice ID url param
		$user_id    = get_current_user_id();
		$invoice_id = isset( $_GET['invoice_id'] ) ? sanitize_text_field( wp_unslash( $_GET['invoice_id'] ) ) : '';

		smartwoo_pdf_invoice_template( $invoice_id, $user_id );
	}
}

add_action( 'sw_download_invoice', 'smartwoo_invoice_download' );

/**
 * Invoice PDF generation handler.
 * 
 * @param string $invoice_id 	The public Invoice ID.
 * @param int $user_id			The user ID of the invoice owner.
 * @param string $dest			The download destination. Possible values:
 *                              I = inline: send the PDF to the browser for preview.
 *                              D = download: send the PDF to the browser for download.
 *                              F = file: save the PDF to a file (e.g., for email attachments).
 *                              E = email: generate the PDF and return the file path for attachment.
 * 
 * @return string|void          File path for email ('E') or exits for inline/download ('I', 'D').
 */
function smartwoo_pdf_invoice_template( $invoice_id, $user_id = 0, $dest = 'D' ) {
	if ( 0 === $user_id ) {
		$user_id = get_current_user_id();
	} 

	$invoice = SmartWoo_Invoice_Database::get_invoice_by_id( $invoice_id );

	if ( ! $invoice || ! $invoice->current_user_can_access() ) {
		if ( wp_doing_ajax() ) {
			wp_send_json_error( array( 'message' => __( 'Invalid invoice ID.', 'smart-woo-service-invoicing' ) ), 200 );
		} else {
			wp_die( 'invalid_invoice', esc_html__( 'Invalid or unauthorized invoice ID.', 'smart-woo-service-invoicing' ), array( 'response' => 404 ) );
		}
	}

	$biller_details		= smartwoo_biller_details();
	$business_name      = $biller_details->business_name;
	$invoice_logo_url   = $biller_details->invoice_logo_url;
	$admin_phone_number = $biller_details->admin_phone_number;

	$invoice_watermark_url		= get_option( 'smartwoo_invoice_watermark_url', '' );
	$user_data					= $invoice->get_user();
	$first_name					= $user_data->get_billing_first_name();
	$last_name					= $user_data->get_billing_last_name();
	$billing_email				= $invoice->get_billing_email();
	$billing_phone				= $user_data->get_billing_phone();
	$customer_company_name		= $user_data->get_billing_company();
	$customer_billing_address	= $invoice->get_billing_address();
	$product_name 				= $invoice->get_product() ? $invoice->get_product()->get_name() : 'Product Not Found';
	$date_created     			= $invoice->get_date_created();
	$transaction_date 			= $invoice->get_date_paid();
	$due_date					= $invoice->get_date_due();
	$payment_method 			= ! empty( $invoice->get_payment_method() ) ? $invoice->get_payment_method() : 'N/A';
	$invoice_status          	= $invoice->get_status();
	$transaction_id          	= ! empty( $invoice->get_transaction_id() ) ? $invoice->get_transaction_id() : 'N/A';
	$invoice_items				= $invoice->get_items();

	// Include mPDF library.
	include_once SMARTWOO_PATH . 'vendor/autoload.php';
	// Create a new mPDF instance.
	$pdf = new \Mpdf\Mpdf();
	$pdf->AddPage();
	if ( ! empty( $invoice_watermark_url ) ) {
		$pdf->SetWatermarkImage( $invoice_watermark_url );
		$pdf->showWatermarkImage = true;
	}
	$spacer = '<br><br><br>';
	
	$invoice_header = '
	<!-- Header Section -->
	<div style="display: flex; justify-content: space-between; align-items: center; margin: 5px; position: relative;">
		<div>
			<img src="' . esc_url( $invoice_logo_url ) . '" alt="Site Logo" width="150">
		</div>
	</div>';

	// Write the header first.
	$pdf->WriteHTML( $invoice_header );
	$pdf->SetXY(150, 0);

	// Rotate the status text by 40 degrees counterclockwise.
	$pdf->Rotate(-40, -1, -35);
	$pdf->SetFont('Arial', 'B', 20);
	$pdf->SetTextColor(0, 0, 0);
	$pdf->SetFillColor(241, 241, 241);

	$pdf->Cell(119, 10, ucfirst( $invoice_status ), 0, 1, 'C', true ); 
	$pdf->Rotate(0);

	$pdf->WriteHTML( $spacer );
	$pdf->WriteHTML( $spacer );
	$pdf->WriteHTML( $spacer );
	$pdf->WriteHTML( $spacer );
	
	$business_section = '
	<!-- Business Information Section -->
	<div style="text-align: right; font-size: 12px;">
		<p style="font-weight: bold;">' . esc_html( $business_name ) . '</p>
		<p>' . wordwrap( esc_html( smartwoo_get_formatted_biller_address() ), 50, "<br>" ) . '</p>
		<p>' . esc_html( $admin_phone_number ) . '</p>
	</div>';
	$pdf->WriteHTML( $business_section );

	$invoice_number_section = '
	<!-- Invoice Number and Date Section -->
	<div style="background-color: #f2f2f2; padding: 10px; text-align: center; margin: 10px;">
		<div style="font-size: 16px; font-weight: bold;">Invoice ' . esc_html( $invoice_id ) . '</div>
		<div style="font-size: 12px;">Invoice Date: ' . esc_html( smartwoo_check_and_format( $date_created ) ) . '</div>
		<div style="font-size: 12px;">Due On: ' . esc_html( smartwoo_check_and_format( $due_date ) ) . '</div>
	</div>';
	$pdf->WriteHTML( $invoice_number_section );

	$client_info_section = '
	<!-- Invoiced To Section -->
	<div style="text-align: left; font-size: 12px;">
		<h4>Invoiced To:</h4>
		<p style="font-weight: bold;">' . esc_html( $customer_company_name ) . '</p>
		<p>' . esc_html( $first_name ) . ' ' . esc_html( $last_name ) . '</p>
		<p>' . wordwrap( esc_html( $customer_billing_address ), 50, "<br>" ) . '</p>
		<p>' . esc_html( $billing_phone ) . '</p>

	</div>';
	$pdf->WriteHTML( $client_info_section );

	$pdf->WriteHTML( $spacer );

	$invoice_items_table_open = '
	<table style="width: 100%; margin: 0 auto; border-collapse: collapse; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
		<!-- Invoice Items Header -->
		<thead style="background-color: #f2f2f2;">
			<tr>
				<th style="border-bottom: 1px solid #ccc; text-align: left; padding: 5px 10px; font-weight: bold; background-color: #f2f2f2;">Item(s)</th>
				<th style="border-bottom: 1px solid #ccc;text-align: left; padding: 5px 10px; font-weight: bold; background-color: #f2f2f2;">Qty</th>
				<th style="border-bottom: 1px solid #ccc;text-align: left; padding: 5px 10px; font-weight: bold; background-color: #f2f2f2;">Unit Price</th>
				<th style="border-bottom: 1px solid #ccc;text-align: left; padding: 5px 10px; font-weight: bold; background-color: #f2f2f2;">Total</th>
			</tr>
		</thead>
		
		<tbody>';
		$pdf->WriteHTML( $invoice_items_table_open );

			foreach( $invoice_items as $item_name => $data ) {
				$invoice_items_list = '
				<!-- Invoice Item -->
				<tr>
					<td style="border: 1px solid #eee; text-align: left; padding: 10px;">' . esc_html( $item_name ) . '</td>
					<td style="border: 1px solid #eee; text-align: left; padding: 10px;">' . absint( $data['quantity'] ) . '</td>
					<td style="border: 1px solid #eee; text-align: left; padding: 10px;">' . smartwoo_price( $data['price'] ) . '</td>
					<td style="border: 1px solid #eee; text-align: left; padding: 10px;">' . smartwoo_price( $data['total'] ) . '</td>
				</tr>';
				$pdf->WriteHTML( $invoice_items_list );

			}
			
			$table_closure = '
		</tbody>
		
		<!-- Subtotal -->
		<tfoot>
			<tr>
				<td colspan="3" style="border: 1px solid #eee; padding: 10px; font-weight: bold; background-color: #f2f2f2; text-align: center">Subtotal</td>
				<td style="border: 1px solid #eee; text-align: right; padding: 10px; background-color: #f2f2f2;">' . smartwoo_price( $invoice->get_subtotal() ) . '</td>
			</tr>
		</tfoot>

		<!-- Discount -->
		<tfoot>
			<tr>
				<td colspan="3" style="border: 1px solid #eee; padding: 10px; font-weight: bold; background-color: #f2f2f2; text-align: center">Discount</td>
				<td style="border: 1px solid #eee; text-align: right; padding: 10px; background-color: #f2f2f2;">' . smartwoo_price( $invoice->get_discount() ) . '</td>
			</tr>
		</tfoot>
		<!-- Total -->
		<tfoot>
			<tr>
				<td colspan="3" style="border: 1px solid #eee; padding: 10px; font-weight: bold; background-color: #f2f2f2; text-align: center">Total</td>
				<td style="border: 1px solid #eee; text-align: right; padding: 10px; background-color: #f2f2f2;">' . smartwoo_price( $invoice->get_totals() ) . '</td>
			</tr>
		</tfoot>
	</table>';
	$pdf->WriteHTML( $table_closure );

	$invoice_footer_section = '<p style="text-align: center;">Auto-generated on ' . smartwoo_check_and_format( current_time( 'mysql' ), true ) . '</p>';
	$pdf->SetHTMLFooter( $invoice_footer_section );

	$invoice_id = esc_html( $invoice->getInvoiceId() );

	if ( get_option( 'smartwoo_allow_invoice_tracking', false ) ) {
		$pdf->SetTitle( $invoice->getInvoiceType() );
		$pdf->SetSubject( $invoice->getInvoiceType() );
		$pdf->SetAuthor( SMARTWOO );
		$pdf->SetCreator( get_bloginfo( 'name' ) );
		$keywords = "ID: " . $invoice->get_id() . " Origin: " . site_url() . " SmartWoo Version: " . SMARTWOO_VER;
		$pdf->SetKeywords( $keywords );
	}

	$pdf->WriteHTML( $spacer );
	$pdf->Cell( 40, 10, 'Transaction ID: ' . $transaction_id . ' | Payment Method: ' . $payment_method . ' | Date Paid: ' . smartwoo_check_and_format( $transaction_date ) );

    // Handle destination.
    $file_name = $invoice_id . '.pdf';
    if ( 'D' === $dest ) {
        $pdf->Output( $file_name, 'D' );
        exit;
    } elseif ( 'I' === $dest ) {
        $pdf->Output( $file_name, 'I' );
        exit;
    } elseif ( 'F' === $dest || 'E' === $dest ) {
        $file_path  = trailingslashit( SMARTWOO_UPLOAD_DIR ) . $file_name;
        $pdf->Output( $file_path, 'F' );

        if ( 'E' === $dest ) {
            return $file_path; // Return the file path for email attachment.
        }
    }
	
}

