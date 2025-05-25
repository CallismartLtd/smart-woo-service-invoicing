<?php
/**
 * Admin template for viewing an invoice.
 * 
 * @author Callistus
 * @package SmartWoo\Admin\Templates
 * @since 2.2.3
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access.
smartwoo_set_document_title( 'Invoice Details');
?>
<?php echo wp_kses_post( smartwoo_sub_menu_nav( $tabs, 'Invoice Informations','sw-invoices', $args, $query_var ) ); ?>
<?php if ( ! $invoice ) : ?>
    <?php echo wp_kses_post( smartwoo_notice( 'Invalid or deleted invoice <a href="' . admin_url( 'admin.php?page=sw-invoices') .'">Back</a>' ) ); ?>

<?php else: ?>
    <div class="smartwoo-admin-invoice-view">
        <div class="smartwoo-admin-invoice-options">
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=sw-invoices&tab=edit-invoice&invoice_id=' . $invoice->get_invoice_id() ) ); ?>"><button title="Edit Invoice"><span class="dashicons dashicons-edit"></span></button></a>
            <button title="Print Invoice" id="smartwoo-print-invoice-btn" style="cursor: not-allowed;" disabled><span class="dashicons dashicons-printer"></span></button>
            <a href="<?php echo esc_url( $invoice->download_url( 'admin' ) ); ?>"><button title="Download Invoice"><span class="dashicons dashicons-download"></span></button></a>
            <?php echo wp_kses_post( smartwoo_delete_invoice_button( $invoice->get_invoice_id() ) ); ?>
            <div id="swloader" style="background-color:#f1f1f100"></div>
            <button class="sw-icon-button-admin smartwoo-admin-invoice-actions" title="Invoice action links"><span class="dashicons dashicons-admin-links"></span></button>
        </div>
        <div class="smartwoo-admin-invoice-action-div">
            <span style="float: right; color: red; cursor: pointer;" class="dashicons dashicons-dismiss" onclick="document.querySelector('.smartwoo-admin-invoice-actions').click(); document.querySelector('#response-div').innerHTML = '';"></span>
            <h3 style="text-align: center">Action Links</h3>
            <button title="Send new invoice email" data-value="send_new_email"><span class="dashicons dashicons-email-alt"></span></button>
            <button title="Send payment reminder" data-value="send_payment_reminder" <?php echo esc_attr( 'unpaid' === $invoice->get_status() ) ? '':'disabled style="cursor: not-allowed;"' ?>><span style="font-size: 16px; font-weight: 900; color: red; position:absolute; right: 225px" class="dashicons dashicons-money-alt"></span><span class="dashicons dashicons-email-alt"></span></button>
            <button title="Generate payment url" data-value="paymen_url"><span class="dashicons dashicons-money-alt"></span></button>
            <button title="Generate checkout url" data-value="checkout_order_pay"><span class="dashicons dashicons-cart"></span></button>
            <div id="swSpinner" style="text-align: center;"></div>
            <div id="response-div" data-invoice-id="<?php echo esc_attr( $invoice->get_invoice_id() ) ?>" style="margin: 20px;"></div>
        </div>

        <div class="smartwoo-admin-invoice-body">
            <!-- Invoice Header. -->
            <div class="smartwoo-admin-invoice-header">
                <!-- Invoice Header data -->
                <div class="smartwoo-invoice-header-data">
                    <p>Invoice ID</p>
                    <p><?php echo esc_html( $invoice->get_invoice_id() ); ?></p>
                </div>

                <div class="smartwoo-invoice-header-data">
                    <p>Invoice Date</p>
                    <p><?php echo esc_html( smartwoo_check_and_format( $invoice->get_date_created(), true ) ); ?></p>
                </div>

                <div class="smartwoo-invoice-header-data">
                    <p>Due Date</p>
                    <p><?php echo esc_html( smartwoo_check_and_format( $invoice->get_date_due(), true ) ); ?></p>
                </div>

                <div class="smartwoo-invoice-header-data">
                    <p>Status</p>
                    <p style="color: #ffffff; border-radius: 40%; background-color:rgb(252, 7, 7); font-weight: 900; width: 50%; margin: 10px auto"><?php echo esc_html( ucfirst( $invoice->get_status() ) ); ?></p>
                </div>
            </div>

            <div class="smartwoo-admin-invoice-toggle">
                <div class="sw-toggle-btn" style="border-bottom: solid #000000;">
                    <p>Invoice Items</p>
                </div>
                <div class="sw-toggle-btn">
                    <p>Billing Details</p> 
                </div>
                
            </div>

            <div class="smartwoo-admin-invoice-items">
                <table class="sw-admin-invoice-item-table">
                    <thead>
                        <tr>
                            <th><?php echo esc_html__( 'Item(s)', 'smart-woo-service-invoicing' ); ?></th>
                            <th width="25x"><?php echo esc_html__( 'Quantity', 'smart-woo-service-invoicing' ); ?></th>
                            <th width="95px"><?php echo esc_html__( 'Unit Price', 'smart-woo-service-invoicing' ); ?></th>
                            <th width="150px"><?php echo esc_html__( 'Total', 'smart-woo-service-invoicing' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if( empty( $invoice->get_items() ) ): ?>
                            <tr>
                                <td colspan="4" style="text-align:center;"><?php echo esc_html__( 'No items found', 'smart-woo-service-invoicing' ); ?></td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ( $invoice->get_items() as $item => $data ) : ?>
                                <tr>
                                    <td><?php echo esc_html( $item ); ?></td>
                                    <td><?php echo esc_html( $data['quantity'] ); ?></td>
                                    <td><?php echo esc_html( smartwoo_price( $data['price'] ) ); ?></td>
                                    <td><?php echo esc_html( smartwoo_price( $data['total'] ) ); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr>
                                <td colspan="3" style="text-align:right;"><strong><?php echo esc_html__( 'Subtotal:', 'smart-woo-service-invoicing' ); ?></strong></td>
                                <td><?php echo esc_html( smartwoo_price( $invoice->get_subtotal() ) ); ?></td>
                            </tr>
                            <tr>
                                <td colspan="3" style="text-align:right;"><strong><?php echo esc_html__( 'Discount:', 'smart-woo-service-invoicing' ); ?></strong></td>
                                <td><?php echo esc_html( smartwoo_price( $invoice->get_discount() ) ); ?></td>
                            </tr>
                            <tr>
                                <td colspan="3" style="text-align:right;"><strong><?php echo esc_html__( 'Total:', 'smart-woo-service-invoicing' ); ?></strong></td>
                                <td><?php echo esc_html( smartwoo_price( $invoice->get_totals() ) ); ?></td>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="smartwoo-admin-invoice-billing-info smartwoo-hide">
                <table class="sw-table" style="width:100%;">
                    <thead>
                        <tr col-span="2">
                            <th style="text-align:center; font-size: 19px; cursor: pointer" colspan="2">Billing Details</th>
                            
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong><?php echo esc_html__( 'Name:', 'smart-woo-service-invoicing' ); ?></strong></td>
                            <td><a style="text-decoration: none; font-size: 16px; font-weight: 600;" href="<?php echo esc_url( get_edit_user_link( $invoice->get_user_id() ) ) ?>"><?php echo esc_html( $invoice->get_user() ? $invoice->get_user()->get_billing_first_name() .' '. $invoice->get_user()->get_billing_last_name(): 'N/A' ); ?></a></td>
                        </tr>

                        <tr>
                            <td><strong><?php echo esc_html__( 'Company:', 'smart-woo-service-invoicing' ); ?></strong></td>
                            <td><?php echo esc_html( $invoice->get_user() ?  $invoice->get_user()->get_billing_company() : 'N/A' ); ?></td>
                        </tr>

                        <tr>
                            <td><?php echo esc_html__( 'Email:', 'smart-woo-service-invoicing' ); ?></td>
                            <td><a style="text-decoration: none;" href="<?php echo esc_url( 'mailto:' . $invoice->get_billing_email() ); ?>"><?php echo esc_html( $invoice->get_billing_email() ); ?></a></td>
                        </tr>

                        <tr>
                            <td><?php echo esc_html__( 'Phone:', 'smart-woo-service-invoicing' ); ?></td>
                            <td><?php echo esc_html( $invoice->get_user() ? $invoice->get_user()->get_billing_phone() : '' ); ?></td>
                        </tr>
                        <tr>
                            <td><?php echo esc_html__( 'Address:', 'smart-woo-service-invoicing' ); ?></td>
                            <td><?php echo esc_html( $invoice->get_billing_address() ); ?></td>
                        </tr>
                    </tbody>

                </table>
            </div>

            <!-- invoice meta data. -->
            <div class="sw-invoice-metadata">
                <!-- Payment Method. -->
                <div class="sw-invoice-meta-cards">
                    <p><?php echo esc_html__( 'Payment Method:', 'smart-woo-service-invoicing' ); ?></p>
                    <p class="sw-invoice-card-footer-value"><?php echo esc_html( ! empty( $invoice->get_payment_method() ) ? $invoice->get_payment_method() : 'N/A' ); ?></p>
                </div>

                <!-- Transaction Date. -->
                <div class="sw-invoice-meta-cards">
                    <p><?php echo esc_html__( 'Transaction Date:', 'smart-woo-service-invoicing' ); ?></p>
                    <p class="sw-invoice-card-footer-value"><?php echo esc_html( smartwoo_check_and_format( $invoice->get_date_paid(), true ) ); ?></p>
                </div>

                <!-- Transaction ID. -->
                <div class="sw-invoice-meta-cards">
                    <p><?php echo esc_html__( 'Transaction ID:', 'smart-woo-service-invoicing' ); ?></p>
                    <p class="sw-invoice-card-footer-value"><?php echo esc_html( $invoice->get_transaction_id() ? $invoice->get_transaction_id() : 'N/A' ); ?></p>
                </div>

                <!-- Invoice Type. -->
                <div class="sw-invoice-meta-cards">
                    <p><?php echo esc_html__( 'Invoice Type:', 'smart-woo-service-invoicing' ); ?></p>
                    <p class="sw-invoice-card-footer-value"><?php echo esc_html( $invoice->get_type() ); ?></p>
                </div>

                <!-- Related Service. -->
                <div class="sw-invoice-meta-cards">
                    <p><?php echo esc_html__( 'Related Service', 'smart-woo-service-invoicing' ); ?></p>
                    <p class="sw-invoice-card-footer-value"><?php echo ( ! empty( $invoice->get_service_id() )? '<a href="'. esc_url_raw( smartwoo_service_preview_url( $invoice->get_service_id() ) ) .'">'. esc_html( $invoice->get_service_id() ) . '</a>': 'N/A' ); ?></p>
                </div>
            </div>
        </div>
    </div>

<?php endif; ?>