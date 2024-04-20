<?php
/**
 * File name    :   invoice.downloadable.php
 *
 * @author      :   Callistus
 * Description  :   The file for PDF invoice
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access.


if ( ! function_exists( 'smartwoo_invoice_download' ) ) {
	function smartwoo_invoice_download() {
		if ( isset( $_GET['download_invoice'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'download_invoice_nonce' ) ) {

			// Get user ID and invoice ID url param
			$user_id    = isset( $_GET['user_id'] ) ? absint( $_GET['user_id'] ) : 0;
			$invoice_id = isset( $_GET['invoice_id'] ) ? sanitize_key( $_GET['invoice_id'] ) : '';

			smartwoo_pdf_invoice_template( $invoice_id, $user_id );
		}
	}
}

add_action( 'wp_loaded', 'smartwoo_invoice_download' );

if ( ! function_exists( 'smartwoo_pdf_invoice_template' ) ) {
	function smartwoo_pdf_invoice_template( $invoice_id, $user_id = 0 ) {

		if(  ! is_user_logged_in() ) {
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

			$invoice_html = '
            <!-- Generate the invoice content (HTML) -->
            <!DOCTYPE html>
            <html>
            <head></head>
            <body>
            
            <!-- Header Section -->
            <div class="invoice-header" style="display: flex; justify-content: space-between; align-items: flex-start; margin: 5px; position: relative;">
                <div class="invoice-status" style="position: absolute; top: 0; right: 0; text-align: right;">
                    <span style="background-color: red; color: white; font-size: 20px; font-weight: bold; padding: 5px;">' . esc_html( ucfirst( $invoice_status ) ) . '</span>
                </div>
                
                <div class="pdf-logo">
                    <img src="' . esc_url( $invoice_logo_url ) . '" alt="Site Logo" width="150">
                </div>
            </div>
            
            <!-- Business Information Section -->
            <div class="business-info" style="text-align: right; font-size: 12px;">
                <p style="font-weight: bold;">' . esc_html( $business_name ) . '</p>
                <p>' . esc_html( smartwoo_get_formatted_biller_address() ) . '</p>
                <p>' . esc_html( $admin_phone_number ) . '</p>
            </div>
            
            <!-- Invoice Number and Date Section -->
            <div class="invoice-number-container" style="background-color: #f2f2f2; padding: 10px; text-align: center; margin: 10px;">
                <div class="invoice-number" style="font-size: 12px; font-weight: bold;">Invoice ' . esc_html( $invoice_id ) . ( ! empty( $service_name ) ? ' for ' . esc_html( $service_name ) : '' ) . '</div>
                <div class="invoice-date" style="font-size: 9px;">Invoice Date: ' . esc_html( $formattedInvoiceDate ) . '</div>
                <div class="invoice-date" style="font-size: 9px;">Due Date: ' . esc_html( $formattedInvoiceDueDate ) . '</div>
            </div>
            
            <!-- Invoiced To Section -->
            <div class="invoiced-to" style="text-align: left; font-size: 12px;">
                <h4>Invoiced To:</h4>
                <p style="font-weight: bold;">' . esc_html( $customer_company_name ) . '</p>
                <p>' . esc_html( $first_name ) . ' ' . esc_html( $last_name ) . '</p>
                <p>Email: ' . esc_html( $billing_email ) . '</p>
                <p>Phone: ' . esc_html( $billing_phone ) . '</p>
                <p>' . esc_html( $customer_billing_address ) . '</p>
            </div>
            
            <div class="invoice-card" style="padding: 3px; margin: 0 auto; max-width: 70%; background-color: #fff; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                <!-- Invoice Items Header -->
                <div class="invoice-card-header" style="display: flex; justify-content: space-between; background-color: #f0f0f0; padding: 5px 10px; font-weight: bold;">
                    <h4 class="description-heading" style="flex: 1; text-align: left;">Description </h4>
                    <h4 class="amount-heading" style="flex: 1; text-align: right;">Amount</h4>
                </div>';

				// Add the product name and amount under description and amount respectively
				$invoice_html .= '
                <div class="pdf-invoice-item" style="display: flex; justify-content: space-between; padding: 10px; border-bottom: 1px solid #ccc;">
                <p class="pdf-description" style="flex: 1; text-align: left;">' . esc_html( $product_name ) . ( ! empty( $service_name ) ? ' - ' . esc_html( $service_name ) : '' ) . '</p>
                <p class="pdf-amount" style="flex: 1; text-align: right;">' . wc_price( $invoice->getAmount() ) . '</p>
                </div>';

				// Append Fee to invoice HTML
				$invoice_html .= '
                <!-- Fee -->
                <div class="pdf-invoice-item" style="display: flex; justify-content: space-between; padding: 10px; border-bottom: 1px solid #ccc;">
                    <p class="pdf-description" style="flex: 1; text-align: left;">Fee</p>
                    <p class="pdf-amount" style="flex: 1; text-align: right;">' . wc_price( $invoice->getFee() ) . '</p>
                </div>';

				// Append Total section
				$invoice_html .= '
                <!-- Total -->
                <div style="display: flex; justify-content: space-between; padding: 5px; border-bottom: 1px solid #ccc;">
                    <p style="display: flex; justify-content: space-between;">Total: ' . wc_price( $invoice_total ) . '</p>
                </div>
            </div>

            This Invoice is automatically generated on ' . smartwoo_check_and_format( current_time( 'mysql' ), true ) . '.

            </body>
            </html>';

			// Include mPDF library.
			include_once SMARTWOO_PATH . 'vendor/autoload.php';

			$invoice_id = esc_html( $invoice->getInvoiceId() );
			// Create a new mPDF instance.
			$pdf = new \Mpdf\Mpdf();
			$pdf->AddPage();
			$pdf->SetWatermarkImage( $invoice_watermark_url );
			$pdf->showWatermarkImage = true;

			$pdf->WriteHTML( wp_kses_post( $invoice_html ) );

			$file_name = $invoice_id . '.pdf';
			$pdf->Output( $file_name, 'D' );
			exit;

		}
	}

}
