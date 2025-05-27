<?php
/**
 * Smart Woo client portal where all service subscriptions are listed.
 * 
 * @author Callistus
 * @package SmartWoo\Templates
 */

defined( 'ABSPATH' ) || exit; ?>

<div class="smartwoo-page">
    <?php echo wp_kses_post( smartwoo_get_navbar( 'My Services' ) ); ?>
    <span style="float:right;"><?php smartwoo_table_limit_field( $limit ); ?></span>
    <p style="text-align: center; margin-top: 10px;">Welcome, <?php echo esc_html( $full_name ); ?>!</p>
    
    <div class="status-counts portal"> 
        <p class="active-count"><a href="<?php echo esc_url( $active_count_url ); ?>">Active: <?php echo esc_html( $active_count ); ?></a></p>
		<p class="due-for-renewal-count"><a href="<?php echo esc_url( $due_count_url ); ?>">Due: <?php echo esc_html( $due_for_renewal_count ); ?></a></p>
	    <p class="expired-count"><a href="<?php echo esc_url( $expired_count_url ); ?>">Expired: <?php echo esc_html( $expired_count ); ?></a></p>
	    <p class="grace-period-count"><a href="<?php echo esc_url( $grace_count_url ); ?>">Grace Period: <?php echo esc_html( $grace_period_count ); ?></a></p>
	</div>

    
    <div class="client-services">
        <?php if ( empty( $services ) ) : ?>
            <div class="main-page-card">
                <p><?php esc_html_e( 'No service found.', 'smart-woo-service-invoicing' ); ?></p>
                <a href="<?php echo esc_url( $buy_product_page ); ?>" class="sw-blue-button"><?php esc_html_e( 'Buy New Service', 'smart-woo-service-invoicing' ); ?></a>
            </div>
        <?php else: ?>
            
            <?php foreach ( $services as $service ) : ?>
                <div class="main-page-card">
                    <h3><?php echo esc_html( $service->get_name() ); ?> <small>(<?php echo esc_html( smartwoo_service_status( $service ) ); ?>)</small></h3>
                    <?php echo wp_kses_post( $service->print_expiry_notice() ); ?>
                    <?php if( 'Processing' !== smartwoo_service_status( $service ) ) : ?>
                        <p style="overflow-wrap: anywhere;">Service ID: <?php echo esc_html( $service->get_service_id() ); ?></p>
                        <a href="<?php echo esc_url( smartwoo_service_preview_url( $service->get_service_id() ) ); ?>" class="sw-blue-button"><?php esc_html_e( 'View Details', 'smart-woo-service-invoicing' ); ?></a>
                <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div style="text-align: right;">
        <?php if( $total_pages > 1 ) : ?>
            <div class="sw-pagination-buttons">
                <p><?php echo absint( $total_items_count ) . ' item' . ( $total_items_count > 1 ? 's' : '' ); ?></p>
                    <?php if ( $page > 1 ) : $prev_page = $page - 1; ?>
                        <a class="sw-pagination-button" href="<?php echo esc_url( smartwoo_get_endpoint_url( 'page', $prev_page ) ); ?>"><button><span class="dashicons dashicons-arrow-left-alt2"></span></button></a>
                    <?php endif; ?>
                <p><?php echo absint( $page ) . ' of ' . absint( $total_pages ); ?></p>
                <?php if ( $page < $total_pages ) : $next_page = $page + 1; ?>
                    <a class="sw-pagination-button" href="<?php echo esc_url( smartwoo_get_endpoint_url( 'page', $next_page ) ); ?>"><button><span class="dashicons dashicons-arrow-right-alt2"></span></button></a>
                <?php endif; ?>
            </div>
        <?php elseif ( ! empty( $services ) ): ?>
            <div class="sw-pagination-buttons">
                <p><?php echo absint( $total_items_count ) . ' item' . ( $total_items_count > 1 ? 's' : '' ); ?></p>
            </div>
        <?php endif; ?>
    </div>

    <div syle="position: relative;">
        <div id="swloader">Just a moment</div>
    </div>
	<div class="settings-tools-section">
        <h2>Account Settings</h2>
        <div class="sw-button-container">
            <a class="sw-blue-button" id="sw-billing-details">Billing Details</a>
            <a class="sw-blue-button" id="sw-load-user-details">My Details</a>
            <a class="sw-blue-button" id="sw-account-log">Account Logs</a>
            <a class="sw-blue-button" id="sw-load-transaction-history">Transaction History</a>
        </div>
	    <div id="ajax-content-container"></div>
	</div>
</div>

