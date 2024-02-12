<?php
/* Template Name: Product Configuration */

global $wp_query;
$product_id = isset($wp_query->query_vars['sw_product_id']) ? absint($wp_query->query_vars['sw_product_id']) : 0;
$product = wc_get_product($product_id);
$product_name = $product ? $product->get_name() : '';

function sw_configure_page_title($title_parts) {
    $title_parts['title'] = 'Product Configuration';
    return $title_parts;
}
add_filter('document_title_parts', 'sw_configure_page_title');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sw_add_configured_product_to_cart'])) {
    
    // Sanitize and validate form data
    $service_name = isset($_POST['service_name']) ? sanitize_text_field($_POST['service_name']) : '';
    $service_url = isset($_POST['service_url']) ? esc_url_raw($_POST['service_url']) : '';
    // Validation
    $validation_errors = array();

    if ( !preg_match( '/^[A-Za-z0-9\s]+$/', $service_name ) ) {
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
        wp_safe_redirect( wc_get_cart_url() );
        exit;
    }
}

get_header();

?>
<div id="primary">

<h2>Configure</h2>
    <div class="sw-configure-container">
        <p>Configure your desired options for <strong>"<?php echo $product_name; ?>"</strong> and continue checkout.</p>

        <form method="post" enctype="multipart/form-data">

            <!-- Service Name -->
            <div class="sw-form-row">
                <label for="service_name" class="sw-form-label">Service Name *</label>
                <span class="sw-field-description" title="Enter the service name (required)">?</span>
                <input type="text" name="service_name" class="sw-form-input" id="service_name" required>
            </div>

            <!-- Service URL -->
            <div class="sw-form-row">
                <label for="service_url" class="sw-form-label">Service URL (optional)</label>
                <span class="sw-field-description" title="Enter the service URL e.g., https:// (optional)">?</span>
                <input type="url" name="service_url" class="sw-form-input" id="service_url">
            </div>

            <button type="submit" name="sw_add_configured_product_to_cart" value="' . esc_attr($product_id) . '" class="sw-blue-button">Configure and Proceed</button>

        </form>
    </div>
</div><!-- #primary -->


<?php get_footer(); ?>
