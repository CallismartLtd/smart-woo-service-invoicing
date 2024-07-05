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
 * Callback function for plugin WooCommerce account menu.
 */
function smartwoo_register_woocommerce_account_menu( $items ) {
	
    $new_items = array(
        'smartwoo-invoice' => __( 'Invoices', 'smart-woo-service-invoicing' ),
        'smartwoo-service' => __( 'Services', 'smart-woo-service-invoicing' ),
    );

    $position_invoice = 3;
    $position_service = 4;

    $items = array_slice( $items, 0, $position_invoice, true ) +
        $new_items +
        array_slice( $items, $position_invoice, NULL, true ) +
    array_slice( $items, $position_service, NULL, true );
			 
	return $items;
}


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

// Service Page Content
function smartwoo_service_myaccount_content() {
    $service_content = '<div class="wrap">';
    if ( isset( $_GET['view_service'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        echo  wp_kses_post( smartwoo_service_details() ) ;
    } else {
        $service_content .= '<h2>' . __( 'Services', 'smart-woo-service-invoicing' ). '</h2>';
        $service_content .= smartwoo_active_service_count_shortcode();
        $service_content .= smartwoo_service_mini_card();
        $service_content .= '<div class="settings-tools-section">';
        $service_content .= '<h2>Settings and Tools</h2>';
        $service_content .= '<div id="swloader">Loading...</div>';
        $service_content .= '<div class="sw-button-container">';
        $service_content .= '<button class="minibox-button" id="sw-billing-details">Billing Details</button>';
        $service_content .= '<button class="minibox-button" id="sw-load-user-details">My Details</button>';
        $service_content .= '<button class="minibox-button" id="sw-account-log">Account Logs</button>';
        $service_content .= '<button class="minibox-button" id="sw-load-transaction-history">Transaction History</button>';
        $service_content .= '</div>';
        $service_content .= '<div id="ajax-content-container"></div>'; //Respone container.
        $service_content .= '</div>';
        $service_content .= '</div>';
        echo wp_kses_post( $service_content );
    }
}


/**
 * WooCommerce my-account page title
 * 
 * @param string $title The endpoint title.
 * @return string $title The page the title.
 * @since 1.0.52
 */
function smartwoo_myaccount_titles( $title ) {
    global $wp;

    if ( array_key_exists( 'smartwoo-invoice', $wp->query_vars ) ) {
        $title = __( 'My Invoices', 'smart-woo-service-invoicing' );
    } elseif ( array_key_exists( 'smartwoo-service',  $wp->query_vars )  ) {
        $title = __( 'My Services', 'smart-woo-service-invoicing' );
    }
    
    return $title;
}

