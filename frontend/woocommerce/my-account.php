<?php
/**
 * File name   : my-account.php
 * Author      : Callistus
 * Description : All WooCommerce my-account integration functions
 *
 * @since      : 1.0.1
 * @package    : SmartWooServiceInvoicing
 */

 defined( 'ABSPATH' ) || exit; // Prevent direct access
 
// Add Invoice and Service Menu Items
function sw_register_woo_my_account_menu( $items ) {
	
    // Add new menu items
    $new_items = array(
        'invoice' => 'Invoices',
        'service' => 'Services',
    );

    // Specify the positions for 'Invoices' and 'Services'
    $position_invoice = 3;
    $position_service = 4;

    // Use array_slice to insert the new items at specific positions
    $items = array_slice( $items, 0, $position_invoice, true ) +
             $new_items +
             array_slice( $items, $position_invoice, NULL, true ) +
             array_slice( $items, $position_service, NULL, true );
			 
	return $items;
}
add_filter( 'woocommerce_account_menu_items', 'sw_register_woo_my_account_menu', 40 );

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
	sw_get_navbar( get_current_user_id() );
	echo do_shortcode( '[sw_active_service_count]' );
	echo do_shortcode( '[sw_service_mini_card]' );
}
add_action( 'woocommerce_account_service_endpoint', 'service_page_content' );
