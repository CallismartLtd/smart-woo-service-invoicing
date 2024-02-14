<?php
/**
 * File name    :   admin-menu.php
 * @author      :   Callistus
 * Description  :   Function definition file for admin menus
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
require_once    SW_ABSPATH . 'admin/callback.php';
require_once    SW_ABSPATH . 'includes/sw-service/sw-service-admin-temp.php';
require_once    SW_ABSPATH . 'includes/sw-service/sw-new-service-processing.php';
require_once    SW_ABSPATH . 'includes/sw-invoice/sw-invoice-admin-temp.php';
require_once    SW_ABSPATH . 'includes/sw-product/sw-product-admin-temp.php';
require_once    SW_ABSPATH . 'admin/sw-admin-settings.php';



// Add a separate admin menu for Smart Invoice
function smart_invoice_admin_menu() {
    add_menu_page(
        'Smart Invoice',
        'Smart Invoice',
        'manage_options',
        'sw-admin',
        'smart_woo_service',
        'dashicons-format-aside'
    );

    // Add submenu "Service Orders"
    add_submenu_page(
        'sw-admin',
        'Service Orders',
        'Service Orders',
        'manage_options',
        'sw-service-orders',
        'sw_render_order_for_sw_products'
    );

    // Add submenu "Invoices"
    add_submenu_page(
        'sw-admin',
        'Invoices',
        'Invoices',
        'manage_options',
        'sw-invoices',
        'sw_invoices',
    );

    // Add submenu "Service Products"
     add_submenu_page(
        'sw-admin',
        'Service Products',
        'Service Products',
        'manage_options',
        'sw-products',
        'sw_products_page'
    );

    // Add submenu "Send Mail"
    add_submenu_page(
        'sw-admin',
        'Send Mail',
        'Send Mail',
        'manage_options',
        'sw-mail',
        'send_mail_page'
    );

    // Add submenu "Settings"
    add_submenu_page(
        'sw-admin',
        'Settings',
        'Settings',
        'manage_options',
        'sw-options',
        'sw_options_page'
    );

}

add_action('admin_menu', 'smart_invoice_admin_menu');


/**
 * Reusable navigation function for plugin submenu pages.
 *
 * @param array  $tabs         An associative array of tabs (tab_slug => tab_title).
 * @param string $title        Title you choos to display on each page page.
 * @param string $page_slug    The url slug of the current page
 * @param string $current_tab  The current tab parameter for the submenu page.
 * @param string $query_var    The query variable
 */
function sw_sub_menu_nav($tabs, $title, $page_slug, $current_tab, $query_var ) {
    $output = '<div class="wrap">';
    $output .= '<h1 class="wp-heading-inline">' . esc_html($title) . '</h1>';
    $output .= '<nav class="nav-tab-wrapper">';

    foreach ($tabs as $tab_slug => $tab_title) {
        $active_class = ($current_tab === $tab_slug) ? 'nav-tab-active' : '';
        $output .= "<a href='admin.php?page=$page_slug&$query_var=$tab_slug' class='nav-tab $active_class'>$tab_title</a>";
    }

    $output .= '</nav>';
    $output .= '</div>';
    
    return $output;
}
