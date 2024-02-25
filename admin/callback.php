<?php
/**
 * File name    :   callback.php
 * @author      :   Callistus
 * Description  :   callback function file for admin menu pages
 */
defined( 'ABSPATH' ) || exit; // exit if eccessed directly

/**
 * Main plugin admin page controller callback
 */
function smart_woo_service() {
    // Check if the current user has the required capability to access this page
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die(__( 'You do not have sufficient permissions to access this page.' ) );
    }

    // Determin which URL path to display content
    $action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';

    // Call the appropriate function based on the action
    switch ( $action ) {
        case 'process-new-service':
            sw_process_new_service_order_page();
            break;

        case 'service_details':
            // Call the function for handling service details
            sw_admin_view_service_details();
            break;

        case 'add-new-service':
            sw_handle_new_service_page();
             break;

        case 'edit-service':

            sw_handle_edit_service_page();
            break;

        default:
            // Call the default function
            sw_main_page();
            break;
    }
}



/**
 * Invoice admin invoice page
 */
function sw_invoices() {
    // Determin which URL path to display content

    $action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : 'dashboard';
 
    // Prepare array for submenu navigation
    $tabs = array(
        '' => 'Invoices',
        'add-new-invoice' => 'Add New',

    );

    echo sw_sub_menu_nav( $tabs, 'Invoice', 'sw-invoices', $action, 'action' );

    // Determin which action is set in the url path to display content

    switch ( $action ) {
        case 'add-new-invoice':
            sw_create_new_invoice_form();
            break;

        case 'edit-invoice':
            
            sw_edit_invoice_page();
          
            break;

        case 'invoice-by-status':
            sw_handle_admin_invoice_by_status();
            
            break;

        case 'view-invoice':
            
            sw_view_invoice_page();

            break;

        default:
            sw_invoice_dash();
            break;
    }
}


/**
 * Callback function for "Product" submenu page
 */
function sw_products_page() {
    // Check for URL parameters
    $action = isset( $_GET[ 'action' ] ) ? sanitize_text_field( $_GET[ 'action' ] ) : '';
    $product_id = isset($_GET[ 'product_id' ]) ? intval($_GET[ 'product_id' ]) : 0;

    $tabs = array(
        '' => 'Products',
        'add-new' => 'Add New',

    );


      echo sw_sub_menu_nav( $tabs, 'Products', 'sw-products', $action, 'action' );

    // Handle different actions
    switch ($action) {
        case 'add-new':
            sw_render_new_product_form();
            break;
        case 'edit':
            display_edit_form($product_id);
            break;
        default:
            display_product_details_table();
            break;
    }
}



//Callback for Settings Page
function sw_options_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
     // Check for URL parameters
     $action = isset( $_GET[ 'tab' ] ) ? sanitize_text_field( $_GET[ 'tab' ] ) : '';

     $tabs = array(
        '' => 'General',
        'business' => 'Business',
        'invoicing' => 'Invoicing',
        'emails' => 'Emails',

    );


    echo sw_sub_menu_nav( $tabs, 'Settings', 'sw-options', $action, 'tab' );
 
     // Handle different actions
     switch ($action) {
         case 'business':
             sw_render_service_options_page();
             break;
         case 'invoicing':
             sw_render_invoice_options_page();
             break;
        case 'emails':
            sw_render_email_options_page();
            break;
         default:
         sw_options_dash_page();
             break;
     }
}


/**
 * Register custom post states for specific pages.
 *
 * This function adds custom post states to pages based on their IDs.
 * It is hooked into the 'display_post_states' filter.
 *
 * @param array   $post_states An array of post states.
 * @param WP_Post $post        The current post object.
 *
 * @return array Modified array of post states.
 */
function sw_register_post_states( $post_states, $post ) {
    $service_details_page_id = get_option( 'sw_service_page' );
    $invoice_details_page_id = get_option( 'sw_invoice_page' );

    if ( $post->ID == $service_details_page_id ) {
        $post_states[] = 'Service Subscription Page';
    } elseif ($post->ID == $invoice_details_page_id ) {
        $post_states[] = 'Invoice Management Page';
    }

    return $post_states;
}
add_filter('display_post_states', 'sw_register_post_states', 10, 2);
