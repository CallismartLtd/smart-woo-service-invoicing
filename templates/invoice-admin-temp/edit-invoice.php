<?php
/**
 * Edit invoice form template.
 * 
 * @author Callistus
 * @package SmartWoo\templates
 */

defined( 'ABSPATH' ) || exit;
smartwoo_set_document_title( 'Edit New Invoice' );
?>
<div class="sw-admin-view-details">
    <?php if ( $form_errors = smartwoo_get_form_error() ): ?>
        <?php echo wp_kses_post( smartwoo_error_notice( $form_errors ) );?>
    <?php elseif ( $success = smartwoo_get_form_success() ): ?>
        <?php echo wp_kses_post( $success );?>
    <?php endif;?>
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ) ?>" class="smartwoo-new-invoice-form-editor" id="smartwooInvoiceForm">
        <?php if ( empty( $invoice ) ) : ?>
            <?php echo wp_kses_post( smartwoo_error_notice( 'Sorry, this invoice does not exist <a href="' . admin_url( 'admin.php?page=sw-invoices' ) .'">Back</a>' ) ); ?>
        <?php  else: ?>
            <div id="swloader" style="background-color:rgba(255, 255, 255, 0.1)"></div>
            <input type="hidden" name="action" value="smartwoo_admin_edit_invoice_from_form">
                
            <!-- Header section -->
            <div class="sw-invoice-editor-header">
                <div class="sw-invoice-editor-header-left">
                    <img src="<?php echo esc_url( get_option( 'smartwoo_invoice_logo_url' ) ); ?>" alt="Invoice Logo" class="sw-invoice-logo">
                </div>
                <div class="sw-invoice-editor-header-right">
                    <div class="sw-service-form-row">
                        <label for="invoice_id"><?php esc_html_e( 'Invoice ID', 'smart-woo-service-invoicing' ); ?> #</label>
                        <input type="text" id="invoice_id" name="invoice_id" value="<?php echo esc_attr( $invoice->get_invoice_id() ); ?>" title="<?php esc_attr_e( 'Invoice ID.', 'smart-woo-service-invoicing' ); ?>" readonly>
                        <label for="invoice_date"><?php esc_html_e( 'Date', 'smart-woo-service-invoicing' ); ?></label>
                        <input type="text" id="invoice_date" name="invoice_date" value="<?php echo esc_attr( $invoice->get_date_created() ); ?>" title="<?php esc_attr_e( 'Invoice creation date', 'smart-woo-service-invoicing' ); ?>" disabled>
                        <label for="invoice_due_date"><?php esc_html_e( 'Due Date', 'smart-woo-service-invoicing' ); ?></label>
                        <input type="text" id="invoice_due_date" name="due_date" value="<?php echo esc_html( $invoice->get_date_due() ) ?>" smartwoo-datetime-picker="true" autocomplete="off">
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
                            $selected, 
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

                        <p id="companyName"><?php echo esc_html( $invoice->get_user()->get_billing_company() ); ?></p>
                        <p id="fullname"><?php echo esc_html( $invoice->get_user()->get_billing_first_name() . ' ' . $invoice->get_user()->get_billing_last_name() ) ?></p>
                        <p id="billingEmail"><?php echo esc_html( $invoice->get_billing_email() ); ?></p>
                        <p id="billingAddress"><?php echo esc_html( $invoice->get_billing_address() ); ?></p>
                        <p id="billingPhone"><?php echo esc_html( $invoice->get_user()->get_billing_phone() ); ?></p>
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
                            <?php if ( empty( $invoice_items ) ) : ?>
                                <tr><td colspan="4" class="sw-not-found"><?php esc_html_e( 'No items', 'smart-woo-service-invoicing' ); ?></td></tr>
                            <?php else: $index =1; ?>
                                <?php foreach( $invoice_items as $name => $data ) : ?>
                                    <?php if ( SmartWoo::pro_is_installed() ) : ?>
                                        <tr>
                                            <td><input type="text" class="sw-invoice-editor-item-name-input" value="<?php echo esc_attr( $name ); ?>" id="<?php echo( absint( $index ) ); ?>" autocomplete="off"></td>
                                            <td><input type="number" class="sw-invoice-editor-quantity-input" value="<?php echo absint( $data['quantity'] ); ?>" step="1" min="1"></td>
                                            <td><input type="number" class="sw-invoice-editor-unit-price-input" value="<?php echo floatval( $data['price'] ); ?>" step="0.01"></td>
                                            <td class="sw-invoice-editor-line-total-input"></td>
                                            <td><span class="dashicons dashicons-trash sw-remove" <?php echo wp_kses_post( is_numeric( $data['id'] ) ? 'data-id="' . $data['id'] . '"' : '' ); ?> title="Remove item" style="cursor: pointer; color: red;"></span></td>
                                        </tr>
                                    <?php else: ?>
                                        <tr>
                                            <td><input type="text" class="sw-invoice-editor-item-name-input" value="<?php echo esc_attr( $name ); ?>" id="<?php echo( absint( $index ) ); ?>" autocomplete="off"></td>
                                            <td><input type="number" class="sw-invoice-editor-quantity-input" value="<?php echo absint( $data['quantity'] ); ?>" step="1" min="1" disabled></td>
                                            <td><input type="number" class="sw-invoice-editor-unit-price-input" value="<?php echo floatval( $data['price'] ); ?>" step="0.01" <?php echo wp_kses_post( ( 'Fee' === $name ) ? 'name="fee"': 'disabled' ); ?>></td>
                                            <td class="sw-invoice-editor-line-total-input"></td>
                                            <td><span class="dashicons dashicons-trash sw-remove" title="Remove item" style="cursor: pointer; color: red;"></span></td>
                                        </tr>
                                    <?php endif; ?>

                                    <?php $index++; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <?php if ( has_action( 'smartwoo_invoice_editor_item_selector' ) ) : ?>
                        <?php do_action( 'smartwoo_invoice_editor_item_selector' ); ?>
                    <?php else: ?>
                        <div class="sw-invoice-editor-add-product-select">
                            <?php smartwoo_product_dropdown( $invoice->get_product_id(), true ); ?>
                            <?php if ( ! SmartWoo::pro_is_installed() ) : ?>
                                <span type="button" class="button" id="smartwoo-no-pro-add-item-btn"> <i class="dashicons dashicons-plus"></i> <?php esc_html_e( 'Add Items', 'smart-woo-service-invoicing' ) ?></span>
                                <span id="pro-target"></span>
                            <?php endif; ?>
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
                    <?php smartwoo_invoice_payment_status_dropdown( array( 'required' => true, 'selected' => $invoice->get_status() ) ); ?>
                </div>
                <div class="sw-service-form-row">
                    <label for="invoice_type"><?php esc_html_e( 'Invoice Type', 'smart-woo-service-invoicing' ); ?>:</label>
                    <?php smartwoo_invoice_type_dropdown( array( 'required' => true, 'selected' => $invoice->get_type() ) ); ?>
                </div>
                <div class="sw-service-form-row">
                    <label for="service_id"><?php esc_html_e( 'Associated Service ID', 'smart-woo-service-invoicing' ); ?>:</label>
                    <input type="text" name="service_id" id="service_id" value="<?php echo esc_attr( $invoice->get_service_id() ); ?>">
                </div>
           
                <?php if ( ! empty( $available_gateways ) ) : ?>
                    <div class="sw-invoice-editor-payment-gateways">
                        <h2><?php esc_html_e( 'Payment Method', 'smart-woo-service-invoicing' ); ?>:</h2>
                        <div class="sw-invoice-editor-payment-gateway-group">
                            <?php foreach( $available_gateways as $id => $gateway ) : ?>
                                <div class="sw-invoice-editor-payment-gateway-row">
                                    <label for="<?php echo esc_attr( $id ); ?>">
                                        <input type="radio" name="payment_method" id="<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $id ); ?>" <?php checked( $id, $invoice->get_payment_method() ); ?>>
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
        <?php endif; ?>
    </form>
</div>