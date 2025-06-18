<?php
/**
 * Invoice page footer section template.
 * 
 * @author Callistus
 * @package SmartWoo\templates.
 * @since 2.0.15
 */

defined( 'ABSPATH' ) || exit; ?>

<?php if ( ! empty( $invoice ) && $invoice->current_user_can_access() ) : ?>
	<!-- Footer section. -->
	<div class="sw-invoice-footer">
		<!-- Print Button -->
		<div class="sw-block-button-container">
			<button class="smartwoo-icon-button-blue" id="smartwoo-print-invoice-btn" title="Print Invoice"><span class="dashicons dashicons-printer"></span></button>
			<button class="smartwoo-icon-button-blue" id="smartwoo-download-invoice-btn" data-package-url="<?php echo esc_url( $download_url ); ?>" title="Download Invoice"><span class="dashicons dashicons-download"></span></button>
		</div>
		<hr>
		<!-- invoice meta data. -->
		<div class="sw-invoice-metadata">
			<!-- Payment Method. -->
			<div class="sw-invoice-meta-cards">
				<p><?php echo esc_html__( 'Payment Method:', 'smart-woo-service-invoicing' ); ?></p>
				<p class="sw-invoice-card-footer-value"><?php echo esc_html( ! empty( $invoice->get_payment_method_title() ) ? $invoice->get_payment_method_title() : 'N/A' ); ?></p>
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
				<p class="sw-invoice-card-footer-value"><?php echo esc_html( $invoice->get_type() ); ?></p>
			</div>

			<!-- Related Service. -->
			<div class="sw-invoice-meta-cards">
				<p><?php echo esc_html__( 'Related Service', 'smart-woo-service-invoicing' ); ?></p>
				<p class="sw-invoice-card-footer-value"><?php echo ( ! empty( $service_id )? '<a href="'. esc_url_raw( smartwoo_service_preview_url( $service_id ) ) .'">'. esc_html( $service_id ) . '</a>': 'N/A' ); ?></p>
			</div>

		</div>
		<hr>
		<!--  Thank you message. -->
		<p class="sw-thank-you"><?php echo esc_html( $invoice->get_footer_text() ); ?></p>
	</div>

<?php endif; ?>