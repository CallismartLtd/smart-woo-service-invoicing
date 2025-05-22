<?php
/**
 * Template file for client details, client associated with a service and list of services owned by a client.
 * 
 * @author Callistus
 * @package SmartWoo\Admin\templates
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="sw-admin-view-details">
    <?php echo wp_kses_post( smartwoo_sub_menu_nav( $tabs, 'Client','sw-admin', $tab, 'service_id=' . $service_id . '&tab' ) ); ?>

    <?php if ( ! $service ) : ?>
        <?php echo wp_kses_post( smartwoo_notice( 'Invalid or deleted service <a href="' . admin_url( 'admin.php?page=sw-admin' ) . '">back</a>' ) ); ?>
    <?php else : ?>
        <?php do_action( 'smartwoo_admin_view_service_client_before_client_details_section', $service ); ?>

        <div class="sw-admin-client-info-wrapper">
            <div class="sw-admin-client-info-data">
                <div class="sw-admin-client-info-essentials">
                    <?php echo wp_kses_post( get_avatar( $client->get_id(), 72 ) ); ?>
                    <h3><?php echo esc_html( $client_full_name ) ?></h3>
                    <div class="sw-admin-client-services-meta-counts">
                        <div class="sw-admin-client-meta-count">
                            <h4><?php echo absint( $total_services ) ?></h4>
                            <p>Services</p>
                        </div>
                        <span></span>
                        <div class="sw-admin-client-meta-count">
                            <h4><?php echo absint( $total_invoices ); ?></h4>
                            <p>Invoices</p>
                        </div>
                        <span></span>
                        <div class="sw-admin-client-meta-count">
                            <h4><?php echo ( $is_paying_client ) ? '<label class="dashicons dashicons-yes-alt" style="color: green;"></label>' : '<label class="dashicons dashicons-no" style="color: red;"></label>'; ?></h4>
                            <p>Retained</p>
                        </div>
                    </div>

                    <button class="button smartwoo-prevent-default" href="<?php echo esc_url( $edit_user_url ); ?>">Edit Client</button>

                </div>

                <div class="sw-admin-client-info-billing">
                    <?php foreach( $client_billing_data as $h4 => $p ) : ?>
                        <div class="sw-admin-client-billing-info-tab">
                            <h4><?php echo esc_html( $h4 ); ?></h4>
                            <p><?php echo esc_html( $p ); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="sw-admin-client-info-pro-data<?php echo esc_attr( class_exists( 'SmartWooPro', false ) ? ' has-pro': '' ); ?>">
                <h3>Client Data</h3>
                <?php if ( has_filter( 'smartwoo_additional_client_details' ) && class_exists( 'SmartWooPro' ) ) : ?>
                    <div class="sw-admin-client-info-pro-items">
                        <?php foreach( $additional_details as $title => $value ) :?>
                            <p class="smartwoo-container-item"><span><?php echo wp_kses_post( $title ); ?>: </span> <?php echo wp_kses_post( $value ); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <div class="sw-admin-client-pro-sell">
                        <img src="<?php echo esc_url( SMARTWOO_DIR_URL . '/assets/images/admin-client-pro-data.png' ) ?>" alt="Pro Data">
                        <button class="sw-upgrade-to-pro">Activate Pro Feature</button>
                    </div>
                <?php endif; ?>
                
            </div>
        </div>

        <?php do_action( 'smartwoo_admin_view_service_client_after_client_details_section', $service ); ?>

        <div class="sw-admin-client-pro-services-invoices">
            <?php if ( has_action( 'smartwoo_client_services_and_invoices' ) ) : ?>
                <?php do_action( 'smartwoo_client_services_and_invoices', $client->get_id() ); ?>
            <?php else : ?>
                <div class="sw-admin-client-service-invoice-pro-sell">
                    <img src="<?php echo esc_url( SMARTWOO_DIR_URL . '/assets/images/admin-client-pro-services-invoices.png' ) ?>" alt="Pro Data">
                    <button class="sw-upgrade-to-pro">Activate Pro Feature</button>
                </div>
            <?php endif; ?>
        </div>

    <?php endif; ?>
</div>