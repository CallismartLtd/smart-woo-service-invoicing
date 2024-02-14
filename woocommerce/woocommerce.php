<?php

/**
 * File name    :   woocommerce-my-account.php
 * @author      :   Callistus
 * Description  :   Handles WooCommerce my account version of this plugin
 */
 Defined( 'ABSPATH' ) || exit;

 // Add Invoice and Service Menu Items
function add_invoice_service_menu_items( $items ) {
    $items['invoice'] = 'Invoice';
    $items['service'] = 'Service';
    return $items;
}
add_filter('woocommerce_account_menu_items', 'add_invoice_service_menu_items');

// Register Invoice and Service Endpoints
function register_invoice_service_endpoints() {
    add_rewrite_endpoint( 'invoice', EP_PAGES );
    add_rewrite_endpoint( 'service', EP_PAGES );
}
add_action( 'init', 'register_invoice_service_endpoints' );

// Invoice Page Content
function invoice_page_content() {
    echo '<h2>Invoices</h2>';
    $current_user_id     = get_current_user_id();
    $current_user        = wp_get_current_user();
    sw_get_navbar( $current_user_id );
    echo do_shortcode( '[unpaid_invoices_count]' );
    echo do_shortcode('[invoices]');
}
add_action( 'woocommerce_account_invoice_endpoint', 'invoice_page_content' );

// Service Page Content
function service_page_content() {
    echo '<h2>Services</h2>';
    echo do_shortcode( '[service_count]' );
    echo do_shortcode( '[client_services]' );
}
add_action( 'woocommerce_account_service_endpoint', 'service_page_content' );