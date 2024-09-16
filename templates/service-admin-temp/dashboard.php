<?php
/**
 * The Smart Woo Admin dashboard template file.
 * 
 * @author Callistus
 * @package SmartWoo\templates
 * @since 2.0.12
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="sw-admin-dashboard" id="sw-admin-dash">
    <div class="sw-admin-dash-header">
        <!-- Smart Woo Info -->
        <div class="sw-admin-dash-info">
        <img src="<?php echo esc_url( SMARTWOO_DIR_URL . '/assets/images/smart-woo-official-icon.png' ) ?>" alt="Smart Woo Icon">
        <h1><?php echo wp_kses_post( apply_filters( 'smartwoo_dashboard_name', 'Smart Woo' ) );?></h1>
        <em><?php echo esc_html( apply_filters( 'smartwoo_dashboard_version', SMARTWOO_VER ) );?></em>
        </div>

        <!-- Navigation buttons -->
        <div class="sw-admin-dash-nav">
            <a id="dashboardBtn">Dashboard</a>
            <a>Dashboard</a>
            <a>Dashboard</a>
            <a>Dashboard</a>
            <a>Dashboard</a>
        </div>
        <?php if( ! class_exists( 'SmartWooPro', false ) ):?>
            <div class="sw-upgrade-to-pro">
                <a><?php echo esc_html__( 'Upgrade to Pro', 'smart-woo-service-invoicing' );?></a>
            </div>
        <?php endif;?>

    </div>

    <div id="swloader">Just a moment</div>
    <div class="sw-admin-dash-body">

        <div class="sw-dash-content-container">
            <div class="sw-dash-content">

                <div class="sw-skeleton sw-skeleton-text"></div>
                <div class="sw-skeleton sw-skeleton-text"></div>
                <div class="sw-skeleton sw-skeleton-text"></div>
                <div class="sw-skeleton sw-skeleton-text"></div>
                <div class="sw-skeleton sw-skeleton-text"></div>
                <div class="sw-skeleton sw-skeleton-text"></div>
                <div class="sw-skeleton sw-skeleton-text"></div>
                <div class="sw-skeleton sw-skeleton-text"></div>
                <div class="sw-skeleton sw-skeleton-text"></div>
            </div>
        </div>

    </div>

    <div class="sw-admin-dash-footer">

    </div>
</div>