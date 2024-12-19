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
    
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ) ?>" class="sw-form-container">

            
        <?php wp_nonce_field( 'sw_create_invoice_nonce', 'sw_create_invoice_nonce' ); ?>
        <input type="hidden" name="action" value="smartwoo_admin_create_invoice_from_form">
            
        <!-- Choose a Client -->
        <div class="sw-form-row">
            <label for="user_id" class="sw-form-label">Choose a Client *</label>
            <span class="sw-field-description" title="Choose a user from WordPress.(required)">?</span>
            
            <?php wp_dropdown_users(
                array(
                    'name'             => 'user_id',
                    'show_option_none' => 'Select User',
                    'class'            => 'sw-form-input',
                )
            );?> 
        </div>

        <!-- Service Products -->
        <div class="sw-form-row">
            <label for="service_products" class="sw-form-label">Add Product *</label>
            <span class="sw-field-description" title="Select one product. This product price and fees will be used to create next invoice. Only Service Products will appear here.">?</span>
            <?php smartwoo_product_dropdown(); ?>  
        </div>

        <!-- Fee -->
        <div class="sw-form-row">
            <label for="fee" class="sw-form-label">Fee (optional)</label>
            <span class="sw-field-description" title="charge a fee for the invoice">?</span>
            <input type="number" class="sw-form-input" name="fee" id="fee" step="0.01">
        </div>

        <?php do_action( 'smartwoo_invoice_form_item_section' ); ?>

        <!-- Invoice Type -->
        <div class="sw-form-row">
            <label for="service_type" class="sw-form-label">Invoice Type *</label>
            <span class="sw-field-description" title="Enter the service type (optional)">?</span>
            <?php smartwoo_invoice_type_dropdown(); ?>
        </div>

        <!-- Service ID-->
        <div class="sw-form-row">
            <label for="service_id" class="sw-form-label">Service ID (optional)</label>
            <span class="sw-field-description" title="associate this invoice with service.">?</span>
            <input type="text" class="sw-form-input" name="service_id" id="service_id">
        </div>


        <!-- Payment status -->
        <div class="sw-form-row">
            <label for="payment_status" class="sw-form-label">Payment Status *</label>
            <span class="sw-field-description" title="Choose a payment status. If the status is unpaid, a new order will be created.">?</span>
            <?php smartwoo_invoice_payment_status_dropdown(); ?>
        </div>

        <!-- Input field for Due Date -->
        <div class="sw-form-row">
            <label for="due_date" class="sw-form-label">Date Due *</label>
            <span class="sw-field-description" title="Choose the date due.">?</span>
            <input type="datetime-local" class="sw-form-input" name="due_date" id="due_date">
        </div>

        <input type="submit" class="sw-blue-button" name ="create_invoice" value="Create Invoice">
    </form>
</div>