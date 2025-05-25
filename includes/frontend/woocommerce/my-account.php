<?php
/**
 * File name   : my-account.php
 * 
 * Description All WooCommerce my-account integration functions.
 *
 * @author Callistus
 * @since  1.0.1
 * @package SmartWooServiceInvoicing.
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access.

/**
 * Content callback for my Invoice account menu item.
 */
function smartwoo_invoice_myaccount_content() {
    $invoice_content  = '<div class="wrap">';
    if ( isset( $_GET['view_invoice'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        echo wp_kses_post( smartwoo_invoice_details() );
    } else {
        $invoice_content .= '<h2>' . __( 'Invoices', 'smart-woo-service-invoicing' ) . '</h2>';
        $invoice_content .= smartwoo_get_unpaid_invoices_count();
        $invoice_content .= smartwoo_invoice_mini_card();
        echo wp_kses_post( $invoice_content );
    }
}
