<?php
/**
 * File name    :   sw-product-configure-temp.php
 * @author      :   Callistus
 * Description  :   This file defines the checkout flow of Smart Woo Product
 */

/**
 * Configure product form and submission handling
 */
// Hook the form rendering function to the action
add_action('smart_woo_product_configuration_page', 'render_configuration_form');

// HTML form function to render the configuration form
function render_configuration_form( $product_id ) {
    get_header();

    echo '<div class="wp-block wp-block-group">';
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sw_add_configured_product_to_cart'])) {
        
        // Sanitize and validate form data
        $service_name = isset($_POST['service_name']) ? sanitize_text_field($_POST['service_name']) : '';
        $service_url = isset($_POST['service_url']) ? esc_url_raw($_POST['service_url']) : '';
        // Validation
        $validation_errors = array();

        if ( !preg_match( '/^[A-Za-z0-9]+$/', $service_name ) ) {
            $validation_errors[] = 'Service name should only contain letters, and numbers.';
        }

        if ( !empty( $service_url ) && filter_var($service_url, FILTER_VALIDATE_URL) === false) {
            $validation_errors[] = 'Invalid service URL format.';
        }

        if ( !empty( $validation_errors ) ) {
            sw_error_notice( $validation_errors );
        }

        if ( empty( $validation_errors ) ) {
            // Add the product to the cart with custom data
            $cart_item_data = array(
                'service_name' => $service_name, 
                'service_url'  => $service_url,
            );

            WC()->cart->add_to_cart($product_id, 1, 0, array(), $cart_item_data);

            // Redirect to the cart page or any other page as needed
            wp_safe_redirect(wc_get_cart_url());
            exit;
        }
    }
    
    $product = wc_get_product( $product_id );
    $product_name = $product->get_name();
    // Add a straight line under the heading
    echo '<form class="sw-cart-form" method="post" enctype="multipart/form-data">';
    echo '<h2 class="sw-form-heading">Configure <span class="sw-form-heading-line"></span></h2>';
    echo 'configure your desired option for <strong>"' . $product_name .'"</strong> and continue checkout.';
    echo '<div class="serv-details-card">';
    // Service Name (required)
    echo '<label for="sw-service-name" class="sw-form-label">Service Name <span class="sw-required">*</span></label>';
    echo '<input type="text" name="service_name" id="sw-service-name" class="sw-form-input" required>';
    
    // Service URL (optional)
    echo '<label for="sw-service-url" class="sw-form-label">Service URL (optional)</label>';
    echo '<input type="url" name="service_url" id="sw-service-url" class="sw-form-input">';
    
    // Add to Cart button
    echo '<button type="submit" name="sw_add_configured_product_to_cart" value="' . esc_attr( $product_id ) . '" class="sw-blue-button">Add to Cart</button>';
    
    echo '</form>';
    echo '</div>';
    echo '</div>';

    
    get_footer();
}



// Add configured product data to cart item session
add_filter('woocommerce_add_cart_item_data', 'sw_add_configured_product_to_cart', 10, 3);

function sw_add_configured_product_to_cart($cart_item_data, $product_id, $variation_id) {
    if (isset($_POST['service_name'])) {
        $cart_item_data['sw_service_name'] = wc_clean($_POST['service_name']);
    }

    if (isset($_POST['service_url'])) {
        $cart_item_data['sw_service_url'] = esc_url_raw($_POST['service_url']);
    }

    return $cart_item_data;
}

// Get configured product data from cart item session
add_filter('woocommerce_get_cart_item_from_session', 'sw_get_configured_product_from_session', 10, 2);

function sw_get_configured_product_from_session($cart_item, $values) {
    if (isset($values['sw_service_name'])) {
        $cart_item['sw_service_name'] = $values['sw_service_name'];
    }

    if (isset($values['sw_service_url'])) {
        $cart_item['sw_service_url'] = $values['sw_service_url'];
    }

    return $cart_item;
}

// Display configured product data in cart and checkout
add_filter('woocommerce_get_item_data', 'sw_display_configured_product_data_in_cart', 10, 2);

function sw_display_configured_product_data_in_cart($cart_data, $cart_item) {
    if (isset($cart_item['sw_service_name'])) {
        $cart_data[] = array(
            'name'    => '<div class="sw-configured-product-container"><strong>' . __('Service Name', 'your-text-domain') . '</strong>',
            'value'   => '<span class="sw-configured-product">' . esc_html($cart_item['sw_service_name']) . '</span></div>',
            'display' => '',
        );
    }

    if (isset($cart_item['sw_service_url'])) {
        $cart_data[] = array(
            'name'    => '<div class="sw-configured-product-container"><strong>' . __('Service URL', 'your-text-domain') . '</strong>',
            'value'   => '<span class="sw-configured-product">' . esc_html($cart_item['sw_service_url']) . '</span></div>',
            'display' => '',
        );
    }

    return $cart_data;
}



// Configure the order with the data the customer gave us at product configuration stage, save it to order item meta
add_action('woocommerce_checkout_create_order_line_item', 'sw_save_configured_product_data_to_order_item_meta', 10, 4);

function sw_save_configured_product_data_to_order_item_meta($item, $cart_item_key, $values, $order) {
    if (isset($values['sw_service_name'])) {
        $item->add_meta_data('Service Name', $values['sw_service_name'], true);
    }

    if (isset($values['sw_service_url'])) {
        $item->add_meta_data('Service URL', $values['sw_service_url'], true);
    }

}



