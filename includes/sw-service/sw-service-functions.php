<?php
/**
 * File name    :   sw-service-functions.php
 * @author      :   Callistus
 * Description  :   Helper function for service datas
 */

/**
 * Generate a new Sw_Service object and save it to the database.
 *
 * @param int         $user_id            User ID associated with the service.
 * @param int         $product_id         Product ID for the service.
 * @param string|null $service_name       Name of the service (optional).
 * @param string|null $service_url        URL associated with the service (optional).
 * @param string|null $service_type       Type or category of the service (optional).
 * @param string|null $invoice_id         Invoice ID associated with the service (optional).
 * @param string|null $start_date         Start date of the service (optional).
 * @param string|null $end_date           End date of the service (optional).
 * @param string|null $next_payment_date  Date of the next payment for the service (optional).
 * @param string|null $billing_cycle      Billing cycle for the service (optional).
 * @param string|null $status             Status of the service (optional).
 *
 * @return Sw_Service|false The generated Sw_Service object or false on failure.
 * 
 */
function sw_generate_service(
    int $user_id,
    int $product_id,
    ?string $service_name = null,
    ?string $service_url = null,
    ?string $service_type = null,
    ?string $invoice_id = null,
    ?string $start_date = null,
    ?string $end_date = null,
    ?string $next_payment_date = null,
    ?string $billing_cycle = null,
    ?string $status = null
) {
    // Generate service ID using the provided service_name or any other logic
    $service_id = sw_generate_service_id( $service_name );

    // Create a new Sw_Service object
    $new_service = new Sw_Service(
        $user_id,
        $product_id,
        $service_id,
        $service_name,
        $service_url,
        $service_type,
        $invoice_id,
        $start_date,
        $end_date,
        $next_payment_date,
        $billing_cycle,
        $status
    );

    // Save the new service to the database
    $saved_service_id = Sw_Service_Database::sw_create_service( $new_service );

    // Check if the service was successfully saved
    if ($saved_service_id !== false) {
        $new_service = Sw_Service_Database::get_service_by_id( $saved_service_id );
        // Trigger  action after service is created
        do_action( 'sw_new_service_created', $new_service );

        // Retrieve the newly created service from the database using the saved ID
        return $new_service;
    }

    // Return false if the service creation or database insertion failed
    return false;
}

/**
 * The button to access the client URL without password
 * @param object $service       The service.
 * @return string HTML markup button with url keypass
 */

function sw_client_service_url_button( $service ){
    $user_id = $service->getUserId();
    $user_info = get_userdata($user_id);
    $service_status = sw_service_status( $service->getServiceId() );
    if ($service_status === 'Active' && $service->getServiceType() === 'Web Service'){

        if ($user_info) {
            $user_email = $user_info->user_email;
            // Construct the service URL with specified parameters
            $access_client_service_url = esc_url($service->getServiceUrl()) . '?auth=1&email=' . urlencode($user_email) . '&userisfromcallismartparentwebsite=1&serviceid=' . $service->getServiceId() . '&requestingaccess=1';

            return '<a href="' . esc_url($access_client_service_url) . '" class="sw-red-button" target="_blank">Access Client Service</a>';
        }
    }
}


/**
 * Get service information based on various parameters.
 *
 * @param int|null    $user_id       The user ID (optional).
 * @param string|null $service_id    The service ID (optional).
 * @param int|null    $invoice_id    The invoice ID (optional).
 * @param string|null $service_name  The service name (optional).
 * @param string|null $billing_cycle The billing cycle (optional).
 *
 * @return array|object|false An array of services if no specific parameters are provided,
 *                            or service information as an object, or false if not found.
 */
function sw_get_service( $user_id = null, $service_id = null, $invoice_id = null, $service_name = null, $billing_cycle = null, $service_type = null ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'sw_service';

    // Prepare the base query
    $query = "SELECT * FROM $table_name WHERE 1";

    // Add conditions based on provided parameters
    if ($user_id !== null) {
        $query .= $wpdb->prepare(" AND user_id = %d", $user_id);
    }
    if ($service_id !== null) {
        $query .= $wpdb->prepare(" AND service_id = %s", $service_id);
    }
    if ($invoice_id !== null) {
        $query .= $wpdb->prepare(" AND invoice_id = %d", $invoice_id);
    }    
    if ($service_name !== null) {
        $query .= $wpdb->prepare(" AND service_name = %s", $service_name);
    }
    if ($billing_cycle !== null) {
        $query .= $wpdb->prepare(" AND billing_cycle = %s", $billing_cycle);
    }

    // Execute the query
    if ($service_id !== null) {
        return $wpdb->get_row($query);
    } else {
        return $wpdb->get_results($query);
    }
}


/**
 * Insert data into the 'sw_log_old_renewed_services_info' table.
 *
 * @param int    $user_id               The ID of the renewed user.
 * @param string $service_name          The name of the renewed service.
 * @param string $service_url           The URL of the renewed service.
 * @param string $service_id            The ID of the renewed service.
 * @param int    $order_id              The ID of the renewed order.
 * @param string $start_date            The start date of the renewed service.
 * @param string $end_date              The end date of the renewed service.
 * @param string $next_payment_date     The next payment date of the renewed service.
 * @param string $billing_cycle         The billing cycle of the renewed service.
 */

function sw_log_old_renewed_services_info(
    $user_id,
    $service_name,
    $service_url,
    $service_id,
    $order_id,
    $start_date,
    $end_date,
    $next_payment_date,
    $billing_cycle
) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'sw_invoice_auto_renew';

    // Insert data into the table
    $wpdb->insert(
        $table_name,
        array(
            'renewed_user_id' => $user_id,
            'renewed_service_name' => $service_name,
            'renewed_service_url' => $service_url,
            'renewed_service_id' => $service_id,
            'renewed_order_id' => $order_id,
            'renewed_start_date' => $start_date,
            'renewed_end_date' => $end_date,
            'renewed_next_payment_date' => $next_payment_date,
            'renewed_billing_cycle' => $billing_cycle,
        ),
        array('%d', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s')
    );
}


/**
 * Update data in the 'sw_update_old_renewed_services_info' table.
 *
 * @param int    $user_id               The ID of the renewed user.
 * @param string $service_name          The name of the renewed service.
 * @param string $service_url           The URL of the renewed service.
 * @param string $service_id            The ID of the renewed service.
 * @param int    $order_id              The ID of the renewed order.
 * @param string $start_date            The start date of the renewed service.
 * @param string $end_date              The end date of the renewed service.
 * @param string $next_payment_date     The next payment date of the renewed service.
 * @param string $billing_cycle         The billing cycle of the renewed service.
 */

function sw_update_old_renewed_services_info(
    $user_id,
    $service_name,
    $service_url,
    $service_id,
    $order_id,
    $start_date,
    $end_date,
    $next_payment_date,
    $billing_cycle
) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'sw_invoice_auto_renew';

    // Update data in the table
    $wpdb->update(
        $table_name,
        array(
            'renewed_user_id' => $user_id,
            'renewed_service_name' => $service_name,
            'renewed_service_url' => $service_url,
            'renewed_service_id' => $service_id,
            'renewed_order_id' => $order_id,
            'renewed_start_date' => $start_date,
            'renewed_end_date' => $end_date,
            'renewed_next_payment_date' => $next_payment_date,
            'renewed_billing_cycle' => $billing_cycle,
        ),
        array('%d', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s')
    );
}


/**
 * Move (copy) service information from 'sw_service' to 'sw_invoice_auto_renew'.
 *
 * @param int $service_id The ID of the service to move.
 *
 * @global wpdb $wpdb WordPress database access abstraction object.
 *
 * @return bool Whether the move was successful.
 */
function sw_move_service_to_log($service_id) {
    

            // Fetch service information from sw_service table
        $service_data = sw_get_service(null, $service_id);

        // Check if the service was found
        if ($service_data) {
            // Extract data into variables
            $user_id = $service_data->user_id;
            $service_name = $service_data->service_name;
            $service_url = $service_data->service_url;
            $service_id = $service_data->service_id;
            $invoice_id = $service_data->invoice_id;
            $start_date = $service_data->start_date;
            $end_date = $service_data->end_date;
            $next_payment_date = $service_data->next_payment_date;
            $billing_cycle = $service_data->billing_cycle;

            // Call function to log the old renewed service info
            sw_log_old_renewed_services_info(
                $user_id,
                $service_name,
                $service_url,
                $service_id,
                $invoice_id,
                $start_date,
                $end_date,
                $next_payment_date,
                $billing_cycle
            );

        } else {
            // Handle the case where the service is not found
            error_log("Service not found.");
        }
}



/**
 * Get the count of renewed services.
 * @param int $user_id The user ID to filter by (optional).
 * @param string $service_id The service ID to filter by (optional).
 * @return int The count of renewed services.
 */
function get_renewed_services_count($user_id = null, $service_id = null) {
    global $wpdb;
    $auto_renew_table_name = $wpdb->prefix . 'sw_invoice_auto_renew';

    $query = "SELECT COUNT(*) FROM $auto_renew_table_name";

    if ($user_id !== null) {
        $query .= " WHERE renewed_user_id = " . intval($user_id);
    }

    if ($service_id !== null) {
        $query .= " AND renewed_service_id = '" . esc_sql($service_id) . "'";
    }

    return $wpdb->get_var($query);
}

/**
 * Get user's full name and service IDs for renewed services.
 * @param int $user_id The user ID to filter by (optional).
 * @param string $service_id The service ID to filter by (optional).
 * @return array An array of user's full name and service IDs for renewed services.
 */
function get_renewed_services_info($user_id = null, $service_id = null) {
    global $wpdb;
    $auto_renew_table_name = $wpdb->prefix . 'sw_invoice_auto_renew';

    $query = "SELECT renewed_user_id, GROUP_CONCAT(renewed_service_id) AS service_ids FROM $auto_renew_table_name";

    if ($user_id !== null) {
        $query .= " WHERE renewed_user_id = " . intval($user_id);
    }

    if ($service_id !== null) {
        $query .= " AND renewed_service_id = '" . esc_sql($service_id) . "'";
    }

    $query .= " GROUP BY renewed_user_id";

    return $wpdb->get_results($query, ARRAY_A);
}



/**
 * Check if a service subscription is active.
 *
 * @param object $service The service object.
 *
 * @return bool True if the subscription is active, false otherwise.
 */
function sw_is_service_active( $service ) {
    // Extract only the date portion from the end date, next payment date, and current date
    $end_date          = sw_extract_date_only( $service->getEndDate() );
    $next_payment_date = sw_extract_date_only( $service->getNextPaymentDate() );
    $current_date      = sw_extract_date_only( current_time( 'mysql' ) );

    // Compare date strings to check if the subscription is active
    if ( $next_payment_date > $current_date && $end_date > $current_date ) {
        return true;
    } else {
        return false;
    }
}



/**
 * Check if a service subscription is due.
 * @param object $service  The service object
 * 
 * @return bool True if the subscription is due, false otherwise
 */
function sw_is_service_due( $service ) {
    $end_date          = sw_extract_date_only( $service->getEndDate() );
    $next_payment_date = sw_extract_date_only( $service->getNextPaymentDate() );
    $current_date      = sw_extract_date_only( current_time( 'mysql' ) );
    if( $next_payment_date <= $current_date && $end_date >= $current_date ) {
        return true;
    
    } else {
        return false;
    }
}

/**
 * Check if a service is on grace period.
 *
 * @param object $service The service object.
 *
 * @return bool true if the subscription is on grace period, false otherwise.
 */
function sw_is_service_on_grace( $service ){

    $end_date = sw_extract_date_only( $service->getEndDate() );
    $current_date = sw_extract_date_only( current_time( 'mysql' ) );

    // Check if the service has passed its end date
    if ( $current_date > $end_date ) {
        $product_id = $service->getProductId();

        // Get the grace period end date using the end date as the reference
        $grace_period_date = get_grace_period_end_date( $product_id, $end_date );

        // Check if there is a valid grace period and if the current date is within the grace period
        if (!empty( $grace_period_date ) && $current_date <= sw_extract_date_only( $grace_period_date ) ) {
            return true;
        }
    }

    return false;
}


/**
 * Check if a service has expired.
 *
 * @param object $service The service object.
 *
 * @return bool true if the subscription has expired, false otherwise.
 */
function sw_has_service_expired( $service ){

    $product_id = $service->getProductId();
    $end_date = sw_extract_date_only( $service->getEndDate() );
    $current_date = sw_extract_date_only( current_time( 'mysql') );

    // Get the grace period end date using the end date as the reference
    $grace_period_date = get_grace_period_end_date( $product_id, $end_date );

    // Determine the expiration date (either grace period end date or end date)
    $expiration_date = sw_extract_date_only($grace_period_date) ?? $end_date;

    // Check if the current date has passed the expiration date
    if ($current_date > $expiration_date) {
        return true; // Service has expired
    }

    return false; // Service has not expired
}


/**
 * Get the status of a service.
 *
 * @param string $service_id The service ID.
 *
 * @return string The status.
 */
function sw_service_status( $service_id ){

    // Get the service object
    $service = Sw_Service_Database::get_service_by_id( $service_id );

    // Get the status text in the DB which overrides the calculated status
    $overriding_status = $service->getStatus();

    // Check calculated statuses
    $active         = sw_is_service_active( $service );
    $due            = sw_is_service_due( $service );
    $grace_period   = sw_is_service_on_grace( $service );
    $expired        = sw_has_service_expired( $service );

    // Check overriding status first
    if ( !empty( $overriding_status ) ) {
        return $overriding_status;
    }

    // Check calculated statuses in order of priority
    if ( $active ) {
        return 'Active';
    } elseif ( $due ) {
        return 'Due for Renewal';
    } elseif ( $grace_period ) {
        return 'Grace Period';
    } elseif ( $expired ) {
        return 'Expired';
    }

    // Default status if none of the conditions match
    return 'Unknown';
}

/**
 * Normalize the status of a service before expiration date, this is
 * used to handle 'Cancelled', 'Active NR' and other custom service, it ensures
 * the service is autocalculated at the end of each billing period.
 */

function sw_regulate_service_status() {
    $services = Sw_Service_Database::get_all_services();
 
    // Loop
    foreach ($services as $service) {
        $expiry_date    = sw_get_service_expiration_date( $service );
        if (  $expiry_date ===  date( 'Y-m-d', strtotime('+1 day') ) ) {

        $field = array( 
            'status' => null,
        );
        Sw_Service_Database::update_service_fields( $service->getServiceID(), $field );

        }
    }
}
// Hook to run daily
add_action( 'smart_woo_daily_task', 'sw_regulate_service_status' );

/**
 * Generate a unique service ID based on the provided service name.
 *
 * @param string $service_name The name of the service.
 *
 * @return string The generated service ID.
 */
function sw_generate_service_id( $service_name ) {
    // Service ID Prefix 
    $service_id_prefix = get_option( 'sw_service_id_prefix', 'SID' );

    // Extract the first alphabet of each word in the service name
    $first_alphabets = array_map( function ( $word ) {
        return strtolower( substr( $word, 0, 1 ) );
    }, explode( ' ', $service_name ) );

    // Combine the first alphabets with a hyphen and a unique ID
    $unique_id = uniqid();

    // Final service ID structure: SID-each_first_alphabet_in_service_nameunique_id
    $generated_service_id = $service_id_prefix . '-' . implode('', $first_alphabets) . $unique_id;

    return $generated_service_id;
}


// AJAX action to generate service ID
add_action('wp_ajax_generate_service_id', 'generate_service_id_callback');

function generate_service_id_callback() {
    // Get the service name from AJAX request
    $service_name = sanitize_text_field($_POST['service_name']);

    // Call your existing function to generate service ID
    $generated_service_id = sw_generate_service_id($service_name);

    // Return the generated service ID
    echo esc_html($generated_service_id);
   // avoid extra output
    wp_die();
}


/**
 * Get the expiration date for a service based on its end date and grace period.
 *
 * @param object $service The service object.
 * @return string The calculated expiration date.
 */
function sw_get_service_expiration_date( $service ) {
    // Get necessary service information
    $end_date     = sw_extract_date_only( $service->getEndDate() );
    $product_id   = $service->getProductId();

    // Get the grace period end date using the end date as the reference
    $grace_period_date = sw_extract_date_only( get_grace_period_end_date( $product_id, $end_date ) );

    // Determine the expiration date (either grace period end date or end date)
    $expiration_date = $grace_period_date ?? $end_date;

    // Format the expiration date as 'Y-m-d'
    return $expiration_date;
}


/**
 * Service Expiration Action Trigger
 *
 * This function is hooked into the 'sw_service_suspension_event' action to check for services
 * that have expired today (end date is today) and trigger the 'sw_service_expired' action.
 *
 * @return void
 */
add_action( 'smart_woo_daily_task', 'sw_check_services_expired_today' );

/**
 * Check services for expiration today and trigger 'sw_service_expired' action if found.
 *
 * @return void
 */
function sw_check_services_expired_today() {
    // Get all services
    $services = Sw_Service_Database::get_all_services();

    // Loop through each service
    foreach ( $services as $service ) {
        // Get necessary service information
        $current_date     = sw_extract_date_only( current_time( 'mysql' ) );

        // Determine the expiration date (either grace period end date or end date)
        $expiration_date   = sw_get_service_expiration_date( $service );

        // Check if the current date has passed the expiration date
        if ( $current_date === $expiration_date ) {
            // Trigger the 'sw_service_expired' action with the current service
            do_action( 'sw_service_expired', $service );
        }
    }
}

/**
 * Check and Calculate Service Usage, Unused Amount, and Service Cost based on Pro-Rata feature
 *
 * @param int    $user_id      The ID of the service owner
 * @param string $service_id   The ID of the service
 *
 * @return array|false Array containing used amount, unused amount, service cost, and additional metrics, or false on failure
 */
function sw_check_service_usage( $service_id ) {
    // Get service details 
    $service_details = Sw_Service_Database::get_service_by_id( $service_id );

    if (!$service_details) {
        // Service not found
        return false;
    }

    // Extract relevant service details
    $start_date = strtotime( esc_html( $service_details->getStartDate() ) );
    $end_date = strtotime( esc_html( $service_details->getEndDate() ) );
    $current_date = current_time( 'timestamp', 0 );

    $product_id = $service_details->getProductId();

    // Get product details from WooCommerce
    $product = wc_get_product( $product_id );

    if (!$product) {
        // Product not found
        return false;
    }

    // Get the cost of the first product
    $service_cost = (float) $product->get_price(); // Treat as float

    // Ensure non-negative values for service cost
    $service_cost = max( 0, $service_cost );

    // Calculate the total days and days passed
    $total_days = max( 1, ( $end_date - $start_date ) / 86400 ); // 86400 seconds in a day
    $days_passed = max( 0, min( $total_days, (int) ( ( $current_date - $start_date ) / 86400 ) ) );

    // Calculate the unused amount based on the daily rate
    $daily_rate = $total_days > 0 ? $service_cost / $total_days : 0;
    $unused_amount = $service_cost - ( $daily_rate * $days_passed );

    // Calculate used amount
    $used_amount = $service_cost - $unused_amount;

    // Additional Metrics
    $total_service_cost = $service_cost;
    $average_daily_cost = $total_days > 0 ? $total_service_cost / $total_days : 0;

    // Cost Per Product
    $product_costs = array();
    $product_name = $product->get_name();
    $product_price = (float) $product->get_price(); // Treat as float

    $product_costs[$product_name] = max( 0, $product_price ); // Ensure non-negative value

    // Percentage Usage
    $percentage_used = ( $total_service_cost > 0 ) ? ( $used_amount / $total_service_cost ) * 100 : 0;
    $percentage_unused = ( $total_service_cost > 0 ) ? ( $unused_amount / $total_service_cost ) * 100 : 0;

    // Days Remaining
    $days_remaining_seconds = max( 0, $total_days - $days_passed ) * 86400;
    $days_remaining = floor( $days_remaining_seconds / 86400 );
    $hours_remaining = floor( ( $days_remaining_seconds % 86400 ) / 3600 );
    $minutes_remaining = floor( ( $days_remaining_seconds % 3600 ) / 60 );
    $seconds_remaining = $days_remaining_seconds % 60;

    // Average Hourly Usage
    $average_hourly_usage = ( $total_days > 0 ) ? ( $used_amount / $total_days ) / 24 : 0;

    // Convert to readable format
    $readable_remaining = sprintf( '%d days %02d:%02d:%02d', $days_remaining, $hours_remaining, $minutes_remaining, $seconds_remaining );

    return array(
        'used_amount' => max( 0, $used_amount ), // Ensure non-negative value
        'unused_amount' => max( 0, $unused_amount ), // Ensure non-negative value
        'service_cost' => max( 0, $total_service_cost ), // Ensure non-negative value
        'average_daily_cost' => max( 0, $average_daily_cost ), // Ensure non-negative value
        'product_costs' => $product_costs,
        'percentage_used' => max( 0, $percentage_used ), // Ensure non-negative value
        'percentage_unused' => max( 0, $percentage_unused ), // Ensure non-negative value
        'days_remaining' => $readable_remaining,
        'total_days' => max( 1, $total_days ), // Ensure non-zero value
        'total_used_days' => max( 0, $days_passed ),
        'remaining_days' => max( 0, $total_days - $days_passed ),
        'current_date_time' => date( 'Y-m-d g:i a', $current_date ),
        'average_hourly_usage' => max( 0, $average_hourly_usage ), // Ensure non-negative value
    );
}







/**
 * Get the price of a service
 *
 * @param object $service The service.
 *
 * @return float|false The price of the service or false if not found.
 */
function sw_get_service_price( $service ) {

    // Check if the service is found
    if ($service !== false) {
        // Retrieve the Product ID from the service
        $product_id = $service->getProductId();
        $product = wc_get_product( $product_id );

        // Check if products were retrieved successfully
        if ($product !== false && !empty( $product ) ) {
            // Assuming the price is stored in the first product, you can modify this based on your data structure
            $product_price = $product->get_price();

            // Check if the product price is available
            if ($product_price !== false) {
                return floatval( $product_price );
            }
        }
    }

    // Return false if the service product price is not found
    return false;
}



/**
 * Get grace period information based on a Product ID and it's grace period settings.
 *
 * @param int $product_id The ID of the Product.
 * @param string $reference_date Reference date for calculating the grace period end date.
 * @return int|null Numeric representation of the grace period in hours, or null if not applicable.
 */
function get_grace_period_end_date( $product_id, $reference_date ) {
    // Default grace period values
    $grace_period = array('number' => 0, 'unit' => '');

    // Get grace period from product metadata
    $grace_period['number'] = get_post_meta( $product_id, 'grace_period_number', true );
    $grace_period['unit'] = get_post_meta( $product_id, 'grace_period_unit', true );

    // Calculate the end date of the grace period
    if (!empty( $grace_period['number'] ) && !empty( $grace_period['unit'] ) ) {
        switch ( $grace_period['unit'] ) {
            case 'days':
                $end_date = date( 'Y-m-d', strtotime( "+" . $grace_period['number'] . " days", strtotime( $reference_date ) ) );
                break;
            case 'weeks':
                $end_date = date( 'Y-m-d', strtotime( "+" . ( $grace_period['number'] * 7 ) . " days", strtotime( $reference_date ) ) );
                break;
            case 'months':
                $end_date = date('Y-m-d', strtotime( "+" . $grace_period['number'] . " months", strtotime( $reference_date ) ) );
                break;
            case 'years':
                $end_date = date( 'Y-m-d', strtotime( "+" . $grace_period['number'] . " years", strtotime( $reference_date ) ) );
                break;
            default:
                $end_date = null;
        }

        return $end_date;
    }

    // Return null if no grace period information is found or applicable
    return null;
}




function sw_delete_service_button($service_id) {
    // Output the delete button with data-invoice-id attribute
    return '<button class="delete-service-button" data-service-id="' . esc_attr( $service_id ) . '">Delete Service</button>';
}

// Add Ajax actions
add_action('wp_ajax_delete_service', 'sw_delete_service_callback');
add_action('wp_ajax_nopriv_delete_service', 'sw_delete_service_callback');

function sw_delete_service_callback() {
    // Verify the nonce for security
    check_ajax_referer('smart_woo_nonce', 'security');

    // Get the invoice ID from the Ajax request
    $service_id = isset($_POST['service_id']) ? sanitize_text_field($_POST['service_id']) : '';

    // Validate the service ID
    if (empty($service_id)) {
        wp_send_json_error('Invalid Service ID.');
    }

    // Attempt to delete the invoice
    $delete_result = Sw_Service_Database::delete_service($service_id);

    // Check the result and send appropriate response
    if (is_string($delete_result)) {
        // An error occurred
        wp_send_json_error($delete_result);
    } else {
        // Success
        wp_send_json_success($delete_result);
        exit();
    }
}
