<?php

/**
 * File name    :   woocommerce-my-account.php
 * @author      :   Callistus
 * Description  :   Handles WooCommerce my account version of this plugin
 */
 Defined( 'ABSPATH' ) || exit;

 // Add Invoice and Service Menu Items
function sw_register_woo_my_account_menu( $items ) {
    $items['invoice'] = 'Invoices';
    $items['service'] = 'Services';
    return $items;
}
add_filter('woocommerce_account_menu_items', 'sw_register_woo_my_account_menu');

// Register Invoice and Service Endpoints
function sw_register_url_endpoints() {
    add_rewrite_endpoint( 'invoice', EP_PAGES );
    add_rewrite_endpoint( 'service', EP_PAGES );
}
add_action( 'init', 'sw_register_url_endpoints' );

// Invoice Page Content
function invoice_page_content() {
    echo '<h2>Invoices</h2>';
    sw_get_navbar( get_current_user_id() );
    echo do_shortcode( '[unpaid_invoices_count]' );
    echo do_shortcode( '[sw_invoice_mini_card]' );
}
add_action( 'woocommerce_account_invoice_endpoint', 'invoice_page_content' );

// Service Page Content
function service_page_content() {
    echo '<h2>Services</h2>';
    echo do_shortcode( '[sw_active_service_count]' );
    echo do_shortcode( '[sw_service_mini_card]' );
}
add_action( 'woocommerce_account_service_endpoint', 'service_page_content' );