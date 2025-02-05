<?php
/**
 * Add new invoice form template.
 * 
 * @author Callistus
 * @package SmartWoo\templates
 */

defined( 'ABSPATH' ) || exit;
smartwoo_set_document_title( 'Add New Invoice' );
?>
<div class="wrap">
    <h1>Create New Invoice</h1>
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=sw-invoices' ) ); ?>"><button title="Invoice Dashboard"><span class="dashicons dashicons-admin-home"></span></button></a>
    
    <?php if ( $form_errors = smartwoo_get_form_error() ): ?>
        <?php echo wp_kses_post( smartwoo_error_notice( $form_errors ) );?>
    <?php elseif ( $success = smartwoo_get_form_success() ): ?>
        <?php echo wp_kses_post( $success );?>
    <?php endif;?>
    
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ) ?>" class="sw-form-container" id="createInvoiceForm">
        <div id="swloader" style="background-color:rgba(255, 255, 255, 0.1)"></div>
        <?php wp_nonce_field( 'sw_create_invoice_nonce', 'sw_create_invoice_nonce' ); ?>
        <input type="hidden" name="action" value="smartwoo_admin_create_invoice_from_form">
            
        <!-- Invoice to -->
        <div class="sw-form-row">
            <label for="user_data" class="sw-form-label"><?php esc_html_e( 'Invoice To', 'smart-woo-service-invoicing' ); ?></label>
            <span class="sw-field-description" title="<?php esc_html_e('Select a registered user or add a guest.', 'smart-woo-service-invoicing' ); ?>">?</span>
            <?php smartwoo_dropdown_users(); ?> 
        </div>

        <!-- Service Products -->
        <div class="sw-form-row">
            <label for="service_products" class="sw-form-label"><?php esc_html_e( 'Add Product', 'smart-woo-service-invoicing' ); ?></label>
            <span class="sw-field-description" title="<?php esc_html_e( 'Select one product. This product price and fees will be used to create next invoice. Only Service Products will appear here.', 'smart-woo-service-invoicing' ); ?>">?</span>
            <?php smartwoo_product_dropdown(); ?>  
        </div>

        <!-- Fee -->
        <div class="sw-form-row">
            <label for="fee" class="sw-form-label"><?php esc_html_e( 'Fee (optional)', 'smart-woo-service-invoicing' ); ?></label>
            <span class="sw-field-description" title="<?php esc_html_e( 'charge a fee for the invoice', 'smart-woo-service-invoicing' ); ?>">?</span>
            <input type="number" class="sw-form-input" name="fee" id="fee" step="0.01">
        </div>

        <?php do_action( 'smartwoo_invoice_form_item_section' ); ?>

        <!-- Invoice Type -->
        <div class="sw-form-row">
            <label for="service_type" class="sw-form-label"><?php esc_html_e( 'Invoice Type *', 'smart-woo-service-invoicing' ); ?></label>
            <span class="sw-field-description" title="<?php esc_html_e( 'Enter the service type (optional)', 'smart-woo-service-invoicing' ); ?>">?</span>
            <?php smartwoo_invoice_type_dropdown(); ?>
        </div>

        <!-- Service ID-->
        <div class="sw-form-row">
            <label for="service_id" class="sw-form-label"><?php esc_html_e( 'Service ID (optional)', 'smart-woo-service-invoicing' ); ?></label>
            <span class="sw-field-description" title="<?php esc_html_e( 'associate this invoice with a service.', 'smart-woo-service-invoicing' ); ?>">?</span>
            <input type="text" class="sw-form-input" name="service_id" id="service_id">
        </div>


        <!-- Payment status -->
        <div class="sw-form-row">
            <label for="payment_status" class="sw-form-label"><?php esc_html_e( 'Payment Status *', 'smart-woo-service-invoicing' ); ?></label>
            <span class="sw-field-description" title="<?php esc_html_e( 'Choose a payment status. If the status is unpaid, a new order will be created.', 'smart-woo-service-invoicing' ); ?>">?</span>
            <?php smartwoo_invoice_payment_status_dropdown(); ?>
        </div>

        <!-- Input field for Due Date -->
        <div class="sw-form-row">
            <label for="due_date" class="sw-form-label"><?php esc_html_e( 'Date Due *', 'smart-woo-service-invoicing' ); ?></label>
            <span class="sw-field-description" title="<?php esc_html_e( 'Choose the date due.', 'smart-woo-service-invoicing' ); ?>">?</span>
            <input type="datetime-local" class="sw-form-input" name="due_date" id="due_date">
        </div>

        
        <div class="sw-form-row">
            <label for="send_mail" class="sw-form-label"><?php esc_html_e( 'Send New Invoice Mail', 'smart-woo-service-invoicing' ); ?></label>
            <span class="sw-field-description" title="<?php esc_html_e( 'Send mail to user after invoice creation.', 'smart-woo-service-invoicing' ); ?>">?</span>
            <select class="sw-form-input" name="smartwoo_send_new_invoice_mail" id="send_mail">
                <option value="no"><?php esc_html_e( 'No', 'smart-woo-service-invoicing' ); ?></option>
                <option value="yes"><?php esc_html_e( 'Yes', 'smart-woo-service-invoicing' ); ?></option>
            </select>
        </div>

        <input type="submit" class="sw-blue-button" name ="create_invoice" value="Create Invoice" id="sw-create-invoice">
    </form>
</div>