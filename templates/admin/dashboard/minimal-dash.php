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
<div class="smartwoo-admin-page-content sw-admin-dashboard" id="sw-admin-dash">
    <div class="sw-admin-dash-header">
        <div class="sw-admin-header-content">
            <!-- Smart Woo Info -->
            <div class="sw-admin-dash-info">
                <img src="<?php echo esc_url( SMARTWOO_DIR_URL . '/assets/images/smart-woo-official-icon.png' ) ?>" alt="Smart Woo Icon">
                <h1><?php echo wp_kses_post( apply_filters( 'smartwoo_dashboard_name', 'Smart Woo' ) );?></h1>
                <em><?php echo esc_html( apply_filters( 'smartwoo_dashboard_version', SMARTWOO_VER ) );?></em>
            </div>

            <!-- Navigation buttons -->
            <div class="sw-admin-dash-nav">
                <ul>
                    <li id="dashboardBtn" class="active">Dashboard</li>
                    <li id="dashAddNew">Add New</li>
                    <li id="dashOrderBtn">Orders</li>
                    <li id="dashInvoicesBtn">Invoices</li>
                    <li id="dashProductBtn">Products</li>
                    <li id="dashSettingsBtn">Settings</li>
                </ul>
            </div>
            <div class="sw-admin-menu-icon">
                <span class="dashicons dashicons-menu"></span>
            </div>

            <?php if( ! class_exists( 'SmartWooPro', false ) ):?>
                <div class="sw-upgrade-to-pro">
                    <a><?php echo esc_html( apply_filters( 'smartwoo_dash_pro_button_text', __( 'Activate Pro Features', 'smart-woo-service-invoicing' ) ) );?></a>
                </div>
            <?php endif;?>
        </div>
    </div>

    <div id="swloader" style="background-color: #f1f1f100"></div>

    <div class="sw-admin-dash-body">
        <?php do_action( 'smartwoo_admin_header' ) ?>
        <button type="button" class="button" id="smartwoo-dashboard-switcher">
            <?php printf( '%s', $is_advanced_dashboard ? esc_html__( 'Switch to minimal dashboad', 'smart-woo-service-invoicing' ) : esc_html__( 'Switch to advanced dashboad', 'smart-woo-service-invoicing' ) ); ?>
        </button>
        <div class="sw-search-container">
           <div class="sw-search-item-container">
                <input type="search" name="sw_service_search" id="sw_service_search" placeholder="Search for services">
                <button id="swSearchBtn" title="click to search"><span class="dashicons dashicons-search"></span></button>
                <div id="search-notification" class="notification-tooltip"></div>
            </div> 
        </div>
        

        <div class="sw-dash-content-container">            
            <div class="sw-dash-content">
                <div class="sw-skeleton sw-skeleton-text"></div>
                <div class="sw-skeleton sw-skeleton-text"></div>
            </div>
            
        </div>

    </div>

    <div class="sw-admin-dash-footer">
        <?php do_action( 'smartwoo_admin_dash_footer' ); ?>        
    </div>
</div>
