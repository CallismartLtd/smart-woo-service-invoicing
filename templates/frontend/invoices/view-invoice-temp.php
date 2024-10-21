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
            <p><?php echo esc_html__( 'Generated: ', 'smart-woo-service-invoicing' ) . esc_html( $invoice_date ); ?></p>
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
                <p><?php echo esc_html__( 'Email: ', 'smart-woo-service-invoicing' ) . esc_html( $billing_email ); ?></p>
	            <p><?php echo esc_html__( 'Phone: ', 'smart-woo-service-invoicing' ) . esc_html( $billing_phone ); ?></p>
	            <p><?php echo esc_html( $user_address ); ?></p>
	        </div>
	    </div>

        <!-- Biller details section. -->
	    <div class="sw-invoice-reference-pay-to">
            <h3><?php echo esc_html__( 'Pay To', 'smart-woo-service-invoicing' ); ?></h3>
	        <div class="invoice-business-info">
	            <p><?php echo esc_html( $business_name ); ?></p>
                <p><?php echo esc_html( $admin_phone_number ); ?></p>
	            <p><?php echo esc_html( smartwoo_get_formatted_biller_address() ); ?></p>
	        </div>
	    </div>
	</div>
	<?php do_action( 'smartwoo_invoice_content', $invoice ); ?>

	<!-- Invoice Items section. -->
	<div class="sw-invoice-items-container">
	    <div class="sw-invoice-item-item-container">
	        <table class="sw-invoice-item-table">
                <thead>
                    <tr>
                    <th>Description</th>
                    <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( (array) $invoice_items as $item_name => $item_value ): ?> 
                        <tr>
                        <td><?php echo esc_html( $item_name ); ?></td>
                        <td><?php echo esc_html( smartwoo_price( $item_value ) ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    
                    <tr style="height:90px;">
                    <th><?php echo esc_html__( 'Total', 'smart-woo-service-invoicing' );?></th>
                    <td><?php echo esc_html( smartwoo_price( apply_filters( 'smartwoo_display_invoice_total', $invoice_total, $invoice ) ) ); ?></td>
                </tr>
                </tbody>
            </table>
        </div>
	</div>
</div>