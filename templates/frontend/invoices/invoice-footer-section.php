<?php
/**
 * Invoice page footer section template.
 * 
 * @author Callistus
 * @package SmartWoo\templates.
 * @since 2.0.15
 */

defined( 'ABSPATH' ) || exit;
?>

<!-- Footer section. -->
<div class="sw-invoice-footer">
	<!-- Print Button -->
	 <div class="sw-block-button-container">
		<button class="smartwoo-icon-button-blue" id="smartwoo-print-invoice-btn" title="Print Invoice"><span class="dashicons dashicons-printer"></span></button>
		<a href="<?php echo esc_url( $download_url ); ?>"><button class="smartwoo-icon-button-blue" id="smartwoo-print-invoice-btn" title="Download Invoice"><span class="dashicons dashicons-download"></span></button></a>
	 </div>
	<!-- invoice meta data. -->
	<div class="sw-invoice-metadata">
		<!-- Payment Method. -->
		<div class="sw-invoice-meta-cards">
			<p><?php echo esc_html__( 'Payment Method:', 'smart-woo-service-invoicing' ); ?></p>
			<p class="sw-invoice-card-footer-value"><?php echo esc_html( ! empty( $invoice->getPaymentGateway() ) ? $invoice->getPaymentGateway() : 'N/A' ); ?></p>
		</div>

		<!-- Transaction Date. -->
		<div class="sw-invoice-meta-cards">
			<p><?php echo esc_html__( 'Transaction Date:', 'smart-woo-service-invoicing' ); ?></p>
			<p class="sw-invoice-card-footer-value"><?php echo esc_html( $transaction_date ); ?></p>
		</div>

		<!-- Transaction ID. -->
		<div class="sw-invoice-meta-cards">
			<p><?php echo esc_html__( 'Transaction ID:', 'smart-woo-service-invoicing' ); ?></p>
			<p class="sw-invoice-card-footer-value"><?php echo esc_html( $transaction_id ); ?></p>
		</div>

		<!-- Invoice Type. -->
		<div class="sw-invoice-meta-cards">
			<p><?php echo esc_html__( 'Invoice Type:', 'smart-woo-service-invoicing' ); ?></p>
			<p class="sw-invoice-card-footer-value"><?php echo esc_html( $invoice->getInvoiceType() ); ?></p>
		</div>

		<!-- Related Service. -->
		<div class="sw-invoice-meta-cards">
			<p><?php echo esc_html__( 'Related Service', 'smart-woo-service-invoicing' ); ?></p>
			<p class="sw-invoice-card-footer-value"><?php echo ( ! empty( $service_id )? '<a href="'. esc_url_raw( smartwoo_service_preview_url( $service_id ) ) .'">'. esc_html( $service_id ) . '</a>': 'N/A' ); ?></p>
		</div>

	</div>
	<!--  Thank you message. -->
	<p class="sw-thank-you"><?php echo esc_html( apply_filters( 'smartwoo_invoice_footer_text',  'Thank you for the continued business and support. We value you so much.') ); ?></p>
</div>