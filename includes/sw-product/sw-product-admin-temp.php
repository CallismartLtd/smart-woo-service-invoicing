<?php

/**
 * File name    :   sw-product-admin-temp.php
 * @author      :   Callistus
 * Description  :   Admin Template file
 */


 

// Function to display the form for adding a new product
function sw_render_new_product_form() {
    echo '<div class="wrap"><h2>Add New Service Product</h2>';
    echo '<a href="' . admin_url( 'admin.php?page=sw-products' ) . '" class="sw-blue-button">Products</a>';
    echo '<a href="' . admin_url( 'admin.php?page=send-mail-menu' ) . '" class="sw-blue-button">Send Mail</a>';
    echo '<a href="' . admin_url( 'admin.php?page=invoice-options' ) . '" class="sw-blue-button">Settings</a>';
    
    sw_handle_new_product_form();

    echo '<form method="post" action="" enctype="multipart/form-data" class="sw-product-form-class">';

    // Product Name
    echo '<label for="product_name" class="label-class">Product Name:</label>';
    echo '<input type="text" name="product_name" class="input-class" required>';
    echo '<p class="description-class">Enter the main name of the product.</p><br>';
    
    // Product Price
    echo '<label for="product_price" class="label-class">Product Price:</label>';
    echo '<input type="number" name="product_price" class="input-class" step="0.01" required>';
    echo '<p class="description-class">Enter the main price of the product.</p><br>';
    
    // Sign-Up Fee
    echo '<label for="sign_up_fee" class="label-class">Sign-Up Fee:</label>';
    echo '<input type="number" name="sign_up_fee" class="input-class" step="0.01">';
    echo '<p class="description-class">Enter the sign-up fee for the product (if applicable).</p><br>';
    
    // Short Description
    echo '<label for="short_description" class="label-class">Short Description:</label>';
    echo '<textarea name="short_description" class="textarea-class"></textarea>';
    echo '<p class="description-class">Enter a brief description of the product.</p><br>';
    
    // Billing Circle
    echo '<label for="billing_cycle" class="label-class">Billing Circle:</label>';
    echo '<select name="billing_cycle" class="select-class">
    <option value="" selected>Select Billing Cycle</option>
    <option value="Monthly">Monthly</option>
    <option value="Quarterly">Quarterly</option>
    <option value="Six Monthtly">Six Monthtly</option>
    <option value="Yearly">Yearly</option>
      </select>';
    echo '<p class="description-class">Select the billing circle for the product.</p><br>';

    
    // Grace Period
    echo '<label for="grace_period_number" class="grace-period-label">Grace Period:</label>';
    echo '<div class="grace-period-container">'; // Container for side-by-side elements
    echo '<p class="description-class">Expires after</p>';
    echo '<input type="number" name="grace_period_number" class="grace-period-number" min="1" >';
    echo '<select name="grace_period_unit" class="select-grace period-unit">
            <option value="">Select Grace Period</option>
            <option value="">No Grace Period</option>
            <option value="days">Days</option>
            <option value="weeks">Weeks</option>
            <option value="months">Months</option>
            <option value="years">Years</option>
        </select>';
    echo '</div>'; // Close the container
    echo '<p class="description-grace-period">Enter the time before the product expires. Use the number input to specify the duration, and select the unit (or choose "No Grace Period" if applicable).</p><br>';

    
    // Long Description
    echo '<label for="long_description" class="label-class">Long Description:</label>';
    echo '<textarea name="long_description" class="textarea-class"></textarea>';
    echo '<p class="description-class">Enter a detailed description of the product.</p><br>';
    
    // Product Image
    echo '<label for="product_image" class="label-class">Product Image:</label>';
    echo '<input type="button" id="upload_image_button" class="button-class" value="Upload Image">';
    echo '<input type="hidden" name="product_image_id" id="product_image_id" value="" class="input-class">';
    echo '<div id="image_preview" class="image-preview-class"></div>';
    echo '<p class="description-class">Select an image for the product from the WordPress Media Library.</p><br>';
    
    echo '<input type="submit" name="create_sw_product" value="Create Product" class="submit-button-class">';
    echo '</form></div>';
    
}


/**
 * Display the edit form for sw_service product.
 */
function display_edit_form() {
    // Get the product ID from the URL parameter
    $product_id = isset($_GET['product_id']) ? absint($_GET['product_id']) : 0;

    // Check if a valid product ID is provided
    if ($product_id) {
         // Handle form submission for updating the product
         if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_service_product'])) {
            // Update the product
            $updated = update_sw_service_product($product_id);

            // Display success or error message
            if ($updated) {
                echo '<div class="updated"><p>Product updated successfully!</p></div>';
            } else {
                echo '<div class="error"><p>Error updating the product. Please try again.</p></div>';
            }
        }
    // Get the product details
    $product_data = get_sw_service_product($product_id, 'name', 'price', 'sign_up_fee', 'short_description', 'billing_cycle', 'grace_period_number', 'grace_period_unit', 'long_description', 'product_image_id');

        // Check if the product details are available
        if ($product_data) {
            echo '<div class="wrap"><h2>Edit Service Product</h2>';

            echo '<a href="' . admin_url('admin.php?page=service-products&action=add-new') . '" class="sw-blue-button">Add Products</a>';
            echo '<a href="' . admin_url( 'admin.php?page=sw-products' ) . '" class="sw-blue-button">Products</a>';
            echo '<a href="' . admin_url('admin.php?page=send-mail-menu') . '" class="sw-blue-button">Send Mail</a>';
            echo '<a href="' . admin_url('admin.php?page=invoice-options') . '" class="sw-blue-button">Settings</a>';

            echo '<form method="post" action="" enctype="multipart/form-data">';

            // Product Name
            echo '<label for="product_name">Product Name:</label>';
            echo '<input type="text" name="product_name" class="input-class" value="' . esc_attr($product_data['name']) . '" required>';
            echo '<p class="description-class">Enter the main name of the product.</p><br>';

            // Product Price
            echo '<label for="product_price">Product Price:</label>';
            echo '<input type="number" name="product_price" step="0.01" class="input-class" value="' . esc_attr($product_data['price']) . '" required>';
            echo '<p class="description-class">Enter the main price of the product.</p><br>';

            // Sign-up Fee
            echo '<label for="sign_up_fee">Sign-up Fee:</label>';
            echo '<input type="number" name="sign_up_fee" step="0.01" class="input-class" value="' . esc_attr($product_data['sign_up_fee']) . '">';
            echo '<p class="description-class">Enter the sign-up fee for the product (if applicable).</p><br>';

            // Short Description
            echo '<label for="short_description">Short Description:</label>';
            echo '<textarea name="short_description" class="textarea-class">' . esc_textarea($product_data['short_description']) . '</textarea>';
            echo '<p class="description-class">Enter a brief description of the product.</p><br>';
           
            
            // Billing Circle
            echo '<label for="billing_cycle">Billing Circle:</label>';
            echo '<select name="billing_cycle" class="select-class"> 
                    <option value="" ' . selected('', $product_data['billing_cycle'], false) . '>Select Billing Cycle</option>
                    <option value="Monthly" ' . selected(in_array(strtolower($product_data['billing_cycle']), ['monthly', 'Monthly']), true, false) . '>Monthly</option>
                    <option value="Quarterly" ' . selected(in_array(strtolower($product_data['billing_cycle']), ['quarterly', 'Quarterly']), true, false) . '>Quarterly</option>
                    <option value="Six Monthtly" ' . selected(in_array(ucfirst($product_data['billing_cycle']), ['6_months', 'Six Months', 'Six Monthtly']), true, false) . '>Six Monthtly</option>
                    <option value="Yearly" ' . selected(in_array(strtolower($product_data['billing_cycle']), ['yearly', 'Yearly']), true, false) . '>Yearly</option>
                </select>';
            echo '<p class="description-class">Select the billing circle for the product.</p><br>';


           // Grace Period
            echo '<label for="grace_period_number" class="grace-period-label">Grace Period:</label>';
            echo '<div class="grace-period-container">'; // Container for side-by-side elements
            echo '<p class="description-class">Expires after</p>';
            echo '<input type="number" name="grace_period_number" class="grace-period-number input-class" min="1" value="' . esc_attr($product_data['grace_period_number']) . '">';
            echo '<select name="grace_period_unit" class="select-class">';
            echo '<option value="" ' . selected('', $product_data['grace_period_unit'], false) . '>No Grace Period</option>';
            echo '<option value="days" ' . selected('days', $product_data['grace_period_unit'], false) . '>Days</option>';
            echo '<option value="weeks" ' . selected('weeks', $product_data['grace_period_unit'], false) . '>Weeks</option>';
            echo '<option value="months" ' . selected('months', $product_data['grace_period_unit'], false) . '>Months</option>';
            echo '<option value="years" ' . selected('years', $product_data['grace_period_unit'], false) . '>Years</option>';
            echo '</select>';
            echo '</div>'; // Close the container
            echo '<p class="description-grace-period">Enter the time before the product expires. Use the number input to specify the duration, and select the unit (or choose "No Grace Period" if applicable).</p><br>';


            // Long Description
            echo '<label for="long_description">Long Description:</label>';
            echo '<textarea name="long_description" class="textarea-class">' . esc_textarea($product_data['long_description']) . '</textarea>';
            echo '<p class="description-class">Enter a detailed description of the product.</p><br>';

            // Product Image
            echo '<label for="product_image">Product Image:</label>';
            echo '<input type="button" id="upload_image_button" class="button-class" value="Upload Image">';
            echo '<input type="hidden" name="product_image_id" id="product_image_id" value="' . absint($product_data['product_image_id']) . '">';
            echo '<div id="image_preview"><img src="' . esc_url(wp_get_attachment_image_url($product_data['product_image_id'], 'thumbnail')) . '" style="max-width: 200px;"></div>';
            echo '<p class="description-class">Select an image for the product from the WordPress Media Library.</p><br>';

            echo '<input type="submit" name="update_service_product" class="submit-button-class" value="Update Product">';
            echo '</form></div>';
        } else {
            echo '<div class="error"><p>Error: Product not found or invalid product ID.</p></div>';
        }
    } else {
        echo '<div class="error"><p>Error: Invalid product ID.</p></div>';
    }
}



/**
 * Display a table of sw_service products.
 */
function display_product_details_table() {
    // Get all sw_service products
    $products_data = get_sw_service_product();

    // Check if there are any products
    if (!$products_data) {
        echo '<div class="wrap"><h2>Service Products</h2>';
        echo '<a href="' . admin_url('admin.php?page=sw-admin') . '" class="sw-blue-button">Dashboard</a>';
        echo '<a href="' . admin_url('admin.php?page=service-products&action=add-new') . '" class="sw-blue-button">Add Product</a>';
        echo '<a href="' . admin_url('admin.php?page=send-mail-menu') . '" class="sw-blue-button">Send Mail</a>';
        echo '<a href="' . admin_url('admin.php?page=invoice-options') . '" class="sw-blue-button">Settings</a>';
        echo '<div class="notice notice-info"><p>No sw_service products found.</p></div>';
        return;
    }

    // Display the product details table
    echo '<div class="wrap"><h2>Service Products</h2>';
    echo '<a href="' . admin_url('admin.php?page=sw-admin') . '" class="sw-blue-button">Dashboard</a>';
    echo '<a href="' . admin_url('admin.php?page=sw-products&action=add-new') . '" class="sw-blue-button">Add Product</a>';
    echo '<a href="' . admin_url('admin.php?page=sw-mail') . '" class="sw-blue-button">Send Mail</a>';
    echo '<a href="' . admin_url('admin.php?page=sw-options') . '" class="sw-blue-button">Settings</a>';
    echo '</div>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr>';
    echo '<th>Product</th>';
    echo '<th>Product Price</th>';
    echo '<th>Sign Up Fee</th>';
    echo '<th>Billing Circle</th>';
    echo '<th>Action</th>';
    echo '</tr></thead>';
    echo '<tbody>';

    foreach ($products_data as $product_id => $product_data) {
        echo '<tr>';
        echo '<td>' . esc_html($product_data['name']) . '</td>';
        echo '<td>' . wc_price($product_data['price']) . '</td>';
        echo '<td>' . wc_price($product_data['sign_up_fee']) . '</td>';
        echo '<td>' . esc_html($product_data['billing_cycle']) . '</td>';
        echo '<td>';
        echo '<a href="' . esc_url(admin_url('admin.php?page=sw-products&action=edit&product_id=' . $product_id)) . '" class="button">Edit</a>';
        echo '<button class="button" onclick="deleteProduct(' . esc_js($product_id) . ')">Delete</button>';
        echo '</td>';
        echo '</tr>';
    }

    echo '</tbody></table>';
    echo '<p style="text-align: right;">' . count($products_data) . ' items</p>';

    // Include JavaScript for handling product deletion via AJAX
    echo '<script>
        function deleteProduct(productId) {
            var confirmDelete = confirm("Are you sure you want to delete this product?");
            if (confirmDelete) {
                // Perform AJAX deletion
                var data = {
                    action: "delete_service_product",
                    security: "' . wp_create_nonce("delete_service_product_nonce") . '",
                    product_id: productId
                };

                jQuery.post(ajaxurl, data, function(response) {
                    if (response.success) {
                        alert("Product deleted successfully!");
                        location.reload(); // Reload the page after deletion
                    } else {
                        alert("Error deleting the product. Please try again.");
                    }
                });
            }
        }
    </script>';
}






// Function to render the new service order form
function sw_render_new_service_order_form($order_id) {
    // Get order details and user data
    $order = wc_get_order($order_id);
    $user_id = $order->get_user_id();
    $user_info = get_userdata($user_id);
    $user_full_name = $order ? $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() : 'Not Found';

    // Get order item meta data
    $items = $order->get_items();
    foreach ($items as $item_id => $item) {
        $service_name = wc_get_order_item_meta($item_id, 'Service Name', true);
        $service_url = wc_get_order_item_meta($item_id, 'Service URL', true);
    }

    // Get other form data if needed (e.g., billing_cycle, etc.)
    $start_date = $order->get_date_paid() ? date('Y-m-d', strtotime($order->get_date_paid())) : date('Y-m-d');

    $billing_cycle = '';
    $next_payment_date = '';
    $end_date = '';
    $status = 'Pending';

    // Check if there are items in the order
    $items = $order->get_items();
    if (!empty($items)) {
        // Retrieve billing_cycle from the product in the order
        $first_item = reset($items); // Get the first item
        $product_id = $first_item->get_product_id();

        // Fetch the billing cycle from product metadata
        $billing_cycle = get_post_meta($product_id, 'billing_cycle', true);

        // Set next payment date and end date based on billing cycle
        switch ($billing_cycle) {
            case 'Monthly':
                $end_date = date('Y-m-d', strtotime($start_date . ' +1 month'));
                $next_payment_date = date('Y-m-d', strtotime($end_date . ' -7 days'));
                break;
            case 'Quarterly':
                $end_date = date('Y-m-d', strtotime($start_date . ' +3 months'));
                $next_payment_date = date('Y-m-d', strtotime($end_date . ' -7 days'));
                break;
            case 'Six Monthly':
                $end_date = date('Y-m-d', strtotime($start_date . ' +6 months'));
                $next_payment_date = date('Y-m-d', strtotime($end_date . ' -7 days'));
                break;
            case 'Yearly':
                $end_date = date('Y-m-d', strtotime($start_date . ' +1 year'));
                $next_payment_date = date('Y-m-d', strtotime($end_date . ' -7 days'));
                break;
            // Add additional cases as needed
            default:
                // Handle unknown billing cycle
                break;
        }
    }

    // Display the form
    sw_render_new_service_order_form_html($user_id, $order_id, $service_name, $service_url, $user_full_name, $start_date, $billing_cycle, $next_payment_date, $end_date, $status);
}

// Function to render the HTML structure of the form
function sw_render_new_service_order_form_html($user_id, $order_id, $service_name, $service_url, $user_full_name, $start_date, $billing_cycle, $next_payment_date, $end_date, $status) {

    echo '<h1>Process New Service Order</h1>';
    echo '<p>After processing, this order will be marked as completed.</p>';


    echo '<div class="sw-form-container">';

    echo '<form method="post" action="">';

    // Add a nonce field for security
    wp_nonce_field( 'sw_process_new_service_nonce', 'sw_process_new_service_nonce' );

    // Add a hidden input for the action
    echo '<input type="hidden" name="action" value="sw_process_new_service">';



    $product_id = 0;
    $order = wc_get_order( $order_id );
    if ( !empty( $order ) ) {
        $items = $order->get_items();
        if (!empty($items)) {
            $first_item = reset( $items );
            $product_id = $first_item->get_product_id();
        }
    }
    $product_name = wc_get_product( $product_id )->get_name();
    echo '<div class="sw-form-row">'; 
    echo '<label for="order_id" class="sw-form-label">Order:</label>';
    echo '<span class="sw-field-description" title="The order ID and Product Name, to be used to set up new service subscription.">?</span>';
    echo '<input type="text" name="order_id" id="order_id" class="sw-form-input" value="' . esc_attr($order_id) .' - '. esc_html($product_name) . '" readonly>';
    echo '</div>';

    // Include the service ID generator input
    sw_render_service_id_generator_input( $service_name );
    echo '<input type="hidden" name="product_id" value="' . esc_attr( $product_id ) . '">';

    echo '<div class="sw-form-row">';
    echo '<label for="service_url" class="sw-form-label">Service URL:</label>';
    echo  '<span class="sw-field-description" title="Enter the service URL e.g., https:// (optional)">?</span>';
    echo '<input type="url" name="service_url" class="sw-form-input" id="service_url" value="' .esc_url( $service_url ) .'" >';
    echo '</div>';

    echo '<div class="sw-form-row">';
    echo '<label for="service_type" class="sw-form-label">Service Type</label>';
    echo '<span class="sw-field-description" title="Enter the service type (optional)">?</span>';
    echo '<input type="text" name="service_type" class="sw-form-input" id="service_type">';
    echo'</div>';

    echo '<div class="sw-form-row">';
    echo '<label for="user_id" class="sw-form-label">Client\'s Name </label>';
    echo '<span class="sw-field-description" title="The user whose ID is associated with the order">?</span>';
    echo '<input type="text"  class="sw-form-input" name="user_id" id="user_id" value="' . esc_attr( $user_full_name ) . '" readonly>';
    echo '</div>';

    echo '<input type="hidden" name="user_id" value="' . esc_attr( $user_id ) . '">';

    echo '<div class="sw-form-row">';
    echo '<label for="start_date" class="sw-form-label">Start Date:</label>';
    echo '<span class="sw-field-description" title="Choose the start date for the service subscription, service was ordered on this date.">?</span>';
    echo '<input type="date" name="start_date" class="sw-form-input" id="start_date" value="' . esc_attr($start_date).'" required>';
    echo '</div>';

    echo '<div class="sw-form-row">';
    echo '<label for="billing_cycle" class="sw-form-label">Billing Cycle</label>';
    echo '<span class="sw-field-description" title="This billing cycle was set from the product, you may edit it, invoices are created toward to the end of the billing cycle">?</span>';
    echo '<select name="billing_cycle" id="billing_cycle" class="sw-form-input" required>';
    echo '<option value="" selected disabled>Select billing cycle</option>';
    echo '<option value="Monthly" ' . selected('Monthly', $billing_cycle, false) . '>Monthly</option>';
    echo '<option value="Quarterly" ' . selected('Quarterly', $billing_cycle, false) . '>Quarterly</option>';
    echo '<option value="Six Monthly" ' . selected('Six Monthly', $billing_cycle, false) . '>6 Months</option>';
    echo '<option value="Yearly" ' . selected('Yearly', $billing_cycle, false) . '>Yearly</option>';
    echo '</select>';
    echo '</div>';

    echo '<div class="sw-form-row">';
    echo '<label for="next_payment_date" class="sw-form-label">Next Payment Date</label>';
    echo '<span class="sw-field-description" title="Choose the next payment date, services wil be due and invoice is created on this day.">?</span>';
    echo '<input type="date" class="sw-form-input" name="next_payment_date" id="next_payment_date" value="' . esc_attr( $next_payment_date ) . '" required>';
    echo '</div>';

    echo '<div class="sw-form-row">';
    echo '<label for="end_date" class="sw-form-label">End Date</label>';
    echo '<span class="sw-field-description" title="Choose the end date for the service. This service will expire on this day if the product does not have a grace period set up.">?</span>';
    echo '<input type="date" class="sw-form-input" name="end_date" id="end_date" value="' . esc_attr($end_date) . '" required>';
    echo '</div>';

    echo '<div class="sw-form-row">';
    echo '<label for="status" class="sw-form-label">Set Service Status:</label>';
    echo '<span class="sw-field-description" title="Set the status for the service. Status should be automatically calculated, choose another option to override the status. Please Note: invoice will be created if the status is set to Due for Renewal">?</span>';
    echo '<select name="status" class="sw-form-input" id="status">';
    $status_options = array(
        '' => 'Auto Calculate',
        'Pending' => 'Pending',
        'Active (NR)' => 'Active (NR)',
        'Suspended' => 'Suspended',
        'Due for Renewal' => 'Due for Renewal',
        'Expired' => 'Expired',
    );

    foreach ($status_options as $value => $label) {
        echo '<option value="' . esc_attr($value) . '" ' . selected($value, $status, false) . '>' . esc_html($label) . '</option>';
    }
    echo '</select>';
    echo '</div>';

    // Button to submit the form
    echo '<button type="submit" name="sw_process_new_service" class="sw-blue-button" id="create_new_service">Process</button>';

    echo '</form>';
    echo '</div>';
    sw_process_new_service();
}