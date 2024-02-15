<?php
/**
 * File name    :   contr.php
 * @author      :   Callistus
 * Description  :   Controller file for Sw_Product
 */





/**
 * Controls the new product creation form submission
 */
function sw_handle_new_product_form(){
    // Handle form submission
    if ( $_SERVER["REQUEST_METHOD"] == "POST" && isset( $_POST['create_sw_product'] ) ) {
        //Validate the product name
        $product_name = sanitize_text_field( $_POST['product_name'] );
        
        // Validation
        $validation_errors = array();

        if ( empty( $product_name ) ){
            $validation_errors[] = 'Product Name is required';
        }

        if ( ! preg_match( '/^[A-Za-z0-9\s]+$/', $product_name ) ) {
            $validation_errors[] = 'Product name should only contain letters, and numbers.';
        }

        if ( ! empty( $validation_errors ) ) {
            // Display validation errors using the custom error notice function
            sw_error_notice( $validation_errors );
        
        } elseif ( empty( $validation_errors ) ) {

            // Create the product
            $product_id = wp_insert_post( array(
                'post_title'    => $product_name,
                'post_type'     => 'product',
                'post_status'   => 'publish',
                'post_content'  => sanitize_textarea_field( $_POST['long_description'] ),
                'post_excerpt'  => sanitize_text_field( $_POST['short_description'] ),
            ));

            if (!is_wp_error( $product_id ) ) {
                // Set product type
                wp_set_object_terms( $product_id, 'sw_product', 'product_type' );

                // Set regular price (main product price)
                update_post_meta( $product_id, '_regular_price', floatval($_POST['product_price']) );
                update_post_meta( $product_id, '_price', floatval($_POST['product_price']) );

                // Set sign-up fee (product metadata)
                $sign_up_fee         = isset( $_POST['sign_up_fee'] ) ? floatval( $_POST['sign_up_fee'] ) : 0;
                update_post_meta( $product_id, 'sign_up_fee', $sign_up_fee );

                // Set billing circle (product metadata)
                $billing_cycle       = isset( $_POST['billing_cycle'] ) ? sanitize_text_field( $_POST['billing_cycle'] ) : '';
                update_post_meta( $product_id, 'billing_cycle', $billing_cycle );

                // Set grace period (product metadata)
                $grace_period_number = isset( $_POST['grace_period_number'] ) ? intval( $_POST['grace_period_number'] ) : 0;
                $grace_period_unit   = isset($_POST['grace_period_unit']) ? sanitize_text_field($_POST['grace_period_unit'] ) : '';
                update_post_meta( $product_id, 'grace_period_number', $grace_period_number );
                update_post_meta( $product_id, 'grace_period_unit', $grace_period_unit );

                // Set main product image (featured image)
                $product_image_id    = isset( $_POST['product_image_id'] ) ? absint( $_POST['product_image_id'] ) : 0;
                if ( $product_image_id ) {
                    // Set the attached image as the featured image
                    set_post_thumbnail( $product_id, $product_image_id );
                }


                // Show success message with product links
                $product_link = get_permalink( $product_id );
                $edit_link = admin_url( 'admin.php?page=sw-products&action=edit&product_id=' . $product_id );
                echo '<div class="updated"><p>New product created successfully! View your product <a href="' . esc_url($product_link) . '">here</a>.</p>';
                echo '<p>Edit the product <a href="' . esc_url( $edit_link ) . '">here</a>.</p></div>';
            }
        }
    }
}



/**
 * Configure button 
 */

 // Hook to display the "Configure Product" button under the main product price
add_action( 'woocommerce_single_product_summary', 'sw_configure_button_on_single_product', 15 );

function sw_configure_button_on_single_product() {
    global $product;

    // Check if the product is of type 'sw_product'
    if ( $product && $product->get_type() === 'sw_product' ) {
        // Remove default "Read more" button
        remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );

        // Display the "Configure Product" button
        echo '<div class="configure-product-button">';
        echo '<a href="' . home_url( '/configure/' . $product->get_id() ) . '" class="sw-blue-button alt">' . esc_html__( 'Configure Product', 'woocommerce' ) . '</a>';
        echo '</div>';
    }
}


function sw_service_rewrite_rule() {
    add_rewrite_rule('^configure/([^/]+)/?', 'index.php?pagename=configure&sw_product_id=$matches[1]', 'top');
}
add_action('init', 'sw_service_rewrite_rule');


function sw_service_query_vars($vars) {
    // Add 'sw_product_id' to the list of recognized query variables
    $vars[] = 'sw_product_id';
    return $vars;
}
add_filter('query_vars', 'sw_service_query_vars');


function sw_template_for_configure_page($template) {
    if ( get_query_var('pagename') === 'configure' ) {
        return SW_ABSPATH . '/templates/configure.php';
    }

    return $template;
}
add_filter('template_include', 'sw_template_for_configure_page');







/**
 * Get all orders for the configured service or check if a specific order is configured.
 * @param int|null $order_id_to_get Optional. If provided, returns the ID of the specified order.
 * @return int|array The ID of the specified order if $order_id_to_get is provided and is configured, or an array of order IDs for configured orders.
 */
function sw_get_orders_for_configured_products( $order_id_to_get = null ) {
    // If $order_id_to_get is provided, return the ID of the specified order
    if ( $order_id_to_get !== null ) {
        $order = wc_get_order( $order_id_to_get );
        if ( $order && has_sw_configured_products( $order ) ) {
            return $order_id_to_get;
        } else {
            return 0; // Return 0 to indicate that the specified order is not configured
        }
    }

    // Initialize an empty array to store order IDs
    $order_ids = array();

    // Query WooCommerce orders
    $orders = wc_get_orders( array(
        'limit' => -1,  // Retrieve all orders
    ));

    // Loop through the orders
    foreach ( $orders as $order ) {
        // Check if the order has configured products
        if ( $order && has_sw_configured_products( $order ) ) {
            $order_ids[] = $order->get_id();
        }
    }

    // If $order_id_to_get is not provided, return the array of order IDs
    return $order_ids;
}

/**
 * Check if an order has configured products.
 * @param WC_Order $order The WooCommerce order object.
 * @return bool True if the order has configured products, false otherwise.
 */
function has_sw_configured_products( $order ) {
    $items = $order->get_items();

    foreach ( $items as $item_id => $item ) {
        $service_name = wc_get_order_item_meta( $item_id, 'Service Name', true );
        if (!empty( $service_name ) ) {
            return true; // Configured product found
        }
    }

    return false; // No configured products found
}
 


// Main function to process and display the form based on the order ID
function sw_process_new_service_order_page() {

    // Get the order ID from the query parameter
    $order_id = isset( $_GET['order_id'] ) ? absint( $_GET['order_id'] ) : 0;

    // Check if the order ID is a configured order
    $is_configured_order = sw_get_orders_for_configured_products( $order_id );

    if ( $order_id > 0 && $order_id === $is_configured_order ) {
        // Render the form
        sw_render_new_service_order_form( $order_id );

        } else {
        echo '<div class="wrap"><p>This order is not a configured order</p></div>';
    }
}



/**
 * Create an Invoice for New configured order
 * @param object        The WooCommerce Order Object
 */
//Hook into action after the user checks out
add_action( 'woocommerce_checkout_order_created', 'sw_create_invoice_for_new_order', 10, 2 );

function sw_create_invoice_for_new_order( $order ) {

    //Check if the order is configured
    $is_configured_order = has_sw_configured_products( $order );

    // Check if the new order has configured products
    if ( $is_configured_order ) {
        // Get all fees associated with the order
        $fees = $order->get_fees();

        // Specify the fee name you are looking for
        $target_fee_name = 'Sign-up Fee';

        $fee = array_reduce( $fees, function ( $foundFee, $currentFee ) use ( $target_fee_name, $order ) {
            return $currentFee->get_name() === $target_fee_name ? $currentFee : $foundFee;
        }, 0);

        // Decode the JSON-encoded fee string
        $fee_data = json_decode( $fee, true );

        // Extract the fee amount
        $fee_amount = isset( $fee_data['total'] ) ? floatval( $fee_data['total'] ) : 0;

        // Get all items in the order
        $order_items = $order->get_items();
        // Extract the order ID 
        $order_id =  $order->get_id();
        
        foreach ( $order_items as $item_id => $item ) {
            // Check if the product is of type 'sw_product'
            $product = $item->get_product();
            if ( $product && $product->get_type() === 'sw_product' ) {

                /**
                * Set up the necessary properties for new invoice
                */

                $invoice_id          = sw_generate_invoice_id();
                $product_id          = $product->get_id();
                $amount              = $product->get_price();
                $total               = $amount + ( $fee_amount ?? 0 );
                $payment_status      = 'unpaid';
                $user_id             = $order->get_user_id();
                $billing_address     = sw_get_user_billing_address( $user_id );
                $service_id          = null;
                $invoice_type        = 'New Service Invoice';
                $service_id          = null; // Will be set when Service is processed
                $date_due            =  current_time('Y-m-d H:i:s'); // New Service invoices are due same day

            
                //generate an invoice for the order
                $newInvoice = new Sw_Invoice(
                    $invoice_id,
                    $product_id,
                    $amount,
                    $total,
                    $payment_status,
                    null, // Date Created will be set to the current date in the constructor
                    $user_id,
                    $billing_address,
                    $invoice_type,
                    $service_id,
                    $fee_amount,
                    $order_id
                );
                $newInvoice->setDateDue($date_due);

                // Call the sw_create_invoice method to save the invoice to the database
                $new_invoice_id = Sw_Invoice_Database::sw_create_invoice($newInvoice);


                if ( $new_invoice_id ){
                    $order->update_meta_data('Order Type', 'Invoice Payment');
                    $order->update_meta_data('Invoice ID', $new_invoice_id ); 

                    // Save the order to persist the changes
                    $order->save();
                }
            }
        }

    }
}



function sw_configure_page_template(){



}
