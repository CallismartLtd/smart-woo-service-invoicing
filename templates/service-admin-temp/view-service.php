<?php
/**
 * Admin view service subscription details template
 * 
 * @author Callistus
 * @package SmartWoo\Admin\templates
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="sw-admin-view-details">

    <?php echo wp_kses_post( smartwoo_sub_menu_nav( $tabs, 'Service Informations <a href="' . smartwoo_service_edit_url( $service_id ) .'">Edit</a>','sw-admin', $tab, 'service_id=' . $service_id . '&tab' ) ); ?>
    <?php if ( ! $service ) : ?>
        <?php echo wp_kses_post( smartwoo_notice( 'Invalid or deleted service <a href="' . admin_url( 'admin.php?page=sw-admin' ) . '">back</a>' ) ); ?>

    <?php else : ?>
        <div class="sw-admin-view-service-buttons-container">
            <?php echo wp_kses_post( smartwoo_client_service_url_button( $service ) ); ?>
            <?php do_action( 'smartwoo_admin_view_service_button_area', $service ); ?>
            <a href="<?php echo esc_url( smartwoo_service_edit_url( $service->get_service_id()  ) ); ?>"><button title="Edit Service"><span class="dashicons dashicons-edit"></span></button></a>
            <button class="delete-service-button" service-id="<?php echo esc_attr( $service_id ); ?>" title="Delete Service"><span class="dashicons dashicons-trash"></span></button>
            <span id="sw-admin-spinner" style="text-align:center; position: absolute; left: 50%; top: 25%;"></span>
        </div>
            
        <?php do_action( 'smartwoo_admin_view_service_before_service_details_section', $service ); ?>

        <div class="admin-view-details-data">
            <div class="sw-view-details-service-product">
                <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $product_name) ?>"/>
                <p><strong><?php echo esc_html( $product_name ); ?></strong></p>
                <div class="sw-admin-view-details-price-format">
                    <small><?php echo esc_html( get_woocommerce_currency_symbol() ) ?></small>
                    <h3><?php echo esc_html( $service->get_pricing() ); ?></h3>
                    <span><?php echo esc_html( $service->get_billing_cycle() ); ?></span>
                </div>
                <?php echo wp_kses_post( wpautop( $description ) ); ?>
                <a href="<?php echo esc_url( $product_url ) ?>" target="_blank"><button class="button">View product</button></a>
            </div>

            <div class="sw-admin-view-details-subinfo">
                <div class="sw-admin-subinfo">
                    <span class="smartwoo-status <?php echo esc_attr( $status_class ); ?>"><?php echo esc_html( $status ); ?></span>
                    <h3><?php echo esc_html( $service->get_name() ); ?></h3>
                    <hr>
                    <div>
                        <p class="smartwoo-container-item"><span>ID:</span> <?php echo esc_html( $service->get_id() ); ?></p>
                        <p class="smartwoo-container-item"><span>Service ID:</span> <?php echo esc_html( $service->get_service_id() ); ?></p>
                        <p class="smartwoo-container-item"><span>Type:</span> <?php echo esc_html( $service->get_type() ? $service->get_type() : 'N/A' ); ?></p>
                        <p class="smartwoo-container-item"><span>Billing Cycle:</span> <?php echo esc_html( $service->get_billing_cycle() ); ?></p>
                        <p class="smartwoo-container-item"><span>URL:</span> <?php echo esc_html( $service->get_service_url() ); ?></p>
                    </div>
                </div>

                <div class="sw-admin-subinfo">
                    <h3>Dates</h3>
                    <hr>
                    <div>
                        <p class="smartwoo-container-item"><span>Start Date:</span> <?php echo esc_html( smartwoo_check_and_format( $service->get_start_date(), true ) ); ?></p>
                        <p class="smartwoo-container-item"><span>Next Payment Date:</span> <span style="color:rgb(255, 60, 1)"><?php echo esc_html( smartwoo_check_and_format( $service->get_next_payment_date(), true ) ); ?></span></p>
                        <p class="smartwoo-container-item"><span>End Date:</span> <?php echo esc_html( smartwoo_check_and_format( $service->get_end_date(), true ) ); ?></p>
                        <p class="smartwoo-container-item"><span>Expiration Date:</span> <?php echo esc_html( smartwoo_check_and_format( $service->get_expiry_date(), true ) ); ?></p>
                    </div>
                </div>
                
            </div>
        </div>

        <?php do_action( 'smartwoo_admin_view_service_before_invoices_section', $service ); ?>

        <div class="admin-view-service-invoices">
            <h3>Recent Invoices <span class="dashicons dashicons-pdf"></span></h3>
            <?php if ( empty( $invoices ) ) : ?>
                <p><?php esc_html_e( 'No invoice associated with this service.', 'smart-woo-service-invoicing' ) ?></p>
            <?php else : ?>
                <div class="admin-view-service-invoices-contents">
                    <table>
                        <thead>
                            <tr>
                                <th>Invoice ID</th>
                                <th>Invoice Date</th>
                                <th>Due Date</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $invoices as $invoice ) : ?>
                                <tr class="admin-view-service-invoices-items smartwoo-prevent-default" href="<?php echo esc_url( $invoice->preview_url() ); ?>" title="View Invoice">
                                    <td><?php echo esc_html( $invoice->get_invoice_id() ); ?></td>
                                    <td><?php echo esc_html( smartwoo_check_and_format( $invoice->get_date_created() ) ); ?></td>
                                    <td><?php echo esc_html( smartwoo_check_and_format( $invoice->get_date_due() ) ); ?></td>
                                    <td><?php echo esc_html( smartwoo_price( $invoice->get_totals(), array( 'currency' => $invoice->get_currency() ) ) ); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=sw-invoices' ) ) ?>"><button style="float: right;" class="sw-blue-button">View All</button></a>

                </div>
            <?php endif; ?>
        </div>
        <?php do_action( 'smartwoo_admin_view_service_after_invoices_section', $service ); ?>

    <?php endif; ?>

</div>