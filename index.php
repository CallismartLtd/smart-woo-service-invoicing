<?php

//Must Silence be golden?

   /**      // Fetch the transaction_status and timestamp for $service_id
        $service_logs = sw_get_service_log(null, $service_id);

        if (!empty($service_logs)) {
            $log = $service_logs[0]; // Assuming a unique service ID
            $log_status = $log->transaction_status;            $amount = $log->amount;
            $currency_symbol = get_woocommerce_currency_symbol();
            $formatted_amount = $currency_symbol . number_format($amount, 2);
            $remark = $log->details;
            // Get the timestamp and format it
            $timestamp = strtotime($log->created_at);
            $current_time = time();
            $time_difference = $current_time - $timestamp;

            if ($time_difference < 3600) { // Less than 1 hour (3600 seconds)
                $minutes = floor($time_difference / 60);
                $seconds = $time_difference % 60;

                // Display the time difference in minutes and seconds
                $time_difference_formatted = "{$minutes}m {$seconds}s ago";
            } elseif ($time_difference < 172800) { // Less than 48 hours (48 hours = 2 days * 24 hours)
                $hours = floor($time_difference / 3600);
                $minutes = floor(($time_difference % 3600) / 60);

                // Display the time difference in hours and minutes
                $time_difference_formatted = "{$hours}h {$minutes}m ago";
            } else {
                // More than 48 hours, show formatted date and time
                $formatted_time = date('d/m/Y H:i', $timestamp);
                $time_difference_formatted = "on {$formatted_time}";
            }

            // Display the status and timestamp inside the existing div
            echo '<div class="service-log-container">';
            echo '<div class="user-refund-details-card">';
            echo '<p>Status: ' . $log_status . '</p>';
            echo '<p>Amount: ' . $formatted_amount . '</p>';
            echo '<p>Reference: ' . $remark . '</p>';
            echo '<p>Timestamp: ' . $time_difference_formatted . '</p>';
            echo '<p>Note: Scheduled refunds are automatically processed within 48hrs, you can take action with the buttons below</p>';
            echo '</div>';
            
            if ($log_status === 'Pending Refund') {
                // Add the button inside the same div, passing the service ID as a parameter
                echo '<form method="post" class="execute-refund-button">';
                echo '<input type="hidden" name="service_id" value="' . $service_id . '">';
                echo '<input type="submit" name="execute_refund" value="Refund Manually">';
                echo '</form>';
            }
            
            echo '</div>';
            echo '<br>';
        }
        // Process the button click action
        if (isset($_POST['execute_refund'])) {
            // Check if the "service_id" parameter is set
            if (isset($_POST['service_id'])) {
                $service_id_to_refund = sanitize_text_field($_POST['service_id']);
                
                // Call the process_pending_refund_services function with the specific service_id
                process_pending_refund_services($service_id_to_refund);
                
                // Redirect back to the current page or display a success message
                wp_redirect($_SERVER['REQUEST_URI']);
                exit;
            }
        }*/





/** 




                // Access properties of the $service object
                $product_id = $service->getProductId();
                // Fetch customer details
                $user_id = $service->getUserId();
                $user_info = get_userdata($user_id);
                $customer_name = 'Customer Name Not Found'; // Default value
                $customer_email = 'Email Not Found'; // Default value
                $customer_billing_address = 'Billing Address Not Found'; // Default value
                $customer_phone = 'Phone Number Not Found'; // Default value
        
                if ($user_info && is_object($user_info)) {
                    $customer_name = $user_info->first_name . ' ' . $user_info->last_name;
                    $customer_email = $user_info->user_email;
        
                    $customer_billing_address = sw_get_user_billing_address($user_id);
                    // Get user's phone number
                    $phone_number = get_user_meta($user_id, 'billing_phone', true);
        
                    if (!empty($phone_number)) {
                        $customer_phone = $phone_number;
                    }
                }
                $product_id = $service->getProductId();
        
                // Get product details from WooCommerce
                $product = wc_get_product($product_id);
            
                if ($product) {
                    // Access product properties
                    $product_name = $product->get_name();
                    $product_price = $product->get_price();
                    $product_amount = wc_price( $product_price );
                    $currency_symbol = get_woocommerce_currency_symbol();
                    // Concatenate the currency symbol with the product price
                    $price_with_currency = $currency_symbol . $product_price;
        
                }
        
                $main_service_status = sw_service_status( $service_id );
        
                // Display the customer details and the service details in separate containers
                echo '<div class="serv-wrap">';
                echo '<h1>Service Informations</h1>';
                echo '<div class="sw-button">';
                echo '<a href="javascript:history.go(-1);" class="sw-blue-button">Back</a>';
                echo '<a href="' . admin_url('admin.php?page=page=sw-admin&action=add-new-service') . '" class="sw-blue-button">Add New</a>';
                echo '<a href="' . admin_url('admin.php?page=send-mail-menu') . '" class="sw-blue-button">Send Mail</a>';
                echo '<a href="' . admin_url('admin.php?page=invoice-options') . '" class="sw-blue-button">Settings</a>';
                echo '</div>';
                    

            
                        
*/





function sw_handle_email_options() {
    if (isset($_POST['sw_save_email_options'])) {

        // Update billing email
        if (isset($_POST['sw_billing_email'])) {
            update_option('sw_billing_email', sanitize_email($_POST['sw_billing_email']));
        }

        // Update sender name
        if (isset($_POST['sw_sender_name'])) {
            update_option('sw_sender_name', sanitize_text_field($_POST['sw_sender_name']));
        }

        // Define an array of checkbox names
        $checkboxes = array(
            'sw_cancellation_mail_to_user',
            'sw_service_opt_out_mail',
            'sw_payment_reminder_to_client',
            'sw_service_expiration_mail',
            'sw_new_invoice_mail',
            'sw_send_renewal_mail',
            'sw_reactivation_mail',
            'sw_invoice_paid_mail',
            'sw_service_cancellation_mail_to_admin',
            'sw_service_expiration_mail_to_admin',
        );

        // Update checkbox options
        foreach ($checkboxes as $checkbox_name) {
            if (isset($_POST[$checkbox_name])) {
                update_option($checkbox_name, 1); // Use 1 to represent checked
            } else {
                update_option($checkbox_name, 0); // Use 0 to represent unchecked
            }
        }
        echo '<div class="updated notice updated is-dismissible"><p>Settings saved!</p></div>';

    }
}






function sw_handle_options_submission() {
    // Handle form submission for all settings
    if ( isset( $_POST['sw_save_options'] ) ) {
        // Handle form submission for existing settings
        if ( isset( $_POST['sw_invoice_page'] ) ) {
            update_option( 'sw_invoice_page', intval( $_POST['sw_invoice_page'] ) );
        }

        if ( isset( $_POST['sw_invoice_logo_url'] ) ) {
            update_option( 'sw_invoice_logo_url', sanitize_text_field( $_POST['sw_invoice_logo_url'] ) );
        }

        if ( isset( $_POST['sw_invoice_watermark_url'] ) ) {
            update_option( 'sw_invoice_watermark_url', sanitize_text_field( $_POST['sw_invoice_watermark_url'] ) );
        }

        if ( isset( $_POST['sw_business_name'] ) ) {
            $business_name = sanitize_text_field($_POST['sw_business_name']) ? sanitize_text_field($_POST['sw_business_name']) : get_bloginfo( 'name' );
            update_option('sw_business_name', $business_name );
        }

        if ( isset( $_POST['sw_admin_phone_numbers'] ) ) {
            update_option( 'sw_admin_phone_numbers', sanitize_text_field( $_POST['sw_admin_phone_numbers'] ) );
        }


        if ( isset( $_POST['sw_service_page'] ) ) {
            update_option( 'sw_service_page', intval( $_POST['sw_service_page'] ) );
        }


        if ( isset( $_POST['sw_prorate'] ) ) {
            $sw_prorate_value = sanitize_text_field( $_POST['sw_prorate'] );
            update_option( 'sw_prorate', $sw_prorate_value );
        }

        if ( isset( $_POST['sw_invoice_id_prefix'] ) ) {
            $invoice_number_prefix = preg_replace( '/[^a-zA-Z0-9]/', '', $_POST['sw_invoice_id_prefix'] );
            update_option('sw_invoice_id_prefix', $invoice_number_prefix);
        }

        if ( isset( $_POST['sw_service_id_prefix'] ) ) {
            $service_id_prefix = preg_replace('/[^a-zA-Z0-9]/', '', $_POST['sw_service_id_prefix'] );
            update_option( 'sw_service_id_prefix', $service_id_prefix );
        }
        

        // Handle form submission for existing settings
        if ( isset( $_POST['sw_allow_migration'] ) ) {
            $sw_allow_migration = sanitize_text_field( $_POST['sw_allow_migration'] );
            update_option( 'sw_allow_migration', $sw_allow_migration );
        }
       // Handle form submission for existing settings
        if ( isset( $_POST['sw_upgrade_product_cat'] ) ) {
            $selected_upgrade_category = sanitize_text_field( $_POST['sw_upgrade_product_cat'] );
            update_option('sw_upgrade_product_cat', $selected_upgrade_category);
        }

        if (isset($_POST['sw_downgrade_product_cat'])) {
            $selected_downgrade_category = sanitize_text_field( $_POST['sw_downgrade_product_cat'] );
            update_option( 'sw_downgrade_product_cat', $selected_downgrade_category );
        }



    
        echo '<div class="updated notice updated is-dismissible"><p>Settings saved!</p></div>';
    }
}





/** 
    $invoice_logo_url = get_option('sw_invoice_logo_url', '');
    $invoice_watermark_url = get_option('sw_invoice_watermark_url', '');
    $billing_email = get_option('sw_billing_email', '');
    $sender_name = get_option('sw_sender_name', '');


    ?>
    <div class="inv-settings">
        <h2 class="smart-invoice-heading">Smart Invoice Settings</h2>

    <a href="<?php echo admin_url('admin.php?page=sw-admin'); ?>" class="sw-blue-button">Dashboard </a>
    <a href="<?php echo admin_url( 'admin.php?page=sw-invoices')?>" class="sw-blue-button">Invoices</a>
    <a href="<?php echo admin_url('admin.php?page=sw-mail'); ?>" class="sw-blue-button">Send Mail</a>
    <a href="<?php echo admin_url('admin.php?page=sw-admin&action=add-new-service'); ?>" class="sw-blue-button">Add New</a>

<form method="post" class="inv-settings-form">

<!-- Save Settings Button -->
<div class="form-group">
        <input type="submit" class="inv-sw-blue-button" name="save_settings" value="Save Settings">
    </div>

    <!-- Select the page for invoice preview -->
    <div class="form-group">
        <label for="invoice_preview_page">Select the page for invoice preview:</label>
        <select name="invoice_preview_page" id="invoice_preview_page" class="inv-select">
            <option value="0">Select a page</option>
            <?php
            foreach ($pages as $page) {
                $selected = ($invoice_preview_page == $page->ID) ? 'selected' : '';
                echo '<option value="' . $page->ID . '" ' . $selected . '>' . $page->post_title . '</option>';
            }
            ?>
        </select>
        <p> This is the page where users can view and download invoices. Selected page must have <br> [preview_invoices] shortcode</p>
    </div>






    <!-- Business Name -->
    <div class="form-group">
        <label for="business_name">Business Name:</label>
        <input type="text" name="business_name" id="business_name" value="<?php echo esc_attr($business_name); ?>" placeholder="Enter business name" class="inv-text">
        <p><em>All invoices are issued on this business name</em></p>
    </div>

    <!-- Admin Phone Numbers -->
    <div class="form-group">
        <label for="admin_phone_numbers">Admin Phone Numbers (separated by commas):</label>
        <input type="text" name="admin_phone_numbers" id="admin_phone_numbers" value="<?php echo esc_attr($admin_phone_numbers); ?>" placeholder="Enter admin phone numbers" class="inv-text">
        <p><em>Enter admin phone numbers separated by commas (e.g., +123456789, +987654321).</em></p>
    </div>

    <!-- Select the service page -->
    <div class="form-group">
        <label for="service_page">Select the service page:</label>
        <select name="service_page" id="service_page" class="inv-select">
            <option value="0">Select a page</option>
            <?php
            foreach ($pages as $page) {
                $selected = ($service_page == $page->ID) ? 'selected' : '';
                echo '<option value="' . $page->ID . '" ' . $selected . '>' . $page->post_title . '</option>';
            }
            ?>
        </select>
        <p><em>you may want to choose woocommerce my-account page, select any other page if you want to use a different page. <br> 
    Any other page should have this shortcode [client_services] </em></p>

    </div>

  

    <!-- Form field for service_id_prefix -->
    <div class="form-group">
            <label for="service_id_prefix">Service ID Prefix:</label>
            <input type="text" name="service_id_prefix" id="service_id_prefix" value="<?php echo esc_attr($service_id_prefix); ?>" placeholder="Enter prefix (numbers and text only)">
            <p><em>Enter the prefix for service IDs (numbers and text only, no special characters).</em></p>
    </div>
  


     <!-- Form field for invoice_number_prefix -->
     <div class="form-group">
            <label for="invoice_number_prefix">Invoice Number Prefix:</label>
            <input type="text" name="invoice_number_prefix" id="invoice_number_prefix" value="<?php echo esc_attr($invoice_number_prefix); ?>" placeholder="Enter prefix (numbers and text only)">
            <p><em>Enter the prefix for invoice numbers (numbers and text only, no special characters).</em></p>
        </div>

    <!-- Form field for sw_prorate -->
    <div class="form-group">
        <label for="sw_prorate">Prorate Service:</label>
        <select name="sw_prorate" id="sw_prorate" class="inv-select">
            <option value="Select option" <?php selected($sw_prorate, 'Select option'); ?>>Select option</option>
            <option value="Enable" <?php selected($sw_prorate, 'Enable'); ?>>Enable</option>
            <option value="Disable" <?php selected($sw_prorate, 'Disable'); ?>>Disable</option>
        </select>
        <p><em>Select enable to prorate refunds, if enabled, all cancelled services will be automatically refunded within 48hr or more, <br>can always check the status of services and do manual refunds</em></p>
    </div>

    <!-- Form field for allow_service_upgrade -->
    <div class="form-group">
        <label for="allow_service_upgrade">Allow Service Upgrade:</label>
        <select name="allow_service_upgrade" id="allow_service_upgrade" class="inv-select">
            <option value="Enable" <?php selected(get_option('sw_allow_service_upgrade', 'Disable'), 'Enable'); ?>>Enable</option>
            <option value="Disable" <?php selected(get_option('sw_allow_service_upgrade', 'Disable'), 'Disable'); ?>>Disable</option>
        </select>
        <p><em>Select enable to allow service upgrades.</em></p>
    </div>


        <!-- Logo URL -->
        <div class="form-group">
        <label for="invoice_logo_url">Upload Logo for Invoice:</label>
        <input type="text" name="invoice_logo_url" id="invoice_logo_url" value="<?php echo esc_attr($invoice_logo_url); ?>" placeholder="Enter logo URL" class="inv-text">
        <p><em>Enter the URL of the logo image you want to use in the invoice.</em></p>
    </div>

    <!-- Watermark URL -->
    <div class="form-group">
        <label for="invoice_watermark_url">Upload Watermark for Invoice:</label>
        <input type="text" name "invoice_watermark_url" id="invoice_watermark_url" value="<?php echo esc_attr($invoice_watermark_url); ?>" placeholder="Enter watermark URL" class="inv-text">
        <p><em>Enter the URL of the watermark image you want to use in the invoice.</em></p>
    </div>

    <!-- Save Settings Button -->
    <div class="form-group">
        <input type="submit" class="inv-sw-blue-button" name="save_settings" value="Save Settings">
    </div>
</form>


    <?php

        }*/