<?php
/**
 * Default invoice view template.
 * 
 * @author Callistus
 * @package SmartWoo\templates.
 * @since 2.0.15
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="smartwoo-page">
	<?php echo wp_kses_post( smartwoo_get_navbar( 'My Invoice', smartwoo_invoice_page_url() ) ); ?>

	<?php if ( empty( $invoice ) || ! $invoice->current_user_can_access() ) : ?>
		<?php echo wp_kses_post( smartwoo_notice( 'Invalid or deleted invoice' ) ); ?>
	<?php else : ?>
		<div style="margin: 20px">
			<a href="<?php echo esc_url( smartwoo_invoice_page_url() ); ?>" class="sw-blue-button"><span class="dashicons dashicons-admin-home"></span> <?php echo esc_html__( 'Invoices', 'smart-woo-service-invoicing' ); ?></a>
			<?php if ( 'unpaid' ===  strtolower( $invoice_status ) ) : ?>
				<a href="<?php echo esc_url( $invoice->pay_url() ); ?>" class="invoice-pay-button"><span class="dashicons dashicons-money-alt"></span> <?php echo esc_html__( 'Pay Now', 'smart-woo-service-invoicing' ); ?></a>
			<?php endif; ?>
		</div>
		
		<div class="sw-invoice-template">
			<!-- Header section. -->
			<div class="sw-invoice-header">
				<div class="sw-invoice-logo">
					<img src="<?php echo esc_url( $invoice_logo_url ); ?>" alt="Invoice Logo">
				</div>
				<div class="sw-invoice-status">
					<p><?php echo esc_html( ucfirst( $invoice_status ) ); ?></p>
				</div>
			</div>

			<!-- Invoice Number section. -->
			<div class="sw-invoice-number">
				<span><?php echo esc_html__( 'Invoice #', 'smart-woo-service-invoicing' ) . esc_html( $invoice->getInvoiceId() ); ?></span>
				<!--Invoice Date. -->
				<div class="sw-invoice-dates">
					<p><?php echo esc_html__( 'Invoice Date: ', 'smart-woo-service-invoicing' ) . esc_html( $invoice_date ); ?></p>
					<p><?php echo esc_html__( 'Due On: ', 'smart-woo-service-invoicing' ) . esc_html( $invoice_due_date ); ?></p>
				</div>
			</div>


			<!-- Invoice Reference section. -->
			<div class="sw-invoice-reference">
				<div class="sw-invoice-reference-invoiced-to">
					<h3><?php echo esc_html__( 'Invoiced To', 'smart-woo-service-invoicing' ); ?></h3>
					<div class="invoice-customer-info">
						<p><?php echo esc_html( $customer_company_name ); ?></p>
						<p><?php echo esc_html( $first_name ) . ' ' . esc_html( $last_name ); ?></p>
						<p><?php echo esc_html( $user_address ); ?></p>
					</div>
				</div>

				<!-- Biller details section. -->
				<div class="sw-invoice-reference-pay-to">
					<h3><?php echo esc_html__( 'Pay To', 'smart-woo-service-invoicing' ); ?></h3>
					<div class="invoice-business-info">
						<p><?php echo esc_html( $business_name ); ?></p>
						<p><?php echo esc_html( smartwoo_get_formatted_biller_address() ); ?></p>
						<p><?php echo esc_html( $admin_phone_number ); ?></p>

					</div>
				</div>
			</div>
			<?php do_action( 'smartwoo_invoice_content', $invoice ); ?>

			<!-- Invoice Items section. -->
			<div class="sw-invoice-items-container">
				<div class="sw-invoice-item-item-container">
					<table class="sw-invoice-item-table" align="center">
						<thead>
							<tr>
							<th><?php echo esc_html__( 'Item(s)', 'smart-woo-service-invoicing' ); ?></th>
								<th><?php echo esc_html__( 'Quantity', 'smart-woo-service-invoicing' ); ?></th>
								<th><?php echo esc_html__( 'Unit Price', 'smart-woo-service-invoicing' ); ?></th>
								<th><?php echo esc_html__( 'Total', 'smart-woo-service-invoicing' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php if( empty( $invoice->get_items() ) ): ?>
									<tr>
										<td colspan="4" style="text-align:center;"><?php echo esc_html__( 'No items found', 'smart-woo-service-invoicing' ); ?></td>
									</tr>
							<?php else: ?>
								<?php foreach ( (array) $invoice_items as $item => $data ): ?> 
									<tr>
										<td data-label="Item(s)"><?php echo esc_html( $item ); ?></td>
										<td data-label="Qty"><?php echo esc_html( $data['quantity'] ); ?></td>
										<td data-label="Price"><?php echo esc_html( smartwoo_price( $data['price'] ) ); ?></td>
										<td data-label="Total"><?php echo esc_html( smartwoo_price( $data['total'] ) ); ?></td>
									</tr>
								<?php endforeach; ?>
							<?php endif; ?>

							<tr>
								<th style="font-size: 14px;" colspan="3"><?php echo esc_html__( 'Subtotal', 'smart-woo-service-invoicing' ); ?></td>
								<td data-label="Subtotal"><?php echo esc_html( smartwoo_price( $invoice->get_subtotal() ) ); ?></td>
							</tr>
							<tr>
								<th colspan="3" style="font-size: 14px;" ><strong><?php echo esc_html__( 'Discount:', 'smart-woo-service-invoicing' ); ?></strong></td>
								<td data-label="Discount"><?php echo esc_html( smartwoo_price( $invoice->get_discount() ) ); ?></td>
							</tr>
							<tr style="height:90px;">
								<th colspan="3"><?php echo esc_html__( 'Total', 'smart-woo-service-invoicing' );?></th>
								<td data-label="Total"><?php echo esc_html( smartwoo_price( $invoice->get_totals() ) ); ?></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<?php include_once SMARTWOO_PATH . 'templates/frontend/invoices/invoice-footer-section.php'; ?>
	<?php endif; ?>

</div>
