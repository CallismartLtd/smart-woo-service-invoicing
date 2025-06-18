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
<div class="sw-admin-view-details">
    <?php if ( $form_errors = smartwoo_get_form_error() ): ?>
        <?php echo wp_kses_post( smartwoo_error_notice( $form_errors ) );?>
    <?php elseif ( $success = smartwoo_get_form_success() ): ?>
        <?php echo wp_kses_post( $success );?>
    <?php endif;?>
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ) ?>" class="smartwoo-new-invoice-form-editor" id="smartwooInvoiceForm">
        <div id="swloader" style="background-color:rgba(255, 255, 255, 0.1)"></div>
        <?php wp_nonce_field( 'sw_create_invoice_nonce', 'sw_create_invoice_nonce' ); ?>
        <input type="hidden" name="action" value="smartwoo_admin_create_invoice_from_form">
            
        <!-- Header section -->
        <div class="sw-invoice-editor-header">
            <div class="sw-invoice-editor-header-left">
                <img src="<?php echo esc_url( get_option( 'smartwoo_invoice_logo_url' ) ); ?>" alt="Invoice Logo" class="sw-invoice-logo">
            </div>
            <div class="sw-invoice-editor-header-right">
                <div class="sw-service-form-row">
                    <label for="invoice_id"><?php esc_html_e( 'Invoice ID', 'smart-woo-service-invoicing' ); ?> #</label>
                    <input type="text" id="invoice_id" value="<?php echo esc_attr( smartwoo_generate_invoice_id() ); ?>" title="<?php esc_attr_e( 'Actual invoice ID will be generated after invoice is created.', 'smart-woo-service-invoicing' ); ?>" disabled>
                    <label for="invoice_date"><?php esc_html_e( 'Date', 'smart-woo-service-invoicing' ); ?></label>
                    <input type="text" id="invoice_date" name="invoice_date" value="<?php echo esc_attr( date_i18n( 'Y-m-d H:i:s' ) ); ?>" title="<?php esc_attr_e( 'Actual date and time will be updated after creating invoice.', 'smart-woo-service-invoicing' ); ?>" disabled>
                    <label for="invoice_due_date"><?php esc_html_e( 'Due Date', 'smart-woo-service-invoicing' ); ?></label>
                    <input type="text" id="invoice_due_date" name="due_date" smartwoo-datetime-picker="true" autocomplete="off">
                </div>
            </div>
        </div>
        <hr>
        <!-- Invoice Reference Section -->
        <div class="sw-invoice-editor-reference-section">
            <!-- Customer section -->
            <div class="sw-invoice-editor-customer-section">
                <h2><?php esc_html_e( 'Invoice To', 'smart-woo-service-invoicing' ); ?></h2>

                <div class="sw-service-form-row">
                    <?php smartwoo_dropdown_users( 
                        '', 
                        array(
                            'class'		    => 'sw-service-user-dropdown',
                            'id'		    => 'user_data',
                            'add_guest'     => true,
                            'name'		    => 'user_data',
                            'option_none'	=> 'Choose client',
                            'required'		=> true,
                            'field_name'	=> 'A client'
                        )
                    ); ?>

                    <p id="companyName"></p>
                    <p id="fullname"></p>
                    <p id="billingEmail"></p>
                    <p id="billingAddress"></p>
                    <p id="billingPhone"></p>
                </div>
            </div>

            <!-- Biller section -->
            <div class="sw-invoice-editor-biller-section">
                <h2><?php esc_html_e( 'Pay To', 'smart-woo-service-invoicing' ); ?></h2>
                <p><?php echo esc_html( get_option( 'smartwoo_business_name' ) ); ?></p>
                <p><?php echo esc_html( smartwoo_get_formatted_biller_address() ); ?></p>
                <p><?php echo esc_html( get_option( 'smartwoo_admin_phone_numbers' ) ); ?></p>
            </div>

        </div>
        <hr>

        <!-- Invoice Items Section -->
        <div class="sw-invoice-editor-items-section">
            <h2><?php esc_html_e( 'Invoice Items', 'smart-woo-service-invoicing' ); ?></h2>
            <div class="sw-invoice-editor-items-list">
                <table id="swInvoiceItemsTable" class="sw-invoice-editor-items-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Item(s)', 'smart-woo-service-invoicing' ); ?></th>
                            <th><?php esc_html_e( 'Quantity', 'smart-woo-service-invoicing' ); ?></th>
                            <th><?php esc_html_e( 'Unit Price', 'smart-woo-service-invoicing' ); ?></th>
                            <th><?php esc_html_e( 'Total', 'smart-woo-service-invoicing' ); ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="swInvoiceItemsBody">
                        <tr><td colspan="4" class="sw-not-found"><?php esc_html_e( 'No items', 'smart-woo-service-invoicing' ); ?></td></tr>
                    </tbody>
                </table>
                <?php if ( has_action( 'smartwoo_invoice_editor_item_selector' ) ) : ?>
                    <?php do_action( 'smartwoo_invoice_editor_item_selector' ); ?>
                <?php else: ?>
                    <div class="sw-invoice-editor-add-product-select">
                        <?php smartwoo_product_dropdown( '', true ); ?>
                        <span type="button" class="button" id="smartwoo-no-pro-add-item-btn"> <i class="dashicons dashicons-plus"></i> <?php esc_html_e( 'Add Items', 'smart-woo-service-invoicing' ) ?></span>
                        <span id="pro-target"></span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="sw-invoice-editor-item-summary-container">
                <?php do_action( 'smartwoo_invoice_editor_after_subtotal' ); ?>
                <div class="sw-service-form-row">
                    <label for="subtotal-value"><?php esc_html_e( 'Subtotal', 'smart-woo-service-invoicing' ) ?>: </label>
                    <input type="number" id="subtotal-value" value="0" disabled>
                </div>

                <?php do_action( 'smartwoo_invoice_editor_after_subtotal' ); ?>

                <div class="sw-service-form-row">
                    
                    <label for="grandTotal"><?php esc_html_e( 'Total', 'smart-woo-service-invoicing' ) ?>: </label>
                    <input type="number" name="total" id="grandTotal" value="0" disabled>
                </div>
            </div>
        </div>
        <hr>

        <div class="sw-invoice-editor-core-data">
            <div class="sw-service-form-row">
                <label for="payment_status"><?php esc_html_e( 'Payment Status', 'smart-woo-service-invoicing' ); ?>:</label>
                <?php smartwoo_invoice_payment_status_dropdown( array( 'required' => true ) ); ?>
            </div>
            <div class="sw-service-form-row">
                <label for="invoice_type"><?php esc_html_e( 'Invoice Type', 'smart-woo-service-invoicing' ); ?>:</label>
                <?php smartwoo_invoice_type_dropdown( array( 'required' => true ) ); ?>
            </div>
            <div class="sw-service-form-row">
                <label for="service_id"><?php esc_html_e( 'Associated Service ID', 'smart-woo-service-invoicing' ); ?>:</label>
                <input type="text" name="service_id" id="service_id">
            </div>
            <div class="sw-service-form-row">
                <label for="send_mail"><?php esc_html_e( 'Send New Invoice Email', 'smart-woo-service-invoicing' ); ?>:</label>
                <select name="smartwoo_send_new_invoice_mail" id="send_mail">
                    <option value="no"><?php esc_html_e( 'No', 'smart-woo-service-invoicing' ); ?></option>
                    <option value="yes"><?php esc_html_e( 'Yes', 'smart-woo-service-invoicing' ); ?></option>
                </select>
            </div>
            
            <?php if ( ! empty( $available_gateways ) ) : ?>
                <div class="sw-invoice-editor-payment-gateways">
                    <h2><?php esc_html_e( 'Payment Method', 'smart-woo-service-invoicing' ); ?>:</h2>
                    <div class="sw-invoice-editor-payment-gateway-group">
                        <?php foreach( $available_gateways as $id => $gateway ) : ?>
                            <div class="sw-invoice-editor-payment-gateway-row">
                                <label for="<?php echo esc_attr( $id ); ?>">
                                    <input type="radio" name="payment_method" id="<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $id ); ?>">
                                    <span><?php echo esc_html( $gateway->get_title() ); ?></span>
                                    <?php echo wp_kses_post( $gateway->get_icon() ); ?>
                                </label>
                                
                            </div>
                            
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="sw-button-container">
            <button type="submit" class="button sw-invoice-editor-form-submit-button"><?php esc_html_e( 'Save Invoice', 'smart-woo-service-invoicing') ?></button>
        </div>
    </form>
</div>