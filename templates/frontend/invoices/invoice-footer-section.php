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
		<button class="smartwoo-block-button" id="smartwoo-print-invoice-btn" title="Print Invoice"><span class="dashicons dashicons-printer"></span></button>
		<a href="<?php echo esc_url( $download_url ); ?>"><button class="smartwoo-block-button" id="smartwoo-print-invoice-btn" title="Download Invoice"><span class="dashicons dashicons-download"></span></button></a>
	 </div>
	<!-- invoice meta data. -->
	<div class="sw-invoice-metadata">
		<!-- Invoice Type. -->
		<div class="sw-invoice-meta-cards">
			<p><?php echo esc_html__( 'Invoice Type:', 'smart-woo-service-invoicing' ); ?></p>
			<p class="footer-value"><?php echo esc_html( $invoice->getInvoiceType() ); ?></p>
		</div>

		<!-- Transaction Date. -->
		<div class="sw-invoice-meta-cards">
			<p><?php echo esc_html__( 'Transaction Date:', 'smart-woo-service-invoicing' ); ?></p>
			<p class="footer-value"><?php echo esc_html( $transaction_date ); ?></p>
		</div>

		<!-- Transaction ID. -->
		<div class="sw-invoice-meta-cards">
			<p><?php echo esc_html__( 'Transaction ID:', 'smart-woo-service-invoicing' ); ?></p>
			<p class="footer-value"><?php echo esc_html( $transaction_id ); ?></p>
		</div>

		<!-- Related Service. -->
		<div class="sw-invoice-meta-cards">
			<p><?php echo esc_html__( 'Related Service', 'smart-woo-service-invoicing' ); ?></p>
			<p class="footer-value"><?php echo esc_html( $service_id ); ?></p>
		</div>

	</div>
	<!--  Thank you message. -->
	<p class="sw-thank-you"><?php echo apply_filters( 'smartwoo_invoice_footer_text', esc_html__( 'Thank you for the continued business and support. We value you so much.', 'smart-woo-service-invoicing' ) ); ?></p>
</div>