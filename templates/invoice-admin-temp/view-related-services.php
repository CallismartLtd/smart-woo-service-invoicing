<?php
/**
 * Template file to view the service associated with an invoice
 * 
 * @author Callistus
 * @package SmartWoo\Admin\templates
 */

defined( 'ABSPATH' ) || exit;
smartwoo_set_document_title( 'Related Service');
?>
<?php echo wp_kses_post( smartwoo_sub_menu_nav( $tabs, 'Invoice Informations','sw-invoices', $args, $query_var ) ); ?>

<?php if ( ! $invoice ) : ?>
    <?php echo wp_kses_post( smartwoo_error_notice( 'Invalid or deleted invoice' ) ); ?>
    <?php return; ?>
<?php elseif( empty( $service ) ): ?>
    <?php echo wp_kses_post( smartwoo_notice( 'Invoice is not related to any service.' ) ); ?>
    <?php return; ?>
    <?php else: ?>
	<div class="serv-details-card">'
		<h3><?php echo esc_html__( 'Related Service Details', 'smart-woo-service-invoicing' ); ?></h3>
        <p class="smartwoo-container-item"><span><?php echo esc_html__( 'Service Name:', 'smart-woo-service-invoicing' ); ?></span><?php echo esc_html( $service->get_name() ); ?></p>
        <p class="smartwoo-container-item"><span><?php echo esc_html__( 'Billing Cycle:', 'smart-woo-service-invoicing' ); ?></span><?php echo esc_html( $service->get_billing_cycle() ); ?></p>
        <p class="smartwoo-container-item"><span><?php echo esc_html__( 'End Date:', 'smart-woo-service-invoicing' ); ?></span><?php echo esc_html( $service->get_end_date() ); ?></p>
        <a class="sw-blue-button" href="<?php echo esc_url( smartwoo_service_preview_url( $service->get_service_id() ) ); ?>"><span class="dashicons dashicons-welcome-learn-more"></span> <?php echo esc_html__( 'Learn more', 'smart-woo-service-invoicing' ); ?></a>
    </div>
<?php endif; ?>