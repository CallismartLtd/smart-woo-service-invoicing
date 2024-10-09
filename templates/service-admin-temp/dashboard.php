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
        <a id="dashAddNew">Add New</a>
        <a id="dashOrderBtn">Orders</a>
            <a id="dashInvoicesBtn">Invoices</a>
            <a id="dashProductBtn">Products</a>
            <a id="dashSettingsBtn">Settings</a>
        </div>
       <div class="sw-admin-menu-icon">
	        <span class="dashicons dashicons-menu"></span>
	    </div>

        <?php if( ! class_exists( 'SmartWooPro', false ) ):?>
            <div class="sw-upgrade-to-pro">
                <a><?php echo esc_html( apply_filters( 'smartwoo_dash_pro_button_text', __( 'Upgrade to Pro', 'smart-woo-service-invoicing' ) ) );?></a>
            </div>
        <?php endif;?>

    </div>

    <div id="swloader">Just a moment</div>
    <div class="sw-admin-dash-body">
        <div class="sw-search-container">
            <input type="search" name="sw_service_search" id="sw_service_search" placeholder="Search service">
            <button id="swSearchBtn" title="click to search"><span class="dashicons dashicons-search"></span></button>
            <div id="search-notification" class="notification-tooltip"></div>
        </div>

        <div class="sw-dash-content-container">            
            <div class="sw-dash-content">

                <div class="sw-skeleton sw-skeleton-text"></div>
                <div class="sw-skeleton sw-skeleton-text"></div>
            </div>
        </div>

    </div>

    <div class="sw-admin-dash-footer">

    </div>
</div>