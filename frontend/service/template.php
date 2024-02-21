<?php

/**
 * File name    :   Template.php
 * @author      :   Callistus
 * Description  :   View File for frontend service
 */
defined ( 'ABSPATH' ) || exit;


/**
 * Handles service details page
 *
 * @param int    $current_user_id    Current user ID.
 * @param string $current_user_email Current user email.
 *
 * @return string Error message or service details.
 */
function sw_handle_service_details( $current_user_id, $current_user_email ) {
    // Check if the 'service_page' parameter is in the URL
    if ( ! isset( $_GET['service_page'] ) ) {
        return esc_html( 'Invalid request. Service page is missing.' );
    }

    // Check if the 'service_id' parameter is in the URL
    if ( ! isset( $_GET['service_id'] ) ) {
        return esc_html( 'Invalid request. Service ID is missing.' );
    }

    // Sanitize and validate the 'service_page' and 'service_id' parameters
    $service_page = sanitize_text_field( $_GET['service_page'] );
    $service_id   = sanitize_text_field( $_GET['service_id'] );

    // Validate the 'service_page' parameter against a list of allowed values
    $allowed_actions = array( 'service_details', 'another_action', 'active', 'renewal_due', 'expired', 'grace_period' );
    if ( ! in_array( $service_page, $allowed_actions, true ) ) {

        return esc_html( 'Invalid action.' );
    }

    // Service ID is provided in the URL, fetch and display service details
    $url_service_id = sanitize_text_field( $_GET['service_id'] );
    if ( empty( $url_service_id ) ) {
        return 'Invalid Service ID';
    }
    $service    = Sw_Service_Database::get_service_by_id( $url_service_id );

    if ( $service && $service->getUserId() !== $current_user_id || !$service ) {
        return 'Service Not Found';
    }

        // Display service details as a card
        $service_name   = esc_html( $service->getServiceName() ? $service->getServiceName() :'Not Available');
        $service_id     = esc_html( $service->getServiceId() ? $service->getServiceId() :'Not Available');
        $product_id     = esc_html( $service->getProductId() );
        $service_type   = esc_html( $service->getServicetype() ? $service->getServiceType() :'Not Available');

        $product_info   = wc_get_product( $product_id );
        $product_name   = $product_info ? $product_info->get_name() : 'Product Not Found';
        $product_price  = $product_info ? $product_info->get_price() : 0;

        $billing_cycle      = esc_html( $service->getBillingCycle() ? $service->getBillingCycle() :'Not Available' );
        $start_date         = date( 'l, F jS Y', strtotime( esc_html( $service->getStartDate() ? $service->getStartDate() :'Not Available')) );
        $next_payment_date  = date( 'l, F jS Y', strtotime( esc_html( $service->getNextPaymentDate() ? $service->getNextPaymentDate() :'Not Available')) );
        $end_date           = date('l, F jS Y', strtotime(esc_html($service->getEndDate() ? $service->getEndDate() :'Not Available')) );

        $service_url = esc_url($service->getServiceUrl() ? $service->getServiceUrl() : 'Not Available'); 
        $service_button = sw_client_service_url_button( $service );

        // Use sw_service_status to get the service status
        $status = sw_service_status( $service_id );
        $usage_metrics = sw_get_usage_metrics( $service_id );
        $expiry_date   = sw_get_service_expiration_date( $service );

        if ( $expiry_date === sw_extract_date_only( current_time( 'mysql' ) ) ) {
            echo sw_notice('Expiring Today');
        } elseif ( $expiry_date ===  date( 'Y-m-d', strtotime('+1 day') ) ) {
            echo sw_notice('Expiring Tomorrow');
        }elseif ( $expiry_date ===  date( 'Y-m-d', strtotime('-1 day') ) ) {
            echo sw_notice('Expired Yesterday');
        }
        // Add the status tag to the service name

        $service_name_with_status = $service_name . ' (' . $status . ')';
        sw_get_navbar($current_user_id);

        // Add the heading
        $output = '<h3 style="text-align: center;">' . $service_name_with_status . '</h3>';
        if ( $status === 'Active' ){
        $output .= ''. $usage_metrics .'';
        }
        $output .= '<div class="serv-details-card">';
        $output .= '<p>Service ID: ' . $service_id . '</p>';
        $output .= '<p>Service Type: ' . $service_type . '</p>';
        $output .= '<p>Product Name: ' . $product_name . '</p>';
        $output .= '<p>Amount: ' . $product_price . '</p>';
        $output .= '<p>Billing Cycle: ' . $billing_cycle . '</p>';
        $output .= '<p>Start Date: ' . $start_date . '</p>';
        $output .= '<p>Next Payment Date: ' . $next_payment_date . '</p>';
        $output .= '<p>End Date: ' . $end_date . '</p>';
        $output .= '<p>Expiry Date: ' . sw_check_and_format( $expiry_date ) . '</p>';
        $output .= '</div>';



    // Container for buttons
    $output .= '<div class="inv-button-container" style="text-align: center;">';

    // "Back to Services" button
    $output .= '<a href="' . get_permalink() . '" class="back-button">Back to Services</a>';

    $renew_button_text = ( $status === 'Due for Renewal' || $status === 'Grace Period' ) ? 'Renew':'Reactivate';
   
    // "Renew" button when the service is due for renewal or expired
    if ( $status === 'Due for Renewal' || $status === 'Expired' || $status === 'Grace Period' ) {
        // Generate a nonce for the renew action
        $renew_nonce = wp_create_nonce( 'renew_service_nonce' );

        // Add the nonce to the URL
        $renew_link = esc_url( wp_nonce_url( add_query_arg( array( 'service_id' => $service_id, 'action' => 'renew-service' ), get_permalink() ), 'renew_service_nonce', 'renew_nonce' ) );

        // Output the "Renew" button with the nonce
        $output .= '<a href="' . $renew_link . '" class="renew-button">'. $renew_button_text . '</a>';
    }

    // "Quick Action" button when the service status is 'Active' 
    if ($status === 'Active') {
        $current_url = esc_url(add_query_arg(array('action' => 'cancel')));
        $cancel_service_link = esc_url(add_query_arg(array('action' => 'cancel'), get_permalink()));
        $output .= '<a href="#" class="sw-red-button" onclick="return openCancelServiceDialog(\'' . $service_name . '\');">Quick Action</a>';
    }
    $output .= '' . $service_button . '';


        $output .= '</div>';

        return $output;
}


/**
 * Handles the main service page
 * @param int $current_user_id The current user's id
 */


function sw_handle_main_page( $current_user_id ) {
    
    // Output the service navigation bar
    sw_get_navbar( $current_user_id );


    // Output the full name of the current user
    $current_user = wp_get_current_user();
    $full_name = esc_html( $current_user->display_name );
    echo '<p style="text-align: center; margin-top: 10px;">Welcome, ' . $full_name . '!</p>';

    $user_id = get_current_user_id();

    $active_count = count_active_services( $user_id );
    $due_for_renewal_count = count_due_for_renewal_services( $user_id );
    $expired_count = count_expired_services( $user_id );
    $grace_period_count = count_grace_period_services( $user_id );
    
   // Get and sanitize the 'service_page' parameter
    $url_param = isset( $_GET['service_page'] ) ? sanitize_key( $_GET[ 'service_page' ] ) : '';

    // Create a base URL with the 'service_page' parameter
    $current_page_url = esc_url(add_query_arg('service_page', $url_param, get_permalink()));

    echo '<div class="status-counts">';
    echo '<p class="active-count"><a href="' . esc_url( add_query_arg(array('service_page' => 'active', 'action' => 'active'), get_permalink())) . '">Active: ' . $active_count . '</a></p>';
    echo '<p class="due-for-renewal-count"><a href="' . esc_url(add_query_arg(array('service_page' => 'renewal_due', 'action' => 'renewal_due'), get_permalink())) . '">Due: ' . $due_for_renewal_count . '</a></p>';
    echo '<p class="expired-count"><a href="' . esc_url(add_query_arg(array('service_page' => 'expired', 'action' => 'expired'), get_permalink())) . '">Expired: ' . $expired_count . '</a></p>';
    echo '<p class="grace-period-count"><a href="' . esc_url(add_query_arg(array('service_page' => 'grace_period', 'action' => 'grace_period'), get_permalink())) . '">Grace Period: ' . $grace_period_count . '</a></p>';
    echo '</div>';



    // Service ID is not provided in the URL, display the list of services
    $services = Sw_Service_Database::get_services_by_user( $current_user_id );
    $pending_services = sw_user_unprocessed_service( $current_user_id );

    // Output services as cards
    $output = '<div class="client-services">';

    if (!empty( $services || !empty($pending_services ) ) ) {

        echo $pending_services;

        foreach ($services as $service) {
            $service_name = esc_html( $service->getServiceName() );
            $service_id = esc_html( $service->getServiceId() );

            // Create a link to view service details with the service_id as a URL parameter
            $service_page_id = get_option( 'sw_service_page', 0 );
            $page_url = get_permalink( $service_page_id );
            $view_link = esc_url( add_query_arg( array('service_page' => 'service_details', 'service_id' => $service_id ), $page_url ) );
            // Use sw_service_status to get the service status
            $status = sw_service_status( $service_id );
            $expiry_date   = sw_get_service_expiration_date( $service );



            // Add the status tag to the service name
            $service_name_with_status = $service_name . ' (' . $status . ')';

            $output .= '<div class="main-page-card">';
            $output .= '<h3>' . $service_name_with_status . '</h3>';
            if ( $expiry_date === sw_extract_date_only( current_time( 'mysql' ) ) ){
                $output .= sw_notice( 'Expiring Today' );
            } elseif ( $expiry_date ===  date( 'Y-m-d', strtotime('+1 day') ) ) {
                $output .= sw_notice( 'Expiring Tomorrow' );
            }elseif ( $expiry_date ===  date( 'Y-m-d', strtotime('-1 day') ) ) {
                $output .= sw_notice( 'Expired Yesterday' );
            }
            $output .= '<p>Service ID: ' . $service_id . '</p>';
            $output .= '<a href="' . $view_link . '" class="view-details-button">View Details</a>';
            $output .= '</div>';
        }
    } else {
        $output .= '<div class="main-page-card">';
        $output .= '<p style="color: black;">All your services will appear here.</p>';
        $output .= '</div>';
    }


    //Setting and tools Section

    $output .= '<div class="settings-tools-section">';
    $output .= '<h2>Settings and Tools</h2>';
    $output .= '<div class="minibox-buttons">';

    // Hidden loading element
    $output .= '<div id="swloader">Loading...</div>';
    // Button 1: Billing Details
    $output .= '<div class="minibox">';
    $output .= '<button class="minibox-button" onclick="loadBillingDetails()">Billing Details</button>';
    $output .= '</div>';

    // Button 2: My Details
    $output .= '<div class="minibox">';
    $output .= '<button class="minibox-button" onclick="loadMyDetails()">My Details</button>';
    $output .= '</div>';

    // Button 3: Account Logs
    $output .= '<div class="minibox">';
    $output .= '<button class="minibox-button" onclick="loadAccountLogs()">Account Logs</button>';
    $output .= '</div>';

    // Button 4: Transaction History
    $output .= '<div class="minibox">';
    $output .= '<button class="minibox-button" onclick="loadTransactionHistory()">Transaction History</button>';
    $output .= '</div>';


    $output .= '</div>'; // Close minibox-buttons
    $output .= '<div id="ajax-content-container"></div>'; // Container for AJAX content

    $output .= '</div>'; // Close settings-tools-section

    $output .= '</div>'; // Close the client-services div


    echo $output;
}




/**
 * Get all pending Services for a user
 * @param int       $user_id        The user's ID
 * 
 * @return string HTML markup containing the service name and status
 */
function sw_user_unprocessed_service( $user_id ) {
    // Get orders for the user
    $orders = wc_get_orders( array(
        'customer' => $user_id,
    ) );

    // Initialize output variable
    $output = '';

    foreach ( $orders as $order ) {
        $is_config_order = has_sw_configured_products( $order );
        $order_status    = $order->get_status();

        if ( $is_config_order && $order_status === 'processing' ) {
            $items = $order->get_items();

            foreach ( $items as $item_id => $item ) {
                // Get the service name from order item meta
                $service_name = wc_get_order_item_meta( $item_id, 'Service Name', true );

                // Break the loop once the service name is found
                if ( $service_name ) {
                    break;
                }
            }

            $order_id       = $order->get_id();
            $service_status = 'Pending';

            $service_name_with_status = $service_name . ' (' . $service_status . ')';

            // Concatenate the HTML content for each order
            $output .= '<div class="main-page-card">';
            $output .= '<h3>' . $service_name_with_status . '</h3>';
            $output .= sw_notice( 'We are currently processing this service. It will be active as soon as we are done processing it.' );
            $output .= '</div>';
        }
    }

    return $output;
}

/**
 * Render Services filtered by status
 * 
 * @param $current_user_id  The user associated with the service
 */

function sw_handle_service_by_status( $current_user_id ) {
    // Validate the 'service_page' parameter against a list of allowed values
    $allowed_actions = array( 'service_details', 'another_action', 'active', 'renewal_due', 'expired', 'grace_period' );

    // Check if the 'service_page' parameter is in the URL and is a valid action
    if ( !isset( $_GET['service_page'] ) || !in_array( $_GET['service_page'], $allowed_actions ) ) {
        return 'Invalid request. Service page is missing or invalid.';
    }

    // Get validated 'service_page' value directly
    $service_page = sanitize_text_field( $_GET['service_page'] );

    // Get status label based on the 'service_page'
    switch ( $service_page ) {
        case 'active':
            $status_label = 'Active';
            break;
        case 'renewal_due':
            $status_label = 'Due for Renewal';
            break;
        case 'expired':
            $status_label = 'Expired';
            break;
        case 'grace_period':
            $status_label = 'Grace Period';
            break;
        default:
            return 'Invalid action.';
    }

    // Get all services for the current user
    $services = Sw_Service_Database::get_services_by_user( $current_user_id );
    
    sw_get_navbar( $current_user_id );


    // Display services in a table
    $output = '<h2>' . esc_html( $status_label ) . '</h2>';

    $output .= '<table>';
    $output .= '<thead>';
    $output .= '<tr>';
    $output .= '<th>Service Name</th>';
    $output .= '<th>Service ID</th>';
    $output .= '<th>Billing Cycle</th>';
    $output .= '<th>End Date</th>';
    $output .= '<th>Action</th>'; // Add a common column for action links
    $output .= '</tr>';
    $output .= '</thead>';
    $output .= '<tbody>';

    $found_services = false;

    foreach ($services as $service) {
        // Get the status of each service using sw_service_status
        $status = sw_service_status( $service->getServiceId() );

        // Create a link to view service details with the service_id as a URL parameter
        $service_page_id = get_option('sw_service_page', 0);
        $page_url = get_permalink( $service_page_id );
        $view_link = esc_url( add_query_arg( array( 'service_page' => 'service_details', 'service_id' => $service->getServiceId() ), $page_url ) );

        // Only display services matching the current status
        if ($status === $status_label) {
            $found_services = true;
            $output .= '<tr>';
            $output .= '<td>' . esc_html( $service->getServiceName() ) . '</td>';
            $output .= '<td>' . esc_html( $service->getServiceId() ) . '</td>';
            $output .= '<td>' . esc_html( $service->getBillingCycle() ) . '</td>';
            $output .= '<td>' . esc_html( $service->getEndDate() ) . '</td>';

            // Add a common action link based on the current 'service_page'
            $output .= '<td><a href="' . $view_link . '" class="sw-blue-button">View Details</a></td>';

            $output .= '</tr>';
        }
    }

    $output .= '</tbody>';
    $output .= '</table>';

    if (!$found_services) {
        $output .= '<p>No ' . esc_html( $status_label ) . ' services found.</p>';
    }

    return $output;
}






/**
 * Function Code For Service Mini Card
 */

 function sw_service_mini_card_loader() {
    // Check if the user is logged in
    if ( ! is_user_logged_in() ) {
        return 'Hello! It looks like you\'re not logged in.';
    }

    // Get the current user's ID
    $current_user_id = get_current_user_id();

    // Fetch all services for the current user
    $services = Sw_Service_Database::get_services_by_user( $current_user_id );

    // Get the page ID where client_services shortcode is used
    $service_page_id = get_option( 'sw_service_page', 0 );

    // Get the URL of the page by its ID
    $service_page_url = get_permalink( $service_page_id );

    // Output services as a mini-card
    $output = '<div class="mini-card">';

    // Add the heading "My Services" inside the mini-card
    $output .= '<h2>My Services</h2>';

    if ( empty( $services) ) {
        // Display a message if no services are found
        $output .= '<p>All Services will appear here.</p>';
    } else {
        foreach ( $services as $service ) {
            $service_name = esc_html( $service->getServiceName() );
            $service_id = esc_html( $service->getServiceId() );

            // Create a link to the client_services page with the service_id as a URL parameter
            $service_link = esc_url( add_query_arg( array( 'service_page' => 'service_details', 'service_id' => $service_id ), $service_page_url ) );
            $status = sw_service_status( $service_id );

            // Add each service name, linked row, and status with a horizontal line
            $output .= '<p><a href="' . $service_link . '">' . $service_name . '</a>  ' . $status . '</p>';
            $output .= '<hr>';
        }
    }

    $output .= '</div>';
    // Add the "View All Services" button
    $output .= '<div style="text-align: center">';
    $output .= '<p><a href="' . $service_page_url . '" class="sw-blue-button">View All Services</a></p>';
    $output .= '</div>';

    return $output;
}

/**
 * Render the count for active Service, usefull if you want to
 *  just show active service count for the logged user
 * 
 * @return int $output incremented number of active service(s) or 0 if there is none
 */

function sw_active_service_count_shortcode() {
    // Check if the user is logged in
    if ( is_user_logged_in() ) {
        // Get the current user's ID
        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;

        // Execute the query
        $count = count_active_services( $user_id );

        // Output the count and "Services" text with inline CSS for centering
        $output = "<div style='text-align: center;'>";
        $output .= "<h1 class='centered' style='text-align: center; margin: 0 auto; font-size: 45px;'>$count</h1>";
        $output .= "<p class='centered' style='text-align: center; font-size: 18px;'>Services</p>";
        $output .= "</div>";

        return $output;
    } else {
        return 0;
    }
}



/**
 * Handles the 'service_upgrade' action.
 *
 * @param int $current_user_id Current user ID.
 * @return string Output or result of the upgrade service operation.
 */
function sw_handle_upgrade_service( $current_user_id ) {

    // Validate URL structure
    if ( ! isset( $_GET['service_page'] ) || $_GET['service_page'] !== 'service_upgrade' ) {
        return 'Invalid URL structure for service upgrade.';
    }

    // Retrieve user's services
    $services = Sw_Service_Database::get_services_by_user( $current_user_id );
    // Check if form is submitted
    if ( isset( $_POST['upgrade_service_submit'] ) ) {
        // Nonce verification
        if ( ! isset( $_POST['upgrade_service_nonce'] ) || ! wp_verify_nonce( $_POST['upgrade_service_nonce'], 'upgrade_service_nonce' ) ) {
            return 'Nonce verification failed.';
        }

        // Retrieve selected service and product
        $selected_service_id = sanitize_text_field( $_POST['selected_service'] );
        $selected_product_id = sanitize_text_field( $_POST['selected_product'] );

        // Find the selected service in the user's services
        $service_to_upgrade = null;
        foreach ( $services as $service ) {
            if ( $service->getServiceId() === $selected_service_id ) {
                $service_to_upgrade = $service;
                break;
            }
        }

        // Check if the selected service exists
        if ( ! $service_to_upgrade ) {
            return 'Selected service not found.';
        }

        // Check the status of the service using the sw_service_status function
        $service_status  = sw_service_status( $service_to_upgrade->getServiceId() );

        // Check if the service is 'Due for Renewal'
        if ( $service_status !== 'Active' ) {
            return sw_error_notice( 'Only Active Services can be Upgraded, Contact us if you need further assistance.' );
        }
    

        // Retrieve product details for the selected product
        $selected_product = wc_get_product( $selected_product_id );

        // Check if the selected product exists
        if ( ! $selected_product || ! $selected_product->is_purchasable() ) {
            return 'Selected product not found or not purchasable.';
        }

        // Get the prices for the selected service and product
        $service_price = sw_get_service_price( $service_to_upgrade );
        $product_price = $selected_product->get_price();
        $service_product_name = wc_get_product( $service_to_upgrade->getProductId() )->get_name();

        $fee = floatval( get_sw_service_product( $selected_product_id )['sign_up_fee'] ?? 0 );
        $new_service_price = $product_price + $fee;

        $prorate_status = sw_Is_prorate();


        // Calculate usage metrics using the new function
        $usage_metrics = sw_check_service_usage( $selected_service_id );

        // Determine the order total price based on prorate status using the new function
        $order_total_data = sw_calculate_migration_order_total( $new_service_price, $usage_metrics['unused_amount'] );
        sw_get_navbar($current_user_id);

        // Display detailed upgrade order summary with PHP form button
        $output = '<div class="migration-order-container">'; // Add a container
        // Check if there is Service Renewal Invoice for the service
        $existing_invoice_id = sw_evaluate_service_invoices( $service_to_upgrade->getServiceId() , 'Service Upgrade Invoice', 'unpaid' );
        $output .= '<div class="migrate-order-details">';
        $output .= '<p class="upgrade-section-title">Service Upgrade Order</p>';
        if ( $existing_invoice_id ) {
            $output .= sw_notice( 'This service has an outstanding invoice. If you proceed, you will be redirected to make the payment instead.' );

        }
        

        $output .= '<p><strong>Current service Details</strong></p>';
        $output .= '<p><strong>Current Service:</strong> ' . esc_html( $service_to_upgrade->getServiceName() ) . ' - ' . esc_html( $service_to_upgrade->getServiceId() ) . '</p>';
        $output .= '<p><strong>Product Name:</strong> ' . $service_product_name . '</p>';
        $output .= '<p><strong>Pricing:</strong> ' . wc_price( $service_price ) . '</p>';
        if ($prorate_status === 'Enabled'){
        $output .= '<p><strong>Amount Used:</strong> ' . wc_price( $usage_metrics['used_amount'] ) . '</p>';
        $output .= '<p><strong> Balance:</strong> ' . wc_price( $usage_metrics['unused_amount'] ) . '</p>';
        }
        $output .= '<p><strong>New Upgrade Details</strong></p>';
        $output .= '<p><strong>Product:</strong> ' . esc_html( $selected_product->get_name() ) . '</p>';
        $output .= '<p><strong>Pricing:</strong> ' . wc_price( $product_price ) . '</p>';
        $output .= '<p><strong>Sign-up Fee:</strong> ' . wc_price( get_sw_service_product( $selected_product_id )['sign_up_fee'] ) . '</p>';

        $output .= '<p class="migrate-summary-tittle"><strong>Summary:</strong></p>';
        if ($prorate_status === 'Enabled'){
            $output .= '<p><strong>Refund Amount:</strong> ' . wc_price( $order_total_data['remaining_unused_balance'] ) . '</p>';
        }
        $output .= '<p><strong>New Order Total:</strong> ' . wc_price( $order_total_data['order_total'] ) . '</p>';


        // Hidden form to Post migration data
        $output .= '<form method="post" name="migrate_service">';
        $output .= '<input type="hidden" name="sw_migrate_service" value="smart_woo_upgrade">';
        $output .= '<input type="hidden" name="user_id" value="' . esc_attr( $current_user_id ) . '">';
        $output .= '<input type="hidden" name="service_id" value="' . esc_attr( $service_to_upgrade->getServiceId() ) . '">';
        $output .= '<input type="hidden" name="new_service_product_id" value="' . esc_attr( $selected_product_id ) . '">';
        $output .= '<input type="hidden" name="amount" value="' . esc_attr( $product_price ) . '">';
        $output .= '<input type="hidden" name="fee" value="' . esc_attr( $fee ) . '">';
        $output .= '<input type="hidden" name="order_total" value="' . esc_attr( $order_total_data['order_total'] ) . '">';
        $output .= '<input type="hidden" name="refund_amount" value="' . esc_attr( $order_total_data['remaining_unused_balance'] ) . '">';
        $output .= '<div class="upgrade-button-container">';
        $output .= '<button type="submit" name="proceed_with_upgrade">Upgrade Now</button>';
        $output .= '</div>';
        $output .= '</form>';
        $output .= '</div>'; // Close migrate-order-details
        $output .= '</div>'; // Close migration-order-container

        return $output;
    }
    
    sw_get_navbar( $current_user_id );
    if ( sw_Is_migration() ) {
        

        // Check if user has any services to upgrade
        if ( empty( $services ) ) {
            return 'No services found for upgrade.';
        }

        // Open the form tag with nonce and form button name
        $output = '<form method="post" action="">';
        $output .= wp_nonce_field( 'upgrade_service_nonce', 'upgrade_service_nonce', true, false );

        // Create a select input with the user's services
        $select_options = '<select name="selected_service" required>';
        $select_options .= '<option value="" selected disabled>' . esc_html__( 'Select a Service', 'smart-woo' ) . '</option>';

        foreach ( $services as $service ) {
            $select_options .= '<option value="' . esc_attr( $service->getServiceId() ) . '">' . esc_html( $service->getServiceName() ) . '</option>';
        }

        $select_options .= '</select>';

        // Container for select service
        $output .= '<div class="select-service-container">';
        $output .= '<label for="selected_service">Choose a service to upgrade:</label>';
        $output .= $select_options;
        $output .= '</div>';


        // Container for products to use
        $output .= '<div class="products-to-use-container">';

        // Retrieve selected product categories from options
        $selected_upgrade_category = get_option( 'sw_upgrade_product_cat', '0' );

        // Get products from selected categories
        $products_args = array(
            'post_type' => 'product',
            'posts_per_page' => -1,
            'tax_query' => array(
                array(
                    'taxonomy' => 'product_cat',
                    'field' => 'id',
                    'terms' => $selected_upgrade_category,
                ),
            ),
        );
        $products_query = new WP_Query( $products_args );

        if ( $products_query->have_posts() ) {
            while ( $products_query->have_posts() ) {
                $products_query->the_post();

                // Get product data
                $product_id         = get_the_ID();
                $product_name       = get_the_title();
                $product_price      = get_post_meta( $product_id, '_regular_price', true );
                $product_excerpt    = get_post_field( 'post_excerpt', $product_id );

                // Display each product in a container with a radio button (exclusive selection)
                $output .= '<div class="product-container">';
                $output .= '<label>';
                $output .= '<input type="radio" name="selected_product" value="' . esc_attr( $product_id ) . '" required>';
                $output .= '<strong>' . esc_html( $product_name ) . '</strong><br>';
                $output .= 'Price: ' . wc_price($product_price) . '<br>';
                $output .= 'Description: ' . esc_html($product_excerpt);
                $output .= '</label>';
                $output .= '</div>';
            }
            wp_reset_postdata(); // Reset the post data to the main loop
        } else {
            $output .= 'No products found.';
        }

        $output .= '</div>';

        // Upgrade button
        $output .= '<div class="upgrade-button-container">';
        $output .= '<button type="submit" name="upgrade_service_submit">Upgrade</button>';
        $output .= '</div>';

        // Close the form tag
        $output .= '</form>';

        return $output;
    } else {
        // Display a message or take appropriate action when update is not allowed
        echo 'Service upgrade is not allowed. Contact us if you need any assistance';
    }
}





/**
 * Handles the 'service_downgrade' action.
 *
 * @param int $current_user_id Current user ID.
 * @return string Output or result of the downgrade service operation.
 */
function sw_handle_downgrade_service( $current_user_id ) {
    // Validate URL structure
    if ( ! isset( $_GET['service_page'] ) || $_GET['service_page'] !== 'service_downgrade' ) {
        return 'Invalid URL structure for service downgrade.';
    }

    // Retrieve user's services
    $services = Sw_Service_Database::get_services_by_user( $current_user_id );

    // Check if form is submitted
    if ( isset( $_POST['downgrade_service_submit'] ) ) {
        // Nonce verification
        if ( ! isset( $_POST['downgrade_service_nonce'] ) || ! wp_verify_nonce( $_POST['downgrade_service_nonce'], 'downgrade_service_nonce' ) ) {
            return 'Nonce verification failed.';
        }

        // Retrieve selected service and product
        $selected_service_id = sanitize_text_field( $_POST['selected_service'] );
        $selected_product_id = sanitize_text_field( $_POST['selected_downgrade_product'] );

        // Find the selected service in the user's services
        $service_to_downgrade = null;
        foreach ( $services as $service ) {
            if ( $service->getServiceId() === $selected_service_id ) {
                $service_to_downgrade = $service;
                break;
            }
        }

        // Check if the selected service exists
        if ( ! $service_to_downgrade ) {
            return 'Selected service not found.';
        }

        // Check the status of the service using the sw_service_status function
        $service_status  = sw_service_status( $service_to_downgrade->getServiceId() );

        // Check if the service is 'Due for Renewal'
        if ( $service_status !== 'Active' ) {
            return 'Only Active Services can be downgraded, Contact us if you need further assistance.';
        }
    

        // Retrieve product details for the selected product
        $selected_product = wc_get_product( $selected_product_id );

        // Check if the selected product exists
        if ( ! $selected_product || ! $selected_product->is_purchasable() ) {
            return 'Selected product not found or not purchasable.';
        }

        // Get the prices for the selected service and product
        $service_price        = sw_get_service_price( $service_to_downgrade );
        $service_product_name = wc_get_product( $service_to_downgrade->getProductId() )->get_name();
        $product_price        = $selected_product->get_price();
        $fee                  = floatval(get_sw_service_product( $selected_product_id )['sign_up_fee'] ?? 0 );
        $new_service_price    = $product_price + $fee;

        $prorate_status = sw_Is_prorate();

        // Calculate usage metrics using the new function
        $usage_metrics = sw_check_service_usage( $selected_service_id);

        // Determine the order total price based on prorate status using the new function
        $order_total_data = sw_calculate_migration_order_total( $new_service_price, $usage_metrics['unused_amount'] );
        sw_get_navbar($current_user_id);

        // Display detailed upgrade order summary with PHP form button
        $output = '<div class="migration-order-container">';
        // Check if there is Service Downgrade Invoice for the service
        $existing_invoice_id = sw_evaluate_service_invoices( $service_to_downgrade->getServiceId() , 'Service Downgrade Invoice', 'unpaid' );
        $output .= '<div class="migrate-order-details">';
        $output .= '<p class="upgrade-section-title">Service Downgrade Order</p>';
        if ($existing_invoice_id) {
            $output .= sw_notice( 'This service has an outstanding invoice. If you proceed, you will be redirected to make the payment instead.' );
        }
        

        $output .= '<p><strong>Current Service Details</strong></p>';
        $output .= '<p><strong>Current Service:</strong> ' . esc_html( $service_to_downgrade->getServiceName() ) . ' - ' . esc_html($service_to_downgrade->getServiceId() ) . '</p>';
        $output .= '<p><strong>Product Name:</strong> ' . $service_product_name . '</p>';
        $output .= '<p><strong>Pricing:</strong> ' . wc_price( $service_price ) . '</p>';
        if ( $prorate_status === 'Enabled' ){
        $output .= '<p><strong>Amount Used:</strong> ' . wc_price( $usage_metrics['used_amount'] ) . '</p>';
        $output .= '<p><strong> Balance:</strong> ' . wc_price( $usage_metrics['unused_amount'] ) . '</p>';
        }
        $output .= '<p><strong>Substitute Service Details</strong></p>';
        $output .= '<p><strong>Product:</strong> ' . esc_html( $selected_product->get_name() ) . '</p>';
        $output .= '<p><strong>Pricing:</strong> ' . wc_price($product_price) . '</p>';
        $output .= '<p><strong>Sign-up Fee:</strong> ' . wc_price( get_sw_service_product( $selected_product_id )['sign_up_fee'] ) . '</p>';

        $output .= '<p class="migrate-summary-tittle"><strong>Summary:</strong></p>';
        if ( $prorate_status === 'Enabled' ){
            $output .= '<p><strong>Refund Amount:</strong> ' . wc_price( $order_total_data['remaining_unused_balance'] ) . '</p>';
        }
        $output .= '<p><strong>New Order Total:</strong> ' . wc_price( $order_total_data['order_total'] ) . '</p>';


        // Hidden form to Post migration data
        $output .= '<form method="post" name="migrate_service">';
        $output .= '<input type="hidden" name="sw_migrate_service" value="smart_woo_downgrade">';
        $output .= '<input type="hidden" name="user_id" value="' . esc_attr( $current_user_id ) . '">';
        $output .= '<input type="hidden" name="service_id" value="' . esc_attr( $service_to_downgrade->getServiceId() ) . '">';
        $output .= '<input type="hidden" name="new_service_product_id" value="' . esc_attr( $selected_product_id ) . '">';
        $output .= '<input type="hidden" name="amount" value="' . esc_attr( $product_price ) . '">';
        $output .= '<input type="hidden" name="fee" value="' . esc_attr( $fee ) . '">';
        $output .= '<input type="hidden" name="order_total" value="' . esc_attr( $order_total_data['order_total'] ) . '">';
        $output .= '<input type="hidden" name="refund_amount" value="' . esc_attr( $order_total_data['remaining_unused_balance'] ) . '">';
        $output .= '<div class="upgrade-button-container">';
        $output .= '<button type="submit" name="proceed_with_downgrade">Downgrade Now</button>';
        $output .= '</div>';
        $output .= '</form>';
        $output .= '</div>'; // Close migrate-order-details
        $output .= '</div>'; // Close migration-order-container

        return $output;
    }
 
    sw_get_navbar( $current_user_id );
    if  ( sw_Is_migration() ) {

        // Check if user has any services to downgrade
        if ( empty( $services ) ) {
            return 'No services found for downgrade.';
        }

        // Open the form tag with nonce and form button name
        $output = '<form method="post" action="">';
        $output .= wp_nonce_field( 'downgrade_service_nonce', 'downgrade_service_nonce', true, false );

        // Create a select input with the user's services
        $select_options = '<select name="selected_service" required>';
        $select_options .= '<option value="" selected disabled>' . esc_html__( 'Select a Service', 'smart-woo' ) . '</option>';

        foreach ( $services as $service ) {
            $select_options .= '<option value="' . esc_attr( $service->getServiceId() ) . '">' . esc_html( $service->getServiceName() ) . '</option>';
        }
        $select_options .= '</select>';

        // Container for select service
        $output .= '<div class="select-service-container">';
        $output .= '<p>Choose a service to downgrade:</p>' . $select_options;
        $output .= '</div>';

        // Container for downgrade options
        $output .= '<div class="products-to-use-container">';

        // Retrieve selected downgrade categories from options
        $selected_downgrade_category = get_option( 'sw_downgrade_product_cat', '0' );

        // Get products from selected downgrade categories
        $downgrade_args = array(
            'post_type' => 'product',
            'posts_per_page' => -1,
            'tax_query' => array(
                array(
                    'taxonomy' => 'product_cat',
                    'field' => 'id',
                    'terms' => $selected_downgrade_category,
                ),
            ),
        );
        $downgrade_query = new WP_Query( $downgrade_args );

        if ( $downgrade_query->have_posts() ) {
            while ( $downgrade_query->have_posts() ) {
                $downgrade_query->the_post();

                // Get product data
                $product_id = get_the_ID();
                $product_name = get_the_title();
                $product_price = get_post_meta( $product_id, '_price', true );
                $product_excerpt = get_the_excerpt();

                // Display each product in a container with a radio button (exclusive selection)
                $output .= '<div class="product-container">';
                $output .= '<label>';
                $output .= '<input type="radio" name="selected_downgrade_product" value="' . esc_attr( $product_id ) . '" required>';
                $output .= '<strong>' . esc_html( $product_name ) . '</strong><br>';
                $output .= 'Price: ' . esc_html( $product_price ) . '<br>';
                $output .= 'Description: ' . esc_html( $product_excerpt );
                $output .= '</label>';
                $output .= '</div>';
            }
            wp_reset_postdata(); // Reset the post data to the main loop
        } else {
            $output .= 'No products found for downgrade.';
        }

        $output .= '</div>';

        // Downgrade button
        $output .= '<div class="downgrade-button-container">';
        $output .= '<button type="submit" class="sw-blue-button" name="downgrade_service_submit">Downgrade</button>';
        $output .= '</div>';

        // Close the form tag
        $output .= '</form>';

        return $output;
    } else {
        // Display a message or take appropriate action when update is not allowed
        echo 'Service downgrade is not allowed. Contact us if you need any assistance';
    }
}

/**
 * Handle new service product pruchase
 */
function sw_handle_buy_new_service() {
    
    // Get Smart Woo Products
    $sw_service_products = Sw_Product::get_sw_service_products();
    sw_get_navbar( get_current_user_id() );

    $output = '';

    if ( ! empty( $sw_service_products ) ) {
        
        foreach ( $sw_service_products as $product ) {
            $product_id      = $product->get_id();
            $product_name    = $product->get_name();
            $product_price   = $product->get_price();
            $sign_up_fee     = $product->get_sign_up_fee();
            $billing_cycle   = $product->get_billing_cycle();
            $product_excerpt = $product->get_short_description();
        
            $output .= '<div class="sw-product-container">';
            $output .= '<h3>' . esc_html( $product_name ) . '</h3>';
            $output .= '<p>Price: ' . wc_price( $product_price ) . '</p>';
            $output .= '<p>Sign-Up Fee: ' . $sign_up_fee . '</p>';
            $output .= '<p><strong>' . esc_html( $billing_cycle ) . '</strong> Billing Cycle</p>';
            $output .= '<p>' . esc_html( $product_excerpt ) . '</p>';
            $output .= '<a href="' . home_url( '/configure/' . $product_id ) . '" class="sw-blue-button" >' . esc_html__( 'Configure Product', 'smart-woo' ) . '</a>';
            $output .= '</div>';
        }
        
        return $output;
    } else {
        $shop_page_url = get_permalink( wc_get_page_id( 'shop' ) );
        $output .= '<div class="main-page-card">';
        $output .= '<p>We do not have service products for purchase yet!</p>';
        $output .= '<a href="' . $shop_page_url .'" class="sw-blue-button">' . esc_html__( 'Shop Page','smart-woo' ) .'</a>';
        $output .= '<a href="' . get_permalink() .'" class="sw-blue-button">' . esc_html__( 'Dashboard','smart-woo' ) .'</a>';

        $output .= '</div>';
        return $output;
       
       
    }
}




