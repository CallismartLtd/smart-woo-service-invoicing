<?php
/**
 * This file contains all the dynamic hooks in this plugin
 */

 if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This line is for the download button which is dynamically shown
 *  only where the shortcode is displayed
 

 function add_invoice_download_button($content) {
    // Check if we are on the preview invoice page with the specified parameters
    if (isset($_GET['invoice_page']) && $_GET['invoice_page'] === 'view_invoice' && isset($_GET['invoice_id'])) {
        // Extract the invoice_id from the URL query parameter
        $invoice_id = sanitize_text_field($_GET['invoice_id']);
        // Check if the content contains the [preview_invoices] shortcode
        if ( strpos( $content, '[sw_invoice_page') !== false && !empty($invoice_id ) ) {
            // Check if the user is logged in
            if (is_user_logged_in()) {
                $user_id = get_current_user_id();
                // Add a download button to the content
                $download_url = add_query_arg( ['download_invoice' => 'true', 'invoice_id' => $invoice_id, 'user_id' => $user_id], get_permalink() );

                // Add nonce to the URL
                $download_url = wp_nonce_url($download_url, 'download_invoice_nonce');

                $download_button = '<div class="download-button-container">';
                $download_button .= '<a href="' . esc_url($download_url) . '" class="download-button">Download as PDF</a>';
                
                $invoice = Sw_Invoice_Database::get_invoice_by_id($invoice_id);
                $invoice_status = $invoice->getPaymentStatus();

                // Add the "Pay" button if the order is not paid
                if ($invoice_status && strtolower($invoice->getPaymentStatus()) === 'unpaid') {
                    $order_id = $invoice->getOrderId();
                    $checkout_url = wc_get_checkout_url();
                    $order_key = get_post_meta($order_id, '_order_key', true);
                    $pay_button_url = add_query_arg(['pay_for_order' => 'true', 'key' => $order_key], $checkout_url . 'order-pay/' . $order_id);
                    $download_button .= '<a href="' . esc_url($pay_button_url) . '" class="invoice-pay-button">Complete Payment</a>';
                }

                $download_button .= '</div>';

                // Add the download buttons after the content
                $content .= $download_button;
            }
        }
    }

    return $content;
}
add_filter('the_content', 'add_invoice_download_button');*/



// Add sw form fields to the order page
add_action('woocommerce_admin_order_data_after_order_details', 'add_sw_order_fields');

function add_sw_order_fields($order) {
    $order_type = get_post_meta($order->get_id(), 'Order Type', true);
    $service_id = get_post_meta($order->get_id(), 'Service ID', true);
    $service_name = get_post_meta($order->get_id(), 'Service Name', true);

    echo '<div class="order_data_column">';
    

    echo '<h3 style="width: 300px; color: blue; font-size: 18px;"> Service Information </h3>';

    // Custom text field for Order Type
    echo '<p><label for="_order_type">Order Type:</label>';
    echo '<input type="text" id="_order_type" name="_order_type" value="' . esc_attr($order_type) . '" /></p>';

    // Custom text field for Service ID
    echo '<p><label for="_service_id">Service ID:</label>';
    echo '<input type="text" id="_service_id" name="_service_id" value="' . esc_attr($service_id) . '" /></p>';

    // Custom text field for Service Name
    echo '<p><label for="_service_name">Service Name:</label>';
    echo '<input type="text" id="_service_name" name="_service_name" value="' . esc_attr($service_name) . '" /></p>';

    echo '</div>';
}


// Save the form field values with the same keys in the order sw fields when the order is updated
add_action('woocommerce_process_shop_order_meta', 'save_sw_order_fields');

function save_sw_order_fields($order_id) {
    $order = wc_get_order($order_id);

    if (isset($_POST['_order_type'])) {
        $order->update_meta_data('Order Type', sanitize_text_field($_POST['_order_type']));
    }

    if (isset($_POST['_service_id'])) {
        $order->update_meta_data('Service ID', sanitize_text_field($_POST['_service_id']));
    }

    if (isset($_POST['_service_name'])) {
        $order->update_meta_data('Service Name', sanitize_text_field($_POST['_service_name']));
    }

    $order->save();
}








function get_woocommerce_orders_dropdown($selected_order_id = '') {
    // Create an array to store order IDs
    $order_ids = array();

    // Query WooCommerce orders
    $args = array(
        'post_type'      => 'shop_order',
        'post_status'    => array('wc-processing', 'wc-completed'),
        'posts_per_page' => -1,
    );

    $orders = wc_get_orders($args);

    foreach ($orders as $order) {
        $order_id = $order->get_id();

        // Check if the order contains products of the 'sw_service' type
        $order_items = $order->get_items();

        foreach ($order_items as $item) {
            $product_id = $item->get_product_id();
            $product = wc_get_product($product_id);

            if ($product && $product->is_type('sw_service')) {
                $order_ids[] = $order_id;
                break; // No need to check this order further
            }
        }
    }

    $order_dropdown = '<select name="order_id" id="order_id">';
    $order_dropdown .= '<option value="">Select an order</option>';

    // Populate the dropdown with order IDs containing 'sw_service' products
    foreach ($order_ids as $order_id) {
        $order_dropdown .= '<option value="' . esc_attr($order_id) . '" ' . selected($order_id, $selected_order_id, false) . '>' . esc_html($order_id) . '</option>';
    }

    $order_dropdown .= '</select>';

    return $order_dropdown;
}








// Function to generate the service navigation bar with dynamic title and dropdown
function sw_get_navbar($current_user_id) {
    // Fetch user's services
    $services = sw_get_service($current_user_id);

    // Get the URL of the service page from the plugin options
    $service_page_id = get_option( 'sw_service_page', 0 );
    $service_page_url = get_permalink($service_page_id);

    // Get the URL of the invoice preview page from the plugin options
    $invoice_preview_page_id = get_option( 'sw_invoice_page', 0 );
    $invoice_preview_page_url = get_permalink($invoice_preview_page_id);

    // Determine the current page
    $current_page_slug = '';
    $navbar_title = '';

    if (is_page($service_page_id)) {
        $current_page_slug = 'services';
        $navbar_title = 'My Services';
    } elseif (is_page($invoice_preview_page_id)) {
        $current_page_slug = 'invoices';
        $navbar_title = 'My Invoices';
    }

    // Set the default page title
    $page_title = $navbar_title;

    // If the current page is 'services' and a service action is selected
    if ($current_page_slug === 'services' && isset($_GET['service_action'])) {
        $service_action = sanitize_key($_GET['service_action']);

        // Customize the page title based on the selected service action
        switch ($service_action) {
            case 'upgrade':
                $page_title = 'Upgrade Service';
                break;
            case 'downgrade':
                $page_title = 'Downgrade Service';
                break;
            case 'buy_new':
                $page_title = 'Buy New Service';
                break;
            // Add more cases as needed
        }
    }

    echo '<div class="service-navbar">';
    
    // Container for the title (aligned to the left)
    echo '<div class="navbar-title-container">';
    echo '<h3>' . esc_html($page_title) . '</h3>';
    echo '</div>';
    
    // Container for the links (aligned to the right)
    echo '<div class="navbar-links-container">';
    echo '<ul>';

    // Add link to the service page
    echo '<li><a href="' . esc_url($service_page_url) . '" class="' . ($current_page_slug === 'services' ? 'current-page' : '') . '">Services</a></li>';

    // Add link to the invoice preview page
    echo '<li><a href="' . esc_url($invoice_preview_page_url) . '" class="' . ($current_page_slug === 'invoices' ? 'current-page' : '') . '">Invoices</a></li>';

    // Add dropdown for service actions only on the service page
    if ($current_page_slug === 'services') {
        // Dropdown for service actions
        echo '<li class="service-actions-dropdown">';
        echo '<select onchange="redirectBasedOnServiceAction(this.value)">';
        echo '<option value="" selected>Select Action</option>';
        echo '<option value="upgrade">Upgrade Service</option>';
        echo '<option value="downgrade">Downgrade Service</option>';
        echo '<option value="buy_new">Buy New Service</option>';
        // Add more options as needed
        echo '</select>';
        echo '</li>';
    }

    echo '</ul>';
    echo '</div>';
    
    echo '</div>';
}


