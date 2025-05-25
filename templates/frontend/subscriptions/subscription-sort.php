<?php
/**
 * Front-end subscription sorting template.
 * 
 * @author Callistus
 * @package SmartWoo\templates
 */

defined( 'ABSPATH' ) || exit; ?>

<div class="smartwoo-page">
    <?php echo wp_kses_post( smartwoo_get_navbar( $status_label, smartwoo_service_page_url() ) ); ?>
    <div class="sw-table-wrapper">
        <table class="sw-table">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Service Name', 'smart-woo-service-invoicing' ); ?></th>
                    <th><?php esc_html_e( 'Service ID', 'smart-woo-service-invoicing' ); ?></th>
                    <th><?php esc_html_e( 'Billing Cycle', 'smart-woo-service-invoicing' ); ?></th>
                    <th><?php esc_html_e( 'End Date', 'smart-woo-service-invoicing' ); ?></th>
                    <th><?php esc_html_e( 'Action', 'smart-woo-service-invoicing' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ( empty( $services ) ) : ?>
                    <tr><td colspan="5" style="text-align: center;"><?php esc_html_e( 'No service found with this status', 'smart-woo-service-invoicing' ); ?> "<?php echo esc_html( ucfirst( str_replace( '-', ' ', $status ) ) ); ?>"</td></tr>
                <?php else : ?>
                    <?php foreach ( $services as $service ) : ?>
                        <tr>
                            <td><?php echo esc_html( $service->get_name() ); ?></td>
                            <td><?php echo esc_html( $service->get_service_id() ); ?></td>
                            <td><?php echo esc_html( $service->get_billing_cycle() ); ?></td>
                            <td><?php echo esc_html( $service->get_end_date() ); ?></td>
                            <td><a href="<?php echo esc_url( smartwoo_service_preview_url( $service->get_service_id() ) ); ?>" class="sw-blue-button"><?php echo esc_html__( 'View Details', 'smart-woo-service-invoicing' ); ?></a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>