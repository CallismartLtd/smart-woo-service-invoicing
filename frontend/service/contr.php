<?php
/**
 * File name    :   contr.php
 * @author      :   Callistus
 * Description  :   Control File for service frontend
 */

 if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This line handles the client_services shortcode and is 
 * used to determine which url parameters are allowed
 * in the client service page
 */


// Define the shortcode function
function sw_service_shortcode() {
    // Check if the user is not logged in
    if (!is_user_logged_in()) {
        return 'You must be logged in to view this page.';
    }
    // Start output buffering
    ob_start();

    $current_user_id = get_current_user_id();
    $current_user = wp_get_current_user();
    $currentuseremail = $current_user->user_email;

    // Get and sanitize the 'service_page' parameter
    $url_param = isset($_GET['service_page']) ? sanitize_key($_GET['service_page']) : '';

    // Validate the 'service_page' parameter against a list of allowed values
    $allowed_actions = array('service_details', 'service_downgrade', 'active', 'renewal_due', 'expired', 'grace_period', 'service_upgrade', 'service_downgrade', 'buy_new_service');

    // If 'service_page' is set and not empty, validate against allowed actions
    if ($url_param !== '' && !in_array($url_param, $allowed_actions)) {
        echo 'Invalid action type.';
    } else {
        // Switch based on the validated 'service_page' parameter
        switch ($url_param) {
            case 'service_details':
                echo handle_service_details($current_user_id, $currentuseremail);
                break;
            case 'service_upgrade':
                echo handle_upgrade_service($current_user_id);
                break;
            case 'service_downgrade':
                echo handle_downgrade_service($current_user_id);
                break;
            case 'buy_new_service':
                echo handle_buy_new_service($current_user_id);
                break;
            case 'active':
                echo handle_service_by_status($current_user_id, 'Active');
                break;
            case 'renewal_due':
                echo handle_service_by_status($current_user_id, 'Due for Renewal');
                break;
            case 'expired':
                echo handle_service_by_status($current_user_id, 'Expired');
                break;
            case 'grace_period':
                echo handle_service_by_status($current_user_id, 'Grace Period');
                break;
            default:
                echo handle_main_page($current_user_id);
                break;
        }
    }

    // Get the buffered output
    $output = ob_get_clean();

    // Return the output
    return $output;
}




/**
 * Count the number of 'Active' services for a specific user or all users.
 *
 * @param int|null $user_id The user ID (optional).
 * @return int The number of 'Active' services.
 */
function count_active_services($user_id = null) {
    // Get services based on the provided user ID or all users
    $services = ($user_id !== null) ? sw_get_service($user_id) : sw_get_service();

    // Initialize the count of 'Active' services
    $active_services_count = 0;

    // Loop through each service to check its status
    foreach ($services as $service) {
        // Get the status of the current service using user_id and service_id
        $status = sw_service_status( $service->service_id );

        // Check if the status is 'Active'
        if ($status === 'Active') {
            // Increment the count for 'Active' services
            $active_services_count++;
        }
    }

    // Return the count of 'Active' services
    return $active_services_count;
}




/**
 * Count the number of 'Due for Renewal' services for a specific user or all users.
 *
 * @param int|null $user_id The user ID (optional).
 * @return int The number of 'Due for Renewal' services.
 */
function count_due_for_renewal_services($user_id = null) {
    // Get services based on the provided user ID or all users
    $services = ($user_id !== null) ? sw_get_service($user_id) : sw_get_service();

    // Initialize the count of 'Active' services
    $due_services_count = 0;

    // Loop through each service to check its status
    foreach ($services as $service) {
        // Get the status of the current service using user_id and service_id
        $status = sw_service_status( $service->service_id );

        // Check if the status is 'Due for Renewal'
        if ($status === 'Due for Renewal') {
            // Increment the count for 'Active' services
            $due_services_count++;
        }
    }

    // Return the count of 'Due' services
    return $due_services_count;
}




/**
 * Count the number of 'Active No Renewal' services for a specific user or all users.
 *
 * @param int|null $user_id The user ID (optional).
 * @return int The number of 'Active (NR)' services.
 */
function count_nr_services($user_id = null) {
    // Get services based on the provided user ID or all users
    $services = ($user_id !== null) ? sw_get_service($user_id) : sw_get_service();

    // Initialize the count of 'Active (NR)' services
    $nr_services_count = 0;

    // Loop through each service to check its status
    foreach ($services as $service) {
        // Get the status of the current service using user_id and service_id
        $status = sw_service_status( $service->service_id );

        // Check if the status is 'Active (NR)'
        if ($status === 'Active (NR)') {
            // Increment the count for 'Active' services
            $nr_services_count++;
        }
    }

    // Return the count of 'Active' services
    return $nr_services_count;
}





/**
 * Count the number of 'Expired' services for a specific user or all users.
 *
 * @param int|null $user_id The user ID (optional).
 * @return int The number of 'Expired' services.
 */
function count_expired_services($user_id = null) {
    // Get services based on the provided user ID or all users
    $services = ($user_id !== null) ? sw_get_service($user_id) : sw_get_service();

    // Initialize the count of 'Active' services
    $expired_services_count = 0;

    // Loop through each service to check its status
    foreach ($services as $service) {
        // Get the status of the current service using user_id and service_id
        $status = sw_service_status( $service->service_id );

        // Check if the status is 'Expired'
        if ($status === 'Expired') {
            // Increment the count for 'Expired' services
            $expired_services_count++;
        }
    }

    // Return the count of 'Active' services
    return $expired_services_count;
}








/**
 * Count the number of 'Grace Period' services for a specific user or all users.
 *
 * @param int|null $user_id The user ID (optional).
 * @return int The number of 'Grace Period' services.
 */
function count_grace_period_services($user_id = null) {
    // Get services based on the provided user ID or all users
    $services = ($user_id !== null) ? sw_get_service($user_id) : sw_get_service();

    // Initialize the count of 'Active' services
    $grace_period_services_count = 0;

    // Loop through each service to check its status
    foreach ($services as $service) {
        // Get the status of the current service using user_id and service_id
        $status = sw_service_status( $service->service_id );

        // Check if the status is 'Grace Period'
        if ($status === 'Grace Period') {
            // Increment the count for 'Grace Period' services
            $grace_period_services_count++;
        }
    }

    // Return the count of 'Active' services
    return $grace_period_services_count;
}

/**
 * Count the number of 'Suspended' services for a specific user or all users.
 *
 * @param int|null $user_id The user ID (optional).
 * @return int The number of 'Suspended' services.
 */
function count_suspended_services($user_id = null) {
    // Get services based on the provided user ID or all users
    $services = ($user_id !== null) ? sw_get_service($user_id) : sw_get_service();

    // Initialize the count of 'Active' services
    $suspended_services = 0;

    // Loop through each service to check its status
    foreach ($services as $service) {
        // Get the status of the current service using user_id and service_id
        $status = sw_service_status( $service->service_id );

        // Check if the status is 'Grace Period'
        if ($status === 'Suspended') {
            // Increment the count for 'Grace Period' services
            $suspended_services++;
        }
    }

    // Return the count of 'Active' services
    return $suspended_services;
}






/**
 * The set of AJAX callback function below handles the output of the
 * buttons in the settings and tools section of the client service page
 */



// AJAX handler for billing details
add_action('wp_ajax_load_billing_details', 'load_billing_details_callback');
function load_billing_details_callback() {
    // Check if the user is logged in
    if (is_user_logged_in()) {
        // Get the current user ID
        $user_id = get_current_user_id();

        
        // Get additional customer details
        $billingFirstName = get_user_meta($user_id, 'billing_first_name', true);
        $billingLastName = get_user_meta($user_id, 'billing_last_name', true);
        $company_name = get_user_meta($user_id, 'billing_company', true);
        $email = get_user_meta($user_id, 'billing_email', true);
        $phone = get_user_meta($user_id, 'billing_phone', true);
        $website = get_user_meta($user_id, 'billing_website', true);
        $nationality = get_user_meta($user_id, 'billing_country', true);

         // Get the user's billing information using WooCommerce functions
        $billingAddress = sw_get_user_billing_address($user_id);
        
        // Construct the HTML for billing details
        $html = '<div class="billing-details-container">';
        $html .= '<h3>Billing Details</h3>';
        $html .= '<p><strong>Name:</strong> ' . esc_html($billingFirstName . ' ' . $billingLastName) . '</p>';
        $html .= '<p><strong>Company Name:</strong> ' . esc_html($company_name) . '</p>';
        $html .= '<p><strong>Email Address:</strong> ' . esc_html($email) . '</p>';
        $html .= '<p><strong>Phone:</strong> ' . esc_html($phone) . '</p>';
        $html .= '<p><strong>Website:</strong> ' . esc_html($website) . '</p>';
        $html .= '<p><strong>Address:</strong> ' . esc_html($billingAddress) .'</p>';
        $html .= '<p><strong>Nationality:</strong> ' . esc_html($nationality) . '</p>';
        $html .= '<button class="account-button" onclick="confirmEditBilling()">Edit My Billing Address</button>';
        $html .= '</div>';
        
        // Send the HTML response
        echo $html;
    } else {
        // User is not logged in, handle accordingly
        echo 'User not logged in';
    }

    // Always die in functions echoing AJAX content
    die();
}




add_action('wp_ajax_load_my_details', 'load_my_details_callback');
function load_my_details_callback() {
    // Check if the user is logged in
    if (is_user_logged_in()) {
        // Get the current user object
        $current_user = wp_get_current_user();

        // Get user details
        $full_name = esc_html($current_user->display_name);
        $email = esc_html($current_user->user_email);
        $bio = esc_html($current_user->description);
        $user_role = esc_html(implode(', ', $current_user->roles));

        // Construct the HTML for user details
        $html = '<div class="user-details-container">';
        $html .= '<h3>My Details</h3>';
        $html .= '<div class="user-details">';
        $html .= '<p><strong>Full Name:</strong> ' . $full_name . '</p>';
        $html .= '<p><strong>Email:</strong> ' . $email . '</p>';
        $html .= '<p><strong>Bio:</strong> ' . $bio . '</p>';
        $html .= '<p><strong>Account type:</strong> ' . $user_role . '</p>';
        $html .= '</div>';
        $html .= '<button class="account-button" onclick="confirmEditAccount()">Edit My Information</button>';
        $html .= '<button class="account-button" onclick="confirmPaymentMethods()">Payment Methods</button>';
        $html .= '</div>';
        // Send the HTML response
        echo $html;
    } else {
        // User is not logged in, handle accordingly
        echo 'User not logged in';
    }

    // Always die in functions echoing AJAX content
    die();
}






add_action('wp_ajax_load_account_logs', 'load_account_logs_callback');
function load_account_logs_callback() {
    // Check if the user is logged in
    if (is_user_logged_in()) {
        // Get the current user object
        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;

        // Retrieve last active time from wp_wc_customer_lookup table
        global $wpdb;
        $last_active_query = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT date_last_active FROM {$wpdb->prefix}wc_customer_lookup WHERE user_id = %d",
                $user_id
            )
        );

        if ($last_active_query) {
            $last_active = strtotime($last_active_query->date_last_active);

            // Retrieve user registration date from the database
            $registration_date = $wpdb->get_var($wpdb->prepare(
                "SELECT user_registered FROM $wpdb->users WHERE ID = %d",
                $user_id
            ));

            // Start constructing the HTML for account logs
            $html = '<div class="account-logs-container">';
            $html .= '<h3>Account Logs</h3>';
            $html .= '<ul class="account-logs-list">';
            
            // Total service renewed
            $renewed_services_count = get_renewed_services_count($user_id);
            $html .= '<li class="account-log-item"> Total Renewed Services: ' . $renewed_services_count . '</li>';



            // Display Total Amount Spent first
            $total_spent = get_total_spent_by_user($user_id);
            $html .= '<li class="account-log-item">Total Amount Spent: ' . $total_spent . '</li>';
            
            // Display Last Active Time
            $html .= '<li class="account-log-item">Last Active Time: ' . date('F j, Y g:i a', $last_active) . '</li>';
            
            // Display user registration date
            $html .= '<li class="account-log-item">Registration Date: ' . date('F j, Y g:i a', strtotime($registration_date)) . '</li>';
            
            
            // Display IP Address
            $html .= '<li class="account-log-item">IP Address: ' . $_SERVER['REMOTE_ADDR'] . '</li>';
            
            // Retrieve and display the user's location using ip-api.com
            $ip_address = $_SERVER['REMOTE_ADDR'];
            $location_info = file_get_contents('http://ip-api.com/json/' . $ip_address);
            $location_data = json_decode($location_info);
            
            if ($location_data && $location_data->status === 'success') {
                $user_location = $location_data->city . ', ' . $location_data->country;
                $html .= '<li class="account-log-item">Location: ' . $user_location . '</li>';
            } else {
                $html .= '<li class="account-log-item">Location: Unknown</li>';
            }


            $html .= '</ul>';
            $html .= '</div>';
            

            // Send the HTML response
            echo $html;
        } else {
            // No activity information found
            echo '<p>No activity information found.</p>';
        }
    } else {
        // User is not logged in
        echo '<p>Please log in to view user activity information.</p>';
    }

    // Always die in functions echoing AJAX content
    die();
}
function get_total_spent_by_user($user_id) {
    $customer_orders = wc_get_orders(array(
        'customer_id' => $user_id,
        'status' => array('completed', 'processing') // Include processing orders
    ));

    $total_spent = 0;

    foreach ($customer_orders as $order) {
        $total_spent += $order->get_total();
    }

    return wc_price($total_spent);
}




// Add this in your plugin file or functions.php

add_action('wp_ajax_load_transaction_history', 'load_transaction_history_callback');
function load_transaction_history_callback() {
    // Check if the user is logged in
    if (is_user_logged_in()) {
        // Get the current user object
        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;

        // Start constructing the HTML for transaction history
        $html = '<h3>Transaction History</h3>';

        // Manually process and include the [transactions] shortcode
        ob_start();
        echo do_shortcode('[transactions]');
        $transactions_content = ob_get_clean();

        // Include the transactions content in the HTML response
        $html .= $transactions_content;

        // Send the HTML response
        echo $html;
    } else {
        // User is not logged in
        echo '<p>Please log in to view transaction history.</p>';
    }

    // Always die in functions echoing AJAX content
    die();
}









