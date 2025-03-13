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

    <?php echo wp_kses_post( smartwoo_sub_menu_nav( $tabs, 'Service Informations','sw-admin', $tab, 'service_id=' . $service_id . '&tab' ) ); ?>
    <?php if ( ! $service ) : ?>
        <?php echo wp_kses_post( smartwoo_notice( 'Invalid or deleted service <a href="' . admin_url( 'admin.php?page=sw-admin' ) . '">back</a>' ) ); ?>

    <?php else : ?>

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
                    <p class="smartwoo-container-item"><span>URL:</span> <?php echo esc_url( $service->get_service_url() ); ?></p>
                </div>
            </div>

            <div class="sw-admin-subinfo">
                <h3>Dates</h3>
                <hr>
                <div>
                    <p class="smartwoo-container-item"><span>Start Date:</span> <?php echo esc_html( smartwoo_check_and_format( $service->get_start_date(), true ) ); ?></p>
                    <p class="smartwoo-container-item"><span>Next Payment Date:</span> <?php echo esc_html( smartwoo_check_and_format( $service->get_next_payment_date(), true ) ); ?></p>
                    <p class="smartwoo-container-item"><span>End Date:</span> <?php echo esc_html( smartwoo_check_and_format( $service->get_end_date(), true ) ); ?></p>
                    <p class="smartwoo-container-item"><span>Expiration Date:</span> <?php echo esc_html( smartwoo_check_and_format( $service->get_expiry_date(), true ) ); ?></p>
                </div>
            </div>
            
        </div>
    </div>
    <?php endif; ?>

</div>