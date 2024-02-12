<?php
/**
 * File name    :   scrc.php
 * @author      :   Callistus
 * Description  :   Here I defined the linking order for the plugin
 */

if (!defined('ABSPATH')) {
    exit;
}


/**
 * Load woocommerce before loading plugin dependencies
 */
function sw_check_woocommerce() {
    if ( !class_exists( 'WooCommerce' ) ) {
        // Throw error
        $woo_plugin_url = 'https://wordpress.org/plugins/woocommerce/';
        $notice = sprintf(
            'Smart Woo Service and Invoice requires WooCommerce to be active. Please <a href="%s" class="activate-link" target="_blank">activate WooCommerce</a>. or deactive plugin to avoid fatal error',
            $woo_plugin_url
        );
        add_action('admin_notices', function () use ( $notice ) {
            echo '<div class="notice notice-error is-dismissible">' . $notice . '</div>';

        });
    }
}
add_action( 'admin_init', 'sw_check_woocommerce' );

add_action( 'woocommerce_loaded', 'sw_initialization' );

function sw_initialization(){
    if ( class_exists( 'woocommerce' ) ) {
        do_action( 'smart_woo_init' );
    }
}


function enqueue_smart_woo_scripts() {
    
    // Enqueue styles for both admin and frontend
    wp_enqueue_style('smart-woo-invoice-style', SW_DIR_URL . 'assets/css/smart-woo.css', array(), '1.0', 'all');

    if (is_admin()) {
        // Enqueue admin-specific styles
        wp_enqueue_style('smart-woo-invoice-style-admin', SW_DIR_URL . 'assets/css/smart-woo.css', array(), '1.0', 'all');
    }

    // Enqueue the JavaScript file
    wp_enqueue_script('smart-woo-script', SW_DIR_URL . 'assets/js/smart-woo.js', array('jquery'), '1.0', true);
    
    // Define the redirect URL for admin dashboard
    $invoice_admin_dashboard_url = esc_url( admin_url( 'admin.php?page=sw-invoices&action=dashboard' ) );
    $service_admin_dashboard_url = esc_url( admin_url ( 'admin.php?page=sw-admin' ) );

    // Localize the script with your data
    wp_localize_script('smart-woo-script', 'smart_woo_vars', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'admin_invoice_page' => $invoice_admin_dashboard_url,
        'sw_admin_page'      => $service_admin_dashboard_url,
        'security' => wp_create_nonce( 'smart_woo_nonce' ),
        'home_url' => esc_url(home_url('/')),
        'never_expire_value' => '',
    ));

}

// Hook into the appropriate action to enqueue the scripts and styles
add_action('admin_enqueue_scripts', 'enqueue_smart_woo_scripts');
add_action('wp_enqueue_scripts', 'enqueue_smart_woo_scripts');




function sw_load_dependencies(){
    require_once SW_ABSPATH . 'admin/include/cron-schedule.php';
    require_once SW_ABSPATH . 'admin/include/email-templates.php';
    require_once SW_ABSPATH . 'admin/include/hooks.php';
    require_once SW_ABSPATH . 'admin/include/service-remote.php';
    require_once SW_ABSPATH . 'admin/include/smart-renewal.php';
    include_once SW_ABSPATH . 'admin/include/status-api.php';
    require_once SW_ABSPATH . 'admin/include/sw-db-update.php';
    require_once SW_ABSPATH . 'admin/include/sw-db.php';
    require_once SW_ABSPATH . 'admin/include/wallet-debit.php';
    require_once SW_ABSPATH . 'admin/admin-menu.php';
    require_once SW_ABSPATH . 'admin/callback.php';
    require_once SW_ABSPATH . 'admin/helper-function.php';
    require_once SW_ABSPATH . 'frontend/invoice/contr.php';
    require_once SW_ABSPATH . 'frontend/invoice/template.php';
    require_once SW_ABSPATH . 'frontend/service/contr.php';
    require_once SW_ABSPATH . 'frontend/service/mod.php';
    require_once SW_ABSPATH . 'frontend/service/template.php';
    require_once SW_ABSPATH . 'frontend/client-portal.php';
    require_once SW_ABSPATH . 'frontend/shortcode.php';
    require_once SW_ABSPATH . 'includes/sw-invoice/invoice.downloadable.php';
    require_once SW_ABSPATH . 'includes/sw-invoice/class-sw-invoice.php';
    require_once SW_ABSPATH . 'includes/sw-invoice/class-sw-invoice-database.php';
    require_once SW_ABSPATH . 'includes/sw-invoice/contr.php';
    require_once SW_ABSPATH . 'includes/sw-invoice/sw-invoice-admin-temp.php';
    require_once SW_ABSPATH . 'includes/sw-invoice/sw-invoice-function.php';
    require_once SW_ABSPATH . 'includes/sw-service/class-sw-service.php';
    require_once SW_ABSPATH . 'includes/sw-service/class-sw-service-database.php';
    require_once SW_ABSPATH . 'includes/sw-service/contr.php';
    require_once SW_ABSPATH . 'includes/sw-service/sw-service-functions.php';
    require_once SW_ABSPATH . 'includes/sw-service/sw-service-admin-temp.php';
    require_once SW_ABSPATH . 'includes/sw-product/class-sw-product.php';
    require_once SW_ABSPATH . 'includes/sw-product/contr.php';
    require_once SW_ABSPATH . 'includes/sw-product/sw-product-admin-temp.php';
    require_once SW_ABSPATH . 'includes/sw-product/sw-product-functions.php';
    require_once SW_ABSPATH . 'includes/sw-product/sw-product-configure-temp.php';
    require_once SW_ABSPATH . 'woocommerce/smart-woo-product.php';
    require_once SW_ABSPATH . 'woocommerce/woocommerce.php';
    require_once SW_ABSPATH . 'index.php';

    // Do actual action after loading plugin files
    do_action( 'smart_woo_loaded' );
}



add_action( 'smart_woo_init', 'sw_load_dependencies' ); 
