<?php
/**
 * This file contains a the short codes for this plugin and they
 * are organized in a way that it should be easier you reading this to
 * understand
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * This line is for services
 */

// Add shortcodes for displaying counts
add_shortcode('service_count', 'service_count_shortcode');
add_shortcode('service_mini_card', 'service_mini_card_shortcode');

/**
 * This line is for Invoices
 */
// Add a shortcode for displaying the invoices table
add_shortcode('invoices', 'display_invoices_table');
// Add a shortcode for displaying invoice status counts
add_shortcode('invoice_status_counts', 'get_invoice_status_counts');
add_shortcode('unpaid_invoices_count', 'get_unpaid_invoices_count');

/**
 * This line is for All transactions shortcodes
 */  

add_shortcode('pending_transactions_count', 'get_pending_transactions_count');



